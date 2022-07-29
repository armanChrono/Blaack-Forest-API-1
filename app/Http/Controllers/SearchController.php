<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function webSearch($search) {
        $data = Product::where('active_status', 1)->with('images')->search($search)->get();
        // $data = Product::where('active_status', 1)->with('images')->whereHas('tags', function ($query) use ($search){
        //     $query->orWhere('product_tags', 'like', '%'.$search.'%');
        // })->search($search)->get();
        return $this->response->jsonResponse(false, 'Web Search Listed Successfully', $data, 201);
        // $data = [];
        // $data['cater'] = Cater::with(array(
        //     'details' => function($query) use ($search) {
        //         $query->select('cater_id', 'cater_category', 'cater_profile_image', 'cater_address', 'cater_pincode', 'cater_description', 'cater_started_year_at')->orWhere('cater_category', 'LIKE', '%'. $search . '%');
        // }))->select('cater_id', 'cater_name', 'cater_slug', 'top_status')->orWhere('cater_name', 'LIKE', '%'. $search . '%')->get();
        // if(sizeof($data['cater']) === 0 && sizeof($data['categories']) !== 0) {
        //     $getCater = [];
        //     foreach ($data['categories'] as $category) {
        //         array_push($getCater, $this->getCaterWithSlug($category['category_slug']));
        //     }
        //     $data['cater'] = $getCater[0];
        // }
    }

}
