<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'color_name' => 'required|unique:colors,color_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, Color::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Color', 'store'), Color::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Color', 'show'), $this->findColor($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'color_name' => [
                'required',
                Rule::unique('colors')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Color', 'update'), $this->findColor($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $color = $this->findColor($id);
        if($color) {
            return $this->response->jsonResponse(false, $this->response->message('Color', 'destroy'), $color->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Color Not Exists',[], 201);
    }

    public function colorSwitch($id) {
        $color = $this->findColor($id);
        if($color) {
            $value = $color->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Color '.$msg.' SuccessFully', $color->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Color Not Exists',[], 201);
    }

    public function getActiveColor() {
        return $this->response->jsonResponse(false, $this->response->message('Color', 'getActive'), Color::where('active_status', 1)->get(), 200);
    }

    public function searchColor($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Color', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Color', 'search'), Color::where('color_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findColor($id) {
        return Color::find($id);
    }
}
