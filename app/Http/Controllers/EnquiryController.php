<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\log;
use App\Repositories\ResponseRepository;
use Illuminate\Http\Request;
use App\Models\Enquiry;
use Carbon\Carbon;

class EnquiryController extends Controller

{
    public function __construct(ResponseRepository $response) {
        $this->response = $response;
        $this->storeRules = [
            'name' => 'required',
            'mobile' => 'required',
            'occation'=> 'required',
            'order_date'=> 'required',
            'order_time'=> 'required',
            'location'=> 'required'
        ];
    }
    public function createEnquiry(Request $request)
    {
         $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
             if($request->hasFile('image')) {
                  $name = $request->name."_".rand(0, 999);
                 $uploadUrl = $this->response->cloudinaryImage($request->file('image'), 'enquiryImage', $name);
                 $input['image'] = $uploadUrl;
             }else{
                $request['image'] = null;
             }
              return $this->response->jsonResponse(false, "Submitted Successfully, Blaack Forest Team will contact you soon...", Enquiry::create($input), 200);
        } else {
             return $validate;
        }
    }
    public function getAllActiveEnquiry()
    {
        return $this->response->jsonResponse(false, $this->response->message('Enquiry', 'index'), Enquiry::all(), 200);
    }
    
    public function closeEnquiry($id)
    {
        $enquiry = Enquiry::where('id', $id);
        if ($enquiry->exists()) {
            return $this->response->jsonResponse(false, 'Enquiry Closed Successfully', $enquiry->update(['status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'Enquiry Not Available', [], 202);
        }
    }
}
