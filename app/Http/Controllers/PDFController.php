<?php

namespace App\Http\Controllers;
use PDF;
use Mail;
use App\Models\Order;
use App\Models\DispatchOrders;
use App\Models\OrderProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Terbilang;
use App\Models\Product;
use App\Models\Category;

class PDFController extends Controller
{
    public function generateInvoicePdf($orderId)
    {
        $order = Order::where('order_id', $orderId)->with('region.City', 'region.state','orderedAddress', 'orderedAddons', 'orderedAddons.tax' ,'orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.flavour', 'orderedProducts.variation.weight', 'orderGstMerge', 'shops')->first();
        // Log::info("data ".json_encode($order));
        $pdf = PDF::loadView('invoice-pdf', $order->toArray());

        return $pdf->download('nicesnippets.pdf');
    }
    public function generateInvoicePdf1($orderId)
    {
        $orders =  Category::where('active_status', 1)->with('subCategoriesFour')->get();

        // $order = Order::with('region.City', 'region.state','orderedProducts','orderedProducts.productDetails','orderedProducts.flavour','orderedProducts.variation.weight','shops','orderedAddress', 'orderedAddons', 'orderedProducts.productDetails.images')->where('order_id', $orderId)->first();

        // $order = Order::where('order_id', $orderId)->with('region.City', 'region.state','orderedAddress', 'orderedAddons', 'orderedAddons.tax' ,'orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.flavour', 'orderedProducts.variation.weight', 'orderGstMerge', 'shops', 'orderedProducts.productDetails.images')->first();
        return  $orders;
    }

    public function creditMemo($orderId)
    {
        $order = Order::where('order_id', $orderId)->with('region.City', 'region.state','orderedAddress', 'orderedAddons', 'orderedAddons.tax' ,'orderedProducts.productDetails.tax','orderedProducts.productDetails.unit','orderedProducts.flavour', 'orderedProducts.variation.weight', 'orderGstMerge', 'shops', 'orderedProducts.productDetails.images')->first();

        $pdf = PDF::loadView('credit-memo', $order->toArray());

        return $pdf->download('nicesnippets.pdf');
    }

    public function invoice()
    {
        $data["email"] = "arman.chronoinfotech@gmail.com";
        $data["title"] = "sample pdf";
        $data["body"] = "This is Demo";

        $pdf = PDF::loadView('credit-memo', $data);

        Mail::send('credit-memo', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attachData($pdf->output(), "invoice.pdf");
        });

        dd('Mail sent successfully');
    }


}
