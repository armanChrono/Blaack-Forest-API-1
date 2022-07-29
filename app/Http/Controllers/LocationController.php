<?php

namespace App\Http\Controllers;

use App\Models\LocationCity;
use App\Models\LocationState;
use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use GuzzleHttp\Client;

class LocationController extends Controller
{

    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function getStates() {
        return $this->response->jsonResponse(false, 'Listed All States', LocationState::all(), 200);
    }

    public function getActiveStates() {
        return $this->response->jsonResponse(false, 'Listed All Cities', LocationCity::all(), 200);
    }

    public function getCityOfState($id) {
        return $this->response->jsonResponse(false, 'Listed All Cities Of State', LocationCity::where('state_id', $id)->get(), 200);
    }

    public function updateDeliveryLocation(Request $request) {
        $input = $request->all();
        if($input['state_id']) {
            LocationState::where('id', $input['state_id'])->update(['delivery_status' => 1]);
        }
        if($input['city_id']) {
            foreach ($input['city_id'] as $id) {
                LocationCity::where('id', $id)->update(['delivery_status' => 1]);
            }
        }
        return $this->response->jsonResponse(false, 'Delivery Location Updated', [], 200);
    }

    public function getDeliveryLocation() {
        $location = [];
        $location['state'] = LocationState::where('delivery_status', 1)->get();
        $location['city'] = LocationCity::with('state')->where('delivery_status', 1)->get();
        return $this->response->jsonResponse(false, 'Delivery Location Listed', $location, 200);
        // $state = [];
        // $state = LocationState::where('delivery_status', 1)->get();
        // foreach($state as $data) {
            
        //     array_merge($data, ['count' => 10]);
        //     $location = $data;
        // }
        // return $location;
        // $location = [];
        // $location['state'] = LocationState::where('delivery_status', 1)->get();
        // foreach ($location['state'] as $state) {

        //     $location['state']['city_count'] = 'anas';
        // }
        // return $location;

        // LocationCity::where('state_id', $state['id'])->where('delivery_status', 1)->count()
        // $location['city'] = LocationCity::where('delivery_status', 1)->get();
        // $location['state_count'] = LocationState::where('delivery_status', 1)->count();
        // $location['city_count'] = LocationCity::where('delivery_status', 1)->count();
    }

    public function removeDeliveryState($id) {
        if($id) {
            $state = LocationState::where('id', $id)->update(['delivery_status' => 0]);
            $city = LocationCity::where('state_id', $id)->update(['delivery_status' => 0]);
            return $this->response->jsonResponse(false, 'Removed State and their Cities From Delivery', [], 200);
        }
    }

    public function removeDeliveryCity($id) {
        if($id) {
            $city = LocationCity::where('id', $id)->update(['delivery_status' => 0]);
            return $this->response->jsonResponse(false, 'Removed City From Delivery', [], 200);
        }
    }

    public function findLocation($pincode) {
        $client = new Client();
        $res = $client->request('GET', 'https://api.postalpincode.in/pincode/'.$pincode);
        $response = json_decode($res->getBody(), true);
        return $response[0]['PostOffice'][0]['District'];
        return $response[0]['PostOffice'][0]['Name'];
        // echo'<pre></pre>'; print_r((string)$res->getBody()); exit();
    }
}
