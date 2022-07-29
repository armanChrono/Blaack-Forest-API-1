<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Size;
use App\Models\SubCategorySize;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'size_name' => 'required|unique:sizes,size_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Size', 'index'), Size::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Size', 'store'), Size::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Size', 'show'), $this->findSize($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'size_name' => [
                'required',
                Rule::unique('sizes')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Size', 'update'), $this->findSize($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $size = $this->findSize($id);
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Size', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Size Not Exists',[], 201);
    }

    public function sizeSwitch($id) {
        $size = $this->findSize($id);
        if($size) {
            $value = $size->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Size '.$msg.' SuccessFully', $size->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Size Not Exists',[], 201);
    }

    public function getActiveSize() {
        return $this->response->jsonResponse(false, $this->response->message('Size', 'getActive'), Size::where('active_status', 1)->get(), 200);
    }

    public function searchSize($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Size', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Size', 'search'), Size::where('size_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findSize($id) {
        return Size::find($id);
    }


    //Create Size
    public function createSize(Request $request)
    {
        $input = $request->all();
        $checkExisting = Size::where('size_name', $input['size_name'])->exists();
        if ($checkExisting) {
            return $this->response->jsonResponse(true, $input['size_name'] . ' Already Exists', [], 201);
        }

        $validator = Validator::make($input, [
            'size_name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Size Creation Failed', $validator->errors(), 401);
        }

        $create = Size::create($input);
        return $this->response->jsonResponse(false, 'Size Created Successfully', [], 201);
    }

    //updating a Size
    public function updateSize(Request $request)
    {
        $input = $request->all();
        if (Size::where('size_id', $input['size_id'])->exists()) {
        if (Size::where('size_name', $input['size_name'])->exists()) {
            return $this->response->jsonResponse(true, $input['size_name'] . ' Already Exists', [], 201);
        }

        $validator = Validator::make($input, [
            'size_name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Size Updation Failed', $validator->errors(), 401);
        }

        return $this->response->jsonResponse(false, 'Size Updated Successfully', Size::where('size_id', $request->size_id)->update($input), 201);
        } else {
        return $this->response->jsonResponse(false, 'Size Not Available', [], 201);
        }
    }

    //deleting a Size
    public function deleteSize($size_id)
    {
        return $this->response->jsonResponse(false, 'Size Deleted Successfully', Size::where('size_id', $size_id)->delete(), 201);
    }

    //activate a Size will show a tag in a panel
    public function activateSize($size_id)
    {
        $getSize = Size::where('size_id', $size_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Size Activated Successfully', $getSize->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Size Not Available', [], 201);
        }
    }

    //deactivate a Size will show a tag in a panel
    public function deActivateSize($size_id)
    {
        $getSize = Size::where('size_id', $size_id);
        if ($getSize->exists()) {
            return $this->response->jsonResponse(false, 'Size De-Activated Successfully', $getSize->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Size Not Available', [], 201);
        }
    }

    //listing all the listallSizes
    public function listAllSizes()
    {
        return $this->response->jsonResponse(false, 'Size Listed Successfully', Size::get(), 201);
    }

    //listing active Sizes
    public function listActiveSizes()
    {
        return $this->response->jsonResponse(false, 'Active Sizes Listed Successfully', Size::where('active_status', 1)->get(), 201);
    }


    //Searching a Sizes
    // public function searchSize($search)
    // {
    //     if ($search === "null") {
    //         return $this->response->jsonResponse(false, 'Size filtered Successfully', [], 201);
    //     }

    //     return $this->response->jsonResponse(false, 'Size filtered Successfully', Size::where('size_name', 'LIKE', $search . '%')->get(), 201);
    // }

    public function getSizeDetails($id) {
        return $this->response->jsonResponse(false, 'Size Details fetched Successfully', Size::where('size_id', $id)->select('size_id', 'size_name')->first(), 201);
    }

    public function getSCSizeId($sub_category_id) {
        return SubCategorySize::where('sub_category_id', $sub_category_id)->get();
    }
}
