<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\ProductPrepaid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Orders::when($request->search, function (Builder $query, string $search) {
            $query->where('customer_no', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function get($id)
    {
        $data = Orders::find($id);

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function update($id, Request $request)
    {
        $data = Orders::find($id);
        $data->update($request->all());
        return response()->json($data);
    }

    public function destroy($id)
    {
        $data = Orders::find($id);
        $data->delete();
        return response()->json($data);
    }

    public function submitProduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:product_prepaid,id',
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'quantity' => 'required|integer|min:1',
                'customer_no' => 'required|string',
                'customer_name' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            Log::info('Submit Product Request:', $request->all());

            $product = ProductPrepaid::findOrFail($request->product_id);
            $user = User::findOrFail($request->user_id);

            Log::info('Product Data:', [
                'product' => $product->toArray(),
                'user' => $user->toArray()
            ]);

            $order = Orders::create($validated);

            Log::info('Created Order:', $order->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => $order
            ]);
        } catch (ModelNotFoundException $e) {
            $message = str_contains($e->getMessage(), 'Product')
                ? 'Product not found'
                : 'User not found';

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 404);
        } catch (\Exception $e) {
            Log::error('Submit Product Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
