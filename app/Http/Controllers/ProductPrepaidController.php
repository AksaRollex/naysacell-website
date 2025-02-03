<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\ProductPrepaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        try {
            $validated = $request->validate([
                'product_name' => 'required',
                'product_desc' => 'required',
                'product_price' => 'required|numeric',
                'product_category' => 'required',
                'product_provider' => 'required',
            ]);

            // Generate SKU
            $providerPrefix = [
                'Telkomsel' => 'TEL',
                'Indosat' => 'IND',
                'XL' => 'XL',
                'Smartfren' => 'SMF',
                'Three' => 'TRI',
                'Axis' => 'AXS',
                'Dana' => 'DNA',
                'Gopay' => 'GPY',
                'OVO' => 'OVO',
                'Shopeepay' => 'SPY',
            ][$request->product_provider] ?? '';

            $categoryPrefix = [
                'Pulsa' => 'P',
                'Data' => 'D',
                'E-Money' => 'E',
            ][$request->product_category] ?? '';

            $validated['product_sku'] = $providerPrefix . $categoryPrefix . $request->product_price;

            if (ProductPrepaid::where('product_sku', $validated['product_sku'])->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Produk dengan kombinasi Provider, Kategori, dan Harga yang sama sudah ada'
                ], 422);
            }

            $base = ProductPrepaid::create($validated);
            return response()->json([
                'status' => true,
                'message' => 'Produk Berhasil Ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage()
            ], 500);
        }
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

    public function downloadExcel()
    { {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $data = ProductPrepaid::get();

            $sheet->setCellValue('A1', 'No.');
            $sheet->setCellValue('B1', 'Nama Produk');
            $sheet->setCellValue('C1', 'Produk Deskripsi');
            $sheet->setCellValue('D1', 'Produk Kategori');
            $sheet->setCellValue('E1', 'Produk Provider');
            $sheet->setCellValue('F1', 'Produk SKU');
            $sheet->setCellValue('G1', 'Harga Produk');

            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE1B48F');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:G1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(35);
            $sheet->getColumnDimension('C')->setWidth(40);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(45);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(30);

            $row = 2;
            foreach ($data as $i => $productPrepaid) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $productPrepaid->product_name);
                $sheet->setCellValue('C' . $row, $productPrepaid->product_desc);
                $sheet->setCellValue('D' . $row, $productPrepaid->product_category);
                $sheet->setCellValue('E' . $row, $productPrepaid->product_provider);
                $sheet->setCellValue('F' . $row, $productPrepaid->product_sku);
                $sheet->setCellValue('G' . $row, 'Rp ' . number_format($productPrepaid->product_price, 0, ',', '.'));

                $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="Laporan Daftar Produk.xlsx"');
            $writer->save("php://output");
        }
    }
}
