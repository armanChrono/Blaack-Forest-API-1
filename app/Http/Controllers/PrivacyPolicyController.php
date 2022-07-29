<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Repositories\ResponseRepository;

class PrivacyPolicyController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'privacy_policy' => 'required'
        ];
    }

    public function createPrivacyPolicy(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Privacy Policy', 'store'), PrivacyPolicy::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updatePrivacyPolicy(Request $request){
        $validate = $this->response->validate($request->all(), [
            'privacy_policy_id' => [
                'required'
            ],
            'privacy_policy' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Privacy Policy', 'update'), PrivacyPolicy::where('privacy_policy_id', $request->privacy_policy_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllPrivacyPolicyList(){
        return $this->response->jsonResponse(false, 'All Privacy Policy Listed', PrivacyPolicy::get(), 200);
    }
}
