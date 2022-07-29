<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use App\Models\Region;
use App\Models\Pincode;
use App\Models\LocationDetails;
use App\Repositories\ResponseRepository;

class RegionController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required'
        ];

        $this->locationRules = [
            'region_id' => 'required',
            'location_code' => 'required',
            'location_name' => 'required',
            'address' => 'required',
            'mobile_no' => 'required',
            'pincode' => 'required'
        ];

        $this->pinCode= [
            'region_id' => 'required',
            'pincode' => 'required',
            'rate' => 'required'
            
        ];
    }

    public function createRegion(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Region', 'store'), Region::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function createLocationDetails(Request $request){
        $validate = $this->response->validate($request->all(), $this->locationRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Location Details', 'store'), LocationDetails::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function createPincode(Request $request){
        $validate = $this->response->validate($request->all(), $this->pinCode);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Pincode', 'store'), Pincode::create($request->all()), 200);
        } else {
            return $validate;
        }
    }


    public function updateRegion(Request $request){
        $validate = $this->response->validate($request->all(), [
            'region_id' => [
                'required'
            ],
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Region', 'update'), Region::where('region_id', $request->region_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateLocationDetails(Request $request){
        $validate = $this->response->validate($request->all(), [
            'region_id' => [
                'required'
            ],
            'location_details_id' => 'required',
            'location_code' => 'required',
            'location_name' => 'required',
            'address' => 'required',
            'mobile_no' => 'required',
            'pincode' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Location Details', 'update'), LocationDetails::where('location_details_id', $request->location_details_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updatePincode(Request $request){
        $validate = $this->response->validate($request->all(), [
            'region_id' => [
                'required'
            ],
            'pincode' => 'required',
            'pincode_id' => 'required',
            'rate' => 'required'
            
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Pincode', 'update'), Pincode::where('pincode_id', $request->pincode_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllRegionList(){
        return $this->response->jsonResponse(false, 'All Region Listed', Region::with('country', 'state', 'city')->get(), 200);

        // return $this->response->jsonResponse(false, 'All Region Listed', Region::with('country', 'state', 'city')->orderBy('city.name', 'DESC')->get(), 200);
    }

    public function getAllActiveRegionList(){
        return $this->response->jsonResponse(false, 'All Region Listed', Region::Where(['active_status' => 1])->with('country', 'state', 'city')->get(), 200);
    }

    public function getAllLocationDetailsById($id){
        return $this->response->jsonResponse(false, 'All Region Listed', LocationDetails::Where(['location_details_id'=> $id,'active_status' => 1])->with('region.country', 'region.state', 'region.city')->first(), 200);
    }

    public function getAllLocationDetailsByRegionId($id){
        return $this->response->jsonResponse(false, 'All Region Listed', LocationDetails::Where(['region_id'=> $id,'active_status' => 1])->with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllLocationDetails(){
        return $this->response->jsonResponse(false, 'All Location Details Listed', LocationDetails::with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllActiveLocationDetails(){
        return $this->response->jsonResponse(false, 'All Location Details Listed', LocationDetails::Where(['active_status' => 1])->with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllPincode(){
        return $this->response->jsonResponse(false, 'All Pincode  Listed', Pincode::with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllActivePincode(){
        return $this->response->jsonResponse(false, 'AllPincode Listed', Pincode::Where(['active_status' => 1])->with('region.country', 'region.state', 'region.city')->get(), 200);
    }



    public function getCountryList(){
        return $this->response->jsonResponse(false, 'All Countries Listed', Countries::get(), 200);
    }

    public function getStateList($id){
        return $this->response->jsonResponse(false, 'All States Listed', States::where('country_id', $id)->get() , 200);
    }
    public function getCityList($id){
        return $this->response->jsonResponse(false, 'All Cities Listed', Cities::where('state_id', $id)->get(), 200);
    }

    public function activateRegion($region_id)
    {
        $getSize = Region::where('region_id', $region_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Region Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Region Not Available', [], 201);
        }
    }

    public function activateLocationDetails($location_details_id)
    {
        $getSize = LocationDetails::where('location_details_id', $location_details_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Location Details Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Location Details Not Available', [], 201);
        }
    }

    public function activatePincode($pincode_id)
    {
        $getSize = Pincode::where('pincode_id', $pincode_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Pincode Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Pincode Not Available', [], 201);
        }
    }

    //deactivate a Size will show a tag in a panel
    public function deActivateRegion($region_id)
    {
        $getSize = Region::where('region_id', $region_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Region De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Region Not Available', [], 201);
        }
    }

    public function deActivateLocationDetails($location_details_id)
    {
        $getSize = LocationDetails::where('location_details_id', $location_details_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Location Details De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Location Details Not Available', [], 201);
        }
    }

    public function deActivatePincode($pincode_id)
    {
        $getSize = Pincode::where('pincode_id', $pincode_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Pincode De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Pincode Not Available', [], 201);
        }
    }

    public function deleteRegion($id){
        $size = Region::where('region_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Region', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Region Not Exists',[], 201);
    }

    public function deleteLocationDetails($id){
        $size = LocationDetails::where('location_details_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Location Details', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Location Details Not Exists',[], 201);
    }

    public function deletePincode($id){
        $size = Pincode::where('pincode_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Pincode', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Pincode Not Exists',[], 201);
    }


    public function searchLocationDetails($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Location Details', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Location Details', 'search'), LocationDetails::with('region.country', 'region.state', 'region.city')->where('location_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function searchPincode($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Pincode', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Pincode', 'search'), Pincode::with('region.country', 'region.state', 'region.city')->where('pincode', 'LIKE', $search.'%')->get(), 200);
    }

}
