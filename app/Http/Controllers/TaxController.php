<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;
use App\Repositories\ResponseRepository;

class TaxController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'tax_percentage' => 'required'
        ];
    }

    public function createTax(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Taxs', 'store'), Tax::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateTax(Request $request){
        $validate = $this->response->validate($request->all(), [
            'tax_id' => [
                'required'
            ],
            'tax_percentage' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Taxs', 'update'), Tax::where('tax_id', $request->tax_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllTaxList(){
        return $this->response->jsonResponse(false, 'All Taxs Listed', Tax::orderBy('tax_percentage')->get(), 200);
    }

    public function getAllActiveTaxList(){
        return $this->response->jsonResponse(false, 'All Taxs Listed', Tax::Where(['active_status' => 1])->get(), 200);
    }

    public function activateTax($tax_id)
    {
        $getSize = Tax::where('tax_id', $tax_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Tax Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Tax Not Available', [], 201);
        }
    }

    public function deActivateTax($tax_id)
    {
        $getSize = Tax::where('tax_id', $tax_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Tax De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Tax Not Available', [], 201);
        }
    }

    public function deleteTax($id){
        $size = Tax::where('tax_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Tax', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Tax Not Exists',[], 201);
    }
}
