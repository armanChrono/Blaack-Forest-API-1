<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Repositories\ResponseRepository;
use App\Models\ShopOrders;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Events\ShopNotification;
use App\Events\MyEvent;


class ShopOrderController extends Controller
{

    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }

    public function getShopOrderById($id){
        return $this->response->jsonResponse(false, 'All Shop Orders Listed', ShopOrders::with('region', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation','order.orderedProducts.variation.weight','order.orderedProducts.variation.weight.unit','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images')->where('shop_id', $id)->where('picked_up', 0)->Where('driver_id', '=', null)->get(), 200);
    }

    public function getShopOrderByShopOrderId($id){
        return $this->response->jsonResponse(false, 'All Shop Orders Listed', ShopOrders::with('region','driver','driver.city', 'driverOrder','order.orderedProducts','order.orderedAddons', 'order.orderedProducts.flavour', 'order.orderedProducts.variation', 'order.orderedProducts.variation.weight', 'order.orderedProducts.variation.weight.unit','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images', 'order.shops', 'order.region', 'order.orderedAddress')->where('shop_order_id', $id)->get(), 200);
    }
    public function getShopOrderByOrderId($id){
        return $this->response->jsonResponse(false, 'All Shop Orders Listed', ShopOrders::with('region','driver', 'order.orderedProducts', 'order.orderedProducts.flavour', 'order.orderedProducts.variation', 'order.orderedProducts.variation.weight','order.orderedProducts.productDetails', 'order.orderedProducts.productDetails.images','shop')->where('order_id', $id)->get(), 200);
    }

    public function acceptShopOrders(Request $request){
        if($request->dispatch_order_id){
            $shopOrder = ShopOrders::where('shop_order_id', $request->dispatch_order_id);
            $shopOrder = $shopOrder->update(['shop_acceptance'=> 1,'accepted_at'=>date('l Y-M-d g:i A')]);
            $shopOrder =  ShopOrders::with('order.orderedProducts.productDetails.firstImage')->where('order_id', $request->order_id)->first();
            $firstImage = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['firstImage'][0]['product_image'];
            $productName = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['product_name'];
            event(new MyEvent("Order Accepted By Shop", 'For Delivery', $firstImage, $shopOrder['shop_id']));

            return $this->response->jsonResponse(false, 'Shop Accept the order Successfully',[], 200);
        }else{

            return $this->response->jsonResponse(true, 'Accept the order Failed',[], 201);

        }

    }
    public function deliveredFromShop(Request $request){
        if($request->shop_order_id){
            ShopOrders::where('shop_order_id', $request->shop_order_id)->update(['picked_up'=> 1,'picked_up_at'=>date('l Y-M-d g:i A')]);
            $order = Order::where('order_id', $request->order_id);
            $paymentMethod = $order->first()->payment_mode;
            if($paymentMethod == 'Cash On Delivery'){
                $formatedInvoiceNo = $this->response->getInvoiceNo();
                $order->update([
                    'invoice_no' => $formatedInvoiceNo,
                    'paid' => 1,
                    'paid_at' => date('l Y-M-d g:i A'),
                ]);
            }
            $shopOrder =  ShopOrders::with('order.orderedProducts.productDetails.firstImage')->where('order_id', $request->order_id)->first();
            $firstImage = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['firstImage'][0]['product_image'];
            $productName = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['product_name'];
            event(new MyEvent("Order Delivered To Customer", 'Close the order', $firstImage, $shopOrder['shop_id']));
            return $this->response->jsonResponse(false, 'Delivered the order Successfully','', 200);
        }else{

            return $this->response->jsonResponse(true, 'Accept the order Failed',[], 201);

        }

    }

    public function createShopOrder(Request $request){
        if($request->shop_id){
            Order::where('order_id', $request->order_id)->update(['order_status'=> 'ReadyToDelivered']);


           $dispatch =  ShopOrders::create($request->all())->get();
           $shopOrder =  ShopOrders::with('order.orderedProducts.productDetails.firstImage')->where('order_id', $request->order_id)->first();
           $firstImage = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['firstImage'][0]['product_image'];
           $productName = $shopOrder['order']['orderedProducts'][0]['productDetails'][0]['product_name'];
           event(new ShopNotification("New Order Arrived", $productName, $firstImage, $request->shop_id));

            return $this->response->jsonResponse(false, 'Order Pushed To Shop Successfully',$dispatch, 200);
        }

        return $this->response->jsonResponse(true, 'Order Pushed To Driver Team Failed',[], 201);

    }

}
