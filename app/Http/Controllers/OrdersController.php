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
        $data = Orders::when($request->search, function (Builder $query, string $search) {
            $query->where('customer_no', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
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
        $data = Orders::find($id);
        $data->update($request->all());
        return response()->json($data);
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
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:product_prepaid,id',
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'quantity' => 'required|integer|min:1',
                'customer_no' => 'required|string',
                'customer_name' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            Log::info('Submit Product Request:', $request->all());

            $product = ProductPrepaid::findOrFail($request->product_id);

            $userBalance = UserBalance::where('user_id', $request->user_id)->first();

            if (!$userBalance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Saldo Tidak Ditemukan, Silahkan Untuk Melakukan Topup !'
                ], 404);
            }

            // Menghitung total harga
            $totalPrice = $product->product_price * $request->quantity;

            // Memeriksa apakah balance mencukupi
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
            // Mulai transaction database
            DB::beginTransaction();
            try {
                // Generate transaction code using trait
                $transactionCode = $this->getCode();

                // Create transaction record
                $transaction = TransactionModel::create([
                    'transaction_code' => $transactionCode,
                    'transaction_date' => now()->format('Y-m-d'),
                    'transaction_time' => now()->format('H:i:s'),
                    'transaction_number' => $request->customer_no,
                    'transaction_message' => "Pembelian {$request->product_name}",
                    'transaction_status' => 'success',
                    'transaction_product' => $request->product_name,
                    'transaction_total' => $totalPrice,
                    'transaction_user_id' => $request->user_id,
                    'payment_status' => 'success',
                    'payment_date' => now()
                ]);


                // Membuat order
                $order = Orders::create($validated);

                // Mengurangi balance user
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
