<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductPasca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductPascaController extends Controller
{
    protected $header = null;
    protected $url = null;
    protected $user = null;
    protected $key = null;
    protected $model_pasca = null;

    public function __construct()
    {
        $this->header = array(
            'Content-Type => application/json',
        );

        $this->url = env('DIGIFLAZ_URL');
        $this->user = env('DIGIFLAZ_USER');
        $this->key = env('DIGIFLAZ_MODE') == 'development' ? env('DIGIFLAZ_DEV_KEY') : env('DIGIFLAZ_PROD_KEY');

        $this->model_pasca = new ProductPasca();
    }

    public function get_product_pasca()
    {
        $response = Http::withHeaders($this->header)->post($this->url . '/price-list', [
            "cmd" => "pasca",
            "username" => $this->user,
            "sign" => md5($this->user . $this->key . "pricelist"),
        ]);

        Log::info('Digiflazz Pasca Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data === null) {
            Log::error('JSON Decode Failed', [
                'response_body' => $response->body(),
                'json_last_error' => json_last_error_msg()
            ]);
            return response()->json(['error' => 'Failed to decode response'], 500);
        }

        // Only proceed if data is an array
        if (is_array($data['data'])) {
            $this->model_pasca->insert_data($data['data']);
            return response()->json($data['data']);
        } else {
            Log::error('Data is not an array', [
                'data_type' => gettype($data['data']),
                'data' => $data['data']
            ]);
            return response()->json(['error' => 'Invalid data format'], 500);
        }
    }


    public function indexPasca(Request $request)
    {
        $per = $request->per ?? 20;
        $page = $request->page ? $request->page - 1 : 0;
    
        DB::statement('set @no=0+' . $page * $per);
    
        $data = ProductPasca::when($request->product_provider, function ($query, $productProviders) {
                $providers = explode(',', $productProviders);
                $query->whereIn('product_provider', $providers);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('product_name', 'like', "%$search%");
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
    
        return response()->json($data);
    }
    
    
}
