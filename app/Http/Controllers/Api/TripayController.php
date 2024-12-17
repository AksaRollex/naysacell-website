<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class TripayController extends Controller
{
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
}
