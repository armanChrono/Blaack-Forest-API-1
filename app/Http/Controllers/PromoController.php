<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'promo_code' => 'required|unique:promos,promo_code,NULL,id,deleted_at,NULL|alpha_dash',
            'min_value' => 'required|numeric',
            'discount' => 'required|numeric'
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Promo', 'index'), Promo::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Promo', 'store'), Promo::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Promo', 'show'), $this->findPromo($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'promo_code' => [
                'required',
                Rule::unique('promos')->ignore($id)->whereNull('deleted_at'),
            ],
            'min_value' => 'required|numeric',
            'discount' => 'required|numeric'
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Promo', 'update'), $this->findPromo($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $promo = $this->findPromo($id);
        if($promo) {
            return $this->response->jsonResponse(false, $this->response->message('Promo', 'destroy'), $promo->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Promo Not Exists',[], 201);
    }

    public function promoSwitch($id) {
        $promo = $this->findPromo($id);
        if($promo) {
            $value = $promo->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Promo '.$msg.' SuccessFully', $promo->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Promo Not Exists',[], 201);
    }

    public function getActivePromo() {
        return $this->response->jsonResponse(false, $this->response->message('Promo', 'getActive'), Promo::where('active_status', 1)->get(), 200);
    }

    public function searchPromo($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Promo', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Promo', 'search'), Promo::where('promo_code', 'LIKE', $search.'%')->get(), 201);
    }

    public function findPromo($id) {
        return Promo::find($id);
    }

    public function checkPromo($code) {
        $promo = Promo::where('promo_code', $code)->first();
        Log::info("promo : ".$promo);
        if($promo) {
            return $this->response->jsonResponse(false, "PromoCode Applied Successfully", $promo, 201);
        }
        return $this->response->jsonResponse(true, 'Invalid PromoCode', [], 201);
    }

}
