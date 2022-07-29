<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use App\Models\Dispatch;
use App\Models\LocationDetails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'dispatch_name' => 'required|unique:dispatch_team,dispatch_name,NULL,id,deleted_at,NULL',
            'password' => 'required',
            'location_details_id' => 'required'
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'index'), Dispatch::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'store'), Dispatch::create($input), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'show'), Dispatch::with('locationDetails')->get(), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'dispatch_name' => [
                'required',
                //Rule::unique('Dispatch_team')->ignore('dispatch_team_id', $id)->whereNull('deleted_at'),
            ],
            'password' => 'required',
            'location_details_id' => 'required'
        ]);
        if($validate === true) {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            //return $this->response->jsonResponse(true, 'Dispatch Not Exists',$input, 201);
            return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'update'), $this->findUser($id)->update($input), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $user = $this->findUser($id);
        if($user) {
            return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'destroy'), $user->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Dispatch Not Exists',[], 201);
    }

    public function dispatchSwitch($id) {
        $user = $this->findUser($id);
        if($user) {
            $value = $user->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Dispatch '.$msg.' SuccessFully', $user->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Dispatch Not Exists',[], 201);
    }

    public function getActiveTag() {
        return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'getActive'), Dispatch::where('active_status', 1)->get(), 200);
    }

    public function getAllActiveDispatchTeams() {
        return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'getActive'), Dispatch::with('locationDetails')->where('active_status', 1)->get(), 200);
    }

    public function searchTag($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Dispatch', 'search'), Dispatch::where('dispatch_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findUser($id) {
        return Dispatch::find($id);
    }

    //loginUser
    public function loginDispatch(Request $request) {
        $user= Dispatch::where('dispatch_name', $request->dispatch_name)->first();
        $user['location'] = LocationDetails::where('location_details_id', $request->location_details_id)->get();
        //return $this->response->jsonResponse(true, 'This user has been deactivated',$user, 201);

        if (!$user || !Hash::check($request->password, $user['password'])) {
            return $this->response->jsonResponse(true, 'Sorry! Invalid Credentials',[], 201);
        }

        $checkActiveStatus = Dispatch::where('dispatch_name', $request->dispatch_name)->where('location_details_id', $request->location_details_id)->select('active_status')->first();
        // Log::debug("checkActiveStatus = ".json_encode($checkActiveStatus));
        //return $this->response->jsonResponse(false, 'Logged in Successfully',$checkActiveStatus, 201);
        if ((int)$checkActiveStatus['active_status'] === 1) {
            $user['token'] =  $user->createToken($user)->plainTextToken;
            return $this->response->jsonResponse(false, 'Logged in Successfully',$user, 201);
        }
        return $this->response->jsonResponse(true, 'This user has been deactivated',[], 201);
    }

    public function users() {
        return response(Dispatch::all(), 201);
    }
}
