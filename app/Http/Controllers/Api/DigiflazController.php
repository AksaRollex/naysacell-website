<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductPasca;
use App\Models\ProductPrepaid;
use App\Models\TransactionModel;
use App\Traits\CodeGenerate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class DigiflazController extends Controller
{
    use CodeGenerate;
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
        $this->model_pasca = new ProductPasca();
        $this->model_transaction = new TransactionModel();
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

    public function get_product_pasca()
    {
        $response = Http::withHeaders($this->header)->post($this->url . '/price-list', [
            "cmd" => "pasca",
            "username" => $this->user,
            "sign" => md5($this->user . $this->key . "pricelist"),
        ]);

        // Debugging: Log the full response
        Log::info('Digiflazz Pasca Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $data = json_decode($response->getBody(), true);

        // More debugging
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

    // public function digiflazTopup(Request $request)
    // {
    //     $ref_id = $this->getCode();
    //     $product = ProductPrepaid::findProductBySKU($request->sku)->first();
    //     $response = Http::withHeaders($this->header)->post($this->url . '/transaction', [
    //         "username" => $this->user,
    //         "buyer_sku_code" => $request->sku,
    //         "customer_no" => $request->customer_no,
    //         "ref_id" =>  $ref_id,
    //         "sign" => md5($this->user . $this->key . $ref_id)
    //     ]);
    //     $data = json_decode($response->getBody(), true);
    //     $this->model_transaction->insert_transaction_data($data['data'], 'Prepaid', $product->product_provider);
    //     return response()->json($data['data']);
    // }


    public function digiflazTopup(Request $request)
    {
        $ref_id = $this->getCode();
        $response = Http::withHeaders($this->header)->post($this->url . '/transaction', [
            "username" => $this->user,
            "buyer_sku_code" => $request->sku,
            "customer_no" => $request->customer_no,
            "ref_id" =>  $ref_id,
            "testing" => true,
            "sign" => md5($this->user . $this->key . $ref_id)
        ]);

        $data = json_decode($response->getBody(), true);
        return response()->json($data['data']);
    }
    public function laporan(Request $request)
    {
        $per = $request->per ?? 20;
        $page = $request->page ? $request->page - 1 : 0;
        DB::statement('set @no=0+' . $page * $per);

        $data = TransactionModel::when($request->transaction_type, function ($query) use ($request) {
            $query->where('transaction_type', $request->transaction_type);
        })
            ->when($request->search, function (Builder $query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_status', 'like', "%$search%")
                        ->orWhere('transaction_type', 'like', "%$search%")
                        ->orWhere('customer_no', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
        return response()->json($data);
    }

    public function histori(Request $request)
    {
        if (request()->wantsJson()) {
            $per = (($request->per) ? $request->per : 10);
            $page = (($request->page) ? $request->page - 1 : 0);

            DB::statement('set @no=0+' . $page * $per);
            $data = TransactionModel::where(function ($q) use ($request) {
                $q->where('transaction_status', 'LIKE', '%' . $request->search . '%');
            })->where('transaction_status')->orWhere('transaction_user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

            return response()->json($data);
        } else {
            return abort(404);
        }
    }

    public function digiflazCekTagihan(Request $request)
    {
        $ref_id = $this->getCode();
        $response = Http::withHeaders($this->header)->post($this->url . '/transaction', [
            "commands" => "inq-pasca",
            "username" => $this->user,
            "buyer_sku_code" => $request->sku,
            "customer_no" => $request->customer_no,
            "ref_id" =>  $ref_id,
            "sign" => md5($this->user . $this->key . $ref_id)
        ]);
        $data = json_decode($response->getBody(), true);
        return response()->json($data['data']);
    }

    public function digiflazBayarTagihan(Request $request)
    {
        $ref_id = $this->getCode();
        $product = ProductPasca::findBySKU($request->sku)->first();
        $response = Http::withHeaders($this->header)->post($this->url . '/transaction', [
            "commands" => "pay-pasca",
            "username" => $this->user,
            "buyer_sku_code" => $request->sku,
            "customer_no" => $request->customer_no,
            "ref_id" => $ref_id,
            "sign" => md5($this->user . $this->key . $ref_id),
        ]);

        $data = json_decode($response->getBody(), true);
        $this->model_transaction->insert_transaction_data($data['data'], 'Pasca', $product->product_provider);
        return response()->json($data, ['data']);
    }
}
