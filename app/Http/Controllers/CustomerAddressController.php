<?php

namespace App\Http\Controllers;
use App\Models\CustomerAddress;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ResponseRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }

    public function getPincodeDetails($pincode) {
        $client = new Client();
        $api = $client->request('GET', 'https://api.postalpincode.in/pincode/'.$pincode);
        $response = json_decode($api->getBody())[0]->PostOffice;
        $areas = [];
        $i = 1;
        foreach ($response as $value) {
            array_push($areas, ['id' => $i++, 'name' => get_object_vars($value)['Name']]);
        }
        $data = [];
        $data['District'] = get_object_vars($response[0])['District'];
        $data['State'] = get_object_vars($response[0])['State'];
        $data['areas'] = $areas;
        return $this->response->jsonResponse(false, 'Successfully Fetched a details', $data, 201);
    }


    public function createAddress(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'billing_name' => 'required',
            'billing_mobile' => 'required',
            'address_pincode' => 'required',
            'doorNo' => 'required',
            'street' => 'required',
            'area' => 'required',
            'address_locality_town' => 'required',
            'address_city_district' => 'required',
            'address_state' => 'required',
            'address_type' => 'required',
            'default_address' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Failed While Adding Address', $validator->errors(), 401);
        }
        $create = CustomerAddress::create($input);
        if($input['default_address'] == 1) {
            Customer::where('customer_id', $input['customer_id'])->update([
                'default_address_id' => $create['customer_address_id']
            ]);
        }
        return $this->response->jsonResponse(false, 'Successfully Created a Address', [], 201);
    }

    public function editAddress(Request $request) {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_address_id' => 'required',
            'billing_name' => 'required',
            // 'billing_mobile' => 'required',
            'address_pincode' => 'required',
            'doorNo' => 'required',
            'street' => 'required',
            'area' => 'required',
            'address_locality_town' => 'required',
            'address_city_district' => 'required',
            'address_state' => 'required',
            'address_type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Registration Failed', $validator->errors(), 401);
        }

        return $this->response->jsonResponse(false, 'Successfully updated a Address', CustomerAddress::where('customer_address_id', $input['customer_address_id'])->update($input), 201);
    }

    public function getAddressDetails($id) {
        return $this->response->jsonResponse(false, 'Fetched an Address', CustomerAddress::where('customer_address_id', $id)->first(), 201);
    }

    public function listAllAddress($customer_id) {
        return $this->response->jsonResponse(false, 'Successfully Listed a Address', CustomerAddress::where('customer_id', $customer_id)->latest()->get(), 201);
    }

    public function deleteAddress($customer_address_id) {
        $address = CustomerAddress::find($customer_address_id);
        if($address) {
            $address->delete();
            return $this->response->jsonResponse(false, 'Address Deleted Successfully',[] , 201);
        }
        return $this->response->jsonResponse(true, 'This Address Not found', [], 401);
    }

    public function getPrimaryAddress($customer_id) {
        return $this->response->jsonResponse(false, 'Got a Primary Address', Customer::select('default_address_id')->where('customer_id', $customer_id)->first() , 201);
    }

    public function updatePrimaryAddress($customer_id, $address_id) {
        return $this->response->jsonResponse(false, 'Primary Address Updated', Customer::where('customer_id', $customer_id)->update(['default_address_id' => $address_id]) , 201);
    }
}
