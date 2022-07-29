<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelMsg91;
use App\Repositories\ResponseRepository;
use GuzzleHttp\Client;

class SmsController extends Controller
{

    public $authkey = '343294At8mn1OQjeM5f758356P1';
    public $template_id = '5fe40f2370e96e03757a2a7d';
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }

    public function sendOTP($mobile) {
        $otp = $this->generateOTP();
        $baseUrl = 'https://api.msg91.com/api/v5/otp?authkey='.$this->authkey.'&template_id='.$this->template_id.'&mobile='.$mobile.'&invisible=1&otp='.$otp;
        $client = new Client();
        $res = $client->request('GET', $baseUrl);
        return json_decode($res->getBody());
        // return $this->response->jsonResponse(false, 'Success', json_decode($res->getBody()), 201);
    }

    public function verifyOTP($mobile, $otp) {
        $baseUrl = 'https://api.msg91.com/api/verifyRequestOTP.php?authkey='.$this->authkey.'&mobile='.$mobile.'&otp='.$otp;
        $client = new Client();
        $res = $client->request('GET', $baseUrl);
        return $this->response->jsonResponse(false, 'Success', json_decode($res->getBody()), 201);
    }

    public function generateOTP() {
        $generator = "1357902468";
        $otp = ""; 
        for ($i = 1; $i <= 6; $i++) { 
            $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
        }
        return $otp;
    }
}
