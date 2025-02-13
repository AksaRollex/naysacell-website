<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\ProductPrepaid;
use App\Models\TransactionModel;
use App\Models\UserBalance;
use App\Traits\CodeGenerate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $data = Orders::with(['user', 'product', 'transactionModel'])
            ->findOrFail($id);

        return response()->json([
            'data' => $data
        ], 200);
    }


    public function update($id, Request $request)
    {
        $data = TransactionModel::find($id);

        $validatedData = $request->validate([
            'order_status' => 'required',
        ]);

        if ($data) {
            $data->order_status = strtolower($validatedData['order_status']);
            $data->save();
            $data->load('user');
            $this->sendStatusUpdateEmail($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => $data
            ]);
        } else {
            Log::warning('Transaction not found:', ['id' => $id]);
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.'
            ], 404);
        }
    }

    private function sendStatusUpdateEmail($transaction)
    {
        try {
            Log::info('Starting sendStatusUpdateEmail', [
                'transaction_id' => $transaction->id,
                'user_exists' => isset($transaction->user)
            ]);

            if (!$transaction->user || !$transaction->user->email) {
                Log::error('Cannot send email - missing user data', [
                    'transaction_id' => $transaction->id
                ]);
                return;
            }

            $statusMessages = [
                'pending' => 'Transaksi Anda sedang menunggu konfirmasi',
                'processing' => 'Transaksi Anda sedang diproses',
                'success' => 'Transaksi Anda telah berhasil diproses',
                'cancelled' => 'Transaksi Anda telah dibatalkan'
            ];

            $message = $statusMessages[strtolower($transaction->order_status)] ?? 'Status transaksi Anda telah diperbarui';

            $htmlContent = '
                <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #f8f9fa; padding: 20px; text-align: center; }
                            .content { padding: 20px; }
                            .footer { text-align: center; padding: 20px; font-size: 14px; }
                            .details { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h2>Update Status Transaksi</h2>
                            </div>
                            <div class="content">
                                <p>Halo ' . $transaction->user->name . ',</p>
                                <p>' . $message . '</p>
                                <div class="details">
                                    <p><strong>Detail Transaksi:</strong></p>
                                    <ul>
                                        <li>ID Transaksi: #' . $transaction->id . '</li>
                                        <li>Status: ' . ucfirst($transaction->order_status) . '</li>
                                        <li>Total: Rp ' . number_format($transaction->transaction_total, 0, ',', '.') . '</li>
                                    </ul>
                                </div>
                                <p>Terima kasih telah berbelanja di toko kami.</p>
                            </div>
                            <div class="footer">
                                <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                            </div>
                        </div>
                    </body>
                </html>';

            Mail::html($htmlContent, function ($mail) use ($transaction) {
                $mail->to($transaction->user->email)
                    ->subject('Update Status Transaksi #' . $transaction->id);
            });

            Log::info('Email sent successfully', [
                'transaction_id' => $transaction->id,
                'to_email' => $transaction->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // public function sendStatusUpdateEmail(Request $request)
    // {

    //     $statusMessages = [
    //         'pending' => 'Transaksi Anda sedang menunggu konfirmasi',
    //         'processing' => 'Transaksi Anda sedang diproses',
    //         'success' => 'Transaksi Anda telah berhasil diproses',
    //         'cancelled' => 'Transaksi Anda telah dibatalkan'
    //     ];

    //     $transaction = TransactionModel::where('id', $request->transaction_id)->first();
    //     $message = $statusMessages[strtolower($transaction->order_status)] ?? 'Status transaksi Anda telah diperbarui';

    //     try {
    //         $response = Http::withHeaders([
    //             'api-key' => env('SENDINBLUE_API_KEY'),
    //             "Content-Type" => "application/json"
    //         ])->post('https://api.brevo.com/v3/smtp/email', [
    //             "sender" => [
    //                 "name" => env('SENDINBLUE_SENDER_NAME'),
    //                 "email" => env('SENDINBLUE_SENDER_EMAIL'),
    //             ],
    //             'to' => [
    //                 ['email' => $request->$transaction->user->email]
    //             ],
    //             "subject" => "Kode OTP Reset Password",
    //             "htmlContent" => "
    //         <html>
    //         <body>
    //             <h2>Update Status Transaksi</h2>
    //                         <p>Halo ' . $transaction->user->name . ',</p>
    //                         <p>' . $message . '</p>
    //                         <p>Detail Transaksi:</p>
    //                         <ul>
    //                             <li>ID Transaksi: #' . $transaction->id . '</li>
    //                             <li>Status: ' . ucfirst($transaction->order_status) . '</li>
    //                             <li>Total: Rp ' . number_format($transaction->transaction_total, 0, ',', '.') . '</li>
    //                         </ul>
    //                         <p>Terima kasih telah berbelanja di toko kami.</p>
    //         </body>
    //         </html>",
    //         ]);
    //         return response()->json([
    //             Log::info('Email sending response:', [
    //                 'transaction_id' => $transaction->id,
    //                 'status_code' => $response->status(),
    //                 'response_body' => $response->json()
    //             ]),
    //             'status' => true,
    //             'message' => 'status pesanan berhasil diperbarui',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Error sending OTP: ' . $e->getMessage());
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Gagal memperbarui status pesanan',
    //         ], 500);
    //     }
    // }


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
