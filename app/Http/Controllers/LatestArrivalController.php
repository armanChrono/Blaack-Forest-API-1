<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LatestArrival;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class LatestArrivalController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'latest_arrival_name' => 'required|unique:latest_arrivals,latest_arrival_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('LatestArrival', 'index'), LatestArrival::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('LatestArrival', 'store'), LatestArrival::create($request->all()), 200);
        } else {
            return $validate;
        }        
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('LatestArrival', 'show'), $this->findLatestArrival($id), 200);
    }

    public function update(LatestArrival $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'banner_name' => [
                'required',
                Rule::unique('banners')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('LatestArrival', 'update'), $this->findLatestArrival($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $size = $this->findLatestArrival($id);
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('LatestArrival', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Latest Arrival Not Exists',[], 201);
    }

    public function latestArrivalSwitch($id) {
        $size = $this->findLatestArrival($id);
        if($size) {
            $value = $size->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'LatestArrival '.$msg.' SuccessFully', $size->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Latest Arrival Not Exists',[], 201);
    }

    public function findLatestArrival($id) {
        return LatestArrival::find($id);
    }

    //image update for category
    public function latestArrivalImageUpdate(Request $request)
    {
        $getBanner = $this->findLatestArrival($request['latest_arrival_id']);
        if($request->hasFile('latest_arrival_image')) {
            $file = $request->file('latest_arrival_image');
            $name = $this->response->generateSlug($getBanner['latest_arrival_name']);
            $uploadUrl = $this->response->cloudinaryImage($request->file('latest_arrival_image'), 'latest_arrivals', $name);
            return $this->response->jsonResponse(false, $this->response->message('Latest Arrival', 'image'), $getBanner->update(['latest_arrival_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image is too high', [], 201);
        }
    }


    
}

