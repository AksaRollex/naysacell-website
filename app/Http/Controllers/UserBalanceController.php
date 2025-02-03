<?php

namespace App\Http\Controllers;

use App\Models\UserBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserBalanceController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = UserBalance::when($request->search, function (Builder $query, string $search) {
            $query->where('user_name', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function get($id)
    {
        return response()->json([
            'success' => true,
            'data' => UserBalance::find($id)
        ]);
    }

    public function update($id, Request $request)
    {
        $base = UserBalance::find($id);
        $base->update($request->all());
        return response()->json([
            'success' => true,
            'data' => UserBalance::find($id)
        ]);
    }
}
