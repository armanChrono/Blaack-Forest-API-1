<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use App\Repositories\SmsRepository;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\CartAddons;
use App\Models\OrderProducts;
use App\Models\OrderAddress;
use App\Models\OrderAddons;
use App\Models\OrderGstMerge;
use Razorpay\Api\Api;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Log;
use Softon\Indipay\Facades\Indipay;
use Illuminate\Support\Str;
use App\Events\MyEvent;
use Carbon\Carbon;
use Exception;



class OrderController extends Controller
{
    public function __construct(ResponseRepository $response,  SmsRepository $sms)
    {
        $this->response = $response;
        $this->sms = $sms;
    }


    public function submitOrder(Request $request)
    {
        Log::info("request" .$request);

        try {
        $input = $request->all();
        if($input['delivery_mode'] == 'Door Step Delivery'){
            $validator = Validator::make($input, [
                'billing_mobile_number' => 'required',
                'billing_name' => 'required',
                'customer_address_id' => 'required',
                'customer_id' => 'required',
                'order_overall_totall' => 'required',
                'payment_mode' => 'required',
                'contact_mobile' => 'required',
                'expected_delivery' => 'required',
                'slot' => 'required',
                'delivery_mode' => 'required'
            ]);
        }else{
            $validator = Validator::make($input, [
                'billing_mobile_number' => 'required',
                'billing_name' => 'required',
                'customer_id' => 'required',
                'order_overall_totall' => 'required',
                'payment_mode' => 'required',
                'contact_mobile' => 'required',
                'expected_delivery' => 'required',
                'slot' => 'required',
                'delivery_mode' => 'required',
                'shop_id' => 'required',
            ]);
        }
        if($input['payment_mode'] === 'Online Payment') {
            $validator = Validator::make($input, [
                'razorpay_payment_id' => 'required',
            ]);
        }
        if ($validator->fails()) {
             return $this->response->jsonResponse(true, 'This Order Couldnt be Submitted', $validator->errors(), 401);
        }
        if($input['payment_mode'] === 'Online Payment') {
            $capturePayment = $this->capturePayment($input['razorpay_payment_id'], $input['order_overall_totall']);
            if($capturePayment){
                $successOrderId = $this-> processOrderSubmit($input);
            }else{
                return $this->response->jsonResponse(true, 'Error in capturing payments, Try Again','', 203);
            }
        }else{
            $successOrderId = $this-> processOrderSubmit($input);
        }
        $this->sms->orderSms($input['billing_name'], $input['billing_mobile_number'], $successOrderId);
        // $mailData = Order::with('orderedProducts','orderedProducts.productDetails','orderedProducts.flavour','orderedProducts.variation.weight','shops','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images')->where('order_id',  $successOrderId)->first();
        $mailData = Order::with('orderedProducts','orderedProducts.productDetails','orderedProducts.flavour','orderedProducts.variation.weight','shops','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images')->where('order_id',  $successOrderId)->first();
        $productName = $mailData['orderedProducts'][0]['productDetails'][0]['product_name'];
        $firstImage = $mailData['orderedProducts'][0]['productDetails'][0]['images'][0]['product_image'];
        $formatedInvoiceNo = $this->response->getInvoiceNo();
        if($input['payment_mode'] === 'Online Payment') {
            Order::where('order_id', $successOrderId)->update([
                'invoice_no' => $formatedInvoiceNo,
                'paid' => 1,
                'paid_at' => date('l Y-M-d g:i A'),
            ]);
        }
        if($input['email']){
            $result = (new MailController)->html_mail($mailData, 'Submitted');
        }

        $cartCount = $this-> calculateCartCount($input['customer_id']);
        Log::info("cartCount" .$cartCount);

        event(new MyEvent("New Order Placed", $productName, $firstImage, $successOrderId));

            return $this->response->jsonResponse(false, 'Order SuccessFully Placed', $cartCount, 201);
        } catch (\Exception $e) {
            return $this->response->jsonResponse(true, 'Something went wrong! Please try again.', $e->getMessage(), 201);
        }
    }
    public function processOrderSubmit($input){
        $order = Order::create([
            'customer_id' => $input['customer_id'],
            'billing_name' => $input['billing_name'],
            'billing_mobile_number' => $input['billing_mobile_number'],
            'order_sub_total' => $input['order_sub_total'],
            'region_id' => $input['region_id'],
            'razorpay_payment_id' => $input['razorpay_payment_id'],
            'deliver_fee' => $input['deliver_fee'],
            'cgst_tax' => $input['cgst_tax'],
            'cgst_value' => $input['cgst_value'],
            'sgst_tax' => $input['sgst_tax'],
            'sgst_value' => $input['sgst_value'],
            'promo_code' => $input['promo_code'],
            'promo_code_value' => $input['promo_code_value'],
            'order_overall_totall' => $input['order_overall_totall'],
            'payment_mode' => $input['payment_mode'],
            'order_status' => 'Submitted',
            'contact_mobile' => $input['contact_mobile'],
            'email' => $input['email'],
            'expected_delivery' => $input['expected_delivery'],
            'slot' => $input['slot'],
            'delivery_mode' => $input['delivery_mode'],
            'shop_id' => $input['shop_id'],
            'order_submitted_at' => date('l Y-M-d g:i A'),
            'order_tracking_id' => $this->response->generateOrderID(),

        ]);
         $getCart = Cart::where('customer_id', $input['customer_id'])->with('products.images', 'products.tax')->get();
        foreach($getCart as $cart) {
            OrderProducts::create([
                'order_id' => $order['order_id'],
                'product_id' => $cart['product_id'],
                'product_price' => $cart['product_price'],
                'product_discount_price' => $cart['product_discount_price'],
                'product_quantity' => $cart['product_quantity'],
                'product_total' => $cart['product_total'],
                'product_size_id' => $cart['product_size_id'],
                'flavour_id' => $cart['flavour_id'],
                'variation_id' => $cart['variation_ids'],
                'egg_or_eggless' => $cart['eggless'],
                'message_on_cake' => $cart['message_on_cake']
            ]);
        }
         $getAddonCart = CartAddons::where('customer_id', $input['customer_id'])->get();
            foreach($getAddonCart as $addon) {
                OrderAddons::create([
                    'order_id' => $order['order_id'],
                    'addon_id' => $addon['addon_id'],
                    'customer_id' => $addon['customer_id'],
                    'product_name' => $addon['product_name'],
                    'image' => $addon['image'],
                    'price' => $addon['price'],
                    'quantity' => $addon['quantity'],
                    'total' => $addon['total'],
                    'hsn' => $addon['hsn'],
                    'tax_id' => $addon['tax_id']
                ]);
            }
            $gstMerge = $this-> gstMerge($getCart, $getAddonCart, $order['order_id'],  $input['delivery_without_gst']);

         if($input['delivery_mode'] == 'Door Step Delivery'){
            $address = CustomerAddress::find($input['customer_address_id']);
            OrderAddress::create([
                'order_id' => $order['order_id'],
                'address_pincode' => $address['address_pincode'],
                'billing_name' => $address['billing_name'],
                'billing_mobile' => $address['billing_mobile'],
                'doorNo' => $address['doorNo'],
                'street' => $address['street'],
                'area' => $address['area'],
                'address_locality_town' => $address['address_locality_town'],
                'address_city_district' => $address['address_city_district'],
                'address_state' => $address['address_state'],
                'address_type' => $address['address_type']
            ]);
        }
         $clearCart = Cart::where('customer_id', $input['customer_id'])->delete();
         $clearAddonCart = CartAddons::where('customer_id', $input['customer_id'])->delete();
         return $order['order_id'];
    }
    public function gstMerge($cart, $cartAddons, $orderId, $deliveryWithOutGst){
        $gst = 0;
        $addongst = 0;
        $gst_0 = 0;
        $gst_5 = 0;
        $gst_12 = 0;
        $gst_18 = 0;
        $gst_28 = 0;

        foreach ($cart as $row) {
            $overAllGstPercent = $row->products->tax->tax_percentage;
            $cGstPercent = round($overAllGstPercent/2, 2);
            $sGstPercent = round($overAllGstPercent/2, 2);
            $gst +=  round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
            switch ($overAllGstPercent) {
                case '0':
                    $gst_0 +=  round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
                    break;
                case '5':
                    $gst_5 +=  round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
                    break;
                case '12':
                    $gst_12 += round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
                    break;
                case '18':
                    $gst_18 += round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
                    break;
                case '28':
                    $gst_28 +=  round(($row->product_total * $overAllGstPercent)/(100 + $overAllGstPercent), 2);
                    break;
            }
        }
        if($cartAddons){
            foreach ($cartAddons as $row) {
                $addonoverAllGstPercent = $row->tax->tax_percentage;
                $addoncGstPercent = round($addonoverAllGstPercent/2, 2);
                $addonsGstPercent = round($addonoverAllGstPercent/2, 2);
                $addongst +=  round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);

                switch ($addonoverAllGstPercent) {
                    case '0':
                        $gst_0 +=  round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);
                        break;
                    case '5':
                        $gst_5 +=    round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);
                        break;
                    case '12':
                        $gst_12 += round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);
                         break;
                    case '18':
                        $gst_18 += round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);
                        break;
                    case '28':
                        $gst_28 +=  round(($row->total * $addonoverAllGstPercent)/(100 + $addonoverAllGstPercent), 2);
                        break;
                }
            }
        }

        OrderGstMerge::create([
            'order_id' => $orderId,
            'gst_5' => $gst_5,
            'gst_12' => $gst_12,
            'gst_18' => $gst_18,
            'gst_28' => $gst_28,
            'shipping_gst_18' => $deliveryWithOutGst
        ]);
    }
    public function calculateCartCount($customer_id){
        $addonCount = CartAddons::where('customer_id', $customer_id)->get()->count();
        $cartCount = Cart::where('customer_id', $customer_id)->get()->count();
        return $addonCount + $cartCount;
    }
    public function listCustomerOrders($customer_id) {
        return $this->response->jsonResponse(false, 'Customer Order Listed SuccessFully', Order::where('customer_id', $customer_id)->where('order_status', '!=', 'Delivered')->with('region','region.City','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'orderedProducts.productDetails.variation')->latest()->get(), 201);
    }

    public function listCustomerOrderHistory($customer_id) {
        return $this->response->jsonResponse(false, 'Customer Order Listed SuccessFully', Order::where('customer_id', $customer_id)->where('order_status', 'Delivered')->with('region','region.City','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'orderedProducts.productDetails.variation')->latest()->get(), 201);
    }

    public function getOrderDetails($order_id) {
        return $this->response->jsonResponse(false, 'Order Details Fetched SuccessFully', Order::where('order_id', $order_id)->with('region','region.City','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images','orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.size', 'orderedProducts.flavour', 'orderedProducts.variation', 'orderedProducts.variation.weight',  'orderedProducts.variation.weight.unit','dispatchOrders', 'shops')->first(), 201);
    }

    public function listAllOrders() {
        return $this->response->jsonResponse(false, 'All Customer Order Listed SuccessFully', Order::with('region','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'customerDetails', 'shops')->where('order_status', '!=', 'Cancelled')->where('order_status', '!=', 'Delivered')->latest()->get(), 201);
    }
    public function listAllDeliveredOrders() {
        return $this->response->jsonResponse(false, 'All Customer Delivered Order Listed SuccessFully', Order::with('region','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'customerDetails', 'shops')->where('order_status', 'Delivered')->latest()->get(), 201);
    }

    public function listCancelledOrders() {
        return $this->response->jsonResponse(false, 'Cancelled Order Listed SuccessFully', Order::with('region','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'customerDetails')->where('order_status', 'Cancelled')->latest()->get(), 201);
    }

       public function orderStatusUpdate($order_id, $status) {
        $order = Order::where('order_id', $order_id);
        $orderStatus = $order->first();
        if($status === 'Confirmed') {
            $order->update([
                'order_status' => $status,
                'order_processed_at' => date('l Y-M-d g:i A'),
                'order_shipped_at' => null,
                'order_delivered_at' => null
            ]);
        } else if ($status === 'Dispatched') {
            $order->update([
                'order_status' => $status,
                'order_shipped_at' => date('l Y-M-d g:i A'),
                'order_delivered_at' => null
            ]);
        } else if ($status === 'ReadyToDelivered') {
            if($orderStatus['order_status'] == 'Confirmed'){
                $order->update([
                    'order_status' => $status,
                    'order_shipped_at' => date('l Y-M-d g:i A'),
                    'order_delivered_at' => null,
                    'skip_dispatch' => 1
                ]);
            }else{
                $order->update([
                    'order_status' => $status,
                    'order_shipped_at' => date('l Y-M-d g:i A'),
                    'order_delivered_at' => null
                ]);
            }

        } else if ($status === 'Delivered') {
            $order->update([
                'order_status' => $status,
                'order_delivered_at' => date('l Y-M-d g:i A')
            ]);
        }
        if(!config('app.debug')){
            Log::info("Debug mode : False");
            $this->sms->orderSms("arman", "7667762218");
        }

        if($status === 'Delivered'){
            $mailData = Order::where('order_id', $order_id)->with('region.City', 'region.state','orderedAddress', 'orderedAddons', 'orderedAddons.tax' ,'orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.flavour', 'orderedProducts.variation.weight', 'orderGstMerge', 'shops', 'orderedProducts.productDetails.images')->first();
            // event(new MyEvent("Driver Delivered", "Please close the order", '', ''));

        }else{
             $mailData = Order::with('orderedProducts','orderedProducts.productDetails','orderedProducts.flavour','orderedProducts.variation.weight','shops','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images')->where('order_id', $order_id)->first();
        }

        $result = (new MailController)->html_mail($mailData, $status);

        return $this->response->jsonResponse(false, 'Order Updated SuccessFully', [], 201);
    }
    public function orderStatusUpdateWithReason($order_id, $status, $done_by, $reason=null) {
        $order = Order::where('order_id', $order_id);
       if ($status === 'Hold') {
            $order->update([
                'hold' => 1,
                'hold_reason' => $reason,
                'hold_by' => $done_by,
                'order_hold_at' => date('l Y-M-d g:i A')
            ]);
        } else if ($status === 'Un-Hold') {
            $order->update([
                'hold' => 0,
                'order_unhold_at' => date('l Y-M-d g:i A')
            ]);
        }
        if(!config('app.debug')){
            Log::info("Debug mode : False");
            $this->sms->orderSms("arman", "7667762218");
        }

        $mailData = Order::with('orderedProducts','orderedProducts.productDetails','orderedProducts.flavour','orderedProducts.variation.weight','shops','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images')->where('order_id', $order_id)->first();
        $result = (new MailController)->html_mail($mailData, $status);
        return $this->response->jsonResponse(false, 'Order Updated SuccessFully', [], 201);
    }

    public function cancelOrder($order_id, $cancelled_by, $reason) {
        $order = Order::where('order_id', $order_id);
        $update = $order->update(['order_status' => 'Cancelled', 'cancel_reason' => $reason, 'cancelled_by' => $cancelled_by, 'order_cancelled_at' => date('l Y-M-d g:i A')]);
    //  $this->refundAmount($order->select('razorpay_payment_id')->first()->razorpay_payment_id);
       $mailData = Order::where('order_id', $order_id)->with('region.City', 'region.state','orderedAddress', 'orderedAddons', 'orderedAddons.tax' ,'orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.flavour', 'orderedProducts.variation.weight', 'orderGstMerge', 'shops', 'orderedProducts.productDetails.images')->first();
        (new MailController)->html_mail($mailData, "Cancelled");
        return $this->response->jsonResponse(false, 'Order Cancelled SuccessFully', [], 201);
    }

    public function orderTrackingUpdate($order_id, $tracking_id) {
        return $this->response->jsonResponse(false, 'Order Tracking Id Updated SuccessFully', Order::where('order_id', $order_id)->update(['order_tracking_id' => $tracking_id]), 201);
    }

    public function searchOrder($search) {
        if ($search === "null") {
            return $this->response->jsonResponse(false, 'Order filtered Successfully', [], 201);
        }

        return $this->response->jsonResponse(false, 'Order filtered Successfully', Order::where('billing_name', 'LIKE', $search . '%')->orWhere('billing_mobile_number', 'LIKE', $search . '%')->orWhere('order_status', 'LIKE', $search . '%')->with('region','region.City','orderedAddress', 'orderedProducts.productDetails.images', 'orderedProducts.size', 'customerDetails')->latest()->get(), 201);
    }

    public function capturePayment($id, $total) {
        try {
            $api = new Api(env("RAZOR_PAY_KEY_ID"), env("RAZOR_PAY_SECRET"));
            $payment = $api->payment->fetch($id);
            $payment->capture(array('amount' => $total*100, 'currency' => 'INR'));
            return true;
        } catch (\Exception $e) {
            return  false;

        }

    }

    public function refundAmount($id) {
        $api = new Api(env("RAZOR_PAY_KEY_ID"), env("RAZOR_PAY_SECRET"));
        $payment = $api->payment->fetch($id);
        $payment->refund();
        return $this->response->jsonResponse(false, 'Amount Refund Processed Successfully', [], 201);
    }
	public function orderIdGenerate(Request $request){
		$api = new Api(env("RAZOR_PAY_KEY_ID"), env("RAZOR_PAY_SECRET"));
        $order = $api->order->create(array(
            'amount' => $request->amount,
            'currency' => 'INR'
        )); // Creates order
        if($order){
            return $this->response->jsonResponse(false, 'Razor Pay Order ID Generated Successfully', ['order_id' => $order['id']], 201);
        }else{
            return $this->response->jsonResponse(true, 'Razor Pay Order ID Generation Failure', '', 202);
        }

		}

}
