<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use App\Models\DepositTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class DepositTransactionController extends Controller
{

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = DepositTransaction::when($request->search, function (Builder $query, string $search) {
            $query->where('user_name', 'like', "%$search%");
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
}
