<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class TripayController extends Controller
{
    public function cek_server()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/cekserver';


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }
    public function cek_saldo()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/ceksaldo';


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }
    public function get_kategori_prabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembelian/category';


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_operator_prabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembelian/operator';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_produk_prabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembelian/produk';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_detail_produk_prabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'code' => 'AX5'
        ];

        $url = 'https://tripay.id/api/v2/pembelian/produk/cek';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_kategori_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembayaran/category';


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_operator_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembayaran/operator';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_produk_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/pembayaran/produk';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function get_detail_produk_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'code' => 'AX5'
        ];

        $url = 'https://tripay.id/api/v2/pembayaran/produk/cek';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function request_transaksi_prabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'inquiry'       => 'I',
            'code'          => 'AX5',
            'phone'         => '083856000000',
            'no_meter_pln'  => '123232132',
            'api_trxid'     => 'INV123',
            'pin'           => '1234',
        ];

        $url = 'https://tripay.id/api/v2/transaksi/pembelian';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function cek_tagihan_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'product' => 'PLN',
            'phone'   => '089800000000',
            'no_pelanggan' => '212321311',
            'api_trxid'    => 'INV123',
            'pin'       => '1234'
        ];

        $url = 'https://tripay.id/api/v2/pembayaran/cek-tagihan';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function bayar_tagihan_pascabayar()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'order_id' => '788965',
            'api_trxid'    => 'INV123',
            'pin'       => '1234'
        ];

        $url = 'https://tripay.id/api/v2/transaksi/pembayaran';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function riwayat_transaksi()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $url = 'https://tripay.id/api/v2/histori/transaksi/all';

        $header = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = Http::withHeaders($header)->get($url);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function detail_riwayat_transaksi()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'trxid' => '102311',
            'api_trxid' => 'INV123'
        ];

        $url = 'https://tripay.id/api/v2/histori/transaksi/detail';

        $header = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = Http::withHeaders($header)->post($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }

    public function riwayat_transaksi_bydate()
    {
        $apiKey = env('TRIPAY_API_KEY');

        $payload = [
            'start_date' => '2018-03-29',
            'end_date' => '2018-03-31',
        ];

        $url = 'https://tripay.id/api/v2/histori/transaksi/bydate';

        $header = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        $response = Http::withHeaders($header)->post($url, $payload);

        if ($response->successful()) {
            return response()->json($response->json(), 200);
        } else {
            return response()->json([
                'message' => 'Error',
                'error' => $response->body()
            ], $response->status());
        }
    }
}
