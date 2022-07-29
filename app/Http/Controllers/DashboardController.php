<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ResponseRepository;
use DB;
use App\Models\Order;

class DashboardController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function adminDashboard()
    {
        $data = [];
        $data['submitted_order'] = $this->getCount('Submitted');
        $data['confirmed_order'] = $this->getCount('Confirmed');
        $data['processed_order'] = $this->getCount('Processed');
        $data['shipped_order'] = $this->getCount('Shipped');
        $data['delivered_order'] = $this->getCount('Delivered');
        $data['cancelled_order'] = $this->getCount('Cancelled');
        $data['online_payment_order'] = $this->getCount('Online Payments', 'payment_mode');
        $data['cod_order'] = $this->getCount('Cash On Delivery', 'payment_mode');

        return $this->response->jsonResponse(false, 'Dashboard Data Listed Successfully', $data, 201);
    }

    public function getCount($value, $column = 'order_status') {
       return Order::where($column, $value)->get()->count();
    }
}
