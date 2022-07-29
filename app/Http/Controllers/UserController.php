<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'user_name' => 'required ', //|unique:users,user_name,NULL,id,NUL',
            'user_email' => 'required',
            'password' => 'required',
            'change_password_otp' => 'required'
            
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('User', 'index'), User::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            return $this->response->jsonResponse(false, $this->response->message('User', 'store'), User::create($input), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('User', 'show'), $this->findUser($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'user_name' => [
                'required',
                Rule::unique('users')->ignore($id)->whereNull('deleted_at'),
            ],
            'password' => 'required'
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('User', 'update'), $this->findUser($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $user = $this->findUser($id);
        if($user) {
            return $this->response->jsonResponse(false, $this->response->message('User', 'destroy'), $user->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'User Not Exists',[], 201);
    }

    public function tagSwitch($id) {
        $user = $this->findUser($id);
        if($user) {
            $value = $user->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'User '.$msg.' SuccessFully', $user->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'User Not Exists',[], 201);
    }

    public function getActiveTag() {
        return $this->response->jsonResponse(false, $this->response->message('User', 'getActive'), User::where('active_status', 1)->get(), 200);
    }

    public function searchTag($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('User', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('User', 'search'), User::where('tag_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findUser($id) {
        return User::find($id);
    }

    //loginUser
    public function loginUser(Request $request) {
        $user= User::where('user_name', $request->user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->response->jsonResponse(true, 'Sorry! Invalid Credentials',[], 201);
        }

        $checkActiveStatus = User::where('user_name', $request->user_name)->select('active_status')->first();
        if ((int)$checkActiveStatus['active_status'] === 1) {
            $user['token'] =  $user->createToken($user)->plainTextToken;
            return $this->response->jsonResponse(false, 'Logged in Successfully',$user, 201);
        }
        return $this->response->jsonResponse(true, 'This user has been deactivated',[], 201);
    }

    public function users() {
        return response(User::all(), 201);
    }
    public function activateUser($region_id){
        $getSize = User::where('id', $region_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'User Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'User Not Available', [], 201);
        }
    }
    public function deActivateUser($region_id){
        $getSize = User::where('id', $region_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'User De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'User Not Available', [], 201);
        }
    }
    public function createUser(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('User Details create', 'store'), User::create($request->all()), 201);
        } else {
            return $validate;
        }
    }
    public function updateUser(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('User Details update', 'update'), User::create($request->all()), 201);
        } else {
            return $validate;
        }
    }
    
}
