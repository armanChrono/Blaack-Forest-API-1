<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Models\Order;
use App\Events\MyEvent;
use App\Events\DispatchNotification;

    Route::get('getInvoiceNo', 'OrderController@getInvoiceNo');


Route::get('set', function () {
    Artisan::call('optimize');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    return "Optimization Cache is set";
});
Route::get('clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    // Artisan::call('composer:autoload');

    return "Optimization Cache is Cleared";
});
//Ecommerce Web
//Home Page

Route::get('adminEvent', function () {
    // event(new App\Events\MyEvent('Someone'));
    event(new MyEvent('Cake ready For Review', 'https://res.cloudinary.com/de0mpuv0c/image/upload/v1650965663/BlaackForrest/products/pineapple-cake-1.png', '13'));
    return "Event has been sent!";
});
Route::get('dispatchEvent', function () {
    event(new DispatchNotification('this is a test notification', 'https://res.cloudinary.com/de0mpuv0c/image/upload/v1650965663/BlaackForrest/products/pineapple-cake-1.png', '13'));
    return "Event has been sent!";
});

Route::get('getHeaderMenus', 'HomeController@getHeaderMenus');
Route::get('getHomeData', 'HomeController@getHomeData');
Route::get('getBanners', 'HomeController@getBanners');
Route::get('getCategories', 'HomeController@getCategories');
Route::get('getSubCategories', 'HomeController@getSubCategories');
Route::get('getMenSC', 'HomeController@getMenSC');
Route::get('getWomenSC', 'HomeController@getWomenSC');
Route::get('getCategorySC', 'HomeController@getCategorySC');
Route::get('getCategoryDetails/{slug}', 'HomeController@getCategoryDetails');
Route::get('getAllCategory', 'HomeController@getAllCategory');
Route::get('getAllSubCategory', 'HomeController@getAllSubCategory');
Route::get('listActiveSubCategories', 'SubCategoryController@listActiveSubCategories');
Route::get('getAllActiveRegionList', 'RegionController@getAllActiveRegionList');
Route::post('createEnquiry', 'EnquiryController@createEnquiry');
Route::get('getAllActiveEnquiry', 'EnquiryController@getAllActiveEnquiry');
Route::get('closeEnquiry/{id}', 'EnquiryController@closeEnquiry');

Route::get('sortProducts/{slug}/{regionId}/{customerId}/{sortName}/{flavour}/{weight}','HomeController@sortProducts');

//Listings
Route::get('getAllActiveFlavourList', 'FlavourController@getAllActiveFlavourList');
Route::get('getAllActiveWeightList', 'WeightController@getAllActiveWeightList');
Route::get('getAddonsList', 'ProductController@getAddonsList');



//stock
Route::get('stockUpdate', 'StockController@stockUpdate');
Route::get('getStockOfProduct/{id}', 'StockController@getStockOfProduct');
Route::post('updateProductStocks', 'StockController@updateProductStocks');

//settings
Route::get('getSettings', 'SettingController@getSettings');
Route::post('setSettings', 'SettingController@setSettings');

//settings
// Route::get('getDeliveryCharge/{choosedAddress}/{regionId}/{customerId}', 'DeliveryChargeController@getDeliveryCharge');
Route::get('getDeliveryCharge/{choosedAddress}/{regionId}', 'DeliveryChargeController@getDeliveryCharge');
Route::get('getAllDeliveryCharge', 'DeliveryChargeController@getAllDeliveryCharge');


Route::get('getProducts/{slug}/{regionId}/{customerId}', 'HomeController@getProducts');
Route::get('getSuggestedProducts/{regionId}/{customerId}', 'HomeController@getSuggestedProducts');
Route::get('filterProducts/{slug}/{tag_id}/{from_price}/{to_price}', 'HomeController@filterProducts');
Route::get('getProductsForUniversalSearch/{regionId}', 'HomeController@getProductsForUniversalSearch');
Route::get('getProductsForCategory/{slug}/{regionId}/{customerId}', 'HomeController@getProductsForCategory');
Route::get('getLatestProducts/{regionId}/{customerId}', 'HomeController@getLatestProducts');


Route::get('getProductDetails/{slug}/{customerId}', 'HomeController@getProductDetails');
Route::get('getProductDetailsForId/{slug}/{customerId}', 'HomeController@getProductDetailsForId');
Route::get('quickView/{id}', 'HomeController@quickView');
Route::get('productDetails/{product_slug}', 'ProductController@productDetails');
Route::get('getWhishlistId/{customer_id}', 'WishListController@getWhishlistId');

//Customers
Route::get('sendOTP/{mobile}', 'CustomerController@sendOTP');
Route::get('verifyOTP/{mobile}/{otp}', 'CustomerController@verifyOTP');
Route::get('sendSMS', 'CustomerController@sendSMS');


Route::post('register', 'CustomerController@register');
Route::post('login', 'CustomerController@login');
Route::get('logout/{customer_id}', 'CustomerController@logout');

Route::get('webSearch/{search}', 'SearchController@webSearch');
Route::post('addToCart', 'CartController@addToCart');

Route::get('webSearch/{search}', 'HomeController@webSearch');


Route::group(['middleware' => 'auth:sanctum'], function() {

    //WishList
    Route::post('wishlist', 'WishListController@wishlist');
    Route::get('customerWishList/{customer_id}', 'WishListController@customerWishList');
    Route::get('removeFromWishList/{wishlist_id}', 'WishListController@removeFromWishList');
    Route::get('wishListToCart/{wishlist_id}', 'WishListController@wishListToCart');

    //Cart
    // Route::post('addToCart', 'CartController@addToCart');
    Route::get('customerCart/{customer_id}', 'CartController@customerCart');
    Route::get('removeFromCart/{cart_id}/{customer_id}', 'CartController@removeFromCart');
    Route::get('editCart/{cart_id}/{size_id}/{quantity}', 'CartController@editCart');
    Route::get('updateSizeCart/{customer_id}/{product_id}/{cart_id}/{size_id}', 'CartController@updateSizeCart');
    Route::get('updateQuantityCart/{cart_id}/{quantity}/{customer_id}', 'CartController@updateQuantityCart');
    Route::get('updateMessageOnCakeCart/{cart_id}/{message}', 'CartController@updateMessageOnCakeCart');
    Route::get('updateCartAddon/{customer_id}/{cart_id}/{product_id}/{addon_id}/{all_addon_id}/{addon_quantity}', 'CartController@updateCartAddon');
    Route::get('updateAddonQuantity/{customer_id}/{addon_id}/{addon_quantity}', 'CartController@updateCartAddonQuantity');
    Route::post('storeAddonsToCart', 'CartController@storeAddonsToCart');
    Route::get('removeFromAddonCart/{customer_id}/{id}', 'CartController@removeFromAddonCart');


    //Customer
    Route::get('getCustomerDetails/{customer_id}', 'CustomerController@getCustomerDetails');
    Route::post('editCustomerDetails', 'CustomerController@editCustomerDetails');
    Route::get('checkWishAndCart/{customer_id}/{product_id}', 'CustomerController@checkWishAndCart');

    //Order
    Route::post('submitOrder', 'OrderController@submitOrder');
    Route::get('listCustomerOrders/{customer_id}', 'OrderController@listCustomerOrders');
    Route::get('listCustomerOrderHistory/{customer_id}', 'OrderController@listCustomerOrderHistory');
    Route::get('getOrderDetails/{order_id}', 'OrderController@getOrderDetails');
    Route::get('cancelOrder/{order_id}/{cancelled_by}/{reason}', 'OrderController@cancelOrder');
    Route::get('checkPromo/{code}', 'PromoController@checkPromo');
    Route::get('refundAmount', 'OrderController@refundAmount');



    //address
    Route::post('editAddress', 'CustomerAddressController@editAddress');
    Route::get('deleteAddress/{customer_address_id}', 'CustomerAddressController@deleteAddress');
    Route::get('getPincodeDetails/{pincode}', 'CustomerAddressController@getPincodeDetails');
    Route::get('listAllAddress/{customer_id}', 'CustomerAddressController@listAllAddress');
    Route::post('createAddress', 'CustomerAddressController@createAddress');
    Route::get('getAddressDetails/{address_id}', 'CustomerAddressController@getAddressDetails');
    Route::get('getPrimaryAddress/{customer_id}', 'CustomerAddressController@getPrimaryAddress');
    Route::get('updatePrimaryAddress/{customer_id}/{address_id}', 'CustomerAddressController@updatePrimaryAddress');

    //time slot
    // Route::get('timeSlot', 'TimeSlotController@timeSlot');


});
Route::get('timeSlot', 'TimeSlotController@timeSlot');

//Ecommerce Admin Panel
//
Route::post('loginUser', 'UserController@loginUser');
Route::post('registerUser', 'UserController@store');
Route::get('activateUser/{id}', 'UserController@activateUser');
Route::get('deActivateUser/{id}', 'UserController@deActivateUser');
Route::post('createUser', 'UserController@createUser');
Route::post('updateUser', 'UserController@updateUser');

Route::post('loginDispatch', 'DispatchController@loginDispatch');
Route::post('loginDispatch', 'DispatchController@loginDispatch');
Route::post('loginShop', 'ShopController@loginShop');

Route::get('getAllActiveShopDetails', 'ShopController@getAllActiveShopDetails');
Route::get('getAllActiveLocationDetails', 'RegionController@getAllActiveLocationDetails');
Route::get('getAllActiveDispatchTeams', 'DispatchController@getAllActiveDispatchTeams');

Route::get('generateInvoicePdf/{orderId}', 'PDFController@generateInvoicePdf');
Route::get('generateInvoicePdf1/{orderId}', 'PDFController@generateInvoicePdf1');
Route::get('generateCreditMemo/{orderId}', 'PDFController@creditMemo');

// Route::get('generateInvoicePdf', array('as'=> 'generate.invoice.pdf', 'uses' => 'PDFController@generateInvoicePDF'));



// Route::group(['middleware' => 'auth:sanctum'], function() {
    //User
    Route::apiResource('user','UserController');
    Route::apiResource('dispatch','DispatchController');

    Route::get('dispatchSwitch/{id}', 'DispatchController@dispatchSwitch');
    // Route::post('loginDispatch', 'DispatchController@loginDispatch');

    Route::get('adminDashboard', 'DashboardController@adminDashboard');

    //Category
    Route::apiResource('category', 'CategoryController');
    Route::get('categorySwitch/{id}', 'CategoryController@categorySwitch');
    Route::get('getActiveCategory', 'CategoryController@getActiveCategory');
    Route::get('searchCategory/{search}', 'CategoryController@searchCategory');
    Route::post('imageUpdateCategory', 'CategoryController@imageUpdateCategory');
    Route::post('bannerImageCategory', 'CategoryController@bannerImageCategory');

    //Sub Category
    Route::apiResource('subcategory', 'SubCategoryController');
    Route::get('subCategorySwitch/{id}', 'SubCategoryController@subCategorySwitch');
    Route::get('getActiveSubCategory', 'SubCategoryController@getActiveSubCategory');
    Route::get('searchSubCategory/{search}', 'SubCategoryController@searchSubCategory');
    Route::post('imageUpdateSubCategory', 'SubCategoryController@imageUpdateSubCategory');

    //Tags
    Route::apiResource('tag', 'TagController');
    Route::get('tagSwitch/{id}', 'TagController@tagSwitch');
    Route::get('getActiveTag', 'TagController@getActiveTag');
    Route::get('searchTag/{search}', 'TagController@searchTag');
    Route::get('getSCTagId/{sub_category_id}', 'TagController@getSCTagId');

    //Size
    Route::apiResource('size', 'SizeController');
    Route::get('sizeSwitch/{id}', 'SizeController@sizeSwitch');
    Route::get('getActiveSize', 'SizeController@getActiveSize');
    Route::get('searchSize/{search}', 'SizeController@searchSize');

    //color
    Route::apiResource('color', 'ColorController');
    Route::get('colorSwitch/{id}', 'ColorController@colorSwitch');
    Route::get('getActiveColor', 'ColorController@getActiveColor');
    Route::get('searchColor/{search}', 'ColorController@searchColor');

    //Banner
    Route::apiResource('banner', 'BannerController');
    Route::get('bannerSwitch/{id}', 'BannerController@bannerSwitch');
    Route::post('imageUpdateBanner', 'BannerController@imageUpdateBanner');

    //Card
    Route::apiResource('card', 'ViewCardController');
    Route::get('cardSwitch/{id}', 'ViewCardController@cardSwitch');
    Route::post('imageUpdateCard', 'ViewCardController@imageUpdateCard');
    Route::get('searchCard/{search}', 'ViewCardController@searchCard');

    //latest-Arrival
     Route::apiResource('latestArrival', 'LatestArrivalController');
     Route::get('latestArrivalSwitch/{id}', 'LatestArrivalController@latestArrivalSwitch');
     Route::post('latestArrivalImageUpdate', 'LatestArrivalController@latestArrivalImageUpdate');

    //Country city State
    Route::get('getCountryList', 'RegionController@getCountryList');
    Route::get('getStateList/{id}', 'RegionController@getStateList');
    Route::get('getCityList/{id}', 'RegionController@getCityList');

    //Region
    Route::post('createRegion', 'RegionController@createRegion');
    Route::get('getAllRegionList', 'RegionController@getAllRegionList');
    // Route::get('getAllActiveRegionList', 'RegionController@getAllActiveRegionList');
    Route::get('activateRegion/{region_id}', 'RegionController@activateRegion');
    Route::get('deActivateRegion/{region_id}', 'RegionController@deActivateRegion');
    Route::get('deleteRegion/{id}', 'RegionController@deleteRegion');
    Route::post('updateRegion', 'RegionController@updateRegion');

    //Location Details
    Route::post('createLocationDetails', 'RegionController@createLocationDetails');
    Route::get('getAllLocationDetails', 'RegionController@getAllLocationDetails');
    Route::get('getAllLocationDetailsById/{id}', 'RegionController@getAllLocationDetailsById');
    Route::get('getAllLocationDetailsByRegionId/{id}', 'RegionController@getAllLocationDetailsByRegionId');
    // Route::get('getAllActiveLocationDetails', 'RegionController@getAllActiveLocationDetails');
    Route::get('activateLocationDetails/{location_details_id}', 'RegionController@activateLocationDetails');
    Route::get('deActivateLocationDetails/{location_details_id}', 'RegionController@deActivateLocationDetails');
    Route::get('deleteLocationDetails/{id}', 'RegionController@deleteLocationDetails');
    Route::post('updateLocationDetails', 'RegionController@updateLocationDetails');
    Route::get('searchLocationDetails/{search}', 'RegionController@searchLocationDetails');

    //Shop Details
    Route::post('createShopDetails', 'ShopController@createShopDetails');
    Route::get('getAllShopDetails', 'ShopController@getAllShopDetails');
    Route::get('getAllShopDetailsById/{id}', 'ShopController@getAllShopDetailsById');
    Route::get('getAllShopDetailsByRegionId/{id}', 'ShopController@getAllShopDetailsByRegionId');
    // Route::get('getAllActiveShopDetails', 'ShopController@getAllActiveShopDetails');
    Route::get('activateShopDetails/{shop_details_id}', 'ShopController@activateShopDetails');
    Route::get('deActivateShopDetails/{shop_details_id}', 'ShopController@deActivateShopDetails');
    Route::get('deleteShopDetails/{id}', 'ShopController@deleteShopDetails');
    Route::post('updateShopDetails', 'ShopController@updateShopDetails');
    Route::get('searchShopDetails/{search}', 'ShopController@searchShopDetails');

    Route::get('getShopOrderById/{id}', 'ShopOrderController@getShopOrderById');
    Route::get('getShopOrderByShopOrderId/{id}', 'ShopOrderController@getShopOrderByShopOrderId');
    Route::get('getShopOrderByOrderId/{id}', 'ShopOrderController@getShopOrderByOrderId');
    Route::post('acceptShopOrders', 'ShopOrderController@acceptShopOrders');
    Route::post('deliveredFromShop', 'ShopOrderController@deliveredFromShop');
    Route::post('createShopOrder', 'ShopOrderController@createShopOrder');




    //Pincode
    Route::post('createPincode', 'RegionController@createPincode');
    Route::get('getAllPincode', 'RegionController@getAllPincode');
    Route::get('getAllActivePincode', 'RegionController@getAllActivePincode');
    Route::get('activatePincode/{pincode_id}', 'RegionController@activatePincode');
    Route::get('deActivatePincode/{pincode_id}', 'RegionController@deActivatePincode');
    Route::get('deletePincode/{id}', 'RegionController@deletePincode');
    Route::post('updatePincode', 'RegionController@updatePincode');
    Route::get('searchPincode/{search}', 'RegionController@searchPincode');


    //units
    Route::post('createUnit', 'UnitController@createUnit');
    Route::get('getAllUnitList', 'UnitController@getAllUnitList');
    Route::get('getAllActiveUnitList', 'UnitController@getAllActiveUnitList');
    Route::get('activateUnit/{unit_id}', 'UnitController@activateUnit');
    Route::get('deActivateUnit/{unit_id}', 'UnitController@deActivateUnit');
    Route::get('deleteUnit/{id}', 'UnitController@deleteUnit');
    Route::post('updateUnit', 'UnitController@updateUnit');

    //Tax
    Route::post('createTax', 'TaxController@createTax');
    Route::get('getAllTaxList', 'TaxController@getAllTaxList');
    Route::get('getAllActiveTaxList', 'TaxController@getAllActiveTaxList');
    Route::get('activateTax/{tax_id}', 'TaxController@activateTax');
    Route::get('deActivateTax/{tax_id}', 'TaxController@deActivateTax');
    Route::get('deleteTax/{id}', 'TaxController@deleteTax');
    Route::post('updateTax', 'TaxController@updateTax');

    //discount
    Route::post('createDiscount', 'DiscountController@createDiscount');
    Route::get('getAllDiscountList', 'DiscountController@getAllDiscountList');
    Route::get('getAllActiveDiscountList', 'DiscountController@getAllActiveDiscountList');
    Route::get('activateDiscount/{discount_id}', 'DiscountController@activateDiscount');
    Route::get('deActivateDiscount/{discount_id}', 'DiscountController@deActivateDiscount');
    Route::get('deleteDiscount/{id}', 'DiscountController@deleteDiscount');
    Route::post('updateDiscount', 'DiscountController@updateDiscount');


     //discount Category
     Route::post('createDiscountCategory', 'DiscountController@createDiscountCategory');
     Route::get('getAllDiscountCategoryList', 'DiscountController@getAllDiscountCategoryList');
     Route::get('getAllActiveDiscountCategoryList', 'DiscountController@getAllActiveDiscountCategoryList');
     Route::get('activateDiscountCategory/{category_discount_id}', 'DiscountController@activateDiscountCategory');
     Route::get('deActivateDiscountCategory/{category_discount_id}', 'DiscountController@deActivateDiscountCategory');
     Route::get('deleteDiscountCategory/{id}', 'DiscountController@deleteDiscountCategory');
     Route::post('updateDiscountCategory', 'DiscountController@updateDiscountCategory');

     //discount Product
     Route::post('createDiscountProduct', 'DiscountController@createDiscountProduct');
     Route::get('getAllDiscountProductList', 'DiscountController@getAllDiscountProductList');
     Route::get('getAllActiveDiscountProductList', 'DiscountController@getAllActiveDiscountProductList');
     Route::get('activateDiscountProduct/{product_discount_id}', 'DiscountController@activateDiscountProduct');
     Route::get('deActivateDiscountProduct/{product_discount_id}', 'DiscountController@deActivateDiscountProduct');
     Route::get('deleteDiscountProduct/{id}', 'DiscountController@deleteDiscountProduct');
     Route::post('updateDiscountProduct', 'DiscountController@updateDiscountProduct');

    //customer Discount
     Route::post('createDiscountCustomer', 'DiscountController@createDiscountCustomer');
     Route::get('getAllDiscountCustomerList', 'DiscountController@getAllDiscountCustomerList');
     Route::get('getAllActiveDiscountCustomerList', 'DiscountController@getAllActiveDiscountCustomerList');
     Route::get('activateDiscountCustomer/{customer_discount_id}', 'DiscountController@activateDiscountCustomer');
     Route::get('deActivateDiscountCustomer/{customer_discount_id}', 'DiscountController@deActivateDiscountCustomer');
     Route::get('deleteDiscountCustomer/{id}', 'DiscountController@deleteDiscountCustomer');
     Route::post('updateDiscountCustomer', 'DiscountController@updateDiscountCustomer');



    //Flavour
    Route::post('createFlavour', 'FlavourController@createFlavour');
    Route::get('getAllFlavourList', 'FlavourController@getAllFlavourList');
   // Route::get('getAllActiveFlavourList', 'FlavourController@getAllActiveFlavourList');
    Route::get('activateFlavour/{flavour_id}', 'FlavourController@activateFlavour');
    Route::get('deActivateFlavour/{flavour_id}', 'FlavourController@deActivateFlavour');
    Route::get('deleteFlavour/{id}', 'FlavourController@deleteFlavour');
    Route::post('updateFlavour', 'FlavourController@updateFlavour');


    //Customer Details

    Route::post('createCustomerDetails', 'CustomerDetailsController@createCustomerDetails');
    Route::get('getAllCustomerDetailsList', 'CustomerDetailsController@getAllCustomerDetailsList');
    Route::get('getAllActiveCustomerDetailsList', 'CustomerDetailsController@getAllActiveCustomerDetailsList');
    Route::get('activateCustomerDetails/{customer_details_id}', 'CustomerDetailsController@activateCustomerDetails');
    Route::get('deActivateCustomerDetails/{customer_details_id}', 'CustomerDetailsController@deActivateCustomerDetails');
    Route::get('deleteCustomerDetails/{id}', 'CustomerDetailsController@deleteCustomerDetails');
    Route::post('updateCustomerDetails', 'CustomerDetailsController@updateCustomerDetails');


    Route::get('getAllCustomerDetails', 'CustomerController@getAllCustomerDetails');


    //Driver

    Route::post('createDriver', 'DriverController@createDriver');
    Route::get('getAllDriverList', 'DriverController@getAllDriverList');
    Route::get('deleteDriver/{id}', 'DriverController@deleteDriver');
    Route::post('updateDriver', 'DriverController@updateDriver');



    //Rating
    Route::post('createRating', 'RatingController@createRating');
    Route::get('getAllRatingList', 'RatingController@getAllRatingList');
    Route::get('deleteRating/{id}', 'RatingController@deleteRating');
    Route::post('updateRating', 'RatingController@updateRating');

    //DispatchOrders
    Route::get('getDispatchOrderById/{id}', 'DispatchOrderController@getDispatchOrderById');
    Route::post('createDispatchOrder', 'DispatchOrderController@createDispatchOrder');
    Route::get('getDispatchOrderByDispatchId/{id}', 'DispatchOrderController@getDispatchOrderByDispatchId');
    Route::get('getDispatchOrderByOrderId/{id}', 'DispatchOrderController@getDispatchOrderByOrderId');
    Route::post('acceptDispatchOrders', 'DispatchOrderController@acceptDispatchOrders');



    Route::post('dispatchApproveImage', 'DispatchOrderController@dispatchApproveImage');

    Route::get('getDispatchImages', 'DispatchOrderController@getDispatchImages');
    Route::get('getDispatchImagesById/{id}', 'DispatchOrderController@getDispatchImagesById');


    Route::post('approveImage', 'DispatchOrderController@approveImage');
    Route::post('disApproveImage', 'DispatchOrderController@disApproveImage');


    //Driver Orders
    // Route::get('getAllOrdersForDriver/{id}', 'DriverController@getAllOrdersForDriver');
    Route::get('getDriverOrderByOrderId/{id}', 'DriverController@getDriverOrderByOrderId');
    Route::get('getAllDriverListByLocationId/{id}', 'DriverController@getAllDriverListByLocationId');
    Route::get('getAllDriverListByRegionId/{id}', 'DriverController@getAllDriverListByRegionId');
    Route::post('createDriverOrder', 'DriverController@createDriverOrder');
    // Route::get('acceptOrderByDriver/{id}', 'DriverController@acceptOrderByDriver');



    //Addons

    Route::post('createAddons', 'ProductController@createAddons');
    Route::post('updateAddons', 'ProductController@updateAddons');
    Route::get('deleteAddons/{id}', 'ProductController@deleteAddons');
    Route::post('imageUpdateAddons', 'ProductController@imageUpdateAddons');



    //Terms And Condition

    Route::post('createTermsAndCondition', 'TermsAndConditionController@createTermsAndCondition');
    Route::get('getAllTermsAndConditionList', 'TermsAndConditionController@getAllTermsAndConditionList');
     Route::post('updateTermsAndCondition', 'TermsAndConditionController@updateTermsAndCondition');

      //Privacy Policy

    Route::post('createPrivacyPolicy', 'PrivacyPolicyController@createPrivacyPolicy');
    Route::get('getAllPrivacyPolicyList', 'PrivacyPolicyController@getAllPrivacyPolicyList');
     Route::post('updatePrivacyPolicy', 'PrivacyPolicyController@updatePrivacyPolicy');

      //Disclaimer

    Route::post('createDisclaimer', 'DisclaimerController@createDisclaimer');
    Route::get('getAllDisclaimerList', 'DisclaimerController@getAllDisclaimerList');
     Route::post('updateDisclaimer', 'DisclaimerController@updateDisclaimer');


    //weight
    Route::post('createWeight', 'WeightController@createWeight');
    Route::get('getAllWeightList', 'WeightController@getAllWeightList');
    Route::get('getAllActiveWeightListByUnitId/{unit_id}', 'WeightController@getAllActiveWeightListByUnitId');
    Route::get('activateWeight/{weight_id}', 'WeightController@activateWeight');
    Route::get('deActivateWeight/{weight_id}', 'WeightController@deActivateWeight');
    Route::get('deleteWeight/{id}', 'WeightController@deleteWeight');
    Route::post('updateWeight', 'WeightController@updateWeight');

    //Mobile Banner
    Route::apiResource('mobileBanner', 'MobileBannerController');
    Route::get('mobileBannerSwitch/{id}', 'MobileBannerController@mobileBannerSwitch');
    Route::post('imageUpdateMobileBanner', 'MobileBannerController@imageUpdateMobileBanner');

    //Product
    Route::apiResource('product', 'ProductController');
    Route::get('productSwitch/{id}', 'ProductController@productSwitch');
    Route::get('getActiveProduct', 'ProductController@getActiveProduct');
    Route::get('getActiveProductNoDiscount', 'ProductController@getActiveProductNoDiscount');
    Route::get('searchProduct/{search}', 'ProductController@searchProduct');
    Route::post('imageUpdateProduct', 'ProductController@imageUpdateProduct');
    Route::get('deleteImageProduct/{product_image_id}', 'ProductController@deleteImageProduct');
    Route::get('getActiveSuggestedProduct', 'ProductController@getActiveSuggestedProduct');
    Route::get('getActiveProduct', 'ProductController@getActiveProduct');
    Route::post('storeSuggested', 'ProductController@storeSuggested');
    Route::post('updateSuggested', 'ProductController@updateSuggested');

    //Link Product
    Route::apiResource('linkproduct', 'LinkProductController');
    Route::post('addLinkProduct', 'LinkProductController@addLinkProduct');
    Route::get('listLinkProduct/{id}', 'LinkProductController@listLinkProduct');
    Route::get('deleteLinkProduct/{id}', 'LinkProductController@deleteLinkProduct');

    //Orders
    Route::get('listAllOrders', 'OrderController@listAllOrders');
    Route::get('listAllDeliveredOrders', 'OrderController@listAllDeliveredOrders');
    Route::get('listCancelledOrders', 'OrderController@listCancelledOrders');
    Route::get('orderStatusUpdate/{order_id}/{status}', 'OrderController@orderStatusUpdate');
    Route::get('orderStatusUpdate/{order_id}/{status}/{done_by}/{reason}', 'OrderController@orderStatusUpdateWithReason');


    //Promo Codes
    Route::apiResource('promo', 'PromoController');
    Route::get('promoSwitch/{id}', 'PromoController@promoSwitch');
    Route::get('getActivePromo', 'PromoController@getActivePromo');
    Route::get('searchPromo/{search}', 'PromoController@searchPromo');


    Route::post('makeOnlinePayment', 'OrderController@makeOnlinePayment');
    Route::post('orderIdGenerate', 'OrderController@orderIdGenerate');



// });
    //Location
    Route::get('getStates', 'LocationController@getStates');
    Route::get('getCities', 'LocationController@getCities');
    Route::get('getCityOfState/{id}', 'LocationController@getCityOfState');
    Route::post('updateDeliveryLocation', 'LocationController@updateDeliveryLocation');
    Route::get('getDeliveryLocation', 'LocationController@getDeliveryLocation');
    Route::get('removeDeliveryState/{id}', 'LocationController@removeDeliveryState');
    Route::get('removeDeliveryCity/{id}', 'LocationController@removeDeliveryCity');
    Route::get('findLocation/{pincode}', 'LocationController@findLocation');


    Route::get('orderStatusUpdate/{order_id}/{status}/{reason}', 'OrderController@orderStatusUpdate');
    Route::post('acceptOrderByDriver/', 'DriverController@acceptOrderByDriver');
    Route::post('orderPickedUpByDriver/', 'DriverController@orderPickedUpByDriver');
    // Route::post('getAllOrdersForDriver/', 'DriverController@getAllOrdersForDriver');


    //DRIVER APP API
    Route::post('driverLogin', 'DriverController@driverLogin');
    Route::post('deliverDriverOrder', 'DriverController@deliverDriverOrder');
    Route::post('getAllOrdersForDriver/', 'DriverController@getAllOrdersForDriver');





    Route::get('send-mail', [MailController::class, 'sendMail'])->name('sendMail');

