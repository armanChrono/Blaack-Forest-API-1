<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\SubCategoryTag;
use App\Models\Tag;
use App\Repositories\ResponseRepository;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'category_id' => 'required|numeric',
            'sub_category_name' => 'required|unique:sub_categories,sub_category_name,NULL,id,deleted_at,NULL',
            'sub_category_description' => 'required',
            'hsn' => 'required',
            'tax_id' => 'required'           
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'index'), SubCategory::with('tags', 'category')->get(), 200);
    }

   

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
             $create = SubCategory::create($input);
            // $tags = new SubCategoryTag;
            // $tags->sub_category_id = $create['id'];
            // $tags->sub_category_tags = $input['sub_category_tags'];
            // $tags->save();
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'store'), $create, 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'show'), SubCategory::with('tags', 'category')->where('id', $id)->first(), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'category_id' => 'required|numeric',
            'sub_category_name' => [
                'required',
                Rule::unique('sub_categories')->ignore($id)->whereNull('deleted_at'),
            ],
            'sub_category_description' => 'required'
        ]);
        if($validate === true) {
            $input = $request->all();
            $update = $this->findSubCategory($id)->update($input);
            // SubCategoryTag::where('sub_category_id', $id)->update(['sub_category_tags' => $input['sub_category_tags']]);
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'update'), $update, 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $category = $this->findSubCategory($id);
        if($category) {
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'destroy'), $category->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'SubCategory Not Exists',[], 201);
    }

    public function subCategorySwitch($id) {
        $sc = $this->findSubCategory($id);
        if($sc) {
            $value = $sc->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'SubCategory '.$msg.' SuccessFully', $sc->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'SubCategory Not Exists',[], 201);
    }

    public function getActiveSubCategory() {
        return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'getActive'), SubCategory::with('tags', 'category')->where('active_status', 1)->orderBy('category_id')->get(), 200);
    }

    public function searchSubCategory($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'search'), SubCategory::where('sub_category_name', 'LIKE', $search.'%')->with('tags', 'category')->get(), 201);
    }

    public function findSubCategory($id) {
        return SubCategory::find($id);
    }

    //Create SubCategory
    public function createSubCategory(Request $request)
    {
        $input = $request->all();
        $checkExisting = SubCategory::where('sub_category_name', $input['sub_category_name'])->exists();
        if ($checkExisting) {
            return $this->response->jsonResponse(true, $input['sub_category_name'] . ' Already Exists', [], 201);
        }

        $validator = Validator::make($input, [
            'category_id' => 'required',
            'sub_category_name' => 'required',
            'sub_category_description' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'SubCategory Creation Failed', $validator->errors(), 401);
        }
        $input['sub_category_slug'] = $this->response->generateSlug($input['sub_category_name']);
        // $create = SubCategory::create($input);
        // $tags = new SubCategoryTag;
        // $tags->sub_category_id = $create['sub_category_id'];
        // $tags->sub_category_tags = $input['sub_category_tags'];
        // $tags->save();
        return $this->response->jsonResponse(false, 'SubCategory Created Successfully', [], 201);
    }

    //updating a SubCategory
    public function updateSubCategory(Request $request)
    {
        $input = $request->all();
        if (SubCategory::where('sub_category_id', $input['sub_category_id'])->exists()) {
            if (SubCategory::where('sub_category_name', $input['sub_category_name'])->whereNotIn('sub_category_id', [$input['sub_category_id']])->exists()) {
                return $this->response->jsonResponse(true, $input['sub_category_name'] . ' Already Exists', [], 201);
            }

            $validator = Validator::make($input, [
                'sub_category_name' => 'required',
                'sub_category_description' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->response->jsonResponse(true, 'SubCategory Updation Failed', $validator->errors(), 401);
            }

            $input['sub_category_slug'] = $this->response->generateSlug($input['sub_category_name']);
            SubCategory::where('sub_category_id', $request->sub_category_id)->update($input);
            // SubCategoryTag::where('sub_category_id', $request->sub_category_id)->update(['sub_category_tags' => $request->sub_category_tags]);
            return $this->response->jsonResponse(false, 'SubCategory Updated Successfully', [], 201);
        } else {
            return $this->response->jsonResponse(false, 'SubCategory Not Available', [], 201);
        }
    }

    //image update for category
    public function imageUpdateSubCategory(Request $request)
    {
        $getSC = SubCategory::where('id', $request->id)->select('sub_category_slug', 'sub_category_image')->first();
        if($request->hasFile('sub_category_image')) {
            $uploadUrl = $this->response->cloudinaryImage($request->file('sub_category_image'), 'subcategories', $getSC->sub_category_slug);
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'image'), SubCategory::where('id', $request['id'])->update(['sub_category_image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Size is too high', [], 201);
        }
    }

    //deleting a SubCategory
    public function deleteSubCategory($sub_category_id)
    {
        return $this->response->jsonResponse(false, 'SubCategory Deleted Successfully', SubCategory::where('sub_category_id', $sub_category_id)->delete(), 201);
    }

    //activate a SubCategory will show a category in a panel
    public function activateSubCategory($sub_category_id)
    {
        $getSubCategory = SubCategory::where('sub_category_id', $sub_category_id);
        if ($getSubCategory->exists()) {
            return $this->response->jsonResponse(false, 'SubCategory Activated Successfully', $getSubCategory->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'SubCategory Not Available', [], 201);
        }
    }

    //deactivate a SubCategory will show a category in a panel
    public function deActivateSubCategory($sub_category_id)
    {
        $getSubCategory = SubCategory::where('sub_category_id', $sub_category_id);
        if ($getSubCategory->exists()) {
            return $this->response->jsonResponse(false, 'SubCategory De-Activated Successfully', $getSubCategory->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'SubCategory Not Available', [], 201);
        }
    }

    //listing all the listallSubCategories
    public function listAllSubCategories()
    {
        return $this->response->jsonResponse(false, 'SubCategory Listed Successfully', SubCategory::with('tags', 'category')->latest()->get(), 201);
    }

    //listing active SubCategories
    public function listActiveSubCategories()
    {
        return $this->response->jsonResponse(false, 'Active SubCategories Listed Successfully', SubCategory::with('tags','category')->where('active_status', 1)->get(), 201);
    }

    //get SubCategories with product
    public function getSubCategoryWithProducts($sub_category_slug)
    {
        return $this->response->jsonResponse(false, 'SubCategories Product Listed Successfully', SubCategory::with('products.images')->where('active_status', 1)->where('sub_category_slug', $sub_category_slug)->first(), 201);
    }

    // //Searching a SubCategories
    // public function searchSubCategory($search)
    // {
    //     if ($search === "null") {
    //         return $this->response->jsonResponse(false, 'SubCategory filtered Successfully', [], 201);
    //     }

    //     return $this->response->jsonResponse(false, 'SubCategory filtered Successfully', SubCategory::where('sub_category_name', 'LIKE', $search . '%')->get(), 201);
    // }


}
