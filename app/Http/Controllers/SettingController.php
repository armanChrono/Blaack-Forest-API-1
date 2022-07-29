<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryCharge;
use App\Repositories\ResponseRepository;
use App\Models\Setting;


class SettingController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function getSettings()
    {
        return $this->response->jsonResponse(false, $this->response->message('Setting', 'index'), Setting::first(), 200);
    }

    public function setSettings(Request $request) {
        return $this->response->jsonResponse(false, 'Settings Updated SuccessFully', Setting::first()->update([
            'delivery_charge' => $request->delivery_charge,
            'cgst_tax' => $request->cgst_tax,
            'sgst_tax' => $request->sgst_tax,
        ]), 200);
    }
}
