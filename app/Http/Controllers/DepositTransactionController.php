<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use App\Models\DepositTransaction;
use App\Models\ProductPrepaid;
use App\Traits\CodeGenerate;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepositTransactionController extends Controller
{

    public function index(Request $request)
    {
        if (request()->wantsJson()) {
            $per = $request->per ?? 10;
            $page = ($request->page ? $request->page - 1 : 0);

            DB::statement('set @no=0+' . $page * $per);

            $query = DepositTransaction::where('user_id', auth()->user()->id);

            if ($request->search) {
                $query->where('user_name', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $data = $query->orderBy('created_at', 'DESC')
                ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

            return response()->json($data);
        }

        return abort(404);
    }
    public function indexWeb(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = DepositTransaction::when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    private $serverKey;

    public function __construct()
    {
        Log::info('Checking Midtrans Configuration', [
            'config_exists' => config()->has('midtrans'),
            'server_key' => config('midtrans.server_key'),
            'env_server_key' => env('MIDTRANS_SERVER_KEY')
        ]);

        $this->serverKey = config('midtrans.server_key');

        if (empty($this->serverKey)) {
            Log::error('Midtrans server key is empty');
        }

        \Midtrans\Config::$serverKey = $this->serverKey;
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    use CodeGenerate;
    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000|max:10000000'
        ]);

        try {
            DB::beginTransaction();
            Log::info('Topup Request:', $request->all());

            $user = auth()->user();
            $depositCode = $this->getCode();

            if (empty($this->serverKey)) {
                throw new \Exception('Midtrans server key is not configured');
            }

            // Create transaction with nullable payment fields
            $transaction = DepositTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'user_name' => $user->name,
                'deposit_code' => $depositCode,
                'user_number' => $user->phone,
                'payment_type' => null, // Akan diupdate saat callback
                'paid_at' => null // Akan diupdate saat callback
            ]);

            // Persiapkan parameter untuk Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $depositCode,
                    'gross_amount' => (int) $request->amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                    'billing_address' => [
                        'address' => $user->address ?? '',
                    ]
                ],
                'item_details' => [
                    [
                        "id" => "TOPUP-WALLET",
                        "price" => (int)$request->amount,
                        "quantity" => 1,
                        "name" => "Top Up Saldo",
                        "category" => "Wallet",
                        "merchant_name" => config('app.name')
                    ]
                ],
                'callbacks' => [
                    'finish' => route('deposit.finish'),
                    'unfinish' => route('deposit.unfinish'),
                    'error' => route('deposit.error'),
                ],
                'enable_payments' => config('midtrans.enabled_payments', []),
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => 60  // 1 jam expired
                ]
            ];

            // Set mode Midtrans (sandbox/production)
            \Midtrans\Config::$isProduction = config('midtrans.is_production', false);

            // Dapatkan Snap Token dari Midtrans
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Update transaction dengan snap token
            $transaction->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Topup Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat topup saldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        Log::info('Midtrans Callback:', $request->all());

        try {
            $serverKey = config('midtrans.server_key');
            $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

            if ($hashed != $request->signature_key) {
                Log::warning('Invalid signature:', [
                    'received' => $request->signature_key,
                    'calculated' => $hashed
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $transaction = DepositTransaction::where('deposit_code', $request->order_id)
                ->lockForUpdate()  // Tambahkan lock untuk menghindari race condition
                ->first();

            if (!$transaction) {
                Log::error('Transaction not found:', ['order_id' => $request->order_id]);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Cek apakah transaksi sudah diproses sebelumnya
            if ($transaction->status === 'success' && $transaction->paid_at !== null) {
                Log::info('Transaction already processed:', ['order_id' => $request->order_id]);
                return response()->json(['message' => 'Transaction already processed']);
            }

            switch ($request->transaction_status) {
                case 'capture':
                case 'settlement':
                    DB::beginTransaction();
                    try {
                        // Update transaction first
                        $transaction->update([
                            'status' => 'success',
                            'payment_type' => $request->payment_type,
                            'paid_at' => now()
                        ]);

                        // Then update balance
                        $userBalance = UserBalance::lockForUpdate()->firstOrCreate(
                            ['user_id' => $transaction->user_id],
                            ['user_name' => $transaction->user_name, 'balance' => 0]
                        );

                        $userBalance->balance += $transaction->amount;
                        $userBalance->save();

                        DB::commit();

                        Log::info('Transaction processed successfully:', [
                            'order_id' => $request->order_id,
                            'user_id' => $transaction->user_id,
                            'amount' => $transaction->amount
                        ]);

                        return response()->json(['message' => 'Transaction successful']);
                    } catch (\Exception $e) {
                        DB::rollback();
                        Log::error('Failed to process transaction:', [
                            'order_id' => $request->order_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return response()->json(['message' => 'Error processing transaction'], 500);
                    }
                    break;

                    // ... rest of the cases
            }

            return response()->json(['message' => 'Status updated']);
        } catch (\Exception $e) {
            Log::error('Callback processing error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function checkBalance()
    {
        $balance = UserBalance::where('user_id', auth()->id())
            ->first()
            ->balance ?? 0;

        return response()->json([
            'balance' => $balance
        ]);
    }

    public function checkBalanceWb(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $userId = auth()->id();

        DB::statement('set @no=0+' . $page * $per);
        $data = DepositTransaction::where('user_id', $userId)
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('user_name', 'like', "%$search%");
            })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = DepositTransaction::find($id);
        $data->delete();
        return response()->json([
            'status' =>  'true',
            'message' => "Data berhasil dihapus"
        ]);
    }
    public function downloadExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $data = DepositTransaction::with(['user'])->get();

        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Nomor Telepon');
        $sheet->setCellValue('D1', 'Nominal Deposit');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Tanggal Deposit');

        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:F1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:F1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);

        $row = 2;
        foreach ($data as $i => $DepositTransaction) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $DepositTransaction->user->name);
            $sheet->setCellValue('C' . $row, $DepositTransaction->user_number);
            $sheet->setCellValue('D' . $row, 'Rp ' . number_format($DepositTransaction->amount, 0, ',', '.'));
            $sheet->setCellValue('E' . $row, $DepositTransaction->status);
            $sheet->setCellValue('F' . $row, $DepositTransaction->created_at->format('d-m-Y'));

            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Laporan Deposit Pengguna.xlsx"');
        $writer->save("php://output");
    }
}
