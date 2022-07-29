<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Weight;
use App\Repositories\ResponseRepository;

class WeightController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'weight_name' => 'required',
            'unit_id' => 'required',
            'allow_cod' => 'required'
        ];
    }

    public function createWeight(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Weight', 'store'), Weight::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateWeight(Request $request){
        $validate = $this->response->validate($request->all(), [
            'unit_id' => [
                'required'
            ],
            'weight_name' => 'required',
            'weight_id' => 'required',
            'allow_cod' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Weight', 'update'), Weight::where('weight_id', $request->weight_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllWeightList(){
        // return $this->response->jsonResponse(false, 'All Weight Listed', Weight::with('unit')->get(), 200);
        return $this->response->jsonResponse(false, 'All Weight Listed', Weight::with('unit')->orderBy('weight_name')->get(), 200);
    }

    public function getAllActiveWeightList(){
        return $this->response->jsonResponse(false, 'All Weight Listed', Weight::with('unit')->Where(['active_status' => 1])->get(), 200);
    }

    public function getAllActiveWeightListByUnitId($uint_id){
        return $this->response->jsonResponse(false, 'All Weight Listed', Weight::with('unit')->Where(['active_status' => 1, 'unit_id'=> $uint_id])->get(), 200);
    }

    public function activateWeight($weight_id)
    {
        $getSize = Weight::where('weight_id', $weight_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Weight Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Weight Not Available', [], 201);
        }
    }

    public function deActivateWeight($weight_id)
    {
        $getSize = Weight::where('weight_id', $weight_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Weight De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Weight Not Available', [], 201);
        }
    }

    public function deleteWeight($id){
        $size = Weight::where('weight_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Weight', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Weight Not Exists',[], 201);
    }


}
