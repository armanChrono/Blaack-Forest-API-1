<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use App\Models\Region;
use App\Models\Pincode;
use App\Models\ShopDetails;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required'
        ];

        $this->shopRules = [
            'region_id' => 'required',
            'shop_code' => 'required',
            'shop_name' => 'required',
            'address' => 'required',
            'mobile_no' => 'required',
            'pincode' => 'required',
            'password' => 'required'
        ];

        $this->pinCode= [
            'region_id' => 'required',
            'pincode' => 'required',
            'rate' => 'required'

        ];
    }

    public function loginShop(Request $request) {
        $user= ShopDetails::where('shop_code', $request->shop_name)->where('shop_details_id', $request->shop_details_id)->where('active_status', 1);
        if($user->exists()){
             $user = $user->first();
             if(Hash::check($request->password, $user->password)) {
                 $user['token'] =  $user->createToken($user)->plainTextToken;
                return $this->response->jsonResponse(false, 'Logged in Successfully',$user, 201);
            }else{
                 return $this->response->jsonResponse(true, 'In-Correct Password','', 202);
            }
        }else{
             return $this->response->jsonResponse(true, 'Invalid Credentials','', 203);
        }

    }

    public function createRegion(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Region', 'store'), Region::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

  public function createShopDetails(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->shopRules);
        if($validate === true) {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            return $this->response->jsonResponse(false, $this->response->message('Shop Details', 'store'), ShopDetails::create($input), 200);
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

    public function getAllShopDetailsByRegionId($id){
        return $this->response->jsonResponse(false, 'All Region Listed', ShopDetails::Where(['region_id'=> $id,'active_status' => 1])->with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllLocationDetails(){
        return $this->response->jsonResponse(false, 'All Location Details Listed', LocationDetails::with('region.country', 'region.state', 'region.city')->get(), 200);
    }

    public function getAllActiveShopDetails(){
        return $this->response->jsonResponse(false, 'All Shop Details Listed', ShopDetails::Where(['active_status' => 1])->with('region.country', 'region.state', 'region.city')->get(), 200);
    }
    public function getAllShopDetails(){
        return $this->response->jsonResponse(false, 'All Shop Details Listed', ShopDetails::with('region.country', 'region.state', 'region.city')->get(), 200);
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

    public function activateShopDetails($shop_details_id)
    {
        $shopDetails = ShopDetails::where('shop_details_id', $shop_details_id);
        if ($shopDetails->exists()) {
            return $this->response->jsonResponse(false, 'Shop Activated Successfully', $shopDetails->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Shop Not Available', [], 201);
        }
    }
    public function deActivateShopDetails($shop_details_id)
    {
        $shopDetails = ShopDetails::where('shop_details_id', $shop_details_id);
        if ($shopDetails->exists()) {
            return $this->response->jsonResponse(false, 'Shop Activated Successfully', $shopDetails->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Shop Not Available', [], 201);
        }
    }

    public function deleteShopDetails($id){
        $shopDetails = ShopDetails::where('shop_details_id', $id)->first();
        if($shopDetails) {
            return $this->response->jsonResponse(false, $this->response->message('Shop', 'destroy'), $shopDetails->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Shop Not Exists',[], 201);
    }
    public function updateShopDetails(Request $request){
         $validate = $this->response->validate($request->all(), [
            'region_id' => [
                'required'
            ],
            'shop_details_id' => 'required',
            'shop_code' => 'required',
            'shop_name' => 'required',
            'address' => 'required',
            'mobile_no' => 'required',
            'pincode' => 'required'
        ]);

        if($validate === true) {
            $input = $request->all();
            if(isset($input['password'])){
              $input['password'] = Hash::make($input['password']);
            }
            return $this->response->jsonResponse(false, $this->response->message('Shop Details', 'update'), ShopDetails::where('shop_details_id', $request->shop_details_id)->update($input), 200);
        } else {
            return $validate;
        }
    }

}
