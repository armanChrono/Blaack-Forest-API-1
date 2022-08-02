<!DOCTYPE html>
<html>
<head>



    <title>BF_INVOICE_#{{$order_id}}</title>
</head>
<style type="text/css">
    body{
        font-family: 'Roboto Condensed', sans-serif;
    }
    .m-0{
        margin: 0px;
    }
    .p-0{
        padding: 0px;
    }
    .pt-5{
        padding-top:5px;
    }
    .mt-10{
        margin-top:10px;
    }
    .text-center{
        text-align:center !important;
    }
    .w-100{
        width: 100%;
    }
    .w-50{
        width:50%;
    }
    .w-85{
        width:85%;
    }
    .w-15{
        width:15%;
    }
    .logo img{
        width:45px;
        height:45px;
        padding-top:30px;
    }
    .logo span{
        margin-left:8px;
        top:19px;
        position: absolute;
        font-weight: bold;
        font-size:25px;
    }
    .gray-color{
        color:#5D5D5D;
    }
    .text-bold{
        font-weight: bold;
    }
    .border{
        border:1px solid black;
    }
    table tr,th,td{
        border: 1px solid black;
        border-collapse:collapse;
        padding:5px;
    }
    table tr th{
        background: #f0f0f0;
        font-size:11px;
    }
    table tr td{
        font-size:10px;
    }
    table{
        border-collapse:collapse;
    }
    .box-text p{
        line-height:10px;
    }
    .float-left{
        float:left;
    }
    .total-part{
        font-size:16px;
        line-height:12px;
    }
    .total-right p{
        padding-right:20px;
    }
    table tr td th, tr table tr td{
        width: 100%;
    }
  .goods td:nth-child(1){
    text-align: center;
  }
  .goods td:nth-child(2){
    text-align: left;
  }
  .goods td:nth-child(3),td:nth-child(4), td:nth-child(6){
    text-align: center;
  }
  .goods td:nth-child(5), td:nth-child(7){
    text-align: right;
  }
  .table-body tr td, .table-body tr{
    border-top: none !important;
    border-bottom: none !important;
  }
  .less-line_height{
      line-height: 10px;
  }
  .text-right{
      text-align: right;
  }
  .taxes td:nth-child(3), .taxes td:nth-child(5){
    text-align: center;
  }
  .taxes td:nth-child(2), .taxes td:nth-child(4), .taxes td:nth-child(6), .taxes td:nth-child(7){
    text-align: right;
  }
  .total-row td{
      font-weight: bold;
  }



</style>
<body>
<div class="head-title">
    <h4 class="text-center m-0 p-0">TAX INVOICE</h4>
</div>
<table class="table border w-100">
    <tr>
         <td colspan="3" rowspan="2" >
            <h1>Blaack Forest</h1>
            12, LakeView Road, KK Nagar,<br> Madurai - 625020<br> GSTIN/UIN : 33AWBPR0957LIZA<br> E-mail : online@blaackforestcakes.com<br> Contact : +91 8489955500
        </td>
        <td colspan="2"><b>Invoice No</b><br>{{$invoice_no}}</td>
        <td colspan="2"><b>Dated:</b>  <br>{{date('d-m-Y', strtotime($paid_at))}}</td>
    </tr>
    <tr >
        <td colspan="2"><b> Supplier's Ref No</b><br>BF</td>
        <td colspan="2"><b>Mode/Terms of Payment</b><br>{{ucfirst($payment_mode)}}</td>
    </tr>
    <tr>
        @if ($delivery_mode == 'Pick Up')
            <td rowspan="3" colspan="3">Buyer Name:<br>
                <b style="text-transform:capitalize">{{$billing_name}}</b><br>
                Contact : {{$billing_mobile_number}} <br>
                Delivery Mode : Pick Up<br>
                Shop Address :<br> <b>{{$shops['shop_name']}}</b> <br> {{$shops['address']}}<br>
                Shop Contact : {{$shops['mobile_no']}} <br>
                State : &nbsp; <b> {{$region['state']['name']}}</b> <br>
                Place of Supply: India <br>
            </td>
        @else
            <td rowspan="3" colspan="3">Buyer Name:<br>
                <b style="text-transform:capitalize">{{$billing_name}}</b><br>
                {{$ordered_address['doorNo']}}, {{$ordered_address['street']}}<br>
                {{$ordered_address['area']}},<br>
                {{$ordered_address['address_city_district']}},<br>
                State : &nbsp;<b>  {{ucfirst($region['state']['name'])}}</b> <br>
                Place of Supply: India <br>
                E-mail : {{$email}}<br>
                Contact : {{$billing_mobile_number}} <br>
                GSTIN/UIN:
            </td>
        @endif

        <td colspan="2"> <b>Buyer Order No</b> <br>{{$order_id}}</td>
        <td colspan="2"><b>Dated</b> <br>{{date('d-m-Y', strtotime($order_submitted_at))}}</td>
    </tr>
    <tr>
        <td colspan="2"><b>Dispatched Through</b><br>By Road</td>
        <td colspan="2"><b>Destination</b> <br>
            @if ($delivery_mode == 'Pick Up')
            {{ucfirst($region['city']['name'])}}
            @else
            {{ucfirst($ordered_address['address_city_district'])}}
            @endif

        </td>
    </tr>
    <tr rowspan="2">
        <td colspan="4" ><b>Terms of Delivery</b><br>NA</td>
    </tr>
    <tr>
        <th style="width: 10%">S.No</th>
        <th style="width: 30%">Description of Goods</th>
        <th>HSN/SAC</th>
        <th>Quantity</th>
        <th>Rate(INR)</th>
        <th>Per </th>
        <th>Amount(INR) </th>
    </tr>
    <tbody class="table-body" >
        <?php
        $totalWithOutGst = 0;
        $roundableTotal = 0.00;
        $hsnArrayList = array();
        $addonHsnArrayList = array();
        $hsnArray = array();
        $addonHsnArray = array(); 
        $gstDataArray = array();
        $gstArray = array();
        $deliverCharge = $deliver_fee; 
        $deliverChargeWithOutGst = parseAmount($deliver_fee / 1.18); 
        $deliverChargeGst = $deliverCharge - $deliverChargeWithOutGst;
        ?>
        @foreach($ordered_products as $product)

        <tr class="no-Y-border goods goods-min-height" >
            <td style="width: 10%">{{$loop->index+1}}</td>
            <td style="width: 30%">{{$product['product_details'][0]['product_name']}}<br>
                @if ($product['flavour']['flavour_name'] != 'Default')
                <b>Flavour:</b>&nbsp;{{$product['flavour']['flavour_name']}} <br>
                @endif

                <b>Weight:</b>&nbsp;{{$product['variation']['weight'][0]['weight_name']}}&nbsp; {{$product['product_details'][0]['unit']['unit_name']}}
                <br><b>Egg/Eggless:</b>&nbsp;{{$product['egg_or_eggless'] == 1 ? 'Eggless' : 'Egg'}}
            </td>
            <td>{{$product['product_details'][0]['hsn']}}</td>
            <td>{{$product['product_quantity']}}</td>
            <td class="text-right">{{parseAmount((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))}}</td>
            <td >{{$product['product_details'][0]['unit']['unit_name']}}</td>
            <td class="text-right">{{parseAmount((float)$product['product_total'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))}}
            <?php
            $totalWithOutGst += ($product['product_total'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
            $roundableTotal = parseAmount($totalWithOutGst);
            if (!empty($hsnArrayList))
            {
               if(in_array($product['product_details'][0]['hsn'], $hsnArray)){
                    $i = array_search($product['product_details'][0]['hsn'], $hsnArray);
                    $hsnArrayList[$i]['hsn'] = $product['product_details'][0]['hsn'];
                    $hsnArrayList[$i]['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                    $hsnArrayList[$i]['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $hsnArrayList[$i]['c_gst_amount'] += ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                    $hsnArrayList[$i]['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $hsnArrayList[$i]['s_gst_amount'] += ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                    $hsnArrayList[$i]['tax_price'] += $product['product_discount_price'] - ((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
                    $hsnArrayList[$i]['taxable_price'] +=  (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100);
                }else{
                    $data = array();
                    $data['hsn'] = $product['product_details'][0]['hsn'];
                    $data['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                    $data['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $data['c_gst_amount'] = ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                    $data['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $data['s_gst_amount'] = ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                    $data['tax_price'] = $product['product_discount_price'] - ((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
                    $data['taxable_price'] = ((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
                    array_push($hsnArrayList, $data);
                    array_push($hsnArray, $product['product_details'][0]['hsn']);
               }
            }
            else
            {
                $data = array();
                $data['hsn'] = $product['product_details'][0]['hsn'];
                $data['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                $data['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                $data['c_gst_amount'] = ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                $data['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                 $data['s_gst_amount'] = ($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2;
                $data['tax_price'] = $product['product_discount_price'] - ((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
                $data['taxable_price'] = ((float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
                array_push($hsnArrayList, $data);
                array_push($hsnArray, $product['product_details'][0]['hsn']);
             }


            //  Product Gst Calculation
             if (!empty($gstDataArray))
            {
               if(in_array($product['product_details'][0]['tax']['tax_percentage'], $gstArray)){
                
                    $i = array_search($product['product_details'][0]['tax']['tax_percentage'], $gstArray);
                    $gstDataArray[$i]['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                    $gstDataArray[$i]['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $gstDataArray[$i]['c_gst_amount'] += parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                    $gstDataArray[$i]['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $gstDataArray[$i]['s_gst_amount'] += parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                 }else{
                    $data = array();
                     $data['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                    $data['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $data['c_gst_amount'] = parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                    $data['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                    $data['s_gst_amount'] = parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                    array_push($gstDataArray, $data);
                    array_push($gstArray, $product['product_details'][0]['tax']['tax_percentage']);
               }
            }
            else
            {
                $data = array();
                 $data['tax_percent'] = $product['product_details'][0]['tax']['tax_percentage'];
                $data['c_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                $data['c_gst_amount'] = parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                $data['s_gst_percent'] = $product['product_details'][0]['tax']['tax_percentage']/2;
                 $data['s_gst_amount'] = parseAmount(($product['product_discount_price'] - (float)$product['product_discount_price'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100))/2);
                array_push($gstDataArray, $data);
                array_push($gstArray, $product['product_details'][0]['tax']['tax_percentage']);
             }
             ?>
            </td>
        </tr>

        @endforeach
        @if(count($ordered_addons) > 0)
            @foreach($ordered_addons as $addon)
            <tr class="no-Y-border goods goods-min-height" >
                <td style="width: 10%">{{ count($ordered_products) + $loop->index+1}}</td>
                <td style="width: 30%">{{$addon['product_name']}}</td>
                <td>{{$addon['hsn']}}</td>
                <td>{{$addon['quantity']}}</td>
                <td class="text-right">{{parseAmount((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))}}</td>
                <td>Pcs </td>
                <td class="text-right">{{parseAmount((float)$addon['total'] / ((100 + $addon['tax']['tax_percentage'] )/100))}}
                     <?php
                     $totalWithOutGst += ($addon['total'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                     $roundableTotal = parseAmount($totalWithOutGst);
            if (!empty($hsnArrayList))
            {
               if(in_array($addon['hsn'], $addonHsnArray)){
                    $i = array_search($addon['hsn'], $hsnArray);
                    $addonHsnArrayList[$i]['hsn'] = $addon['hsn'];
                    $addonHsnArrayList[$i]['tax_percent'] = $addon['tax']['tax_percentage'];
                    $addonHsnArrayList[$i]['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $addonHsnArrayList[$i]['c_gst_amount'] += ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                    $addonHsnArrayList[$i]['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $addonHsnArrayList[$i]['s_gst_amount'] += ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                    $addonHsnArrayList[$i]['tax_price'] += $addon['price'] - ((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                    $addonHsnArrayList[$i]['taxable_price'] +=  (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100);

                }else{
                    $data = array();
                    $data['hsn'] = $addon['hsn'];
                    $data['tax_percent'] = $addon['tax']['tax_percentage'];
                    $data['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $data['c_gst_amount'] = ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                    $data['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $data['s_gst_amount'] = ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                    $data['tax_price'] = $addon['price'] - ((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                    $data['taxable_price'] = ((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                    array_push($addonHsnArrayList, $data);
                    array_push($hsnArray, $addon['hsn']);
               }
            }
            else
            {
                $data = array();
                $data['hsn'] = $addon['hsn'];
                $data['tax_percent'] = $addon['tax']['tax_percentage'];
                $data['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                $data['c_gst_amount'] = ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                $data['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                $data['s_gst_amount'] = ($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2;
                $data['tax_price'] = $addon['price'] - ((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                $data['taxable_price'] = ((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100));
                array_push($hsnArrayList, $data);
                array_push($addonHsnArray, $addon['hsn']);
             }

              //  Addon Gst Calculation
              if (!empty($gstDataArray))
              {
                 if(in_array($addon['tax']['tax_percentage'], $gstArray)){
                     $i = array_search($addon['tax']['tax_percentage'], $gstArray);
                    $gstDataArray[$i]['tax_percent'] = $addon['tax']['tax_percentage'];
                    $gstDataArray[$i]['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $gstDataArray[$i]['c_gst_amount'] += parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                    $gstDataArray[$i]['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                    $gstDataArray[$i]['s_gst_amount'] += parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                    }else{
                        $data = array();
                        $data['tax_percent'] = $addon['tax']['tax_percentage'];
                        $data['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                        $data['c_gst_amount'] = parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                        $data['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                        $data['s_gst_amount'] = parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                        array_push($gstDataArray, $data);
                        array_push($gstArray, $addon['tax']['tax_percentage']);
                 }
              }
              else
              {
                $data = array();
                $data['tax_percent'] = $addon['tax']['tax_percentage'];
                $data['c_gst_percent'] = $addon['tax']['tax_percentage']/2;
                $data['c_gst_amount'] = parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                $data['s_gst_percent'] = $addon['tax']['tax_percentage']/2;
                $data['s_gst_amount'] = parseAmount(($addon['price'] - (float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100))/2);
                array_push($gstDataArray, $data);
                array_push($gstArray, $addon['tax']['tax_percentage']);
               }
                     ?>
                 </td>
            </tr>
            @endforeach
        @endif

 @if ($delivery_mode == 'Door Step Delivery')
     {{-- space before shipping --}}
     <tr class="no-Y-border goods shipping">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr class="no-Y-border goods shipping">
        <td></td>
        <td>Shipping Cost</td>
        <td>996519</td>
        <td>-</td>
        <td>{{parseAmount((float)$deliver_fee - (float)$order_gst_merge['shipping_gst_18'] )}}</td>
        <td>-</td>
        <td>{{parseAmount((float)$deliver_fee -(float) $order_gst_merge['shipping_gst_18'])}}</td>
    </tr>
  
@endif

         {{-- space after shipping --}}
        <tr class="no-Y-border goods shipping">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td>-------------- <br>
            {{parseAmount((float)$totalWithOutGst + (float)$deliver_fee - (float)$order_gst_merge['shipping_gst_18'])}} </td>
        </tr>
        <?php
             $gstPrice = 0.00;
             Log::info(json_encode($gstDataArray));
             Log::info(json_encode($gstArray));
        ?>
 @foreach($gstDataArray as $gst)
    <?php
        $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($gst['c_gst_amount']/2) + parseTwoDigit($gst['s_gst_amount']/2));
    ?>
    <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ {{$gst['tax_percent']/2}}% </td>
            <td></td>
            <td></td>
            <td>{{$gst['tax_percent']/2}} </td>
            <td>%</td>
            <td>{{parseAmount($gst['c_gst_amount'])}}</td>
        </tr>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ {{$gst['tax_percent']/2}}% </td>
            <td></td>
            <td></td>
            <td>{{$gst['tax_percent']/2}} </td>
            <td>%</td>
            <td>{{parseAmount($gst['c_gst_amount'])}}</td>
        </tr>

 @endforeach
        <!-- Old GST SPLIT CODE -->
        <!-- @if($order_gst_merge['gst_5'] > 0)
         <?php
            //  $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($order_gst_merge['gst_5']/2) + parseTwoDigit($order_gst_merge['gst_5']/2));
             ?>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ 2.5% </td>
            <td></td>
            <td></td>
            <td>2.5 </td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_5'])/2)}}</td>
        </tr>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output SGST @ 2.5% </td>
            <td></td>
            <td></td>
            <td>2.5 </td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_5'])/2)}}</td>
        </tr>
        @endif
        @if($order_gst_merge['gst_12'] > 0)
        <?php
            //  $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($order_gst_merge['gst_12']/2) + parseTwoDigit($order_gst_merge['gst_12']/2));
             ?>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ 6% </td>
            <td></td>
            <td></td>
            <td>6 </td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_12'])/2)}}</td>
        </tr>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output SGST @ 6% </td>
            <td></td>
            <td></td>
            <td>6</td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_12'])/2)}}</td>
        </tr>
        @endif
        @if($order_gst_merge['gst_18'] > 0)
        <?php
            //  $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($order_gst_merge['gst_18']/2) + parseTwoDigit($order_gst_merge['gst_18']/2));
        ?>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ 9% </td>
            <td></td>
            <td></td>
            <td>9</td>
            <td>%</td>
            <td>{{parseTwoDigit((float)($order_gst_merge['gst_18'])/2)}}</td>
        </tr>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output SGST @ 9% </td>
            <td></td>
            <td></td>
            <td>9</td>
            <td>%</td>
            <td>{{parseTwoDigit((float)($order_gst_merge['gst_18'])/2)}}</td>
        </tr>
        @endif
        @if($order_gst_merge['gst_28'] > 0)
        <?php
            //  $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($order_gst_merge['gst_28']/2) + parseTwoDigit($order_gst_merge['gst_28']/2));
             ?>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output CGST @ 14% </td>
            <td></td>
            <td></td>
            <td>14</td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_28'])/2)}}</td>
        </tr>
        <tr class="no-Y-border goods less-line_height">
            <td></td>
            <td>Output SGST @ 14% </td>
            <td></td>
            <td></td>
            <td>14</td>
            <td>%</td>
            <td>{{parseAmount((float)($order_gst_merge['gst_28'])/2)}}</td>
        </tr>
        @endif -->

        <?php
             Log::info("deliverChargeGst = ".$deliverChargeGst);
             Log::info("deliverChargeWithOutGst = ".$deliverChargeWithOutGst);
             Log::info("deliverCharge = ".$deliverCharge);
             Log::info("roundableTotal = ".$roundableTotal);
        ?>
        @if($deliver_fee > 0)
             <?php
             Log::info("deliverChargeGst = ".$deliverChargeGst);
             Log::info("deliverChargeWithOutGst = ".$deliverChargeWithOutGst);
             Log::info("deliverCharge = ".$deliverCharge);
             $roundableTotal = (float)parseTwoDigit($roundableTotal) + (float)(parseTwoDigit($deliverChargeGst));
             Log::info("roundableTotal = ".$roundableTotal);
             ?>
            <tr class="no-Y-border goods less-line_height">
                <td></td>
                <td>Output Shipping CGST @ 9% </td>
                <td></td>
                <td></td>
                <td>9</td>
                <td>%</td>
                <td>{{parseAmount((float)($deliverChargeGst)/2)}}</td>
            </tr>
            <tr class="no-Y-border goods less-line_height">
                <td></td>
                <td>Output Shipping SGST @ 9% </td>
                <td></td>
                <td></td>
                <td>9</td>
                <td>%</td>
                <td>{{parseAmount((float)($deliverChargeGst)/2)}}</td>
            </tr>
         @endif

    </tbody>

    <tfoot>
        <tr>
            <td></td>
            <td>Round Off</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>+ {{round($order_overall_totall - $roundableTotal, 2)}}</td>
        </tr>
        <tr class="total-row">
            <td></td>
            <td>Total</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{number_format((float)$order_overall_totall, 2, '.', ',')}}</td>
        </tr>
        <tr>

            <td colspan="2">AMT Chargeable(In words) <br>
            <b style="text-transform:uppercase"> INR {{numberTowords(round($order_overall_totall, 2))}}</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>E & O.E</td>
        </tr>
        <tr>
            <th>HSN/SAC</th>
            <th>Taxable Value</th>
            <th>CGST <br> Rate</th>
            <th>CGST <br> Amount</th>
            <th>SGST <br> Rate</th>
            <th>SGST <br> Amount</th>
            <th>Total Tax <br>Amount</th>
        </tr>
        <?php
            $totalTaxable = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalTaxPrice = 0;

        ?>

        @foreach($hsnArrayList as $hsn)

        <tr class="taxes">
            <td>{{$hsn['hsn']}}</td>
            <td>{{parseAmount($hsn['taxable_price'])}}</td>
            <td>{{$hsn['c_gst_percent']}}</td>
            <td>{{parseAmount($hsn['c_gst_amount'])}}</td>
            <td>{{$hsn['s_gst_percent']}}</td>
            <td>{{parseAmount($hsn['s_gst_amount'])}}</td>
            <td>{{parseAmount($hsn['tax_price'])}}</td>
         </tr>
         <?php
            $totalTaxable += parseTwoDigit($hsn['taxable_price']);
            $totalCgst += parseTwoDigit($hsn['c_gst_amount']);
            $totalSgst += parseTwoDigit($hsn['s_gst_amount']);
            $totalTaxPrice += parseTwoDigit($hsn['tax_price']);
         ?>
        @endforeach

        {{-- ADDON GST CALCULATION --}}
        @if(count($addonHsnArrayList) > 0)
        @foreach($addonHsnArrayList as $addon)

        <tr class="taxes">
            <td>{{$addon['hsn']}}</td>
            <td>{{parseAmount($addon['taxable_price'])}}</td>
            <td>{{$addon['c_gst_percent']}}</td>
            <td>{{parseAmount($addon['c_gst_amount'])}}</td>
            <td>{{$addon['s_gst_percent']}}</td>
            <td>{{parseAmount($addon['s_gst_amount'])}}</td>
            <td>{{parseAmount($addon['tax_price'])}}</td>

        </tr>
        <?php
            $totalTaxable += parseTwoDigit($addon['taxable_price']);
            $totalCgst += parseTwoDigit($addon['c_gst_amount']);
            $totalSgst += parseTwoDigit($addon['s_gst_amount']);
            $totalTaxPrice += parseTwoDigit($addon['tax_price']);
        ?>
        @endforeach
        @endif


        <tr class="taxes total-row">
            <td>TOTAL</td>
            <td>{{parseAmount($totalTaxable)}} </td>
            <td></td>
            <td>{{ parseAmount($totalCgst)}}</td>
            <td></td>
             <td>{{parseAmount($totalSgst)}}</td>
            <td>{{ parseAmount($totalTaxPrice)}}</td>
        </tr>
        <tr>
            <td colspan="4" style="border-right: transparent">Tax Amount (In words) <br>
            <b style="text-transform:uppercase">INR &nbsp;{{numberTowords($totalTaxPrice)}}</b><br>
        Company's PAN <b>AWBPR0957L</b> <br>
        Declaration We declare that this invoice shows the
        actual price of the goods described and that all
        particulars are true and correct
         <br></td>
<td colspan="3" style="border-left: transparent">
    <table  class="table border w-100">
        <tr>
            <td class="text-right">
                For <b>Blaack Forest</b><br><br>
            Authorized signatory </td>
         </tr>
    </table>
</td>
        </tr>
    </tfoot>

</table>

</html>

<?php
function parseAmount(float $amount){ 
    $amountInTwoDigit = bcdiv($amount, 1, 2);  
    $parseAmount = number_format((float)($amountInTwoDigit), 2, '.', ',');  
    return $parseAmount;
}
function parseTwoDigit(float $amount){ 
    return bcdiv($amount, 1, 2);
}
function numberTowords(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' paise' : '';
    return ($Rupees ? $Rupees . 'rupees ' : '') . $paise;
}

    ?>
