<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewCard;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ViewCardController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'card_name' => 'required',
            'card_link' => 'required',

        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Card', 'index'), ViewCard::all(), 200);
    }


    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Card', 'store'), ViewCard::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Card', 'show'), $this->findBanner($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'card_name' => ['required'],
            'card_link' => ['required'],

        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Card', 'update'), $this->findBanner($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $size = $this->findBanner($id);
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Card', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Banner Not Exists',[], 201);
    }

    public function cardSwitch($id) {
        $size = $this->findBanner($id);
        if($size) {
            $value = $size->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Card '.$msg.' SuccessFully', $size->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Card Not Exists',[], 201);
    }

    public function findBanner($id) {
        return ViewCard::find($id);
    }

    //image update for category
    public function imageUpdateCard(Request $request)
    {
        $getBanner = $this->findBanner($request['card_id']);
        if($request->hasFile('card_image')) {
            $file = $request->file('card_image');
            $name = $this->response->generateSlug($getBanner['banner_name']);
            $uploadUrl = $this->response->cloudinaryImage($request->file('card_image'), 'cardimage', $name);
            return $this->response->jsonResponse(false, $this->response->message('Card Image', 'image'), $getBanner->update(['card_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Banner is too high', [], 201);
        }
    }

    public function searchCard($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Cards', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Cards', 'search'), ViewCard::where('card_name', 'LIKE', $search.'%')->get(), 201);
    }


    
}

