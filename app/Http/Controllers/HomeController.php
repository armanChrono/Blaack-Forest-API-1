<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Banner;
use App\Models\MobileBanner;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\LinkProduct;
use App\Models\ProductDiscont;
use App\Models\CategoryDiscont;
use App\Models\CustomerDiscont;
use App\Models\Customer;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;
use App\Models\LatestArrival;


use Twilio\Rest\Client;
use App\Models\Voicenote;

class HomeController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }



    public function getVoicenotesss() {
        $user = Voicenote::get();
        return $this->response->jsonResponse(false, 'get voice notes Successfully',$user, 201);
    }


    public function getHeaderMenus() {
        return $this->response->jsonResponse(false, 'Header Menu Listed Successfully', Category::has('subCategories')->with('subCategories:category_id,sub_category_name,sub_category_slug')->select('id', 'category_name', 'category_slug')->where('active_status', 1)->get(), 201);
    }

    public function getBanners() {
        return Banner::where('active_status', 1)->select('banner_image')->latest()->get();
    }

    public function getMobileBanners() {
        return MobileBanner::where('active_status', 1)->select('mobile_banner_image')->latest()->get();
    }

    public function getCategories() {
        return Category::where('active_status', 1)->select('category_name', 'category_slug', 'category_image')->take(2)->get();
    }

    public function getSubCategories() {
        return SubCategory::where('active_status', 1)->select('sub_category_name', 'sub_category_slug', 'sub_category_image')->get();
    }

    public function getCategorySC() {
        return Category::has('subCategories')->with('subCategories:id,category_id,sub_category_name,sub_category_slug,sub_category_image')->select('id', 'category_name', 'category_slug', 'category_image')->where('active_status', 1)->get();
    }



    public function getHomeData() {
        $data['banners'] = $this->getBanners();
        $data['mobileBanners'] = $this->getMobileBanners();
        $data['getCategorySC'] = $this->getCategorySC();
        $data['latestArrival'] = LatestArrival::all();
        return $this->response->jsonResponse(false, 'Home Data Listed Successfully', $data, 201);

    }

     public function getProducts($slug, $regionId = null, $customerId = null) {
        $productId = [];
            if($customerId != 'null'){
                $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
            }else{
                $data['customer'] = '';
            }

        if($slug == "instant-cakes"){
            $subCategory = SubCategory::with('tags', 'products.images')->where('active_status', 1)->select('id', 'sub_category_name')->first();

            $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('sub_category_id', $subCategory->id)->where('preparation', 2)->where('active_status', 1)->orderBy('id','desc');


        }else{

            $subCategory = SubCategory::with('tags', 'products.images')->where('active_status', 1)->where('sub_category_slug', $slug)->select('id', 'sub_category_name')->first();
            $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('sub_category_id', $subCategory->id)->where('active_status', 1)->orderBy('id','desc');
        }
                    //return $subCategory;
        foreach ($subCategory->products as $product) {
            foreach ($product->region as $region) {
                if((int)$regionId === (int)$region['id']) {
                    array_push($productId, $product->id);
                }
            }
        }
        if( $regionId != "null"){
            $products->whereIn('id', $productId);
        }
        

         $data['products'] = $products->get();

 
         foreach ($data['products'] as $key => $product){
             if($product->product_discount != 0 && $product->category_discount == 0){
                foreach($product->variation as $vars => $variation){
                    $discount = ProductDiscont::with('discount')->where('product_discount_id', $product->product_discount)->first();
                     (int)$egg = $variation->egg;
                     (int)$eggLess = $variation->eggLess;
                     (int)$percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['p_discount'] = $discount->discount[0];
                     //egg
                     $eggDiscountPrice = round($egg * ($percentage/100));
                     $eggOriginalPrice = $egg - $eggDiscountPrice;
                     //eggLess
                     $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                     $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                     $variation['p_eggOriginalPrice'] = $eggOriginalPrice;
                     $variation['p_eggDiscountPrice'] = $eggDiscountPrice;
                     $variation['p_eggLessDiscountPrice'] = $eggLessDiscountPrice;
                     $variation['p_eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     if($vars == 0){
                         Product::where('id', $product->id)->update(['product_discount_price'=> $eggOriginalPrice]);
                     }
                 }
             }

             if($product->category_discount != 0){
                foreach($product->variation as $vars => $variation){
                    $discount = CategoryDiscont::with('discount')->where('category_discount_id', $product->category_discount)->first();
                     (int)$c_egg = $variation->egg;
                     (int)$c_eggLess = $variation->eggLess;
                     (int)$c_percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['c_discount'] = $discount->discount[0];
                     //egg
                     $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                     $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                     //eggLess
                     $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                     $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                     $variation['c_eggOriginalPrice'] = $c_eggOriginalPrice;
                     $variation['c_eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                     $variation['c_eggDiscountPrice'] = $c_eggDiscountPrice;
                     $variation['c_eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                     if($vars == 0){
                        Product::where('id', $product->id)->update(['product_discount_price'=> $c_eggOriginalPrice]);
                    }
                 }
             }

             if($customerId !='null'){
                if($data['customer']->customer_discount_id != 0){

                    foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                        //return $discount;
                         (int)$customer_egg = $variation->egg;
                         (int)$customer_eggLess = $variation->eggLess;
                         (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                         $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                         //egg
                         $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                         $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                         //eggLess
                         $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                         $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                         $variation['customer_eggOriginalPrice'] = $customer_eggOriginalPrice;
                         $variation['customer_eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                         $variation['customer_eggDiscountPrice'] = $customer_eggDiscountPrice;
                         $variation['customer_eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                         if($vars == 0){
                            Product::where('id', $product->id)->update(['product_discount_price'=> $customer_eggOriginalPrice]);
                         }

                     }
                 }
             }


        }


        return $this->response->jsonResponse(false, 'Product Listed Successfully',$data, 201);
    }

   public function getSuggestedProducts($regionId = null, $customerId = null) {
        $productId = [];
        if($customerId != 'null'){
            $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
        }else{
            $data['customer'] = '';
        }
         $suggestedProducts = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('active_status', 1)->where('suggested', 1)->orderBy('id','desc');
        $products = Product::where('active_status', 1)->orderBy('id','desc')->where('suggested', 1)->get();
             foreach ($products as $product) {

                $explode_id = array_map('intval', explode(',', $product->region_id));
                if (in_array($regionId, $explode_id)) {
                    array_push($productId, $product->id);
                }
            }
         if( $regionId != "null"){
            $suggestedProducts->whereIn('id', $productId);
        }

        $data['products'] = $suggestedProducts->get();


         foreach ($data['products'] as $key => $product){
             if($product->product_discount != 0 && $product->category_discount == 0){
                foreach($product->variation as $vars => $variation){
                    $discount = ProductDiscont::with('discount')->where('product_discount_id', $product->product_discount)->first();
                     (int)$egg = $variation->egg;
                     (int)$eggLess = $variation->eggLess;
                     (int)$percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['p_discount'] = $discount->discount[0];
                     //egg
                     $eggDiscountPrice = round($egg * ($percentage/100));
                     $eggOriginalPrice = $egg - $eggDiscountPrice;
                     //eggLess
                     $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                     $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                     $variation['p_eggOriginalPrice'] = $eggOriginalPrice;
                     $variation['p_eggDiscountPrice'] = $eggDiscountPrice;
                     $variation['p_eggLessDiscountPrice'] = $eggLessDiscountPrice;
                     $variation['p_eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     if($vars == 0){
                         Product::where('id', $product->id)->update(['product_discount_price'=> $eggOriginalPrice]);
                     }
                 }
             }

             if($product->category_discount != 0){
                foreach($product->variation as $vars => $variation){
                    $discount = CategoryDiscont::with('discount')->where('category_discount_id', $product->category_discount)->first();
                     (int)$c_egg = $variation->egg;
                     (int)$c_eggLess = $variation->eggLess;
                     (int)$c_percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['c_discount'] = $discount->discount[0];
                     //egg
                     $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                     $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                     //eggLess
                     $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                     $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                     $variation['c_eggOriginalPrice'] = $c_eggOriginalPrice;
                     $variation['c_eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                     $variation['c_eggDiscountPrice'] = $c_eggDiscountPrice;
                     $variation['c_eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                     if($vars == 0){
                        Product::where('id', $product->id)->update(['product_discount_price'=> $c_eggOriginalPrice]);
                    }
                 }
             }

             if($customerId !='null'){
                if($data['customer']->customer_discount_id != 0){

                    foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                        //return $discount;
                         (int)$customer_egg = $variation->egg;
                         (int)$customer_eggLess = $variation->eggLess;
                         (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                         $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                         //egg
                         $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                         $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                         //eggLess
                         $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                         $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                         $variation['customer_eggOriginalPrice'] = $customer_eggOriginalPrice;
                         $variation['customer_eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                         $variation['customer_eggDiscountPrice'] = $customer_eggDiscountPrice;
                         $variation['customer_eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                         if($vars == 0){
                            Product::where('id', $product->id)->update(['product_discount_price'=> $customer_eggOriginalPrice]);
                         }

                     }
                 }
             }


        }


        return $this->response->jsonResponse(false, 'Product Listed Successfully',$data, 201);
    }
    public function getSortDetailsForLatestArrivals($slug, $regionId, $customerId, $sortName, $flavour, $weight){
        $productId = [];
        if($customerId != 'null'){
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
        }else{
            $data['customer'] = '';
            $customerDisActiveStatus = 0;
            $customerDiscountId = 0;
        }
        $productsItems = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('active_status', 1)->get();
        $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('active_status', 1);

        foreach ($productsItems as $product) {
            foreach ($product['region'] as $region) {
                if((int)$regionId === (int)$region['id']) {
                    array_push($productId, $product->id);
                }
            }
        }



        $products->whereIn('id', $productId);
        if($sortName == 'priceHtoL'){
            $products->orderBy('product_price','desc');
         }

         if($sortName == 'priceLtoH'){
            $products->orderBy('product_price','asc');
         }

         if($sortName == 'bestSeller'){
            $products->where('best_selling', 1);
         }

         if($sortName == 'newArrival'){
            $products->where('new_product', 1);
         }


         if($flavour != 'null'){
            //  gettype($flavour);
            $integerIDs = array_map('intval', json_decode($flavour, true));
            $flavourProductIds=[];
            // foreach ($subCategory->products as $product) {
                foreach ($productsItems->flavour as $flavours) {
                    foreach ($integerIDs as $fla){
                        //return $fla;
                        if((int)$fla === (int)$flavours['id']) {
                            array_push($flavourProductIds, $product->id);
                        }
                    }

                }
            // }

            $products->whereIn('id', $flavourProductIds);
         }

         if($weight != 'null'){

            $weightIds = array_map('intval', json_decode($weight, true));

            $weightProductIds=[];
            // foreach ($subCategory->products as $product) {
                foreach ($product->weight_ids as $weights) {
                    foreach ($weightIds as $innerWeight){
                        //return $fla;
                        if((int)$innerWeight === (int)$weights['id']) {
                            array_push($weightProductIds, $product->id);
                        }
                    }

                }
            // }

            $products->whereIn('id', $weightProductIds);

         }


         $data['products'] = $products->get();

         //return $data;

         foreach ($data['products'] as $key => $product){
             $skip = $product->id."_FALSE";
            if($customerId !='null'){
                if( $customerDiscountId != 0 && $customerDisActiveStatus == 1 && $skip == $product->id."_FALSE"){
                    $skip = $product->id."_TRUE";
                    foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('active_status', 1)->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                            (int)$customer_egg = $variation->egg;
                            (int)$customer_eggLess = $variation->eggLess;
                            if($discount){
                                (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                                $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                             }else{
                                (int)$customer_percentage = 0;
                                $skip = $product->id."_FALSE";
                             }

                            //egg
                            $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                            $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                            //eggLess
                            $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                            $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                            $variation['discountType'] = "Customer Discount";
                            $variation['discountPercent'] = $customer_percentage;
                            $variation['eggOriginalPrice'] = $customer_eggOriginalPrice;
                            $variation['eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                            $variation['eggDiscountPrice'] = $customer_eggDiscountPrice;
                            $variation['eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                            // if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $customer_eggOriginalPrice]);
                            //  }



                     }
                 }
                }

                 if($product->product_discount != 0  &&  $skip == $product->id."_FALSE"){
                      $skip = $product->id."_TRUE";
                    foreach($product->variation as $vars => $variation){
                        $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->product_discount)->first();
                         (int)$egg = $variation->egg;
                         (int)$eggLess = $variation->eggLess;
                         if($discount){
                              (int)$percentage = $discount->discount[0]->discount_percentage;
                            $data['products'][$key]['p_discount'] = $discount->discount[0];
                         }else{
                              (int)$percentage = 0;
                            $skip = $product->id."_FALSE";
                          }

                         //egg
                         $eggDiscountPrice = round($egg * ($percentage/100));
                         $eggOriginalPrice = $egg - $eggDiscountPrice;
                         //eggLess
                         $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                         $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                         $variation['discountType'] = "Product Discount";
                         $variation['discountPercent'] = $percentage;
                         $variation['eggOriginalPrice'] = $eggOriginalPrice;
                         $variation['eggDiscountPrice'] = $eggDiscountPrice;
                         $variation['eggLessDiscountPrice'] = $eggLessDiscountPrice;
                         $variation['eggLessOriginalPrice'] = $eggLessOriginalPrice;

                        //  if($vars == 0){
                        //     Product::where('id', $product->id)->update(['product_price'=> $eggOriginalPrice]);
                        // }
                      }

                 }
                 if($product->category_discount != 0 && $skip == $product->id."_FALSE"){
                      $skip = $product->id."_TRUE";
                        foreach($product->variation as $vars => $variation){
                            $discountC = CategoryDiscont::with('discount')->where('active_status', 1)->where('category_discount_id', $product->category_discount)->first();

                             (int)$c_egg = $variation->egg;
                             (int)$c_eggLess = $variation->eggLess;
                             if($discountC){
                                 (int)$c_percentage = $discountC->discount[0]->discount_percentage;
                                 $data['products'][$key]['c_discount'] = $discountC->discount[0];
                             }else{
                                 (int)$c_percentage = 0;
                                $skip = $product->id."_FALSE";
                             }

                             //egg
                             $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                             $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                             //eggLess
                             $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                             $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                             $variation['discountType'] = "Category Discount";
                             $variation['discountPercent'] = $c_percentage;
                             $variation['eggOriginalPrice'] = $c_eggOriginalPrice;
                             $variation['eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                             $variation['eggDiscountPrice'] = $c_eggDiscountPrice;
                             $variation['eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                             //return $variationC;

                            //  if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $c_eggOriginalPrice]);
                            // }
                         }
                     }
                     if(
                        //  $product->category_discount == 0  && $product->product_discount == 0
                        // && $customerDiscountId == 0  &&
                         $skip == $product->id."_FALSE"){
                         $skip = $product->id."_TRUE";
                        foreach($product->variation as $vars => $variation){

                             (int)$c_egg = $variation->egg;
                             (int)$c_eggLess = $variation->eggLess;

                             $variation['discountType'] = "NO Discount";
                             $variation['discountPercent'] = null;
                             $variation['eggOriginalPrice'] = $c_egg;
                             $variation['eggLessOriginalPrice'] = $c_eggLess;
                             $variation['eggDiscountPrice'] = null;
                             $variation['eggLessDiscountPrice'] = null;

                             //return $variationC;

                            //  if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $c_egg]);
                            // }
                         }
                     }

             }

             return $data;

        }

    public function getSortDetails($slug, $regionId, $customerId, $sortName, $flavour, $weight){

        $productId = [];
        if($customerId != 'null'){
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
        }else{
            $data['customer'] = '';
            $customerDisActiveStatus = 0;
            $customerDiscountId = 0;
        }

        if($slug == 'instant-cakes'){
            $subCategory = SubCategory::with('tags', 'products.images')->where('active_status', 1)->select('id', 'sub_category_name')->first();

            $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('sub_category_id', $subCategory->id)->where('active_status', 1)->whereHas('variation', function($q){
                $q->where('egg_preparation','2');
            });
        }else{
            $category = Category::where('active_status', 1)->where('category_slug', $slug)->select('id', 'category_name')->first();
            if($category){
                $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('category_id', $category->id)->where('active_status', 1)->orderBy('sub_category_id','desc');
                $subCategory = $category;
            }else{
                $subCategory = SubCategory::with('tags', 'products.images')->where('active_status', 1)->where('sub_category_slug', $slug)->select('id', 'sub_category_name')->first();
                $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('sub_category_id', $subCategory->id)->where('active_status', 1);
            }


        }

        foreach ($subCategory->products as $product) {
            foreach ($product->region as $region) {
                if((int)$regionId === (int)$region['id']) {
                    array_push($productId, $product->id);
                }
            }
        }

        $products->whereIn('id', $productId);

         if($sortName == 'priceHtoL'){
            $products->orderBy('product_price','desc');
         }

         if($sortName == 'priceLtoH'){
            $products->orderBy('product_price','asc');
         }

         if($sortName == 'bestSeller'){
            $products->where('best_selling', 1);
         }

         if($sortName == 'newArrival'){
            $products->where('new_product', 1);
         }

         if($flavour != 'null'){
            //  gettype($flavour);
            $integerIDs = array_map('intval', json_decode($flavour, true));
            $flavourProductIds=[];
            foreach ($subCategory->products as $product) {
                foreach ($product->flavour as $flavours) {
                    foreach ($integerIDs as $fla){
                        if((int)$fla === (int)$flavours['id']) {
                            array_push($flavourProductIds, $product->id);
                        }
                    }

                }
            }

            $products->whereIn('id', $flavourProductIds);
         }

         if($weight != 'null'){

            $weightIds = array_map('intval', json_decode($weight, true));

            $weightProductIds=[];
            foreach ($subCategory->products as $product) {
                foreach ($product->weight_ids as $weights) {
                    foreach ($weightIds as $innerWeight){
                        //return $fla;
                        if((int)$innerWeight === (int)$weights['id']) {
                            array_push($weightProductIds, $product->id);
                        }
                    }

                }
            }

            $products->whereIn('id', $weightProductIds);

         }


         $data['products'] = $products->get();

         //return $data;

         foreach ($data['products'] as $key => $product){
             $skip = $product->id."_FALSE";
            if($customerId !='null'){
                if( $customerDiscountId != 0 && $customerDisActiveStatus == 1 && $skip == $product->id."_FALSE"){
                    $skip = $product->id."_TRUE";
                     foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('active_status', 1)->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                            (int)$customer_egg = $variation->egg;
                            (int)$customer_eggLess = $variation->eggLess;
                            if($discount){
                                (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                                $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                             }else{
                                (int)$customer_percentage = 0;
                                $skip = $product->id."_FALSE";
                             }

                            //egg
                            $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                            $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                            //eggLess
                            $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                            $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                            $variation['discountType'] = "Customer Discount";
                            $variation['discountPercent'] = $customer_percentage;
                            $variation['eggOriginalPrice'] = $customer_eggOriginalPrice;
                            $variation['eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                            $variation['eggDiscountPrice'] = $customer_eggDiscountPrice;
                            $variation['eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                            // if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $customer_eggOriginalPrice]);
                            //  }

                     }
                 }
                }

                 if($product->product_discount != 0  &&  $skip == $product->id."_FALSE"){
                      $skip = $product->id."_TRUE";
                     foreach($product->variation as $vars => $variation){
                        // $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->product_discount)->where('product', $product->id)->first();
                        $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->product_discount)->first();                         (int)$egg = $variation->egg;
                         (int)$eggLess = $variation->eggLess;
                         if($discount){
                              (int)$percentage = $discount->discount[0]->discount_percentage;
                            $data['products'][$key]['p_discount'] = $discount->discount[0];
                         }else{
                              (int)$percentage = 0;
                            $skip = $product->id."_FALSE";
                          }

                         //egg
                         $eggDiscountPrice = round($egg * ($percentage/100));
                         $eggOriginalPrice = $egg - $eggDiscountPrice;
                         //eggLess
                         $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                         $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                         $variation['discountType'] = "Product Discount";
                         $variation['discountPercent'] = $percentage;
                         $variation['eggOriginalPrice'] = $eggOriginalPrice;
                         $variation['eggDiscountPrice'] = $eggDiscountPrice;
                         $variation['eggLessDiscountPrice'] = $eggLessDiscountPrice;
                         $variation['eggLessOriginalPrice'] = $eggLessOriginalPrice;

                        //  if($vars == 0){
                        //     Product::where('id', $product->id)->update(['product_price'=> $eggOriginalPrice]);
                        // }
                      }

                 }
                 if($product->category_discount != 0 && $skip == $product->id."_FALSE"){
                      $skip = $product->id."_TRUE";
                         foreach($product->variation as $vars => $variation){
                            $discountC = CategoryDiscont::with('discount')->where('active_status', 1)->where('category_discount_id', $product->category_discount)->first();

                             (int)$c_egg = $variation->egg;
                             (int)$c_eggLess = $variation->eggLess;
                             if($discountC){
                                 (int)$c_percentage = $discountC->discount[0]->discount_percentage;
                                 $data['products'][$key]['c_discount'] = $discountC->discount[0];
                             }else{
                                 (int)$c_percentage = 0;
                                $skip = $product->id."_FALSE";
                             }

                             //egg
                             $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                             $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                             //eggLess
                             $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                             $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                             $variation['discountType'] = "Category Discount";
                             $variation['discountPercent'] = $c_percentage;
                             $variation['eggOriginalPrice'] = $c_eggOriginalPrice;
                             $variation['eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                             $variation['eggDiscountPrice'] = $c_eggDiscountPrice;
                             $variation['eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                             //return $variationC;

                            //  if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $c_eggOriginalPrice]);
                            // }
                         }
                     }

                    //  if($product->category_discount == 0  && $product->product_discount == 0
                    //     && $customerDiscountId == 0  && $skip == $product->id."_FALSE"){
                        if( $skip == $product->id."_FALSE"){
                         $skip = $product->id."_TRUE";
                         foreach($product->variation as $vars => $variation){

                             (int)$c_egg = $variation->egg;
                             (int)$c_eggLess = $variation->eggLess;

                             $variation['discountType'] = "NO Discount";
                             $variation['discountPercent'] = null;
                             $variation['eggOriginalPrice'] = $c_egg;
                             $variation['eggLessOriginalPrice'] = $c_eggLess;
                             $variation['eggDiscountPrice'] = null;
                             $variation['eggLessDiscountPrice'] = null;

                             //return $variationC;

                            //  if($vars == 0){
                            //     Product::where('id', $product->id)->update(['product_price'=> $c_egg]);
                            // }
                         }
                     }
             }

             return $data;

        }


    public function sortProducts($slug, $regionId, $customerId = null, $sortName=null, $flavour=null, $weight= null){

        if($slug == "latest-arrivals"){
            $data = $this->getSortDetailsForLatestArrivals($slug, $regionId, $customerId, $sortName, $flavour, $weight);
        }else{
            $data = $this->getSortDetails($slug, $regionId, $customerId, $sortName, $flavour, $weight);
        }
        return $this->response->jsonResponse(false, 'Product Listed Successfully',$data, 201);

    }

    public function filterProducts($slug, $tag_id = null, $from_price = null, $to_price = null) {


        $getSc = SubCategory::with('tags', 'products.images')->where('active_status', 1)->where('sub_category_slug', $slug)->first();
        return $getSc;
        $getProducts = Product::with('firstImage')->where('sub_category_id', $getSc->id)->where('active_status', 1)->select('id', 'sub_category_id', 'product_name', 'product_slug', 'product_price', 'product_discount_price','product_sizes');
        if($tag_id !== "null") {
            $productId = [];
            foreach ($getSc->products as $product) {
                foreach ($product->product_tags as $prodTag) {
                    if((int)$tag_id === (int)$prodTag['id']) {
                        array_push($productId, $product->id);
                    }
                }
            }
            $getProducts->whereIn('id', $productId);
        }

        if($from_price !== "null" || $to_price !== "null") {
            $getProducts->whereBetween('product_discount_price', [(int)$from_price, (int)$to_price]);
        }

        $data = [];
        $data['sub_category'] = SubCategory::with('tags')->where('active_status', 1)->where('sub_category_slug', $slug)->select('id', 'category_id', 'sub_category_name', 'sub_category_slug')->first();
        $data['products'] = $getProducts->get();
        return $this->response->jsonResponse(false, 'Product Filtered Successfully', $data, 201);
    }

    // public function getProductDetails($product_slug, $customerId=null)
    // {

    //     if($customerId != 'null'){
    //         $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
    //     }else{
    //         $data['customer'] = '';
    //     }

    //     $product = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('product_slug', $product_slug)->first();



    //         //product discount
    //         if($product->product_discount != 0 && $product->category_discount == 0){
    //            foreach($product->variation as $vars => $variation){
    //                $discount = ProductDiscont::with('discount')->where('product_discount_id', $product->product_discount)->first();
    //                 (int)$egg = $variation->egg;
    //                 (int)$eggLess = $variation->eggLess;
    //                 (int)$percentage = $discount->discount[0]->discount_percentage;
    //                 $product->p_discount = $discount->discount[0];
    //                 //egg
    //                 $eggDiscountPrice = round($egg * ($percentage/100));
    //                 $eggOriginalPrice = $egg - $eggDiscountPrice;
    //                 //eggLess
    //                 $eggLessDiscountPrice = round($eggLess * ($percentage/100));
    //                 $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

    //                 $variation['p_eggOriginalPrice'] = $eggOriginalPrice;
    //                 $variation['p_eggDiscountPrice'] = $eggDiscountPrice;
    //                 $variation['p_eggLessDiscountPrice'] = $eggLessDiscountPrice;
    //                 $variation['p_eggLessOriginalPrice'] = $eggLessOriginalPrice;

    //             }
    //         }

    //         if($product->category_discount != 0){
    //            foreach($product->variation as $vars => $variation){
    //                $discount = CategoryDiscont::with('discount')->where('category_discount_id', $product->category_discount)->first();
    //                 (int)$c_egg = $variation->egg;
    //                 (int)$c_eggLess = $variation->eggLess;
    //                 (int)$c_percentage = $discount->discount[0]->discount_percentage;
    //                 $product->c_discount = $discount->discount[0];
    //                 //egg
    //                 $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
    //                 $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
    //                 //eggLess
    //                 $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
    //                 $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

    //                 $variation['c_eggOriginalPrice'] = $c_eggOriginalPrice;
    //                  $variation['c_eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
    //                  $variation['c_eggDiscountPrice'] = $c_eggDiscountPrice;
    //                  $variation['c_eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

    //             }
    //         }

    //         // if($customerId !='null'){
    //            if( $customerId !='null' && $data['customer']->customer_discount_id != 0){

    //                foreach($product->variation as $vars => $variation){
    //                    $discount = CustomerDiscont::with('discount')->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
    //                    //return $discount;
    //                     (int)$customer_egg = $variation->egg;
    //                     (int)$customer_eggLess = $variation->eggLess;
    //                     (int)$customer_percentage = $discount->discount[0]->discount_percentage;
    //                     $product->customer_discount_add = $discount->discount[0];
    //                     //egg
    //                     $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
    //                     $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
    //                     //eggLess
    //                     $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
    //                     $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

    //                     $variation['customer_eggOriginalPrice'] = $customer_eggOriginalPrice;
    //                      $variation['customer_eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
    //                      $variation['customer_eggDiscountPrice'] = $customer_eggDiscountPrice;
    //                      $variation['customer_eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;


    //                 }
    //             }else{
    //                 foreach($product->variation as $vars => $variation){

    //                      (int)$customer_egg = $variation->egg;
    //                      (int)$customer_eggLess = $variation->eggLess;
    //                      (int)$customer_percentage = 0;
    //                      //egg
    //                      $customer_eggDiscountPrice = round($customer_egg);
    //                      $customer_eggOriginalPrice = $customer_egg;
    //                      //eggLess
    //                      $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
    //                      $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

    //                       $variation['customer_eggOriginalPrice'] = $customer_egg;
    //                       $variation['customer_eggLessOriginalPrice'] = $customer_eggLess;


    //                  }
    //             }
    //        //  }

    //         $data['product']= $product;

    //     return $this->response->jsonResponse(false, 'Product Details Fetched Successfully', $data, 201);
    // }

    public function getProductDetails($product_slug, $customerId=null)
    {

        if($customerId != 'null'){
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
        }else{
            $data['customer'] = '';
            $customerDisActiveStatus = 0;
            $customerDiscountId = 0;
        }

        $product = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('product_slug', $product_slug)->first();
           $skip = $product->id."_FALSE";
           if($customerId !='null'){
               if(  $customerDiscountId != 0 && $customerDisActiveStatus == 1 && $skip == $product->id."_FALSE"){
                   $skip = $product->id."_TRUE";
                   foreach($product->variation as $vars => $variation){
                       $discount = CustomerDiscont::with('discount')->where('active_status', 1)->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                           (int)$customer_egg = $variation->egg;
                           (int)$customer_eggLess = $variation->eggLess;
                           if($discount){
                               (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                               $data['product']['customer_discount_add'] = $discount->discount[0];
                            }else{
                               (int)$customer_percentage = 0;
                               $skip = $product->id."_FALSE";
                            }

                           //egg
                           $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                           $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                           //eggLess
                           $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                           $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                           $variation['discountType'] = "Customer Discount";
                           $variation['discountPercent'] = $customer_percentage;
                           $variation['eggOriginalPrice'] = $customer_eggOriginalPrice;
                           $variation['eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                           $variation['eggDiscountPrice'] = $customer_eggDiscountPrice;
                           $variation['eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                    }
                }
               }

                if($product->product_discount != 0  &&  $skip == $product->id."_FALSE"){
                     $skip = $product->id."_TRUE";
                   foreach($product->variation as $vars => $variation){
                       $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->product_discount)->first();
                        (int)$egg = $variation->egg;
                        (int)$eggLess = $variation->eggLess;
                        if($discount){
                             (int)$percentage = $discount->discount[0]->discount_percentage;
                           $data['product']['p_discount'] = $discount->discount[0];
                        }else{
                             (int)$percentage = 0;
                           $skip = $product->id."_FALSE";
                         }

                        //egg
                        $eggDiscountPrice = round($egg * ($percentage/100));
                        $eggOriginalPrice = $egg - $eggDiscountPrice;
                        //eggLess
                        $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                        $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                        $variation['discountType'] = "Product Discount";
                        $variation['discountPercent'] = $percentage;
                        $variation['eggOriginalPrice'] = $eggOriginalPrice;
                        $variation['eggDiscountPrice'] = $eggDiscountPrice;
                        $variation['eggLessDiscountPrice'] = $eggLessDiscountPrice;
                        $variation['eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     }

                }
                if($product->category_discount != 0 && $skip == $product->id."_FALSE"){
                     $skip = $product->id."_TRUE";
                       foreach($product->variation as $vars => $variation){
                           $discountC = CategoryDiscont::with('discount')->where('active_status', 1)->where('category_discount_id', $product->category_discount)->first();
                            (int)$c_egg = $variation->egg;
                            (int)$c_eggLess = $variation->eggLess;
                            if($discountC){
                                 (int)$c_percentage = $discountC->discount[0]->discount_percentage;
                                $data['product']['c_discount'] = $discountC->discount[0];
                            }else{
                                (int)$c_percentage = 0;
                               $skip = $product->id."_FALSE";
                            }
                            //egg
                            $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                            $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                            //eggLess
                            $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                            $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                            $variation['discountType'] = "Category Discount";
                            $variation['discountPercent'] = $c_percentage;
                            $variation['eggOriginalPrice'] = $c_eggOriginalPrice;
                            $variation['eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                            $variation['eggDiscountPrice'] = $c_eggDiscountPrice;
                            $variation['eggLessDiscountPrice'] = $c_eggLessDiscountPrice;
                        }
                    }
                    if($product->category_discount == 0  && $product->product_discount == 0
                       && $customerDiscountId == 0  &&
                        $skip == $product->id."_FALSE"){
                        $skip = $product->id."_TRUE";
                       foreach($product->variation as $vars => $variation){

                            (int)$c_egg = $variation->egg;
                            (int)$c_eggLess = $variation->eggLess;

                            $variation['discountType'] = "NO Discount";
                            $variation['discountPercent'] = null;
                            $variation['eggOriginalPrice'] = $c_egg;
                            $variation['eggLessOriginalPrice'] = $c_eggLess;
                            $variation['eggDiscountPrice'] = null;
                            $variation['eggLessDiscountPrice'] = null;

                            //return $variationC;
                        }
                    }

            $data['product'] = $product;

        return $this->response->jsonResponse(false, 'Product Details Fetched Successfully', $data, 201);
    }
    public function quickView($id) {
        return $this->response->jsonResponse(false, 'Product QuickView Listed Successfully', Product::with('images')->where('id', $id)->where('active_status', 1)->first(), 201);
    }

    public function getCategoryDetails($slug) {
        return $this->response->jsonResponse(false, 'Category Details Listed Successfully', Category::where('category_slug', $slug)->with('subCategories')->first(), 201);
    }

    public function getAllCategory() {
        return $this->response->jsonResponse(false, 'Listed All Category Successfully', Category::where('active_status', 1)->with('subCategoriesFour')->get(), 201);
    }

    public function getAllSubCategory() {
        return $this->response->jsonResponse(false, 'Listed All SubCategory Successfully', SubCategory::where('active_status', 1)->select('id', 'sub_category_name', 'sub_category_slug', 'sub_category_image')->get(), 201);
    }
    public function getProductsForUniversalSearch($regionId  = null) {
        $productId = [];
        // $category = Category::where('active_status', 1)->select('id', 'category_name')->all();
        // $products = DB::table('products')
        //                 ->with('region')
        //                 ->select('products.product_name', 'products.region', 'products.product_slug')
        //                 ->where('active_status', 1)
        //                 ->orderBy('id','desc')->get();
     $products = Product::with('firstImage')->where('active_status', 1)->orderBy('id','desc')->get();


        foreach ($products as $product) {
                foreach ($product->region as $region) {
                    if((int)$regionId === (int)$region['id']) {
                        array_push($productId, $product->id);
                    }
                }
        }
        $productsBasedOnRegion = DB::table('products')
                    ->select('products.product_name', 'products.id')
                    ->where('active_status', 1)
                    ->orderBy('id','desc');
        if( $regionId != "null"){
            $productsBasedOnRegion->whereIn('id', $productId);
        }

        $result = $productsBasedOnRegion->get();
        return $this->response->jsonResponse(false, 'Listed All Category Successfully', $result, 201);
     }

    public function getProductsForCategory($slug, $regionId = null, $customerId = null) {
        $productId = [];
            if($customerId != 'null'){
                $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
            }else{
                $data['customer'] = '';
            }

            $category = Category::where('active_status', 1)->where('category_slug', $slug)->select('id', 'category_name')->first();
            $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('category_id', $category->id)->where('active_status', 1)->orderBy('id','desc');

            foreach ($category->products as $product) {
            foreach ($product->region as $region) {
                if((int)$regionId === (int)$region['id']) {
                    array_push($productId, $product->id);
                }
            }
        }
        if( $regionId != "null"){
            $products->whereIn('id', $productId);
        }
        //  if($suggested == 1){
        //     $products->where('suggested', $suggested);
        //  }


         $data['products'] = $products->get();

         //return $data;

         foreach ($data['products'] as $key => $product){
             if($product->product_discount != 0 && $product->category_discount == 0){
                foreach($product->variation as $vars => $variation){
                    $discount = ProductDiscont::with('discount')->where('product_discount_id', $product->product_discount)->first();
                     (int)$egg = $variation->egg;
                     (int)$eggLess = $variation->eggLess;
                     (int)$percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['p_discount'] = $discount->discount[0];
                     //egg
                     $eggDiscountPrice = round($egg * ($percentage/100));
                     $eggOriginalPrice = $egg - $eggDiscountPrice;
                     //eggLess
                     $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                     $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                     $variation['p_eggOriginalPrice'] = $eggOriginalPrice;
                     $variation['p_eggDiscountPrice'] = $eggDiscountPrice;
                     $variation['p_eggLessDiscountPrice'] = $eggLessDiscountPrice;
                     $variation['p_eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     if($vars == 0){
                         Product::where('id', $product->id)->update(['product_discount_price'=> $eggOriginalPrice]);
                     }
                 }
             }

             if($product->category_discount != 0){
                foreach($product->variation as $vars => $variation){
                    $discount = CategoryDiscont::with('discount')->where('category_discount_id', $product->category_discount)->first();
                     (int)$c_egg = $variation->egg;
                     (int)$c_eggLess = $variation->eggLess;
                     (int)$c_percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['c_discount'] = $discount->discount[0];
                     //egg
                     $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                     $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                     //eggLess
                     $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                     $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                     $variation['c_eggOriginalPrice'] = $c_eggOriginalPrice;
                     $variation['c_eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                     $variation['c_eggDiscountPrice'] = $c_eggDiscountPrice;
                     $variation['c_eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                     if($vars == 0){
                        Product::where('id', $product->id)->update(['product_discount_price'=> $c_eggOriginalPrice]);
                    }
                 }
             }

             if($customerId !='null'){
                if($data['customer']->customer_discount_id != 0){

                    foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                        //return $discount;
                         (int)$customer_egg = $variation->egg;
                         (int)$customer_eggLess = $variation->eggLess;
                         (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                         $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                         //egg
                         $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                         $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                         //eggLess
                         $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                         $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                         $variation['customer_eggOriginalPrice'] = $customer_eggOriginalPrice;
                         $variation['customer_eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                         $variation['customer_eggDiscountPrice'] = $customer_eggDiscountPrice;
                         $variation['customer_eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                         if($vars == 0){
                            Product::where('id', $product->id)->update(['product_discount_price'=> $customer_eggOriginalPrice]);
                         }

                     }
                 }
             }


        }


        return $this->response->jsonResponse(false, 'Product Listed Successfully',$data, 201);
    }

    public function getLatestProducts($regionId = null, $customerId = null) {
        $productId = [];
            if($customerId != 'null'){
                $data['customer'] = Customer::with('customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
            }else{
                $data['customer'] = '';
            }

            $products = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('new_product', 1)->where('active_status', 1)->orderBy('id','desc')->get();
            foreach ($products as $product) {
                foreach ($product->region as $region) {
                    if((int)$regionId === (int)$region['id']) {
                        array_push($productId, $product->id);
                    }
                }
            }
        if( $regionId != "null"){
            $products->whereIn('id', $productId);
        }
        $latestProducts = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('new_product', 1)->where('active_status', 1)->orderBy('id','desc');
        $data['products'] = $latestProducts->get();

         foreach ($data['products'] as $key => $product){
             if($product->product_discount != 0 && $product->category_discount == 0){
                foreach($product->variation as $vars => $variation){
                    $discount = ProductDiscont::with('discount')->where('product_discount_id', $product->product_discount)->first();
                     (int)$egg = $variation->egg;
                     (int)$eggLess = $variation->eggLess;
                     (int)$percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['p_discount'] = $discount->discount[0];
                     //egg
                     $eggDiscountPrice = round($egg * ($percentage/100));
                     $eggOriginalPrice = $egg - $eggDiscountPrice;
                     //eggLess
                     $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                     $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                     $variation['p_eggOriginalPrice'] = $eggOriginalPrice;
                     $variation['p_eggDiscountPrice'] = $eggDiscountPrice;
                     $variation['p_eggLessDiscountPrice'] = $eggLessDiscountPrice;
                     $variation['p_eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     if($vars == 0){
                         Product::where('id', $product->id)->update(['product_discount_price'=> $eggOriginalPrice]);
                     }
                 }
             }

             if($product->category_discount != 0){
                foreach($product->variation as $vars => $variation){
                    $discount = CategoryDiscont::with('discount')->where('category_discount_id', $product->category_discount)->first();
                     (int)$c_egg = $variation->egg;
                     (int)$c_eggLess = $variation->eggLess;
                     (int)$c_percentage = $discount->discount[0]->discount_percentage;
                     $data['products'][$key]['c_discount'] = $discount->discount[0];
                     //egg
                     $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                     $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                     //eggLess
                     $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                     $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                     $variation['c_eggOriginalPrice'] = $c_eggOriginalPrice;
                     $variation['c_eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                     $variation['c_eggDiscountPrice'] = $c_eggDiscountPrice;
                     $variation['c_eggLessDiscountPrice'] = $c_eggLessDiscountPrice;

                     if($vars == 0){
                        Product::where('id', $product->id)->update(['product_discount_price'=> $c_eggOriginalPrice]);
                    }
                 }
             }

             if($customerId !='null'){
                if($data['customer']->customer_discount_id != 0){

                    foreach($product->variation as $vars => $variation){
                        $discount = CustomerDiscont::with('discount')->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                        //return $discount;
                         (int)$customer_egg = $variation->egg;
                         (int)$customer_eggLess = $variation->eggLess;
                         (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                         $data['products'][$key]['customer_discount_add'] = $discount->discount[0];
                         //egg
                         $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                         $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                         //eggLess
                         $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                         $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                         $variation['customer_eggOriginalPrice'] = $customer_eggOriginalPrice;
                         $variation['customer_eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                         $variation['customer_eggDiscountPrice'] = $customer_eggDiscountPrice;
                         $variation['customer_eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                         if($vars == 0){
                            Product::where('id', $product->id)->update(['product_discount_price'=> $customer_eggOriginalPrice]);
                         }

                     }
                 }
             }


        }


        return $this->response->jsonResponse(false, 'Product Listed Successfully',$data, 201);
    }
       public function getProductDetailsForId($productId, $customerId=null)
    {

        if($customerId != 'null'){
            $data['customer'] = Customer::with( 'customerDiscont', 'customerDiscont.discount')->where('customer_id', $customerId)->first();
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
        }else{
            $data['customer'] = '';
            $customerDisActiveStatus = 0;
            $customerDiscountId = 0;
        }

        $product = Product::with('firstImage','tax','subCategory','images', 'unit', 'variation','category', 'productDiscounts','productDiscounts.discount','category','category.categoryDiscount.discount')->where('id', $productId)->first();
           $skip = $product->id."_FALSE";
           if($customerId !='null'){
               if(  $customerDiscountId != 0 && $customerDisActiveStatus == 1 && $skip == $product->id."_FALSE"){
                   $skip = $product->id."_TRUE";
                   foreach($product->variation as $vars => $variation){
                       $discount = CustomerDiscont::with('discount')->where('active_status', 1)->where('customer_dis_id', $data['customer']->customer_discount_id)->first();
                           (int)$customer_egg = $variation->egg;
                           (int)$customer_eggLess = $variation->eggLess;
                           if($discount){
                               (int)$customer_percentage = $discount->discount[0]->discount_percentage;
                               $data['product']['customer_discount_add'] = $discount->discount[0];
                            }else{
                               (int)$customer_percentage = 0;
                               $skip = $product->id."_FALSE";
                            }

                           //egg
                           $customer_eggDiscountPrice = round($customer_egg * ($customer_percentage/100));
                           $customer_eggOriginalPrice = $customer_egg - $customer_eggDiscountPrice;
                           //eggLess
                           $customer_eggLessDiscountPrice = round($customer_eggLess * ($customer_percentage/100));
                           $customer_eggLessOriginalPrice = $customer_eggLess - $customer_eggLessDiscountPrice;

                           $variation['discountType'] = "Customer Discount";
                           $variation['discountPercent'] = $customer_percentage;
                           $variation['eggOriginalPrice'] = $customer_eggOriginalPrice;
                           $variation['eggLessOriginalPrice'] = $customer_eggLessOriginalPrice;
                           $variation['eggDiscountPrice'] = $customer_eggDiscountPrice;
                           $variation['eggLessDiscountPrice'] = $customer_eggLessDiscountPrice;

                    }
                }
               }

                if($product->product_discount != 0  &&  $skip == $product->id."_FALSE"){
                     $skip = $product->id."_TRUE";
                   foreach($product->variation as $vars => $variation){
                       $discount = ProductDiscont::with('discount')->where('active_status', 1)->where('product_discount_id', $product->product_discount)->first();
                        (int)$egg = $variation->egg;
                        (int)$eggLess = $variation->eggLess;
                        if($discount){
                             (int)$percentage = $discount->discount[0]->discount_percentage;
                           $data['product']['p_discount'] = $discount->discount[0];
                        }else{
                             (int)$percentage = 0;
                           $skip = $product->id."_FALSE";
                         }

                        //egg
                        $eggDiscountPrice = round($egg * ($percentage/100));
                        $eggOriginalPrice = $egg - $eggDiscountPrice;
                        //eggLess
                        $eggLessDiscountPrice = round($eggLess * ($percentage/100));
                        $eggLessOriginalPrice = $eggLess - $eggLessDiscountPrice;

                        $variation['discountType'] = "Product Discount";
                        $variation['discountPercent'] = $percentage;
                        $variation['eggOriginalPrice'] = $eggOriginalPrice;
                        $variation['eggDiscountPrice'] = $eggDiscountPrice;
                        $variation['eggLessDiscountPrice'] = $eggLessDiscountPrice;
                        $variation['eggLessOriginalPrice'] = $eggLessOriginalPrice;

                     }

                }
                if($product->category_discount != 0 && $skip == $product->id."_FALSE"){
                     $skip = $product->id."_TRUE";
                       foreach($product->variation as $vars => $variation){
                           $discountC = CategoryDiscont::with('discount')->where('active_status', 1)->where('category_discount_id', $product->category_discount)->first();
                            (int)$c_egg = $variation->egg;
                            (int)$c_eggLess = $variation->eggLess;
                            if($discountC){
                                 (int)$c_percentage = $discountC->discount[0]->discount_percentage;
                                $data['product']['c_discount'] = $discountC->discount[0];
                            }else{
                                (int)$c_percentage = 0;
                               $skip = $product->id."_FALSE";
                            }
                            //egg
                            $c_eggDiscountPrice = round($c_egg * ($c_percentage/100));
                            $c_eggOriginalPrice = $c_egg - $c_eggDiscountPrice;
                            //eggLess
                            $c_eggLessDiscountPrice = round($c_eggLess * ($c_percentage/100));
                            $c_eggLessOriginalPrice = $c_eggLess - $c_eggLessDiscountPrice;

                            $variation['discountType'] = "Category Discount";
                            $variation['discountPercent'] = $c_percentage;
                            $variation['eggOriginalPrice'] = $c_eggOriginalPrice;
                            $variation['eggLessOriginalPrice'] = $c_eggLessOriginalPrice;
                            $variation['eggDiscountPrice'] = $c_eggDiscountPrice;
                            $variation['eggLessDiscountPrice'] = $c_eggLessDiscountPrice;
                        }
                    }
                    if($product->category_discount == 0  && $product->product_discount == 0
                       && $customerDiscountId == 0  &&
                        $skip == $product->id."_FALSE"){
                        $skip = $product->id."_TRUE";
                       foreach($product->variation as $vars => $variation){

                            (int)$c_egg = $variation->egg;
                            (int)$c_eggLess = $variation->eggLess;

                            $variation['discountType'] = "NO Discount";
                            $variation['discountPercent'] = null;
                            $variation['eggOriginalPrice'] = $c_egg;
                            $variation['eggLessOriginalPrice'] = $c_eggLess;
                            $variation['eggDiscountPrice'] = null;
                            $variation['eggLessDiscountPrice'] = null;

                            //return $variationC;
                        }
                    }

            $data['product'] = $product;

        return $this->response->jsonResponse(false, 'Product Details Fetched Successfully', $data, 201);
    }
}
