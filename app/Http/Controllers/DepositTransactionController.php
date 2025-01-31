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

            $transaction = DepositTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'user_name' => $user->name,
                'deposit_code' => $depositCode,
                'user_number' => $user->phone
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
                    'phone' => $user->phone,
                ],
                'callbacks' => [
                    'finish' => route('deposit.finish'),
                    'unfinish' => route('deposit.unfinish'),
                    'error' => route('deposit.error'),
                ]
            ];

            // Dapatkan Snap Token dari Midtrans
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            DB::commit();

            return response()->json([
                'snap_token' => $snapToken,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Topup Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Terjadi kesalahan saat deposit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            $transaction = DepositTransaction::where('deposit_code', $request->order_id)->first();

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                DB::beginTransaction();
                try {
                    // Update status transaksi
                    $transaction->update(['status' => 'success']);

                    // Update saldo user
                    $userBalance = UserBalance::firstOrCreate(
                        ['user_id' => $transaction->user_id],
                        [
                            'user_name' => $transaction->user_name,
                            'balance' => 0
                        ]
                    );

                    $userBalance->balance += $transaction->amount;
                    $userBalance->save();

                    DB::commit();
                    return response()->json(['message' => 'Transaction successful']);
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['message' => 'Error processing transaction'], 500);
                }
            } elseif ($request->transaction_status == 'cancel' || $request->transaction_status == 'deny' || $request->transaction_status == 'expire') {
                $transaction->update(['status' => 'failed']);
                return response()->json(['message' => 'Transaction failed']);
            }
        }

        return response()->json(['message' => 'Invalid signature'], 403);
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
