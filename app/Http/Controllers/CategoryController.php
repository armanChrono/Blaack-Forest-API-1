<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Repositories\ResponseRepository;
use Illuminate\Validation\Rule;
use Cloudinary;

class CategoryController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'category_name' => 'required|unique:categories,category_name,NULL,category_id,deleted_at,NULL',
            'category_description' => 'required'
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Category', 'index'), Category::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Category', 'store'), Category::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Category', 'show'), $this->findCategory($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'category_name' => [
                'required',
                Rule::unique('categories')->ignore($id)->whereNull('deleted_at'),
            ],
            'category_description' => 'required'
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Category', 'update'), $this->findCategory($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $category = $this->findCategory($id);
        if($category) {
            return $this->response->jsonResponse(false, $this->response->message('Category', 'destroy'), $category->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Category Not Exists',[], 201);
    }

    public function categorySwitch($id) {
        $category = $this->findCategory($id);
        if($category) {
            $value = $category->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Category '.$msg.' SuccessFully', $category->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Category Not Exists',[], 201);
    }

    public function getActiveCategory() {
        return $this->response->jsonResponse(false, $this->response->message('Category', 'getActive'), Category::where('active_status', 1)->get(), 200);
    }

    public function searchCategory($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Category', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Category', 'search'), Category::where('category_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findCategory($id) {
        return Category::find($id);
    }

    //image update for category
    public function imageUpdateCategory(Request $request)
    {
        $getCategory = $this->findCategory($request['category_id']);
        if($request->hasFile('category_image')) {
            $uploadUrl = $this->response->cloudinaryImage($request->file('category_image'), 'categories', $getCategory->category_slug);
            return $this->response->jsonResponse(false, $this->response->message('Category', 'image'), $getCategory->update(['category_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Size is too high', [], 201);
        }
    }

    
    public function bannerImageCategory(Request $request)
    {
        $getCategory = $this->findCategory($request['category_id']);
        if($request->hasFile('category_image')) {
            $uploadUrl = $this->response->cloudinaryImage($request->file('category_image'), 'categories/banners', $getCategory->category_slug);
            return $this->response->jsonResponse(false, $this->response->message('Category', 'image'), $getCategory->update(['banner_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Size is too high', [], 201);
        }
    }


    //listing active Categories
    public function listActiveCategories()
    {
        return $this->response->jsonResponse(false, 'Active Categories Listed Successfully', Category::where('active_status', 1)->get(), 201);
    }

    //get Categories with product
    public function getCategoryWithProducts($category_slug)
    {
        return $this->response->jsonResponse(false, 'Categories Product Listed Successfully', Category::where('active_status', 1)->where('category_slug', $category_slug)->first(), 201);
    }

    //get all Categories with SC
    // public function getAllCategoryWithSC()
    // {
    //     return $this->response->jsonResponse(false, 'All Categories SC Listed Successfully', Category::with('subCategories:id,category_id,sub_category_name,sub_category_slug')->select('id', 'category_name')->where('active_status', 1)->get(), 201);
    // }

    //get Categories with SC
    public function getCategoryWithSC($category_slug)
    {
        return $this->response->jsonResponse(false, 'Categories Product Listed Successfully', Category::with('subCategories')->where('active_status', 1)->where('category_slug', $category_slug)->first(), 201);
    }
}
