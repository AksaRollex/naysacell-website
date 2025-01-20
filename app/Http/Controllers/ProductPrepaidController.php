<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\ProductPrepaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ProductPrepaidController extends Controller
{
    protected $header = null;
    protected $url = null;
    protected $user = null;
    protected $key = null;
    protected $model = null;
    protected $model_pasca = null;
    protected $model_transaction = null;

    public function __construct()
    {
        $this->header = array(
            'Content-Type => application/json',
        );

        $this->url = env('DIGIFLAZ_URL');
        $this->user = env('DIGIFLAZ_USER');
        $this->key = env('DIGIFLAZ_MODE') == 'development' ? env('DIGIFLAZ_DEV_KEY') : env('DIGIFLAZ_PROD_KEY');

        $this->model = new ProductPrepaid();
    }

    public function get_product_prepaid()
    {
        $response = Http::withHeaders($this->header)->post($this->url . '/price-list', [
            "cmd" => "prepaid",
            "username" => $this->user,
            "sign" => md5($this->user . $this->key . "pricelist"),
        ]);


        $data = json_decode($response->getBody(), true);
        $this->model->insert_data($data['data']);
    }

    public function indexPrepaid(Request $request)
    {
        if (request()->wantsJson()) {
            $per = $request->per ?? 10;
            $page = ($request->page ?? 1) - 1;

            DB::statement('set @no=0+' . $page * $per);

            $query = ProductPrepaid::query();

            if ($request->search) {
                $query->where('product_name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('product_category', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->product_provider) {
                $query->where('product_provider', $request->product_provider);
            }

            if ($request->product_category) {
                $query->where('product_category', $request->product_category);
            }

            $data = $query->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

            return response()->json($data);
        }
        return abort(404);
    }

    // public function indexPrepaid(Request $request)
    // {
    //     $per = $request->per ?? 10;
    //     $page = $request->page ? $request->page - 1 : 0;

    //     DB::statement('set @no=0+' . $page * $per);

    //     $data = ProductPrepaid::when($request->product_category, function ($q) use ($request) {
    //         $q->where('product_category', $request->product_category);
    //     })->when($request->product_provider, function ($q) use ($request) {
    //         $q->where('product_provider', $request->product_provider);
    //     })
    //         ->when($request->search, function (Builder $query, string $search) {
    //             $query->where('product_name', 'like', "%$search%");
    //         })
    //         ->latest()
    //         ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

    //     return response()->json($data);
    // }


    public function getPBBPrepaid($id)
    {
        $base = ProductPrepaid::find($id);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function updatePBBPrepaid(Request $request, $id)
    {
        try {
            $base = ProductPrepaid::findOrFail($id);
            $base->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Produk Berhasil Dirubah',
                'data' => $base
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Produk Gagal Dirubah: ' . $e->getMessage()
            ], 500);
        }
    }
    public function storePBBPrepaid(Request $request)
    {
        $base = ProductPrepaid::create($request->all());

        return response()->json([
            'status' => 'true',
            'message' => 'Produk Berhasil Ditambahkan'
        ]);
    }

    public function destroyPBBPrepaid($id)
    {
        $base = ProductPrepaid::find($id);
        $base->delete();

        return response()->json([
            'status' => 'true',
            'message' => 'Produk Berhasil Dihapus'
        ]);
    }

  
}
