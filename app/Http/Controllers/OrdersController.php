<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\ProductPrepaid;
use App\Models\TransactionModel;
use App\Models\User;
use App\Models\UserBalance;
use App\Traits\CodeGenerate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);

        $data = Orders::with(['TransactionModel' => function ($query) use ($request) {
            if ($request->transaction_id) {
                $query->where('id', $request->transaction_id); // Filter berdasarkan transaction_id
            }
        }])
            ->with(['user' => function ($query) use ($request) {
                if ($request->user_id) {
                    $query->where('id', $request->user_id);
                }
            }])
            ->with(['product' => function ($query) use ($request) {
                if ($request->product_id) {
                    $query->where('id', $request->product_id);
                }
            }])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->when($request->order_status, function ($query, $order_status) {
                $query->where('order_status', $order_status);
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }



    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'order_status' => 'required|string|in:pending,processing,success,cancelled'
            ]);

            DB::beginTransaction();
            try {
                // Update status di tabel orders
                $order = Orders::findOrFail($id);
                $order->order_status = $validated['order_status'];
                $order->save();

                // Cari transaksi terkait dan update statusnya
                $transaction = TransactionModel::where(function ($query) use ($order) {
                    $query->where('transaction_code', $order->transaction_code)
                        ->orWhere('id', $order->transaction_id);
                })->first();

                if ($transaction) {
                    // Update status di tabel transaction
                    $transaction->order_status = $validated['order_status'];

                    // Update transaction_status sesuai order_status
                    switch ($validated['order_status']) {
                        case 'success':
                            $transaction->transaction_status = 'success';
                            break;
                        case 'cancelled':
                            $transaction->transaction_status = 'failed';
                            break;
                        case 'processing':
                            $transaction->transaction_status = 'pending';
                            break;
                        default:
                            $transaction->transaction_status = 'pending';
                    }

                    $transaction->save();
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Status order berhasil diperbarui',
                    'data' => [
                        'order' => $order,
                        'transaction' => $transaction
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    public function get($id)
    {
        $data = Orders::find($id);

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function update($id, Request $request)
    {
        $data = TransactionModel::find($id);

        $validatedData = $request->validate([
            'order_status' => 'required|string|in:Pending,Processing,success,Cancelled',
        ]);

        if ($data) {
            $data->order_status = $validatedData['order_status'];
            $data->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $data = Orders::find($id);
        $data->delete();
        return response()->json($data);
    }

    use CodeGenerate;
    public function submitProduct(Request $request)
    {
        try {
            Log::info('Submit Product Request:', $request->all());

            $product = ProductPrepaid::findOrFail($request->product_id);

            $userBalance = UserBalance::where('user_id', $request->user_id)->first();

            if (!$userBalance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Saldo Tidak Ditemukan, Silahkan Untuk Melakukan Topup !'
                ], 404);
            }

            $totalPrice = $product->product_price * $request->quantity;

            if ($userBalance->balance < $totalPrice) {
                $kurangSaldo = $totalPrice - $userBalance->balance;

                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo tidak mencukupi',
                    'details' => [
                        'saldo_sekarang' => number_format($userBalance->balance),
                        'total_pembelian' => number_format($totalPrice),
                        'kekurangan_saldo' => number_format($kurangSaldo),
                    ],
                    'suggestion' => 'Silahkan lakukan top up saldo sebesar Rp. ' . number_format($kurangSaldo)
                ], 400);
            }

            DB::beginTransaction();
            try {
                $transactionCode = $this->getCode();

                $transaction = TransactionModel::create([
                    'transaction_code' => $transactionCode,
                    'transaction_date' => now()->format('Y-m-d'),
                    'transaction_time' => now()->format('H:i:s'),
                    'transaction_number' => $request->customer_no,
                    'transaction_message' => "Pembelian {$request->product_name}",
                    'transaction_status' => 'success',
                    'order_status' => 'pending',
                    'transaction_product' => $request->product_name,
                    'transaction_total' => $totalPrice,
                    'transaction_user_id' => $request->user_id,
                ]);

                $order = Orders::create([
                    'product_id' => $request->product_id,
                    'transaction_id' => $transaction->id,
                    'quantity' => $request->quantity,
                    'customer_no' => $request->customer_no,
                    'user_id' => $request->user_id,
                ]);

                $userBalance->balance -= $totalPrice;
                $userBalance->save();

                DB::commit();

                Log::info('Created Order:', $order->toArray());

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order created successfully',
                    'data' => $order,
                    'transaction' => $transaction,
                    'balance_info' => [
                        'saldo_awal' => number_format($userBalance->balance + $totalPrice),
                        'total_pembelian' => number_format($totalPrice),
                        'sisa_saldo' => number_format($userBalance->balance)
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ModelNotFoundException $e) {
            $message = str_contains($e->getMessage(), 'Product')
                ? 'Product not found'
                : 'User not found';

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        } catch (\Exception $e) {
            Log::error('Submit Product Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
