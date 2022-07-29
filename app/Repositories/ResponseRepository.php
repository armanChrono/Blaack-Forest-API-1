<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Carbon\Carbon;



class ResponseRepository
{
    //Returning a json response for overall api in a project
    public function jsonResponse($error, $message, $data, $code)
    {
        return response()->json(array(
            'error' => $error,
            'message' => $message,
            'data' => $data,
            'code' => $code,
        ));
    }

    //setting a api messages here
    public function message($module, $type) {
        $msg = [
            $module => [
                'index' => 'Listed All '.$module,
                'store' => $module.' Created',
                'show' => $module.' Shown',
                'update' => $module.' Updated',
                'destroy' => $module.' Deleted',
                'getActive' => 'Listed Active '.$module,
                'search' => $module.' Filtered',
                'image' => $module.' Image Updated'
            ]
        ];
        return $msg[$module][$type].' SuccessFully';
    }

    public function cloudinaryImage($file, $folder, $name) {
        $image_path = $file->getRealPath();
        $uploadUrl = Cloudinary::upload($image_path, ['folder' => 'BlaackForrest/'.$folder, 'public_id' => $name])->getSecurePath();
        return $uploadUrl;
    }
    public function cloudinaryBase64Image($base64String, $folder, $name) {
        // $uploadUrl = (new UploadApi())->upload("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==");
        $uploadUrl = (new UploadApi())->upload("data:image/png;base64,".$base64String);
        return $uploadUrl['url'];
    }

    public function cloudinaryAudio() {
        $uploadUrl =(new UploadApi())->upload("C:/xampp/htdocs/Black-Forest(11-02-2022)/blaack-forest-api/music/Thenmerku Paruva Kaatru [Masstamilan.In].mp3",
        ["resource_type" => "video"]);
       
        return $uploadUrl;
      
    }
   

    public function validate($request, $rules) {
        $validator = Validator::make($request, $rules);
        if ($validator->fails()) {
            return $this->jsonResponse(true, 'Failed', $validator->errors(), 401);
        } else {
            return true;
        }
    }

    public function resizeMinBannerImage($type, $path, $name) {
        $url = public_path('images/min/'.$type.'/'.$name);
        $image = Image::make($path);
        $image->resize(100, 50);
        $image->save($url);
    }

    public function resizeMidBannerImage($type, $path, $name) {
        $url = public_path('images/mid/'.$type.'/'.$name);
        $image = Image::make($path);
        $image->resize(320, 120);
        $image->save($url);
    }

    public function resizeMinImage($type, $path, $name) {
        $url = public_path('images/min/'.$type.'/'.$name);
        $image = Image::make($path);
        $image->resize(300, 400);
        $image->save($url);
    }

    public function resizeMidImage($type, $path, $name) {
        $url = public_path('images/mid/'.$type.'/'.$name);
        $image = Image::make($path);
        $image->resize(510, 680);
        $image->save($url);
    }

    public function deleteImage($type, $name) {
        $path = public_path('images/'.$type.'/');
        @unlink($path.$name);
        @unlink($path.'min/'.$name);
        @unlink($path.'mid/'.$name);
    }

    public function generateSlug($value) {
        return Str::slug($value, '-');
    }

    public function currentDate() {
        return date("Y-m-d");
    }

    public function currentTime() {
        return date("g:i A");
    }

    public function getCloudUrl() {
        return 'https://storage.cloud.google.com/chrono_e_cart/';
    }

    function generateOrderID($l=10){
        $str = "";
        for ($x=0; $x<$l; $x++) $str .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 1);
        return $str; 
    }
    function formatInvoice($previousInvoiceNo){
        $invoiceNo = $previousInvoiceNo + 1;
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->year;
        $fullInvoiceFormat = 'BF/'.$invoiceNo.'/'.$month.'/'.$year;
        return $fullInvoiceFormat;
    }
    public function getInvoiceNo(){
        $order = Order::whereNotNull('invoice_no')->orderBy('invoice_no', 'desc')->first();

       if($order){
           $previousInvoiceNo = $order->invoice_no;
           //get the invoice number from the format
           preg_match('~/(.*?)/~', $previousInvoiceNo, $output);
           $previousInvoiceNo = $output[1];
           $invoiceNo = $previousInvoiceNo + 1;
       }else{
           $invoiceNo = 1;
       }
       $invoiceNo = sprintf("%02d", $invoiceNo);
       $month = Carbon::now()->format('m');
       $year = Carbon::now()->year;
       $fullInvoiceFormat = 'BF/'.$invoiceNo.'/'.$month.'/'.$year;
       return $fullInvoiceFormat;
    }
}
