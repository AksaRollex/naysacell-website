<?php

namespace App\Http\Controllers;

use App\Models\CodeOperator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodeOperatorController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = CodeOperator::when($request->search, function (Builder $query, string $search) {
            $query->where('code', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
        return response()->json($data);
    }
}
