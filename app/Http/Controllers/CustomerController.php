<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\WishList;
use App\Models\Cart;
use App\Repositories\ResponseRepository;
use App\Repositories\SmsRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{

    public function __construct(ResponseRepository $response, SmsRepository $sms) {
        $this->response = $response;
        $this->sms = $sms;
    }

    public function orderSms() {
        return $this->sms->orderSms();
    }

    public function sendOTP($mobile) {
        $customer = Customer::where('customer_mobile', $mobile);
        $otp = $this->sms->sendOTP($mobile);
        if($customer->exists()) {
            $customer->update(['customer_otp' => $otp]);
        } else {
            Customer::create(['customer_mobile' => $mobile, 'customer_otp' => $otp]);
        }
        return $this->response->jsonResponse(false, 'OTP has been sent to Your Mobile Number', [], 201);
    }

    public function verifyOTP($mobile, $otp) {
        $customer = Customer::where('customer_mobile', $mobile)->first();
        if((int)$customer->customer_otp === (int)$otp) {
            $data = [
                'customer_id'=>$customer->customer_id,
                'customer_name' => $customer->customer_name,
                'customer_mobile'=>$customer->customer_mobile,
                'customer_email'=>$customer->customer_email,
                'token'=>$customer->createToken($customer)->plainTextToken,
                'wishlist'=> $this->getCustomerWishListId($customer->customer_id),
                'cartCount'=> app('App\Http\Controllers\CartController')->calculateCartCount( $customer->customer_id)

            ];
            return $this->response->jsonResponse(false, 'Hi '.$customer->customer_name.' - Welcome To Blaack Forest', $data, 201);
        } else {
            return $this->response->jsonResponse(true, 'You have entered a wrong otp', [], 201);
        }
    }

    public function login(Request $request) {
        $customer = Customer::orWhere('customer_mobile', $request->email_or_number)->orWhere('customer_email', $request->email_or_number)->where('active_status', 1)->first();
        if($customer) {
            if(Hash::check($request->password, $customer->customer_password)) {
                $data = [
                    'customer_id'=>$customer->customer_id,
                    'customer_name' => $customer->customer_name,
                    'customer_mobile'=>$customer->customer_mobile,
                    'customer_email'=>$customer->customer_email,
                    'token'=>$customer->createToken($customer)->plainTextToken,
                    'wishlist'=> $this->getCustomerWishListId($customer->customer_id)
                ];
                return $this->response->jsonResponse(false, 'Hi '.$customer->customer_name.' - Welcome To Blaack Forest',$data, 201);
            }
        }
        return $this->response->jsonResponse(true, 'Invalid Credential',[], 201);
    }

    public function register(Request $request) {
        $checkExisting = Customer::orWhere('customer_mobile', $request->customer_mobile)->orWhere('customer_email', $request->customer_email)->exists();
        if($checkExisting) {
            return $this->response->jsonResponse(true, 'Details Already Exists, Try to Login Directly', [], 201);
        }
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'customer_mobile' => 'required',
            'customer_email' => 'required',
            'customer_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Registration Failed', $validator->errors(), 401);
        }

        $input = $request->all();
        $input['customer_password'] = \Hash::make($request->customer_password);
        return $this->response->jsonResponse(false, 'User Created Successfully', Customer::create($input), 201);
    }

    public function getCustomerWishListId($customer_id) {
        return WishList::where('customer_id', $customer_id)->select('product_id')->get();
    }

    public function getCustomerDetails($customer_id) {
        return $this->response->jsonResponse(false, 'Customer Details Fetched Successfully', Customer::where('customer_id', $customer_id)->first(), 201);
    }

    public function getAllCustomerDetails() {
        return $this->response->jsonResponse(false, 'Customer Details Fetched Successfully', Customer::get(), 201);
    }

    public function editCustomerDetails(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'customer_name' => 'required',
            'customer_mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Please Fill Mandatory Fields', $validator->errors(), 401);
        }

        Customer::find($input['customer_id'])->update($input);
        return $this->response->jsonResponse(false, 'Your Details Updated SuccessFully', [], 201);
    }

    public function logout($customer_id) {
        return $this->response->jsonResponse(false, 'Customer Logged Out Successfully', Customer::where('customer_id', $customer_id)->get(), 201);
    }

    public function checkWishAndCart($customer_id, $product_id) {
        $data['cart'] = Cart::where('customer_id', $customer_id)->where('product_id', $product_id)->exists();
        $data['wishlist'] = WishList::where('customer_id', $customer_id)->where('product_id', $product_id)->exists();
        return $this->response->jsonResponse(false, 'Checked Wishlist and Cart For Customer', $data, 201);
    }
}
