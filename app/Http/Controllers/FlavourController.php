<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flavour;
use App\Repositories\ResponseRepository;

class FlavourController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'flavour_name' => 'required'
        ];
    }

    public function createFlavour(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Flavours', 'store'), Flavour::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateFlavour(Request $request){
        $validate = $this->response->validate($request->all(), [
            'flavour_id' => [
                'required'
            ],
            'flavour_name' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Flavours', 'update'), Flavour::where('flavour_id', $request->flavour_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllFlavourList(){
        return $this->response->jsonResponse(false, 'All Flavours Listed', Flavour::orderBy('flavour_name')->get(), 200);
    }

    public function getAllActiveFlavourList(){
        return $this->response->jsonResponse(false, 'All Flavours Listed', Flavour::Where(['active_status' => 1])->get(), 200);
    }

    public function activateFlavour($flavour_id)
    {
        $getSize = Flavour::where('flavour_id', $flavour_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Flavour Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Flavour Not Available', [], 201);
        }
    }

    public function deActivateFlavour($flavour_id)
    {
        $getSize = Flavour::where('flavour_id', $flavour_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Flavour De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Flavour Not Available', [], 201);
        }
    }

    public function deleteFlavour($id){
        $size = Flavour::where('flavour_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Flavour', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Flavour Not Exists',[], 201);
    }
}
