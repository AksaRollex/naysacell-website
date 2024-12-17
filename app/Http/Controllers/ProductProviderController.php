<?php

namespace App\Http\Controllers;

use App\Models\ProductProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductProviderController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = ProductProvider::when($request->search, function (Builder $query, string $search) {
            $query->where('provider_name', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
        return response()->json($data);
    }

    public function get($id)
    {
        $data = ProductProvider::find($id);
        return response()->json([
            'data' => $data
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $data = ProductProvider::find($id);
        $data->update($request->all());
        return response()->json($data);
    }

    public function add(Request $request)
    {
        $base = ProductProvider::create([
            'provider_name'  => $request->input('provider_name'),
            'provider_photo' => str_replace('public/', '', $request->file('provider_photo')->store('public/provider_photo')),

        ]);

        $base->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Data provider telah disimpan'
        ]);
    }

    public function destroy($id)
    {
        $data = ProductProvider::find($id);
        $data->delete();
        return response()->json($data);
    }
}
