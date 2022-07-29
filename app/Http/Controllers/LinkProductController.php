<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LinkProduct;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;

class LinkProductController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'link_id' => 'required',
            'product_id' => 'required|numeric',
            'color_id' => 'required|numeric',
        ];
    }

    public function index()
    {
        $linkProducts = LinkProduct::with('products.images', 'color')->latest()->get();
        return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'index'), $linkProducts, 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'store'), LinkProduct::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'show'), $this->findLinkProduct($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'update'), $this->findLinkProduct($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $link = $this->findLinkProduct($id);
        if($link) {
            return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'destroy'), $link->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'LinkProduct Not Exists',[], 201);
    }

    public function linkProductSwitch($id) {
        $link = $this->findLinkProduct($id);
        if($link) {
            $value = $link->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'LinkProduct '.$msg.' SuccessFully', $link->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'LinkProduct Not Exists',[], 201);
    }

    public function getActiveLinkedProducts() {
        return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'getActive'), LinkProduct::where('active_status', 1)->get(), 200);
    }

    public function searchLinkedProducts($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'search'), LinkProduct::where('color_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findLinkProduct($id) {
        return LinkProduct::find($id);
    }

    public function addLinkProduct(Request $request) {
        $input = $request->all();
        $validate = $this->response->validate($input, $this->storeRules);
        if($validate === true) {
            if (LinkProduct::where('product_id', $input['product_id'])->exists()) {
                return $this->response->jsonResponse(true, 'This Product Already Linked', [], 200);
            }
            $checkColor = LinkProduct::where('link_id', $input['link_id'])->where('color_id', $input['color_id'])->first();
            if(!$checkColor) {
                return $this->response->jsonResponse(false, $this->response->message('LinkProduct', 'store'), LinkProduct::create($input), 200);
            } else {
                return $this->response->jsonResponse(true, 'Already Linked with this color', [], 200);
            }
        } else {
            return $validate;
        }
    }

    public function listLinkProduct($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('List Link Product', 'show'), LinkProduct::with('products.images', 'color')->where('link_id', $id)->latest()->get(), 200);
    }

    public function deleteLinkProduct($id) {
        $link = $this->findLinkProduct($id);
        if($link) {
            return $this->response->jsonResponse(false, 'Product Removed SuccessFully', $link->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Product Not Exists',[], 201);
    }
}
