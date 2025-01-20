<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use App\Models\DepositTransaction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepositTransactionController extends Controller
{

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $userId = auth()->id();

        DB::statement('set @no=0+' . $page * $per);
        $data = DepositTransaction::where('user_id', $userId)
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('name', 'like', "%$search%");
            })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000|max:10000000'
        ]);

        try {
            DB::beginTransaction();

            // Ambil user yang sedang login
            $user = auth()->user();

            // Buat record transaksi
            $transaction = DepositTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'user_name' => $user->name  // Perbaikan di sini
            ]);

            // Update atau create saldo user
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['user_name' => $user->name],
                ['balance' => 0]
            );

            // Update saldo
            $userBalance->balance += $request->amount;
            $userBalance->save();

            // Update status transaksi
            $transaction->update(['status' => 'success']);

            DB::commit();

            return response()->json([
                'message' => 'Deposit berhasil',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'balance' => $userBalance->balance
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            if (isset($transaction)) {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json([
                'message' => 'Terjadi kesalahan saat deposit',
                'error' => $e->getMessage()
            ], 500);
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

    public function downloadExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $data = DepositTransaction::with(['user'])->get();

        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Nominal Deposit');
        $sheet->setCellValue('D1', 'Status');
        $sheet->setCellValue('E1', 'Tanggal Deposit');

        $sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:E1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);

        $row = 2;
        foreach ($data as $i => $DepositTransaction) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $DepositTransaction->user->name);
            $sheet->setCellValue('C' . $row, 'Rp ' . number_format($DepositTransaction->amount, 0, ',', '.'));
            $sheet->setCellValue('D' . $row, $DepositTransaction->status);
            $sheet->setCellValue('E' . $row, $DepositTransaction->created_at->format('d-m-Y'));

            $sheet->getStyle('A' . $row . ':E' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="grid-export.xlsx"');
        $writer->save("php://output");
    }
}
