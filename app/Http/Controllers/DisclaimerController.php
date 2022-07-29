<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disclaimer;
use App\Repositories\ResponseRepository;
class DisclaimerController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'disclaimer' => 'required'
        ];
    }

    public function createDisclaimer(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Disclaimer', 'store'), Disclaimer::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateDisclaimer(Request $request){
        $validate = $this->response->validate($request->all(), [
            'disclaimer_id' => [
                'required'
            ],
            'disclaimer' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Disclaimer', 'update'), Disclaimer::where('disclaimer_id', $request->disclaimer_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllDisclaimerList(){
        return $this->response->jsonResponse(false, 'All Disclaimer Listed', Disclaimer::get(), 200);
    }
}
