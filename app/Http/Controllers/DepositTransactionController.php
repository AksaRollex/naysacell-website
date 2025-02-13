<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use App\Models\DepositTransaction;
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
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);

        $data = DepositTransaction::with(['user' => function ($query) use ($request) {
            if ($request->user_id) {
                $query->where('id', $request->user_id);
            }
        }])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function indexWeb(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = DepositTransaction::with('user')->when($request->search, function (Builder $query, string $search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
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

        $maxAttempts = 3;
        $attempt = 0;
        while ($attempt < $maxAttempts)

            try {
                Log::info('Topup Request:', $request->all());
                DB::beginTransaction();

                $user = auth()->user();
                $depositCode = $this->getCode();

                if (empty($this->serverKey)) {
                    throw new \Exception('Midtrans server key is not configured');
                }

                $transaction = DepositTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'status' => 'pending',
                    'deposit_code' => $depositCode,
                    'payment_type' => null,
                    'paid_at' => null
                ]);

                $params = [
                    'transaction_details' => [
                        'order_id' => $depositCode,
                        'gross_amount' => $request->amount,
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
                        'duration' => 30
                    ]
                ];

                \Midtrans\Config::$isProduction = config('midtrans.is_production', false);

                $snapToken = \Midtrans\Snap::getSnapToken($params);

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
        try {
            $transaction = DepositTransaction::where('deposit_code', $request->order_id)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                Log::error('Transaction not found:', ['order_id' => $request->order_id]);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if ($transaction->status === 'pending' && in_array($request->transaction_status, ['capture', 'settlement'])) {
                DB::beginTransaction();
                try {
                    $transaction->update([
                        'status' => 'success',
                        'payment_type' => $request->payment_type,
                        'paid_at' => now()
                    ]);

                    $userBalance = UserBalance::where('user_id', $transaction->user_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$userBalance) {
                        Log::info('Creating new user balance record', [
                            'user_id' => $transaction->user_id
                        ]);

                        $userBalance = UserBalance::create([
                            'user_id' => $transaction->user_id,
                            'balance' => 0
                        ]);
                    }

                    $userBalance->balance += $transaction->amount;
                    $userBalance->save();

                    DB::commit();
                    Log::info('Payment processed successfully', [
                        'transaction_id' => $transaction->id
                    ]);

                    return response()->json(['message' => 'Payment processed successfully']);
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            } else {
                Log::info('Payment status not processable:', [
                    'current_status' => $transaction->status,
                    'midtrans_status' => $request->transaction_status
                ]);
            }

            return response()->json(['message' => 'Payment status updated']);
        } catch (\Exception $e) {
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

        try {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $data = DepositTransaction::with(['user'])->get();

            $sheet->setCellValue('A1', 'No.');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Nomor Telepon');
            $sheet->setCellValue('D1', 'Nominal Deposit');
            $sheet->setCellValue('E1', 'Status');
            $sheet->setCellValue('F1', 'Tanggal Pembayaran');
            $sheet->setCellValue('G1', 'Tanggal Deposit');

            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:G1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(30);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);

            $row = 2;
            foreach ($data as $i => $DepositTransaction) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $DepositTransaction->user->name);
                $sheet->setCellValue('C' . $row, $DepositTransaction->user->phone);
                $sheet->setCellValue('D' . $row, 'Rp ' . number_format($DepositTransaction->amount, 0, ',', '.'));
                $sheet->setCellValue('E' . $row, $DepositTransaction->status);
                $sheet->setCellValue('F' . $row, $DepositTransaction->paid_at);
                $sheet->setCellValue('G' . $row, $DepositTransaction->created_at->format('d-m-Y'));

                $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Laporan Deposit Pengguna.xlsx"');
            $writer->save("php://output");
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
