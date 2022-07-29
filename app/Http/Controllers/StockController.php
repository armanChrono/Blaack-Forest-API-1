<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Product;
use App\Repositories\ResponseRepository;

class StockController extends Controller
{

    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function stockUpdate() {
        $all = Product::all();
        foreach ($all as $product) {
            foreach ($product->product_sizes as $size) {
                Stock::create([
                    'product_id' => $product->id,
                    'size_id' => $size['id'],
                    'stock_quantity' => 0
                ]);
            }
        }
        return $this->response->jsonResponse(true, 'Stock Update Success', [], 201);
    }

    public function getStockOfProduct($id) {
        return $this->response->jsonResponse(true, 'Product Stock Listed', Stock::with('size')->where('product_id', $id)->get(), 201);
    }

    public function updateProductStocks(Request $request) {
        $input = $request->all();
        foreach ($input as $key => $value) {
            if($value === null) {
                $value = 0;
            }
            Stock::where('id', $key)->update(['stock_quantity' => $value]);
        }
        return $this->response->jsonResponse(true, 'Product Stock Updated', [], 201);
    }

    //Create a Stock
    public function createStock(Request $request)
    {
        if (Stock::where('stock_name', $request->stock_name)->where('branch_id', $request->branch_id)->exists()) {
            return $this->response->jsonResponse(true, $request->stock_name . ' Already Exists', [], 201);
        }

        if ($request->has('stock_name') && $request->has('branch_id')) {
            return $this->response->jsonResponse(false, 'Product Added Successfully', Stock::create($request->all()), 201);
        }

        return $this->response->jsonResponse(true, 'Stock Creation Failed', [], 201);
    }

    //update Stock
    public function updateStock(Request $request)
    {
        $fetchUpdatedDetails = Stock::where('stock_id', $request->stock_id)->exists();
        if (($fetchUpdatedDetails) != 0) {
            Stock::where('stock_id', $request->stock_id);
            $checkExisting = Stock::where('stock_name', $request->stock_name)->first();
            if ($checkExisting) {
                if ($checkExisting->stock_id == $request->stock_id) {
                    if ($request->stock_id && $request->has('stock_name')) {
                        Stock::where('stock_id', $request->stock_id)->update($request->all());
                        $fetchUpdatedDetails = Stock::where('stock_id', $request->stock_id)->get();
                        return $this->response->jsonResponse(false, 'Product Updated Successfully', $fetchUpdatedDetails, 201);
                    }
                } else {
                    return $this->response->jsonResponse(true, $request->stock_name . ' Already Exists', [], 201);
                }
            } else {
                if ($request->stock_id && $request->has('stock_name')) {
                    Stock::where('stock_id', $request->stock_id)->update($request->all());
                    $fetchUpdatedDetails = Stock::where('stock_id', $request->stock_id)->get();
                    return $this->response->jsonResponse(false, 'Product Updated Successfully', $fetchUpdatedDetails, 201);
                }
            }
            return $this->response->jsonResponse(true, 'Stock Updating Failed', [], 201);
        } else {
            return $this->response->jsonResponse(false, 'Stock Not Available', [], 201);
        }
    }

    //listing all the Stock
    public function listAllStocks($branch_id)
    {
        return $this->response->jsonResponse(false, 'Stock Listed Successfully', Stock::where('branch_id', $branch_id)->get(), 201);
    }

    //listing the Stock
    public function viewStock($stockId)
    {
        $list = Stock::where('stock_id', $stockId)->get();
        return $this->response->jsonResponse(false, 'Stock Listed Successfully', $list, 201);
    }

    //activate a Stock will show a Stock in a panel
    public function activateStock($stockId)
    {
        $getStock = Stock::where('stock_id', $stockId)->exists();
        if (($getStock) != 0) {
            Stock::where('stock_id', $stockId)->update(['active_status' => 1]);
            return $this->response->jsonResponse(false, 'Stock Activated Successfully', Stock::where('stock_id', $stockId)->get(), 201);
        } else {
            return $this->response->jsonResponse(false, 'Stock Not Available', [], 201);
        }
    }

    //deactivate a Stock will show a Stock in a panel
    public function deActivateStock($stockId)
    {
        $getStock = Stock::where('stock_id', $stockId)->exists();
        if (($getStock) != 0) {
            Stock::where('stock_id', $stockId)->update(['active_status' => 0]);
            return $this->response->jsonResponse(false, 'Stock De-Activated Successfully', Stock::where('stock_id', $stockId)->get(), 201);
        } else {
            return $this->response->jsonResponse(false, 'Stock Not Available', [], 201);
        }
    }

    //listing active Stock
    public function listActiveStocks($branch_id)
    {
        $list = Stock::where('active_status', 1)->where('branch_id', $branch_id)->get();
        return $this->response->jsonResponse(false, 'Active Stocks Listed Successfully', $list, 201);
    }

    //deleting a Stock
    public function deleteStock($stockId)
    {
        Stock::where('stock_id', $stockId)->delete();
        return $this->response->jsonResponse(false, 'Stock Deleted Successfully', [], 201);
    }

    //Searching a Stock
    public function searchStock($branch_id, $search)
    {
        if ($search === "null") {
            return $this->response->jsonResponse(false, 'Stock filtered Successfully', [], 201);
        }
        $stockSearch = Stock::where('stock_name', 'LIKE', $search . '%')->where('branch_id', $branch_id)->get();
        return $this->response->jsonResponse(false, 'Stock filtered Successfully', $stockSearch, 201);
    }

    //Current available stock
    public function currentStock($branch_id)
    {
        return $this->response->jsonResponse(false, 'Stock filtered Successfully', Stockavailable::with('stock')->where('branch_id', $branch_id)->get(), 201);
    }
}
