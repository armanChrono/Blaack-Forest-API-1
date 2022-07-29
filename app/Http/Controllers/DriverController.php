<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Order;
use App\Models\DriverOrder;
use App\Models\ShopOrders;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Softon\Indipay\Facades\Indipay;
use App\Http\Controllers\MailController;
use App\Events\ShopNotification;
use App\Events\MyEvent;
use Illuminate\Support\Facades\Hash;



class DriverController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'driver_name' => 'required',
            'driver_mobile' => 'required',
            'city_id'=> 'required',
            'password'=> 'required'
            // 'location_details_id'=> 'required'
        ];
    }

    public function createDriver(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            return $this->response->jsonResponse(false, $this->response->message('Driver', 'store'), Driver::create($input), 200);
        } else {
            return $validate;
        }
    }

    public function createDriverOrder(Request $request){
        if($request->city_id){
            Order::where('order_id', $request->order_id)->update(['order_status'=> 'ReadyToDelivered']);
           $driver =  DriverOrder::create($request->all())->get();
            if($request->shop_id){
                ShopOrders::where('order_id', $request->order_id)->update(['driver_id'=>$request->driver_id]);
                event(new ShopNotification("Driver Assigned for #".$request->order_id, '', '', $request->shop_id));
            }
            return $this->response->jsonResponse(false, 'Order Pushed To Driver Team Successfully',$driver, 200);
        }else{
            return $this->response->jsonResponse(true, 'Order Pushed To Driver Team Failed',[], 201);
        }
    }

    public function updateDriver(Request $request){
        $validate = $this->response->validate($request->all(), [
            'driver_id' => [
                'required'
            ],
            'driver_name' => 'required',
            'driver_mobile' => 'required',
            'city_id'=> 'required'
        ]);

        if($validate === true) {
            $input = $request->all();
            if(isset($input['password'])){
              $input['password'] = Hash::make($input['password']);
            }
            return $this->response->jsonResponse(false, $this->response->message('Driver', 'update'), Driver::where('driver_id', $request->driver_id)->update($input), 200);
        } else {
            return $validate;
        }
    }


    public function getAllDriverList(){
        return $this->response->jsonResponse(false, 'All Driver Listed', Driver::get(), 200);
    }

    public function getAllDriverListByLocationId($id){
        return $this->response->jsonResponse(false, 'All Driver Listed', Driver::where('location_details_id', $id)->get(), 200);
    }
    public function getAllDriverListByRegionId($id){
            return $this->response->jsonResponse(false, 'All Driver Listed', Driver::where('city_id', $id)->get(), 200);
    }

    public function deleteDriver($id){
        $size = Driver::where('driver_id', $id)->first();
        if($size) {

            return $this->response->jsonResponse(false, $this->response->message('Driver', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Driver Not Exists',[], 201);
    }

    public function getDriverOrderByOrderId($id){
        return $this->response->jsonResponse(false, 'All Driver Orders Listed', DriverOrder::with('location', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation', 'order.orderedProducts.variation.weight','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images', 'driver', 'driver.city')->where('order_id', $id)->get(), 200);
    }

    public function getAllOrdersForDriver(Request $request){

        $validate = $this->response->validate($request->all(), [
            'driverId' => 'required',
            'operation' => 'required'
        ]);
        if($request->operation == "GET_DRIVER_ORDERS_OPERATION"){
            $result = DriverOrder::with('order', 'shop','orderedAddress')->where('driver_id', $request->driverId)->whereNull('delivered_at')->whereHas('Order',function($q) {
                $q->whereNotIn('order_status', ['Delivered', 'Cancelled']);
            })->get();
            if($result){
                return $this->response->jsonResponse(false, 'All Driver Orders Listed', $result, 200);
            }else{
                return $this->response->jsonResponse(false, 'No Orders found', '', 201);
            }
        }else{
            return $this->response->jsonResponse(true, 'Driver Operation Error',[], 202);
        }


    }



    public function orderPickedUpByDriver(Request $request){
        Log::info("orderPickedUpByDriver REQUEST : ".$request);

        $validate = $this->response->validate($request->all(), [
            'orderId' => 'required',
            'operation' => 'required'
        ]);

        if($request->operation == "PICK_UP_BY_DRIVER_OPERATION"){
            $driverOrder = DriverOrder::where('order_id', $request->orderId);
            if($driverOrder->exists()) {
                DriverOrder::where('order_id', $request->orderId)->update(['picked_up' => 1, 'picked_up_at'=>Carbon::now()]);
                event(new MyEvent("Order Picked Up By Driver - #".$request->orderId, 'Picked Up', '', ''));
                $shop = $driverOrder->first();
                $shopId = $shop['shop_id'];
                if($shopId){
                    event(new ShopNotification("Order Picked Up By Driver - #".$request->orderId, 'Picked Up', '', $shopId));
                }
                return $this->response->jsonResponse(false, 'Driver Picked The Order SuccessFully', [], 200);
            } else {
                return $this->response->jsonResponse(true, 'No Order Found', [], 201);
            }
        }else{
            return $this->response->jsonResponse(true, 'Operation Error', [], 202);
        }

    }

    public function acceptOrderByDriver(Request $request){
        $validate = $this->response->validate($request->all(), [
            'orderId' => 'required',
            'operation' => 'required'
        ]);

        if($request->operation == "DRIVER_ACCEPTANCE_OPERATION"){
            $driverOrder = DriverOrder::where('order_id', $request->orderId);
            if($driverOrder->exists()) {
                DriverOrder::where('order_id', $request->orderId)->update(['driver_acceptance' => 1, 'accepted_at'=>Carbon::now()]);
                event(new MyEvent("Order Accepted By Driver", 'For Pick Up', '', ''));
                $shop = $driverOrder->first();
                $shopId = $shop['shop_id'];
                if($shopId){
                    event(new ShopNotification("Order Accepted By Driver", 'For Pick Up', '', $shopId));
                }

                return $this->response->jsonResponse(false, 'Driver Accepted The Order SuccessFully', [], 200);
            } else {
                return $this->response->jsonResponse(true, 'No Order Found', [], 201);
            }
        }else{
            return $this->response->jsonResponse(true, 'Operation Error', [], 202);
        }

    }

    public function driverLogin(Request $request) {

        $validate = $this->response->validate($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'operation' => 'required'
        ]);
        if($request->operation == "LOGIN_OPERATION"){
            $driver = Driver::Where('driver_mobile', trim($request->username));
            if($driver->exists()) {
                $driver = $driver->first();
                  if(Hash::check($request->password, $driver->password)) {
                    $data = [
                        'id'=>$driver->driver_id,
                        'name' => $driver->driver_name,
                        'mobile'=>$driver->driver_mobile,
                    ];
                     return $this->response->jsonResponse(false, 'Logged In Successfully',$data, 201);
                }
            }else{
                return $this->response->jsonResponse(true, 'Invalid Credential',[], 202);
            }
        }else{
            return $this->response->jsonResponse(true, 'Operation Error', [], 203);
        }
    }

       public function deliverDriverOrder(Request $request) {

        $validate = $this->response->validate($request->all(), [
            'orderId' => 'required',
            'driverId' => 'required',
            'image' => 'required',
            'operation' => 'required'
        ]);
        if($request->operation == "UPLOAD_IMAGE_AND_DO_DELIVERY"){

            $driverOrder = DriverOrder::Where('driver_id', $request->driverId)->Where('order_id', $request->orderId);
            if($driverOrder->exists()) {
                if($request->image) {
                    $uploadUrl = $this->response->cloudinaryBase64Image($request->image, 'driver_order_images', "driver_uploaded_image_".$request->orderId);
                    $driverOrder->update(['driver_uploaded_image' => $uploadUrl, 'delivered_at' =>Carbon::now()]);
                    $order = Order::where('order_id', $request->orderId);
                    $paymentMethod = $order->first()->payment_mode;
                    if($paymentMethod == 'Cash On Delivery'){
                        $formatedInvoiceNo = $this->response->getInvoiceNo();
                        if($formatedInvoiceNo){
                                 $order->update([
                                'invoice_no' => $formatedInvoiceNo,
                                'paid' => 1,
                                'paid_at' => date('l Y-M-d g:i A'),
                            ]);
                        }
                    }
                    event(new MyEvent("Delivered Successfully", '', '$uploadUrl', ''));

                    return $this->response->jsonResponse(false, 'Delivered Successfully', [], 201);
                } else {
                    return $this->response->jsonResponse(true, 'Image Size is too high', [], 202);
                }

            }else{
                return $this->response->jsonResponse(true, 'No Orders found for the driver','', 203);
            }
        }else{
            return $this->response->jsonResponse(true, 'Operation Error', [], 204);
        }
    }

}
