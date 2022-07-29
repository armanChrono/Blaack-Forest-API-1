<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerDetails;
use App\Repositories\ResponseRepository;

class CustomerDetailsController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'customer_name' => 'required',
            'customer_mobile_no' => 'required',
            'customer_email' => 'required',
            'address' => 'required'
        ];
    }

    public function createCustomerDetails(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('CustomerDetailss', 'store'), CustomerDetails::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateCustomerDetails(Request $request){
        $validate = $this->response->validate($request->all(), [
            'customer_details_id' => [
                'required'
            ],
            'customer_name' => 'required',
            'customer_mobile_no' => 'required',
            'customer_email' => 'required',
            'address' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('CustomerDetails', 'update'), CustomerDetails::where('customer_details_id', $request->customer_details_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllCustomerDetailsList(){
        return $this->response->jsonResponse(false, 'All CustomerDetails Listed', CustomerDetails::get(), 200);
    }

    public function getAllActiveCustomerDetailsList(){
        return $this->response->jsonResponse(false, 'All CustomerDetails Listed', CustomerDetails::Where(['active_status' => 1])->get(), 200);
    }

    public function activateCustomerDetails($customer_details_id)
    {
        $getSize = CustomerDetails::where('customer_details_id', $customer_details_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'CustomerDetails Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'CustomerDetails Not Available', [], 201);
        }
    }

    public function deActivateCustomerDetails($customer_details_id)
    {
        $getSize = CustomerDetails::where('customer_details_id', $customer_details_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'CustomerDetails De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'CustomerDetails Not Available', [], 201);
        }
    }

    public function deleteCustomerDetails($id){
        $size = CustomerDetails::where('customer_details_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('CustomerDetails', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'CustomerDetails Not Exists',[], 201);
    }
}
