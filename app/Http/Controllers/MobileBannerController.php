<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileBanner;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MobileBannerController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'mobile_banner_name' => 'required|unique:banners_mobile,mobile_banner_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'index'), MobileBanner::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'store'), MobileBanner::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'show'), $this->findMobileBanner($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'mobile_banner_name' => [
                'required',
                Rule::unique('banners_mobile')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'update'), $this->findMobileBanner($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $size = $this->findMobileBanner($id);
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'MobileBanner Not Exists',[], 201);
    }

    public function mobileBannerSwitch($id) {
        $size = $this->findMobileBanner($id);
        if($size) {
            $value = $size->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'MobileBanner '.$msg.' SuccessFully', $size->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'MobileBanner Not Exists',[], 201);
    }

    public function findMobileBanner($id) {
        return MobileBanner::find($id);
    }

    //image update
    public function imageUpdateMobileBanner(Request $request)
    {
        $getBanner = $this->findMobileBanner($request['banner_id']);
        if($request->hasFile('mobile_banner_image')) {
            $file = $request->file('mobile_banner_image');
            $name = $this->response->generateSlug($getBanner['mobile_banner_name']);
            $uploadUrl = $this->response->cloudinaryImage($request->file('mobile_banner_image'), 'mobileBanners', $name);
            return $this->response->jsonResponse(false, $this->response->message('MobileBanner', 'image'), $getBanner->update(['mobile_banner_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image MobileBanner is too high', [], 201);
        }
    }
}
