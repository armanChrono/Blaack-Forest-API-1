<!DOCTYPE html>
<html>
<head>
    


    <title>CREDIT_MEMO_#{{$order_id}}</title>
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
 .text-right{
        text-align:right !important;
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
 
    .gray-color{
        color:#5D5D5D;
    }
    .text-bold{
        font-weight: bold;
    }
    .border{
        border:1px solid black;
    }
    .product-table tr,th,td{
        border: 1px solid black;
        border-collapse:collapse;
        padding:5px;
    }
    .memo-info-table tr th{
        background: #f0f0f0;
        font-size:11px;
    }
    table tr td{
        font-size:10px;
    }
    th{
        font-size:11px;
    }

    tr, td{
    border:1px solid black;
    }
 
.product-table{
  border:1px solid black;
  border-collapse:collapse;
}
.memo-info-table{
    border-collapse:collapse;
}
.memo-info-table .space-column{
  background-color:transparent;
  border-top-color:transparent;
  border-bottom-color:transparent;
}
.footer-total td{
 border-top-color:transparent;
   border-bottom-color:transparent;
}
.product-table th{
    background-color:#f0f0f0;
}
.bg-gray{
    background-color:#f0f0f0;
}

</style>
<body>
<div class="head-title">
    <h4 class="text-center m-0 p-0">CREDIT MEMO</h4>
</div>
<h5>Your Credit Memo for Order #{{$order_id}}</h5>
<table class="memo-info-table border w-100" >
    <tr>
        <th>Billing Information: </th>
        <th>Shipping Information:</th>
    </tr>
   <tr>
    @if ($delivery_mode == 'Door Step Delivery')
       <td>
        <b style="text-transform:capitalize">{{$billing_name}}</b><br>
        {{$ordered_address['doorNo']}},&nbsp;  {{$ordered_address['street']}}<br>
        {{$ordered_address['area']}},<br>
        {{$ordered_address['address_city_district']}}<br>
        {{ucfirst($region['state']['name'])}}<br>
        India<br>
        GST No :<br>
        Contact : {{$billing_mobile_number}}
        E-Mail :{{$email}}
    </td>
    @else
    <td>
        <b style="text-transform:capitalize">{{$billing_name}}</b><br>
        {{ucfirst($region['state']['name'])}}<br>
        India<br>
        GST No :<br>
        Contact : {{$billing_mobile_number}}
        E-Mail :{{$email}}
    </td>
    @endif
@if ($delivery_mode == 'Pick Up')
<td>Ship to :<br>
    <b style="text-transform:capitalize">{{$billing_name}}</b><br>
    Contact : {{$billing_mobile_number}} <br>
    Delivery Mode : Pick Up<br>
    Shop Address :<br> <b>{{$shops['shop_name']}}</b> <br> {{$shops['address']}}<br>
    Shop Contact : {{$shops['mobile_no']}} <br>
    State : &nbsp; <b> {{$region['state']['name']}}</b> <br>
    GST No :
    </td>
@else
<td>Ship to :<br>
    <b style="text-transform:capitalize">{{$billing_name}}</b><br>
    {{$ordered_address['doorNo']}},&nbsp;  {{$ordered_address['street']}}<br>
    {{$ordered_address['area']}},<br>
    {{$ordered_address['address_city_district']}}<br>
    E-mail : {{$email}}<br>
    Contact : {{$billing_mobile_number}} <br>
    State : &nbsp; {{ucfirst($region['state']['name'])}} <br>
    Place of Supply: India <br>
    GSTIN/UIN:
    </td>
@endif
      
   </tr>

</table>
<br>
<br>
<table class="product-table w-100 mt-10">
        <th class="bg-gray">Image</th>
        <th class="bg-gray">Product</th>
        <th class="bg-gray">Sku</th>
        <th class="bg-gray">Quantity</th>
        <th class="bg-gray">Price Without Tax</th>
        <th class="bg-gray">Tax</th>
        <th class="bg-gray">Total Price</th>
    <tbody>
        <?php $totalWithOutGst = 0; ?>
        @foreach($ordered_products as $product)
      <tr>
        <td class="text-center">
            <img src="{{$product['product_details'][0]['images'][0]['product_image']}}" alt="" width="50" height="50"></td>
        <td>{{$product['product_details'][0]['product_name']}}
            <br> <b>Flavour:</b>&nbsp;{{$product['flavour']['flavour_name']}} <br>
            <b>Weight:</b>&nbsp;{{$product['variation']['weight'][0]['weight_name']}}&nbsp; {{$product['product_details'][0]['unit']['unit_name']}}
            <br><b>Egg/Eggless:</b>&nbsp;{{$product['egg_or_eggless'] == 1 ? 'Eggless' : 'Egg'}}</td>
        <td class="text-center">{{$product['variation']['sku']}}</td>
        <td class="text-center">{{$product['product_quantity']}}</td>
        <td class="text-right">{{number_format((float)$product['product_total'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100), 2, '.', ',')}}</td>
        <?php $totalWithOutGst += ($product['product_total'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100));
              ?>
        <td class="text-right">{{number_format($product['product_total'] - ($product['product_total'] / ((100 + $product['product_details'][0]['tax']['tax_percentage'] )/100)), 2, '.', ',')}}</td>
        <td class="text-right">{{$product['product_total']}}</td>
       </tr>
       @endforeach 
       @if(count($ordered_addons) > 0)
           @foreach($ordered_addons as $addon)
           <tr class="no-Y-border goods goods-min-height" >
               <td style="width: 10%" class="text-center"><img src="{{$addon['image']}}" alt="" width="50" height="50"></td>
               <td style="width: 30%">{{$addon['product_name']}}</td>
               <td class="text-center">-</td>
               <td>{{$addon['quantity']}}</td>
               <td class="text-right">{{number_format((float)$addon['price'] / ((100 + $addon['tax']['tax_percentage'] )/100), 2, '.', ',')}}</td>
               <?php $totalWithOutGst += ($addon['total'] / ((100 + $addon['tax']['tax_percentage']  )/100));
              ?>
               <td>{{number_format($addon['total'] - $addon['total'] / ((100 + $addon['tax']['tax_percentage']  )/100), 2, '.', ',')}}</td>
               <td class="text-right">{{number_format((float)$addon['total'], 2, '.', ',')}}
                    <?php 
                    $totalWithOutGst += ($addon['total'] / ((100 + $addon['tax']['tax_percentage']  )/100));
                    ?>
                </td>
           </tr>  
           @endforeach 
       @endif
      
       <tr class="text-right footer-total">
        <td colspan="6">Subtotal</td>
        <td> {{number_format((float)$order_sub_total, 2, '.', ',')}}</td>
       </tr>
        <tr class="text-right footer-total">
        <td colspan="6" >Shipping & Handling</td>
        <td> {{number_format($deliver_fee , 2, '.', ',')}}</td>
       </tr>
        <tr class="text-right footer-total">
        <td colspan="6">CGST</td>
        <td>{{number_format($cgst_value , 2, '.', ',')}}</td>
       </tr>
        <tr class="text-right footer-total">
        <td colspan="6">SGST</td>
        <td>{{number_format($sgst_value , 2, '.', ',')}}</td>
       </tr>
        <tr class="text-right footer-total">
        <td colspan="6">GST</td>
        <td>{{number_format($cgst_value + $sgst_value , 2, '.', ',')}}</td>
       </tr>
       <tr class="text-right">
         <td colspan="6"><b>Grand Total	</b></td>
        <td>{{number_format($order_overall_totall , 2, '.', ',')}}</td>
       </tr>
    </tbody>
   
    

</table>
<br>
<br>
<p>Invoice amount INR1212 refunded.</p>
<br>
<br>
<h4 class="text-center">Thank you, Blaack Forest Cakes</h4>
</html>
