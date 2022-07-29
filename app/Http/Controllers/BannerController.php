<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'banner_name' => 'required|unique:banners,banner_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Banner', 'index'), Banner::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Banner', 'store'), Banner::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Banner', 'show'), $this->findBanner($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'banner_name' => [
                'required',
                Rule::unique('banners')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Banner', 'update'), $this->findBanner($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $size = $this->findBanner($id);
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Banner', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Banner Not Exists',[], 201);
    }

    public function bannerSwitch($id) {
        $size = $this->findBanner($id);
        if($size) {
            $value = $size->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Banner '.$msg.' SuccessFully', $size->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Banner Not Exists',[], 201);
    }

    public function findBanner($id) {
        return Banner::find($id);
    }

    //image update for category
    public function imageUpdateBanner(Request $request)
    {
        $getBanner = $this->findBanner($request['banner_id']);
        if($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $name = $this->response->generateSlug($getBanner['banner_name']);
            $uploadUrl = $this->response->cloudinaryImage($request->file('banner_image'), 'banners', $name);
            return $this->response->jsonResponse(false, $this->response->message('Banner', 'image'), $getBanner->update(['banner_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Banner is too high', [], 201);
        }
    }


    
}
