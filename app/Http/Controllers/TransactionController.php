<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\TransactionModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function laporan(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        DB::statement('set @no=0+' . $page * $per);

        // Mulai dengan satu query builder
        $query = TransactionModel::with('user');  // Pindahkan with() ke query utama

        // Tambahkan kondisi pencarian jika ada
        if ($request->search) {
            $query->where('transaction_status', 'LIKE', '%' . $request->search . '%');
        }

        // Tambahkan filter status jika ada
        if ($request->transaction_status) {
            $query->where('transaction_status', $request->transaction_status);
        }

        // Eksekusi query dengan paginate
        $data = $query->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
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
                // validasi field lainnya
            ]);

            $order = Orders::findOrFail($request->order_id);

            DB::beginTransaction();

            // Buat record transaksi
            $transaction = TransactionModel::create([
                'order_id' => $order->id,
                'payment_status' => 'pending',
                'amount' => $order->product_price,
                'payment_date' => now(),
                // field lainnya
            ]);

            // Simulasi proses pembayaran
            // Jika pembayaran berhasil
            $transaction->update([
                'payment_status' => 'success'
            ]);

            // Update status order
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
}
