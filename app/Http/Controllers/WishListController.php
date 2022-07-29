<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WishList;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ResponseRepository;

class WishListController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    //Create WishList
    public function wishlist(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'product_id' => 'required',
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Adding to wishlist failed', $validator->errors(), 401);
        }
        if($input['type'] === 'add') {
            $input['date'] = $this->response->currentDate();
            $input['time'] = $this->response->currentTime();
            $create = WishList::create($input);
            return $this->response->jsonResponse(false, 'Product Added To Wishlist', [], 201);
        } else if($input['type'] === 'remove') {
            WishList::where('customer_id', $input['customer_id'])->where('product_id', $input['product_id'])->delete();
            return $this->response->jsonResponse(false, 'Product Removed From Wishlist', [], 201);
        }
    }

    //listing all the customerWishList
    public function customerWishList($customer_id)
    {
        return $this->response->jsonResponse(false, 'WishList Listed Successfully', WishList::where('customer_id', $customer_id)->with('products.oneImage')->get(), 201);
    }

    //removing WishList
    public function removeFromWishList($wishlist_id)
    {
        return $this->response->jsonResponse(false, 'Product Removed From Wishlist', WishList::where('wishlist_id', $wishlist_id)->delete(), 201);
    }

    public function wishListToCart($customer_id) {
        $getList = WishList::where('customer_id', $customer_id)->get();
        if($getList) {
            Cart::where('customer_id', $customer_id)->delete();
        }
        $data = [];
        foreach($getList as $list) {
            $price = Product::where('product_id', $list['product_id'])->select('product_discount_price')->first()->product_discount_price;
            $data = [
                'customer_id' => $customer_id,
                'product_id' => $list['product_id'],
                'product_price' => $price,
                'product_quantity' => 1,
                'product_total' => $price,
                'date' => $this->response->currentDate(),
                'time' => $this->response->currentTime(),
            ];
            Cart::create($data);
        }
        return $this->response->jsonResponse(false, 'WishList Moved To Cart', [], 201);
    }

    public function getWhishlistId($id) {
        return $this->response->jsonResponse(false, 'WishList Id Listed Successfully', WishList::where('customer_id', $id)->select('product_id')->get(), 201);
    }
}
