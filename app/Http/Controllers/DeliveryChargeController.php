<?php

namespace App\Http\Controllers;
use App\Models\DeliveryCharge;
use App\Repositories\ResponseRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\CustomerAddress;
use App\Models\LocationDetails;
use Illuminate\Support\Facades\Log;


class DeliveryChargeController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    public function getDeliveryCharge($addressId, $regionId)
    {
        $debug = true;
        if($debug){
            $customerAddress =  CustomerAddress::where('customer_address_id', $addressId)->first();
            if($customerAddress){
                $address = $customerAddress ->address_details;
                $town = $customerAddress ->address_locality_town;
                $district = $customerAddress ->address_city_district;
                $state = $customerAddress ->address_state;

                    $data = [
                        'origin_addresses'=>"Kamarajar Salai, Madurai, Tamil Nadu, India",
                        'destination_addresses'=>"Anbu Systems, No 386, 1, Anna Nagar Main Road, Kuruvikaran Salai, Anna Nagar, Ranan Nagar, Madurai, Tamil Nadu 625001, India",
                        'distance'=>1136,
                        'duration'=>"3 mins",
                        'rate'=> "20",
                        'rate_without_gst'=>16.4,
                        'cgst_percent'=>9,
                        'sgst_percent'=>9,
                        'cgst_amount'=>1.8,
                        'sgst_amount'=>1.8,
                        'status'=>"OK"
                    ];

                    return $this->response->jsonResponse(false, "Delivery Charge Listed Successfully.", $data, 200);

             }else{
                return $this->response->jsonResponse(true, "No Address Found.", "", 200);

             }

        }else{
            $customerAddress =  CustomerAddress::where('customer_address_id', $addressId)->first();
            if($customerAddress){
                $street = $customerAddress ->street;
                $area = $customerAddress ->area;
                $town = $customerAddress ->address_locality_town;
                $district = $customerAddress ->address_city_district;
                $state = $customerAddress ->address_state;

                $locationDetails =  LocationDetails::where('region_id', $regionId)->where('active_status', 1)->first();
                if($locationDetails){
                    $response =  $this->getDistance($street, $area, $town, $district, $state, $locationDetails->address);
                    if(isset($response['rows'][0]['elements'][0]['distance'])){
                         $distance = round($response['rows'][0]['elements'][0]['distance']['value'] / 1000 ,0); //convert to km
                        Log::info("DISTANCE : ".json_encode($distance));

                        $deliveryCharge = DeliveryCharge::where('distance_from', '<', $distance)
                                                        ->where('distance_to', '>=', $distance)
                                                        ->where('region_id', $regionId)->first();
                        // $deliveryChargeCount = $deliveryCharge->count();
                        if($deliveryCharge){
                            $rateWithOutGst =  $deliveryCharge['rate'] / ((100 + $deliveryCharge['cgst'] + $deliveryCharge['sgst']) / 100);
                            $rateWithGst = $deliveryCharge['rate'];
                            $gstAmount = ($rateWithGst - $rateWithOutGst);
                            

                            $data = [
                                'origin_addresses'=>$response['origin_addresses'][0],
                                'destination_addresses'=>$response['destination_addresses'][0],
                                'distance'=>$response['rows'][0]['elements'][0]['distance']['value'],
                                'duration'=>$response['rows'][0]['elements'][0]['duration']['text'],
                                'rate'=>round($rateWithGst, 2),
                                'rate_without_gst'=>round($rateWithOutGst, 2),
                                'cgst_percent'=>$deliveryCharge['cgst'],
                                'sgst_percent'=>$deliveryCharge['sgst'],
                                'cgst_amount'=>round($gstAmount/2, 2),
                                'sgst_amount'=>round($gstAmount/2, 2),
                                'status'=>$response['status']
                            ];

                            return $this->response->jsonResponse(false, "Delivery Charge Listed Successfully.", $data, 200);
                         }else{
                            return $this->response->jsonResponse(true, "This address cannot be delivered, Please check the address", "", 201);
                         }
                    }else{
                          return $this->response->jsonResponse(true, "Something went wrong, Please try again later", "", 202);

                    }
                }else{
                     return $this->response->jsonResponse(true, "No Origin Address Found.", "", 205);
                }

             }else{
                return $this->response->jsonResponse(true, "No Address Found.", "", 203);
             }
        }
    }

    public function getDistance($street, $area, $town, $district, $state, $originAddress) {
        $client = new Client();
        $apiKey = env("DISTANCE_MATRIX_KEY", "");
        $url = "https://api.distancematrix.ai/maps/api/distancematrix/json?origins=".$originAddress.",india&destinations=".$street.",".$area.",".$district.",".$state.",india&key=".$apiKey;
        // $url = "https://api.distancematrix.ai/maps/api/distancematrix/json?origins=kamarajar%20salai,%20madurai,tamil%20nadu,india&destinations=".$address.", ".$town.",".$district.",".$state.",india&key=".$apiKey;
        Log::info($url);
        $res = $client->request('GET', $url);

        $response = json_decode($res->getBody(), true);

        return $response;
    }
    public function getAllDeliveryCharge(){
        return $this->response->jsonResponse(false, 'All Delivery Charges Listed', DeliveryCharge::with('location')->orderBy('region_id', 'DESC')->get(), 200);
    }
}
