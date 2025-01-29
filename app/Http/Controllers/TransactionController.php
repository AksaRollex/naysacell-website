<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\TransactionModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransactionController extends Controller
{
    public function laporan(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        DB::statement('set @no=0+' . $page * $per);

        $query = TransactionModel::with('user');

        if ($request->search) {
            $query->where('transaction_status', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->transaction_status) {
            $query->where('transaction_status', $request->transaction_status);
        }

        $data = $query->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function histori(Request $request)
    {
        if (request()->wantsJson()) {
            $per = $request->per ?? 10;
            $page = ($request->page ? $request->page - 1 : 0);

            DB::statement('set @no=0+' . $page * $per);

            $query = TransactionModel::where('transaction_user_id', auth()->user()->id);

            if ($request->transaction_status) {
                $query->where('transaction_status', $request->transaction_status);
            }

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('transaction_status', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('transaction_product', 'LIKE', '%' . $request->search . '%');
                });
            }

            $data = $query->orderBy('created_at', 'DESC')
                ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

            return response()->json($data);
        }

        return abort(404);
    }

    public function historiHome(Request $request)
    {
        if ($request->wantsJson()) {
            $per = 3;
            $page = ($request->page ? $request->page - 1 : 0);

            DB::statement('set @no=0+' . $page * $per);
            $data = TransactionModel::where('transaction_user_id', auth()->user()->id)
                ->orderBy('created_at', 'DESC')
                ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

            return response()->json($data);
        } else {
            return abort(404);
        }
    }
    public function destroy($id)
    {
        $transaction = TransactionModel::find($id);
        $transaction->delete();
        return response()->json([
            'status' => 'true',
            'message' => 'Data Berhasil Dihapus'
        ]);
    }
    public function handlePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Orders::findOrFail($request->order_id);

            DB::beginTransaction();

            // Buat record transaksi
            $transaction = TransactionModel::create([
                'order_id' => $order->id,
                'payment_status' => 'pending',
                'amount' => $order->product_price,
                'payment_date' => now(),
            ]);

            $transaction->update([
                'payment_status' => 'success'
            ]);

            $order->update([
                'order_status' => 'processing'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'order' => $order
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function downloadExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet =  $spreadsheet->getActiveSheet();

        $data = TransactionModel::with(['user'])->get();

        $sheet->setCellValue('A1', 'No.');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Kode TRX');
        $sheet->setCellValue('D1', 'Produk');
        $sheet->setCellValue('E1', 'Nomor Tujuan');
        $sheet->setCellValue('F1', 'Total');
        $sheet->setCellValue('G1', 'Pesan');
        $sheet->setCellValue('H1', 'Tanggal Pembelian');
        $sheet->setCellValue('I1', 'Status Pembayaran');

        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:I1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:I1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);


        $row = 2;
        foreach ($data as $i => $transaction) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $transaction->user->name);
            $sheet->setCellValue('C' . $row, $transaction->transaction_code);
            $sheet->setCellValue('D' . $row, $transaction->transaction_product);
            $sheet->setCellValue('E' . $row, $transaction->transaction_number);
            $sheet->setCellValue('F' . $row, 'Rp ' . number_format($transaction->transaction_total, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, $transaction->transaction_message);
            $sheet->setCellValue('H' . $row, $transaction->created_at->format('d-m-Y'));
            $sheet->setCellValue('I' . $row, $transaction->transaction_status);

            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Laporan Transaksi Pesanan.xlsx"');
        $writer->save("php://output");
    }

    public function getChartData()
    {
        $data = TransactionModel::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(transaction_total) as total_amount')
        )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartData = $data->map(function ($item) {
            return [
                'date' => $item->date,
                'total_transactions' => $item->total_transactions,
                'total_amount' => $item->total_amount,
            ];
        });

        return response()->json([
            'labels' => $chartData->pluck('date')->values(),
            'transactions' => $chartData->pluck('total_transactions')->values(),
            'amounts' => $chartData->pluck('total_amount')->values()
        ]);
    }
}
