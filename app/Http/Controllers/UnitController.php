<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Repositories\ResponseRepository;

class UnitController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'unit_name' => 'required'
        ];
    }

    public function createUnit(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Units', 'store'), Unit::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateUnit(Request $request){
        $validate = $this->response->validate($request->all(), [
            'unit_id' => [
                'required'
            ],
            'unit_name' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Units', 'update'), Unit::where('unit_id', $request->unit_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllUnitList(){
        return $this->response->jsonResponse(false, 'All Units Listed', Unit::get(), 200);
    }

    public function getAllActiveUnitList(){
        return $this->response->jsonResponse(false, 'All Units Listed', Unit::Where(['active_status' => 1])->get(), 200);
    }

    public function activateUnit($unit_id)
    {
        $getSize = Unit::where('unit_id', $unit_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Unit Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Unit Not Available', [], 201);
        }
    }

    public function deActivateUnit($unit_id)
    {
        $getSize = Unit::where('unit_id', $unit_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Unit De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Unit Not Available', [], 201);
        }
    }

    public function deleteUnit($id){
        $size = Unit::where('unit_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Unit', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Unit Not Exists',[], 201);
    }
}
