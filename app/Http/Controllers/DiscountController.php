<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\discount;
use App\Models\Product;
use App\Models\CategoryDiscont;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ProductDiscont;
use App\Models\CustomerDiscont;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Log;


class DiscountController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'discount_name' => 'required',
            'discount_percentage' => 'required',
            'fromDate'=>'required',
            'toDate' => 'required'
        ];

        $this->storeRulesCategoryDiscount = [
            'category' => 'required',
            'discount_id' => 'required'
        ];

        $this->storeRulesProductDiscount = [
            'product' => 'required',
            'discount_id' => 'required'
        ];

        $this->storeRulesCustomerDiscount = [
            'customer' => 'required',
            'category' => 'required',
            'discount_id' => 'required'
        ];
    }

    public function createDiscount(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Discount', 'store'), discount::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function createDiscountCategory(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRulesCategoryDiscount);
        if($validate === true) {
            $categoryDiscount = CategoryDiscont::create($request->all());
            if( strpos($request->category, ',') !== false ) {
                foreach(explode(',', $request->category) as $productId) {
                   Product::where('category_id', $productId)->update(['category_discount'=> $categoryDiscount->category_discount_id ]);
                   Category::where('id', $productId)->update(['category_discount_id'=> $categoryDiscount->category_discount_id ]);
                }
            } elseif ($request->category) {
                Product::where('category_id', $request->category)->update(['category_discount'=> $categoryDiscount->category_discount_id ]);
                Category::where('id', $request->category)->update(['category_discount_id'=> $categoryDiscount->category_discount_id ]);

            }
            return $this->response->jsonResponse(false, $this->response->message('Discount Category', 'store'),$categoryDiscount , 200);
        } else {
            return $validate;
        }
    }

    public function createDiscountCustomer(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRulesCustomerDiscount);
        if($validate === true) {
            $customerDiscount = CustomerDiscont::create($request->all())->first();
            //return $productDiscount;
             if( strpos($request->customer, ',') !== false ) {
                 foreach(explode(',', $request->customer) as $productId) {
                    Customer::where('customer_id', $productId)->update(['customer_discount_id'=> $customerDiscount->customer_dis_id ]);
                 }
             } elseif ($request->customer) {
                Customer::where('customer_id', $request->customer)->update(['customer_discount_id'=> $customerDiscount->customer_dis_id ]);
             }
            return $this->response->jsonResponse(false, $this->response->message('Discount Customer', 'store'), $customerDiscount, 200);
        } else {
            return $validate;
        }
    }

    public function createDiscountProduct(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRulesProductDiscount);
        if($validate === true) {
             ProductDiscont::create($request->all());
           $productDiscount = ProductDiscont::latest('product_discount_id')->first();
           //return $productDiscount;
            if( strpos($request->product, ',') !== false ) {
                foreach(explode(',', $request->product) as $productId) {
                   Product::where('id', $productId)->update(['product_discount'=> $productDiscount->product_discount_id ]);
                }
            } elseif ($request->product) {
                Product::where('id', $request->product)->update(['product_discount'=> $productDiscount->product_discount_id ]);
            }

            // return $request->product;
            return $this->response->jsonResponse(false, $this->response->message('Product Category', 'store'), $productDiscount, 200);

        } else {
            return $validate;
        }
    }

    public function updateDiscountCategory(Request $request){
        $validate = $this->response->validate($request->all(), [
            'category_discount_id' => [
                'required'
            ],
            'category' => 'required',
            'discount_id' => 'required'
        ]);

        if($validate === true) {
            if( strpos($request->category, ',') !== false ) {
                foreach(explode(',', $request->category) as $productId) {
                   Product::where('category_id', $productId)->update(['category_discount'=> $request->category_discount_id ]);
                   Category::where('id', $productId)->update(['category_discount_id'=> $request->category_discount_id ]);
                }
            } elseif ($request->category) {
                Product::where('category_id', $request->category)->update(['category_discount'=> $request->category_discount_id ]);
                Category::where('id', $request->category)->update(['category_discount_id'=> $request->category_discount_id ]);

            }
            return $this->response->jsonResponse(false, $this->response->message('Discount Category', 'update'), CategoryDiscont::where('category_discount_id', $request->category_discount_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateDiscountProduct(Request $request){
        $validate = $this->response->validate($request->all(), [
            'product_discount_id' => [
                'required'
            ],
            'product' => 'required',
            'discount_id' => 'required'
        ]);

        if($validate === true) {
            Product::where('product_discount', $request->product_discount_id)->update(['product_discount'=> 0]);
            if( strpos($request->product, ',') !== false ) {
                foreach(explode(',', $request->product) as $productId) {
                   Product::where('id', $productId)->update(['product_discount'=> $request->product_discount_id ]);
                }
            } elseif ($request->product) {
                Product::where('id', $request->product)->update(['product_discount'=> $request->product_discount_id]);
            }
            return $this->response->jsonResponse(false, $this->response->message('Discount Product', 'update'), ProductDiscont::where('product_discount_id', $request->product_discount_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateDiscount(Request $request){
        $validate = $this->response->validate($request->all(), [
            'discount_id' => [
                'required'
            ],
            'discount_name' => 'required',
            'discount_percentage' => 'required',
            'fromDate'=>'required',
            'toDate' => 'required'
        ]);

        if($validate === true) {
            Log::info("discount ==> ".$request);
            return $this->response->jsonResponse(false, $this->response->message('discount', 'update'), discount::where('discount_id', $request->discount_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }


    public function updateDiscountCustomer(Request $request){
        $validate = $this->response->validate($request->all(), [
            'customer_dis_id'=> ['required'],
            'discount_id' => 'required',
            'category' => 'required',
            'customer' => 'required'
            
        ]);

        //return $request;

        if($validate === true) {
            if( strpos($request->customer, ',') !== false ) {
                foreach(explode(',', $request->customer) as $productId) {
                   Customer::where('customer_id', $productId)->update(['customer_discount_id'=> $request->customer_dis_id]);
                }
            } elseif ($request->customer) {
                Customer::where('customer_id', $request->customer)->update(['customer_discount_id'=> $request->customer_dis_id]);
            }
            return $this->response->jsonResponse(false, $this->response->message('Customer Discount', 'update'), customerDiscont::where('customer_dis_id', $request->customer_dis_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    

    public function getAllDiscountList(){
        return $this->response->jsonResponse(false, 'All Discounts Listed', discount::get(), 200);
    }

    public function getAllDiscountCategoryList(){
        return $this->response->jsonResponse(false, 'All Discounts Category Listed', CategoryDiscont::with('discount')->get(), 200);
    }

    public function getAllDiscountProductList(){
        return $this->response->jsonResponse(false, 'All Discounts Category Listed', ProductDiscont::with('discount')->get(), 200);
    }

    public function getAllDiscountCustomerList(){
        return $this->response->jsonResponse(false, 'All Discounts Category Listed', CustomerDiscont::with('discount')->get(), 200);
    }

    public function getAllActiveDiscountList(){
        return $this->response->jsonResponse(false, 'All Discounts Listed', discount::Where(['active_status' => 1])->get(), 200);
    }

    public function getAllActiveDiscountCategoryList(){
        return $this->response->jsonResponse(false, 'All Discounts Category Listed', CategoryDiscont::Where(['active_status' => 1])->with('discount')->get(), 200);
    }

    public function getAllActiveDiscountProductList(){
        return $this->response->jsonResponse(false, 'All Discounts Product Listed', ProductDiscont::Where(['active_status' => 1])->with('discount')->get(), 200);
    }

    public function getAllActiveDiscountCustomerList(){
        return $this->response->jsonResponse(false, 'All Discounts Customer Listed', CustomerDiscont::Where(['active_status' => 1])->with('discount')->get(), 200);
    }

    public function activateDiscount($discount_id)
    {
        $getSize = discount::where('discount_id', $discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'discount Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'discount Not Available', [], 201);
        }
    }

    public function activateDiscountCategory($category_discount_id)
    {
        $getSize = CategoryDiscont::where('category_discount_id', $category_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Category Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Category Not Available', [], 201);
        }
    }

    public function activateDiscountProduct($product_discount_id)
    {
        $getSize = ProductDiscont::where('product_discount_id', $product_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Product Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Product Not Available', [], 201);
        }
    }

    public function activateDiscountCustomer($customer_discount_id)
    {
        $getSize = CustomerDiscont::where('customer_dis_id', $customer_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Customer Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Customer Not Available', [], 201);
        }
    }

    public function deActivateDiscount($discount_id)
    {
        $getSize = discount::where('discount_id', $discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'discount De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'discount Not Available', [], 201);
        }
    }

    public function deActivateDiscountCategory($category_discount_id)
    {
        $getSize = CategoryDiscont::where('category_discount_id', $category_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Category Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Category Not Available', [], 201);
        }
    }

    public function deActivateDiscountProduct($product_discount_id)
    {
        $getSize = ProductDiscont::where('product_discount_id', $product_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Product Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Product Not Available', [], 201);
        }
    }

    public function deActivateDiscountCustomer($customer_discount_id)
    {
        $getSize = CustomerDiscont::where('customer_dis_id', $customer_discount_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Discounts Customer Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Discounts Customer Not Available', [], 201);
        }
    }

    public function deleteDiscount($id){
        $size = discount::where('discount_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('discount', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'discount Not Exists',[], 201);
    }

    public function deleteDiscountCategory($id){
        $size = CategoryDiscont::where('category_discount_id', $id)->first();
        if($size) {
            Product::where('category_discount', $id)->update(['category_discount'=> 0]);
            Category::where('category_discount_id', $id)->update(['category_discount_id'=> 0]);
            return $this->response->jsonResponse(false, $this->response->message('discount category', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Discounts Category Not Exists',[], 201);
    }

    public function deleteDiscountProduct($id){
        $size = ProductDiscont::where('product_discount_id', $id)->first();
        if($size) {
            Product::where('product_discount', $id)->update(['product_discount'=> 0]);
            return $this->response->jsonResponse(false, $this->response->message('discount Product', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Discounts Product Not Exists',[], 201);
    }

    public function deleteDiscountCustomer($id){
        $size = CustomerDiscont::where('customer_dis_id', $id)->first();
        if($size) {
            Customer::where('customer_discount_id', $id)->update(['customer_discount_id'=> 0]);
            return $this->response->jsonResponse(false, $this->response->message('discount Customer', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Discounts Customer Not Exists',[], 201);
    }
}
