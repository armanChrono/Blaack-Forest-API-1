<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DispatchOrders;
use App\Models\DispatchPrepareImages;
use App\Models\AcceptanceLog;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\ModeDispatchOrderAddons;
use App\Models\OrderAddons;
use App\Models\DispatchOrderAddons;
use DB;
use Carbon\Carbon;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\log;
use App\Events\DispatchNotification;
use App\Events\MyEvent;



class DispatchOrderController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }

    public function createDispatchOrder(Request $request){
         if($request->location_details_id){

            Order::where('order_id', $request->order_id)->update(['push_to_dispatch' => 1,'push_to_dispatched_at'=>date('l Y-M-d g:i A'), 'order_status'=> 'Dispatched']);

           $count =  OrderProducts::where('order_id', $request->order_id)->get();
            $productCount = count($count);
            if(!$request['scheduled_date']){
                $request['scheduled_date'] = Carbon::now();
            }
           $dispatch =  DispatchOrders::create($request->all())->get();
           DispatchOrders::where('order_id', $request->order_id)->update(['cake_done_image' => $productCount]);
           $orderAddons = OrderAddons::where('order_id', $request->order_id)->get();
               foreach($orderAddons as $addon) {
                DispatchOrderAddons::create([
                       'order_id' => $request->order_id,
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
               $DispatchOrders = DispatchOrders::with('order.orderedProducts.productDetails.firstImage')->where('order_id', $request->order_id)->first();
               $firstImage = $DispatchOrders['order']['orderedProducts'][0]['productDetails'][0]['firstImage'][0]['product_image'];
               $productName = $DispatchOrders['order']['orderedProducts'][0]['productDetails'][0]['product_name'];
               event(new DispatchNotification("New Order Arrived", $productName, $firstImage, $request->location_details_id));

           return $this->response->jsonResponse(false, 'Order Pushed To Dispatch Team Successfully',$dispatch, 200);
        }else{

            return $this->response->jsonResponse(true, 'Order Pushed To Dispatch Team Failed',[], 201);
        }


    }
    public function getDispatchOrderById($id){
        // Log::info(Carbon::now());
        $now = Carbon::now();
        return $this->response->jsonResponse(false, 'All dispatch Orders Listed', DispatchOrders::with('location', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation','order.orderedProducts.variation.weight','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images','order.orderedProducts.variation.weight.unit')->where('location_details_id', $id)->where('online_team_accept', 0)->where('scheduled_date', '<=' , Carbon::now())->get(), 200);
    }


    public function getDispatchOrderByDispatchId($id){
        return $this->response->jsonResponse(false, 'All dispatch Orders Listed', DispatchOrders::with('location', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation', 'order.orderedProducts.variation.weight','order.orderedProducts.variation.weight.unit','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images', 'order.orderedAddons')->where('dispatch_order_id', $id)->get(), 200);
    }

    public function getDispatchOrderByOrderId($id){
        return $this->response->jsonResponse(false, 'All dispatch Orders Listed', DispatchOrders::with('location', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation', 'order.orderedProducts.variation.weight','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images')->where('order_id', $id)->get(), 200);
    }

    public function acceptDispatchOrders(Request $request){
        if($request->dispatch_order_id){
            DispatchOrders::where('dispatch_order_id', $request->dispatch_order_id)->update(['dispatch_order_status'=> 1,'dispatch_accepted_at'=>date('l Y-M-d g:i A')]);
            $get = DispatchOrders::where('dispatch_order_id', $request->dispatch_order_id)->get();

            //return $this->response->jsonResponse(false, 'Accept the order Successfully',$get, 200);

            AcceptanceLog::create(['dispatch_order_id'=>$request->dispatch_order_id, 'status'=> 'Order Accepted']);
            event(new MyEvent('', '', '', ''));

            return $this->response->jsonResponse(false, 'Accept the order Successfully',[], 200);
        }else{

            return $this->response->jsonResponse(true, 'Accept the order Failed',[], 201);

        }

    }

     //image update for category
     public function dispatchApproveImage(Request $request)
     {
         if($request->dispatch_prepare_image_id){
            if($request->hasFile('dish_image')) {
                $file = $request->file('dish_image');
                $name = "updated".$request->image_name;
                $uploadUrl = $this->response->cloudinaryImage($request->file('dish_image'), 'dispatchOrderImage', $name);

                DispatchPrepareImages::where('dispatch_prepare_image_id', $request->dispatch_prepare_image_id)->update(['dispatch_order_id'=> $request->dispatch_order_id, 'product_id'=> $request->product_id, 'image'=> $uploadUrl]);
                AcceptanceLog::create(['dispatch_order_id'=>$request->dispatch_order_id, 'status'=> 'Prepared Image re-uploaded']);
                Log::info("before = ".$request->location_details_id);
                event(new MyEvent("Cake Ready For Review", 'For Review', $uploadUrl, $request->location_details_id));
                Log::info("after = ".$request->location_details_id);
                return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'image'), [], 201);
            } else {
                return $this->response->jsonResponse(true, 'Image is too high', [], 201);
            }

         }else{
            if($request->hasFile('dish_image')) {
                $file = $request->file('dish_image');
                //return $file;
                $name = $request->image_name;
                $uploadUrl = $this->response->cloudinaryImage($request->file('dish_image'), 'dispatchOrderImage', $name);

                DispatchPrepareImages::create(['dispatch_order_id'=> $request->dispatch_order_id, 'product_id'=> $request->product_id, 'image'=> $uploadUrl]);
                AcceptanceLog::create(['dispatch_order_id'=>$request->dispatch_order_id, 'status'=> 'Prepared Image uploaded']);
                event(new MyEvent("Cake Ready For Review", 'For Review', $uploadUrl, $request->location_details_id));
                return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'image'), [], 201);
            } else {
                return $this->response->jsonResponse(true, 'Image is too high', [], 201);
            }
         }


     }

     public function approveImage(Request $request){
        $dispatchPrepareImage = DispatchPrepareImages::where('dispatch_prepare_image_id', $request->dispatch_prepare_image_id);
        $dispatchPrepareImage->update(['approve_status'=> 1,'admin_accepted_at'=>date('l Y-M-d g:i A')]);
        $dispatchPrepareImage = $dispatchPrepareImage->first();
        $imageUrl = $dispatchPrepareImage['image'];

        $dispatchOrder = DispatchOrders::where('dispatch_order_id', $request->dispatch_order_id)->update(['online_team_accept' =>  DB::raw('online_team_accept+1'), ]);
        AcceptanceLog::create(['dispatch_order_id'=>$request->dispatch_order_id, 'status'=> 'Approved Image']);
        event(new DispatchNotification("Approved - Order #".$request->dispatch_order_id, 'Well Done! Your cake has been Approved', $imageUrl, $request->location_id));

        return $this->response->jsonResponse(false, 'Image Approved Successfully', [], 201);
     }

     public function disApproveImage(Request $request){
        $dispatchPrepareImage = DispatchPrepareImages::where('dispatch_prepare_image_id', $request->dispatch_prepare_image_id);
        $dispatchPrepareImage->update(['approve_status'=> 0, 'comments'=>$request->comments]);
        $dispatchPrepareImage = $dispatchPrepareImage->first();
        $imageUrl = $dispatchPrepareImage['image'];
        AcceptanceLog::create(['dispatch_order_id'=>$request->dispatch_order_id, 'status'=> 'Dis-Approved Image']);
        event(new DispatchNotification("Rejected - Order #".$request->dispatch_order_id,'Sorry! Your cake has been Rejected', $imageUrl, $request->location_id));
        return $this->response->jsonResponse(false, 'Image Dis-Approved Successfully', [], 201);
     }

     public function getDispatchImages(){
        return $this->response->jsonResponse(false, 'All dispatch Orders Listed', DispatchPrepareImages::with('product', 'dispatchOrder')->get(), 200);
    }

    public function getDispatchImagesById($id){
        return $this->response->jsonResponse(false, 'All dispatch Orders Listed', DispatchPrepareImages::with('product', 'dispatchOrder')->where('dispatch_order_id', $id)->get(), 200);
    }


}
