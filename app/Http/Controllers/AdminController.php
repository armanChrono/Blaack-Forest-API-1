<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DateTime;
use Redirect;
use DB;
use App\Models\Cashier;
use App\Models\Server;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use Illuminate\Support\Facades\Input;
use App\Repositories\ResponseRepository;
use App\Models\KotUser;
use Illuminate\Support\Facades\Hash;


class AdminController extends Controller
{
    public $successStatus = 200;

    public function __construct(ResponseRepository $response) {
        $this->response = $response;
    }

    //createUser
    public function createUser(Request $request) {
        $checkExisting = User::where('user_name', $request->user_name)->exists();
        if($checkExisting) {
            return $this->response->jsonResponse(true, $request->user_name.' Already Exists', [], 201);
        }
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'user_name' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'User Creation Failed', $validator->errors(), 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user['token'] = $user->createToken(md5(uniqid(rand(), true)))->accessToken;
        return $this->response->jsonResponse(false, 'User Created Successfully',$user, 201);
    }

    //updating a User
    public function updateUser(Request $request) {
        $input = $request->all();
        $fetchUpdatedDetails = User::where('id',$request->id)->exists();
        if(($fetchUpdatedDetails) != 0) {
            if ($input['password']) {
                $data = [
                    'user_name' => $input['user_name'],
                    'role_id' => $input['role_id'],
                    'password' => bcrypt($input['password']),
                ];
            } else {
                $data = [
                    'user_name' => $input['user_name'],
                    'role_id' => $input['role_id'],
                ];
            }
            $user = User::where('id', $request->id)->update($data);
            return $this->response->jsonResponse(false, 'User Updated Successfully', $input, 201);
        } else {
            return $this->response->jsonResponse(false, 'User Not Available', [], 201);
        }
    }

    //Login
    public function loginUser(Request $request) {
        if(Auth::attempt(['user_name' => request('user_name'), 'password' => request('user_password')])) {
            $user = Auth::user();
            if($user) {
                $checkActiveStatus = User::where('user_name', $request->user_name)->select('active_status')->first();
                if ((int)$checkActiveStatus['active_status'] === 1) {
                    $user['token'] =  $user->createToken(md5(uniqid(rand(), true)))->accessToken;
                    return $this->response->jsonResponse(false, 'Logged in Successfully',$user, 201);
                }
                return $this->response->jsonResponse(true, 'Invalid Credential for User',[], 201);
            } else {
                return $this->response->jsonResponse(true, 'Invalid Credential for User',[], 201);
            }
        } else {
            return $this->response->jsonResponse(true, 'Invalid Credential for User',[], 201);
        }
    }

    //Fetching a listAllUsers
    public function listAllUsers() {
        $user = User::with('getroleNameAttribute')->get();
        return $this->response->jsonResponse(false, 'Users Listed Successfully',$user, 201);
    }

    //Searching a user
    public function searchUser($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, 'User filtered Successfully', [], 201);
        }
        $user = User::with('getroleNameAttribute')->where('user_name', 'LIKE', $search.'%')
                            ->orWhereHas('getroleNameAttribute', function($q) use ($search) {
                                return $q->where('role_name', 'LIKE', '%' . $search . '%');
                            })->get();
        return $this->response->jsonResponse(false, 'User filtered Successfully', $user, 201);
    }

    //activate a User will show a User in a panel
    public function activateUser($id) {
        $getUser = User::where('id',$id)->exists();
            if(($getUser) != 0){
                User::where('id', $id)->update(['active_status' => 1]);
             return $this->response->jsonResponse(false, 'User Activated Successfully', User::where('id', $id)->get(), 201);
        } else {
            return $this->response->jsonResponse(true, 'User Not Available', [], 201);
          }
    }

    //deactivate a User will show a User in a panel
    public function deActivateUser($id) {
        $getUser = User::where('id',$id)->exists();
            if(($getUser) != 0){
                User::where('id', $id)->update(['active_status' => 0]);
             return $this->response->jsonResponse(false, 'User De-Activated Successfully', User::where('id', $id)->get(), 201);
        } else {
            return $this->response->jsonResponse(true, 'User Not Available', [], 201);
          }
    }

    //listing active User
    public function listActiveUsers() {
        $list = User::with('getroleNameAttribute')->where('active_status', 1)->get();
        return $this->response->jsonResponse(false, 'Active Users Listed Successfully', $list, 201);
    }

    //deleting a User
    public function deleteUser($id) {
        User::where('id', $id)->delete();
        return $this->response->jsonResponse(false, 'User Deleted Successfully', [], 201);
    }

    //Logout
    public function userLogout() {
        if(Auth::check()) {
            Auth::user()->AauthAcessToken()->delete();
            return $this->response->jsonResponse(false, 'User Logout Successfully', [], 201);
        } else {
            return $this->response->jsonResponse(true, 'Guest User Logout Successfully', [], 201);
        }
    }


    


}
