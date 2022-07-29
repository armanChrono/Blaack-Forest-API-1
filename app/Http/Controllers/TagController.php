<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\SubCategoryTag;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'tag_name' => 'required|unique:tags,tag_name,NULL,id,deleted_at,NULL',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Tag', 'index'), Tag::all(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Tag', 'store'), Tag::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Tag', 'show'), $this->findTag($id), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [
            'tag_name' => [
                'required',
                Rule::unique('tags')->ignore($id)->whereNull('deleted_at'),
            ],
        ]);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Tag', 'update'), $this->findTag($id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function destroy($id)
    {
        $tag = $this->findTag($id);
        if($tag) {
            return $this->response->jsonResponse(false, $this->response->message('Tag', 'destroy'), $tag->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Tag Not Exists',[], 201);
    }

    public function tagSwitch($id) {
        $tag = $this->findTag($id);
        if($tag) {
            $value = $tag->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Tag '.$msg.' SuccessFully', $tag->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Tag Not Exists',[], 201);
    }

    public function getActiveTag() {
        return $this->response->jsonResponse(false, $this->response->message('Tag', 'getActive'), Tag::where('active_status', 1)->get(), 200);
    }

    public function searchTag($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Tag', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Tag', 'search'), Tag::where('tag_name', 'LIKE', $search.'%')->get(), 201);
    }

    public function findTag($id) {
        return Tag::find($id);
    }

    //Create Tag
    public function createTag(Request $request)
    {
        $input = $request->all();
        $checkExisting = Tag::where('tag_name', $input['tag_name'])->exists();
        if ($checkExisting) {
            return $this->response->jsonResponse(true, $input['tag_name'] . ' Already Exists', [], 201);
        }

        $validator = Validator::make($input, [
            'tag_name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Tag Creation Failed', $validator->errors(), 401);
        }

        $create = Tag::create($input);
        return $this->response->jsonResponse(false, 'Tag Created Successfully', [], 201);
    }

    //updating a Tag
    public function updateTag(Request $request)
    {
        $input = $request->all();
        if (Tag::where('tag_id', $input['tag_id'])->exists()) {
        if (Tag::where('tag_name', $input['tag_name'])->exists()) {
            return $this->response->jsonResponse(true, $input['tag_name'] . ' Already Exists', [], 201);
        }

        $validator = Validator::make($input, [
            'tag_name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response->jsonResponse(true, 'Tag Updation Failed', $validator->errors(), 401);
        }

        return $this->response->jsonResponse(false, 'Tag Updated Successfully', Tag::where('tag_id', $request->tag_id)->update($input), 201);
        } else {
        return $this->response->jsonResponse(false, 'Tag Not Available', [], 201);
        }
    }

    //deleting a Tag
    public function deleteTag($tag_id)
    {
        return $this->response->jsonResponse(false, 'Tag Deleted Successfully', Tag::where('tag_id', $tag_id)->delete(), 201);
    }

    //activate a Tag will show a tag in a panel
    public function activateTag($tag_id)
    {
        $getTag = Tag::where('tag_id', $tag_id);
        if ($getTag->exists()) {
            return $this->response->jsonResponse(false, 'Tag Activated Successfully', $getTag->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Tag Not Available', [], 201);
        }
    }

    //deactivate a Tag will show a tag in a panel
    public function deActivateTag($tag_id)
    {
        $getTag = Tag::where('tag_id', $tag_id);
        if ($getTag->exists()) {
            return $this->response->jsonResponse(false, 'Tag De-Activated Successfully', $getTag->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Tag Not Available', [], 201);
        }
    }

    //listing all the listallTags
    public function listAllTags()
    {
        return $this->response->jsonResponse(false, 'Tag Listed Successfully', Tag::get(), 201);
    }

    //listing active Tags
    public function listActiveTags()
    {
        return $this->response->jsonResponse(false, 'Active Tags Listed Successfully', Tag::where('active_status', 1)->get(), 201);
    }


    // //Searching a Tags
    // public function searchTag($search)
    // {
    //     if ($search === "null") {
    //         return $this->response->jsonResponse(false, 'Tag filtered Successfully', [], 201);
    //     }

    //     return $this->response->jsonResponse(false, 'Tag filtered Successfully', Tag::where('tag_name', 'LIKE', $search . '%')->get(), 201);
    // }

    public function getTagDetails($id) {
        return $this->response->jsonResponse(false, 'Tag Details fetched Successfully', Tag::where('tag_id', $id)->select('tag_id', 'tag_name')->first(), 201);
    }

    public function getSCTagId($sub_category_id) {
        return SubCategoryTag::where('sub_category_id', $sub_category_id)->get();
    }
}
