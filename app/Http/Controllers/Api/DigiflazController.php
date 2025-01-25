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
}
