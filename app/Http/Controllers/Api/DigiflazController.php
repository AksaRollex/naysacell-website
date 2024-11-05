<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductPasca;
use App\Models\ProductPrepaid;
use App\Traits\CodeGenerate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DigiflazController extends Controller
{
    use CodeGenerate;
    protected $header = null;
    protected $url = null;
    protected $user = null;
    protected $key = null;
    protected $model = null;
    protected $model_pasca = null;

    public function __construct()
    {
        $this->header = array(
            'Content-Type => application/json',
        );

        $this->url = env('DIGIFLAZ_URL');
        $this->user = env('DIGIFLAZ_USER');
        $this->key = env('DIGIFLAZ_MODE') == 'development' ? env('DIGIFLAZ_DEV_KEY') : env('DIGIFLAZ_PROD_KEY');

        $this->model = new ProductPrepaid();
        $this->model_pasca = new ProductPasca();
    }

    // public function get_product_prepaid()
    // {

    //     $header = array(
    //         'Content-Type => application/json',
    //     );
    //     $url = env('DIGIFLAZ_URL');
    //     $user = env('DIGIFLAZ_USER');
    //     $key = env('DIGIFLAZ_DEV_KEY');

    //     $response = Http::withHeaders($header)->post($url . '/price-list', [
    //         "cmd" => "prepaid",
    //         "username" => $user,
    //         "sign" => md5($user . $key . "pricelist"),
    //     ]);

    //     return json_decode($response->getBody(), true);
    // }

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


    // DATA NULL
    public function get_product_pasca()
    {
        $response = Http::withHeaders($this->header)->post($this->url . '/price-list', [
            "cmd" => "pasca",
            "username" => $this->user,
            "sign" => md5($this->user . $this->key . "pricelist")
        ]);
        $data = json_decode($response->getBody(), true);
        // $this->model_pasca->insert_data($data['data']);
        return $data;
    }

    public function digiflazTopup(Request $request)
    {
        $ref_id = $this->getCode();
        $response = Http::withHeaders($this->header)->post($this->url . '/transaction', [
            "username" => $this->user,
            "buyer_sku_code" => $request->sku,
            "customer_no" => $request->customer_no,
            "ref_id" => $ref_id,
            "sign" => md5($this->user . $this->key . $ref_id),
        ]);

        $data = json_decode($response->getBody(), true);
        return response()->json($data);
    }
}
