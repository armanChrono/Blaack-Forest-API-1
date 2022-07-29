<?php

namespace App\Repositories;
use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class SmsRepository
{
    // public $authkey = '343294At8mn1OQjeM5f758356P1';
    // public $template_id = '5fe40f2370e96e03757a2a7d';
    public $apiKey = "vKWfvpIuIOiKE4bk";
    public $senderId = "bfcaks";
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }


    public function sendOTP($mobile) {
        $templateId = '1207162425275175368';
         $otp = $this->generateOTP();
         $baseUrl = 'http://sim.smvnetwork.in/vb/apikey.php?apikey='.$this->apiKey.'&senderid='.$this->senderId.'&templateid='.$templateId.'&number=91'.$mobile.'&message=Confirmation OTP from Blaack Forest, Your OTP is : '.$otp;
         $client = new Client();
         $res = $client->request('GET', $baseUrl);
        return $otp;
        // return $this->response->jsonResponse(false, 'Success', json_decode($res->getBody()), 201);
    }


    public function generateOTP() {
        $generator = "1357902468";
        $otp = ""; 
        for ($i = 1; $i <= 6; $i++) { 
            $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
        }
        return $otp;
    }

    public function orderSms($name, $mobile, $orderId) {
        $templateId = '1207165226869834031';
        $baseUrl = 'http://sim.smvnetwork.in/vb/apikey.php?apikey='.$this->apiKey.'&senderid='.$this->senderId.'&templateid='.$templateId.'&number=91'.$mobile.'&message=Hi, Your order No #'.$orderId.' has been placed successfully. Thanks  for your order-Blaack Forest';   
        $client = new Client();
        $res = $client->request('GET', $baseUrl);
        return $this->response->jsonResponse(false, 'Success', "", 201);
    }

    public function caterUpdates($name, $mobile, $amount) {
        $url = "https://api.msg91.com/api/sendhttp.php?Group_id=group_id&authkey=343721A6wScunjzKv5f7bfad5P1&mobiles={$mobile}&country=91&message=Hai ".ucfirst($name).". Order Request of Rs.{$amount} has been Raised by MealsDeals User. Kindly Accept a Order in your portal within 20 Minutes&sender=MealsDeals&route=4";
        return $this->msg91Api($url);
    }

    public function customerUpdates($caterName, $caterMobile, $custName, $custMobile) {
        $url = "https://api.msg91.com/api/sendhttp.php?Group_id=group_id&authkey=343721A6wScunjzKv5f7bfad5P1&mobiles={$custMobile}&country=91&message=Hai ".ucfirst($custName).". Your Order Was Accepted By Our Cater ".ucfirst($caterName)." From MealsDeals. You Can Contact Our Cater With This Number {$caterMobile} &sender=MealsDeals&route=4";
        return $this->msg91Api($url);
    }

    public function msg91Api($url) {
        $client = new Client();
        $res = $client->request('GET', $url);
        // return $this->response->jsonResponse(false, 'Success', json_decode($res->getBody()), 201);
        return $this->response->jsonResponse(false, 'Success', "", 201);
    }
}
