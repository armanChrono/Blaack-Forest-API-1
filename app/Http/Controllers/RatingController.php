<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Rating;
use App\Repositories\ResponseRepository;

class RatingController extends Controller
{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'rating' => 'required',
            'product_id' => 'required',
            'customer_id' => 'required',
            'rating_starts' => 'required'
        ];
    }

    public function createRating(Request $request){
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Rating', 'store'), Rating::create($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function updateRating(Request $request){
        $validate = $this->response->validate($request->all(), [
            'rating_id' => [
                'required'
            ],
            'rating' => 'required',
            'product_id' => 'required',
            'customer_id' => 'required',
            'rating_starts' => 'required'
        ]);

        if($validate === true) {
            return $this->response->jsonResponse(false, $this->response->message('Rating', 'update'), Rating::where('rating_id', $request->rating_id)->update($request->all()), 200);
        } else {
            return $validate;
        }
    }

    public function getAllRatingList(){
        return $this->response->jsonResponse(false, 'All Rating Listed', Rating::get(), 200);
    }

   
    public function deleteRating($id){
        $size = Rating::where('rating_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Rating', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Rating Not Exists',[], 201);
    }
}
