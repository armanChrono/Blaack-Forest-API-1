<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Addons;
use App\Models\CartAddons;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Log;
use App\Models\ProductDiscont;
use App\Models\CategoryDiscont;
use App\Models\CustomerDiscont;
use App\Models\Customer;

class CartController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    //Add Product to Cart
    public function addToCart(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
                'customer_id' => 'required',
                'product_id' => 'required',
                'product_quantity' => 'required',
                'product_size_id' => 'required',
                'variation_ids'=>'required',
                'flavour_id'=> 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Adding to cart failed', $validator->errors(), 401);
        }
        $checkExisting = Cart::where('customer_id', $input['customer_id'])->where('product_id', $input['product_id'])->where('product_size_id', $input['product_size_id'])->where('flavour_id', $input['flavour_id'])->where('variation_ids', $input['variation_ids'])->where('eggless', $input['eggless'])->exists();
        if (!$checkExisting) {
            // $getPrice = Product::where('id', $input['product_id'])->select('product_price', 'product_discount_price')->first();
            // $input['product_price'] = $getPrice->product_discount_price;
            // $input['product_discount_price'] = $getPrice->product_price;
            // $input['product_total'] = $input['product_discount_price'] * $input['product_quantity'];
            $input['date'] = $this->response->currentDate();
            $input['time'] = $this->response->currentTime();

            $create = Cart::create($input);

            $getCartId = Cart::where('customer_id', $input['customer_id'])->where('product_id', $input['product_id'])->where('product_size_id', $input['product_size_id'])->where('flavour_id', $input['flavour_id'])->where('variation_ids', $input['variation_ids'])->where('eggless', $input['eggless'])->select('cart_id')->first();

            return $this->response->jsonResponse(false, 'Product Added To Cart', ["cart_id"=>$getCartId ->cart_id, "cart_count" => $this-> calculateCartCount( $input['customer_id']) ], 201);
        } else {
            $getCartId = Cart::where('customer_id', $input['customer_id'])->where('product_id', $input['product_id'])->where('product_size_id', $input['product_size_id'])->where('flavour_id', $input['flavour_id'])->where('variation_ids', $input['variation_ids'])->where('eggless', $input['eggless'])->select('cart_id')->first();

            return $this->response->jsonResponse(true, 'Product Already Exist In Cart', [$getCartId ->cart_id], 201);
        }
    }

       //Add Addon's to Cart
       public function storeAddonsToCart(Request $request)
       {
           $input = $request->all();
           $validator = Validator::make($input, [
                   'customer_id' => 'required',
                   'selected_Addon' => 'required'
           ]);
           if ($validator->fails()) {
               return $this->response->jsonResponse(true, 'Adding to cart failed', $validator->errors(), 401);
           }
           foreach($input['selected_Addon'] as $addon) {
                $addonId = $addon['addon_id'];
                $checkExisting = CartAddons::where('addon_id', $addonId)->where('customer_id', $input['customer_id'])->exists();
                if (!$checkExisting) {
                    $getAddon = Addons::where('addon_id', $addonId)->first();
                    $cartAddon['customer_id'] = $input['customer_id'];
                    $cartAddon['addon_id'] = $getAddon->addon_id;
                    $cartAddon['product_name'] = $getAddon->product_name;
                    $cartAddon['image'] = $getAddon->image;
                    $cartAddon['price'] = $getAddon->price;
                    $cartAddon['quantity'] = 1;

                    $cartAddon['total'] =   $cartAddon['quantity'] * $cartAddon['price'];
                    $cartAddon['hsn'] = $getAddon->hsn;
                    $cartAddon['tax_id'] = $getAddon->tax_id;
                    $cartAddon['date'] = $this->response->currentDate();
                    $cartAddon['time'] = $this->response->currentTime();
                    $create = CartAddons::create($cartAddon);
                }
            }
            return $this->response->jsonResponse(false, "Addon's Updated Successfully", ['addon_count' =>$this-> calculateCartCount( $input['customer_id'])], 201);
       }

       public function calculateDiscount($products, $customerId){
            $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
            $customerData = $data['customer'];
            if($customerData){
                $customerData = $customerData ->toArray();
                if($customerData['customer_discont']){
                    $customerDisActiveStatus = $customerData['customer_discont']['active_status'];
                    $customerDiscountId =  $data['customer']->customer_discount_id;
                }else{
                    $customerDisActiveStatus = 0;
                    $customerDiscountId = 0;
                }

            }else{
                $customerDisActiveStatus = 0;
                $customerDiscountId = 0;
            }

            foreach ($products as $key => $product){
                $data['products'] = $products;
                Log::info("product == ".$product);
                $skip = $product->products->id."_FALSE";
                if($customerId !='null'){
                    if( $customerDiscountId != 0 && $customerDisActiveStatus == 1 && $skip == $product->products->id."_FALSE"){
                        $skip = $product->products->id."_TRUE";
                            $discount = CustomerDiscont::with('discount')->where('active_status', 1)->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                                (int)$customer_egg = $product->variations->egg;
                                (int)$customer_eggLess = $product->variations->eggLess;
                                if($discount){
                                    (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                                    $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                                 }else{
                                    (int)$customer_percentage = 0;
                                    $skip = $product->products->id."_FALSE";
                                 }

                                //egg
                                $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                                $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                                //eggLess
                                $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                                $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                                $product->variations['discountType'] = "Customer Discount";
                                $product->variations['discountPercent'] = $customer_percentage;
                                $product->variations['eggOriginalPrice'] = $customer_eggOriginalPrice;
                                $product->variations['eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                                $product->variations['eggDiscountPrice'] = $customer_eggDiscountPrice;
                                $product->variations['eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;
                     }
                }
                if($product->products->product_discount != 0  &&  $skip == $product->products->id."_FALSE"){
                    $skip = $product->products->id."_TRUE";
                      $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->products->product_discount)->first();
                       (int)$egg = $product->variations->egg;
                       (int)$eggLess = $product->variations->eggLess;
                       if($discount){
                            (int)$percentage = $discount->discount[0]->discount_percentage;
                          $data['products'][$key]['p_discount'] = $discount->discount[0];
                       }else{
                          (int)$percentage = 0;
                          $skip = $product->products->id."_FALSE";
                        }

                       //egg
                       $eggDiscountPrice = round($egg * ($percentage/100));
                       $eggOriginalPrice = $egg - $eggDiscountPrice;
                       //eggLess
                       $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                       $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                       $product->variations['discountType'] = "Product Discount";
                       $product->variations['discountPercent'] = $percentage;
                       $product->variations['eggOriginalPrice'] = $eggOriginalPrice;
                       $product->variations['eggDiscountPrice'] = $eggDiscountPrice;
                       $product->variations['eggLessDiscountPrice'] = $eggLessDiscountPrice;
                       $product->variations['eggLessOriginalPrice'] = $eggLessOriginalPrice;

               }
                if($product->products->category_discount != 0 && $skip == $product->products->id."_FALSE"){
                    $skip = $product->products->id."_TRUE";
                          $discountC = CategoryDiscont::with('discount')->where('active_status', 1)->where('category_discount_id', $product->products->category_discount)->first();

                           (int)$c_egg = $product->variations->egg;
                           (int)$c_eggLess = $product->variations->eggLess;
                           if($discountC){

                               (int)$c_percentage = $discountC->discount[0]->discount_percentage;
                               $data['products'][$key]['c_discount'] = $discountC->discount[0];
                           }else{
                                (int)$c_percentage = 0;
                               $skip = $product->products->id."_FALSE";
                            }

                           //egg
                           $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                           $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                           //eggLess
                           $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                           $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                           $product->variations['discountType'] = "Category Discount";
                           $product->variations['discountPercent'] = $c_percentage;
                           $product->variations['eggOriginalPrice'] = $c_eggOriginalPrice;
                           $product->variations['eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                           $product->variations['eggDiscountPrice'] = $c_eggDiscountPrice;
                           $product->variations['eggLessDiscountPrice'] = $c_eggLessDiscountPrice;
                   }

                    if( $skip == $product->products->id."_FALSE"){
                        $skip = $product->products->id."_TRUE";
                        (int)$c_egg = $product->variations->egg;
                        (int)$c_eggLess = $product->variations->eggLess;

                        $product->variations['discountType'] = "NO Discount";
                        $product->variations['discountPercent'] = null;
                        $product->variations['eggOriginalPrice'] = $c_egg;
                        $product->variations['eggLessOriginalPrice'] = $c_eggLess;
                        $product->variations['eggDiscountPrice'] = null;
                        $product->variations['eggLessDiscountPrice'] = null;
                    }
                }
        return $products;
    }

    //listing all the customer cart products
    public function customerCart($customer_id)
    {
        $gst = 0;
        $cgst = 0;
        $sgst = 0;
        $addongst = 0;
        $addoncgst = 0;
        $addonsgst = 0;
        $gst_0 = 0;
        $cgst_0 = 0;
        $sgst_0 = 0;
        $gst_5 = 0;
        $cgst_2_5 = 0;
        $sgst_2_5 = 0;
        $gst_12 = 0;
        $cgst_6 = 0;
        $sgst_6 = 0;
        $gst_18 = 0;
        $cgst_9 = 0;
        $sgst_9 = 0;
        $gst_28 = 0;
        $cgst_14 = 0;
        $sgst_14 = 0;

        $cart = Cart::where('customer_id', $customer_id)->with('products.oneImage', 'products', 'products.subCategory', 'size', 'flavour', 'products.variation', 'products.tax','products.unit', 'variations.weight')->get();
        $cartAddons = CartAddons::with('tax')->where('customer_id', $customer_id)->get();

        $cart = $this->calculateDiscount($cart, $customer_id);

        foreach ($cart as $row) {
            $overAllGstPercent = $row->products->tax->tax_percentage;
            $cGstPercent = round($overAllGstPercent/2, 2);
            $sGstPercent = round($overAllGstPercent/2, 2);
            $gst +=  ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
            $cgst += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
            Log::info('cgst = '.$cgst);
            $sgst += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
            switch ($overAllGstPercent) {
                case '0':
                    $gst_0  += ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
                    $cgst_0 += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
                    $sgst_0 += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
                    break;
                case '5':
                    $gst_5    += ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
                    $cgst_2_5 += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
                    $sgst_2_5 += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
                    break;
                case '12':
                    $gst_12 += ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
                    $cgst_6 += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
                    $sgst_6 += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
                    break;
                case '18':
                    $gst_18 += ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
                    $cgst_9 += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
                    $sgst_9 += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
                    break;
                case '28':
                    $gst_28 +=  ($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent);
                    $cgst_14 += ($row->product_total * $cGstPercent)/(100 + $overAllGstPercent);
                    $sgst_14 += ($row->product_total * $sGstPercent)/(100 + $overAllGstPercent);
                    break;
            }
        }


        if($cartAddons){
            foreach ($cartAddons as $row) {
                $addonoverAllGstPercent = $row->tax->tax_percentage;
                $addoncGstPercent = round($addonoverAllGstPercent/2, 2);
                $addonsGstPercent = round($addonoverAllGstPercent/2, 2);
                $addongst  += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                $addoncgst += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                $addonsgst += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);

                switch ($addonoverAllGstPercent) {
                    case '0':
                        $gst_0  += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                        $cgst_0 += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                        $sgst_0 += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);
                        break;
                    case '5':
                        $gst_5    += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                        $cgst_2_5 += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                        $sgst_2_5 += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);
                        break;
                    case '12':
                        $gst_12 += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                        $cgst_6 += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                        $sgst_6 += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);
                         break;
                    case '18':
                        $gst_18 += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                        $cgst_9 += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                        $sgst_9 += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);
                        break;
                    case '28':
                        $gst_28  += ($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent);
                        $cgst_14 += ($row->total * $addoncGstPercent)/(100 + $addonoverAllGstPercent);
                        $sgst_14 += ($row->total * $addonsGstPercent)/(100 + $addonoverAllGstPercent);
                        break;
                }
            }
        }
        $value['split_gst'] = [
           'gst_0'=> ['gst_0'  => round($gst_0, 2), 'cgst_0'  => round($cgst_0, 2), 'sgst_0'  => round($sgst_0,2)],
            'gst_5'=>  ['gst_5'  =>round($gst_5, 2) , 'cgst_2_5'  => round($cgst_2_5, 2), 'sgst_2_5'  => round($sgst_2_5,2)],
            'gst_12'=>   ['gst_12'  => round($gst_12, 2), 'cgst_6'  => round($cgst_6, 2), 'sgst_6'  => round($sgst_6,2)],
            'gst_18'=>  ['gst_18' => round($gst_18, 2), 'cgst_9'  => round($cgst_9, 2), 'sgst_9'  => round($sgst_9,2)],
            'gst_28'=>  ['gst_28'  => round($gst_28, 2), 'cgst_14'  => round($cgst_14, 2), 'sgst_14'  => round($sgst_14, 2)]
        ];

        $value['gst'] = round($gst + $addongst, 2);
        $value['cgst'] = round($cgst + $addoncgst, 2) ;
        $value['sgst'] = round($sgst + $addonsgst, 2);
        $value['details'] = $cart;
        $value['addons'] = $cartAddons;
        if(!$value['details']) {
            $value = [];
        }
        return $this->response->jsonResponse(false, 'Cart Listed Successfully', $value, 201);
    }

    public function getTax($productId) {
        return Cart::where('customer_id', $this->customer_id)->sum('product_total');
    }

    //removing Cart
    public function removeFromCart($cart_id, $customer_id = null)
    {
        $removeCart = Cart::where('cart_id', $cart_id)->delete();
        if($removeCart){

            return $this->response->jsonResponse(false, 'Product Removed From Cart',  ['cart_count' => $this-> calculateCartCount($customer_id)] , 201);
        }else{
            return $this->response->jsonResponse(false, 'Error in Cart Removal','' , 205);
        }

    }

    public function removeFromAddonCart($customer_id, $id)
    {
        $removeAddon = CartAddons::where('id', $id)->delete();

        if($removeAddon){
            return $this->response->jsonResponse(false, "Addon's Removed From Cart",  ['cart_count' => $this-> calculateCartCount($customer_id)] , 201);
        }else{
            return $this->response->jsonResponse(false, 'Error in Cart Removal','' , 205);
        }

    }

    public function calculateCartCount($customer_id){
        $addonCount = CartAddons::where('customer_id', $customer_id)->get()->count();
        $cartCount = Cart::where('customer_id', $customer_id)->get()->count();
        return $addonCount + $cartCount;
    }

    public function editCart($cart_id, $size_id, $quantity) {
        $cart =  Cart::where('cart_id', $cart_id);
        $getCart = $cart->first();
        $updateCart = $cart->update([
            'product_size_id' => $size_id,
            'product_quantity' => $quantity,
            'product_total' => $getCart->product_price * $quantity
        ]);
        return $this->response->jsonResponse(false, 'Cart Updated Successfully', [], 201);
    }

    public function updateSizeCart($customer_id, $product_id, $cart_id, $size_id) {
        $fetchCart = Cart::where('customer_id', $customer_id)->where('product_id', $product_id)->where('product_size_id', $size_id)->exists();
        if(!$fetchCart) {
            Cart::where('cart_id', $cart_id)->update([
                'product_size_id' => $size_id
            ]);
        } else {
            Cart::where('cart_id', $cart_id)->delete();
        }

        return $this->response->jsonResponse(false, 'Size Updated Successfully', [], 201);
    }


    public function updateCartAddon($customer_id, $cart_id, $product_id, $addon_id, $all_addon_ids, $addon_quantity) {

         $fetchCart = Cart::where('customer_id', $customer_id)->where('product_id', $product_id)->where('cart_id', $cart_id)->exists();
        if($fetchCart) {
            $checkCartAddon = CartAddons::where('addon_id', $addon_id)->exists();
            if($checkCartAddon) {
                CartAddons::where('addon_id', $addon_id)->update([
                    'addon_quantity' => $addon_quantity
                ]);
            }else{
                $input['cart_id'] = $cart_id;
                $input['addon_id'] = $addon_id;
                $input['addon_quantity'] = $addon_quantity;
                $create = CartAddons::create($input);
            }

            Cart::where('cart_id', $cart_id)->update([
                'addon_ids' => $all_addon_ids == null ? null : $all_addon_ids,
            ]);
        return $this->response->jsonResponse(false, 'Cart Addon Updated Successfully', [], 201);
        }else{
            return $this->response->jsonResponse(true, 'Cart does not exist', [], 201);
        }

    }

    public function updateCartAddonQuantity($customer_id, $addon_id, $addon_quantity) {
        $updateCart = CartAddons::where('customer_id', $customer_id)->where('addon_id', $addon_id)->first();
        $price = $updateCart -> price;
        if($addon_quantity == 0){
            $this -> removeFromAddonCart($customer_id, $updateCart->id);
        }else{
            $result = $updateCart->update(['quantity' => $addon_quantity, 'total'=> $price * $addon_quantity]);
        }
        return $this->response->jsonResponse(false, 'Addon Quantity Updated Successfully',['cart_count' => $this-> calculateCartCount( $customer_id)], 201);
    }

    public function updateQuantityCart($cart_id, $quantity, $customer_id) {
        $getCart = Cart::where('cart_id', $cart_id)->first();
        if($quantity == 0){
            $this -> removeFromCart($cart_id);

        }else{
            $updateCart = Cart::where('cart_id', $cart_id)->update([
                'product_quantity' => $quantity,
                'product_total' => $getCart['product_discount_price'] * $quantity,
            ]);
        }

        return $this->response->jsonResponse(false, 'Quantity Updated Successfully',['cart_count' => $this-> calculateCartCount( $customer_id)], 201);
    }

    public function updateMessageOnCakeCart($cart_id, $message) {
        $getCart = Cart::where('cart_id', $cart_id)->first();
        $updateCart = Cart::where('cart_id', $cart_id)->update([
            'message_on_cake' => $message
        ]);
        return $this->response->jsonResponse(false, 'Message Updated Successfully',[], 201);
    }
}
