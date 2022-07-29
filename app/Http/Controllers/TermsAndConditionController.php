<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TermsAndCondition;
use App\Repositories\ResponseRepository;

class TermsAndConditionController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'terms_and_condition' => 'required'
        ];
    }

    public function createTermsAndCondition(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('TermsAndConditions', 'store'), TermsAndCondition::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateTermsAndCondition(Request $request){
        $validate = $this->response->validate($request->all(), [
            'terms_id' => [
                'required'
            ],
            'terms_and_condition' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('TermsAndConditions', 'update'), TermsAndCondition::where('terms_id', $request->terms_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllTermsAndConditionList(){
        return $this->response->jsonResponse(false, 'All TermsAndConditions Listed', TermsAndCondition::get(), 200);
    }
}
