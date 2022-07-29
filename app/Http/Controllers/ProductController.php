<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\Stock;
use App\Models\SubCategory;
use App\Models\Flavour;
use App\Models\Addons;
use App\Models\product_variation;
use App\Models\product_flavour;
use App\Models\ProductDiscont;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\OrderProducts;

class ProductController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
        $this->storeRules = [
            'category_id' => 'required|numeric',
            'sub_category_id' => 'required|numeric',
            // 'product_name' => 'required|unique:products,product_name,NULL,id,deleted_at,NULL',
            'product_name' => 'required',
            'region' => 'required',
            'hsn' => 'required',
            'tax_id' => 'required',
            'product_description' => 'required',
             'new_product' => 'required',
            'best_selling' => 'required',
            'variation'=>'required',
            'flavour'=> 'required',
            'unit_id'=> 'required',
            'short_description' => 'required',
            'product_price'=>'required',
            'COD_egg' => 'required',
            'COD_eggless'=>'required',
        ];

        $this->addOns=[
            'product_name' => 'required|unique:addons,product_name,NULL,addons_id,deleted_at,NULL',
            'price' => 'required',
        ];
    }

    public function index()
    {
        return $this->response->jsonResponse(false, $this->response->message('Product', 'index'), Product::with('subCategory','images', 'unit', 'variation','category')->latest()->get(), 200);
    }

    public function store(Request $request)
    {
        $validate = $this->response->validate($request->all(), $this->storeRules);
        if($validate === true) {
            $input = $request->all();
            $productNameCheck = Product::where('product_name',$input['product_name'])->where('sub_category_id', $input['sub_category_id'])->whereNotNull('deleted_at');
            if($productNameCheck->exists()){
                 return $this->response->jsonResponse(true, 'Product Name Already Available', '', 205);
            }
            $create = Product::create($input);
            $variationId = [];
            $weightId = [];
            if($create){
                foreach ($input['variation'] as $variation) {
                    $productVariation =  product_variation::create([
                         'product_id' => $create['id'],
                         'weight_id'=> $variation['weight'],
                         'egg' => $variation['egg'],
                         'eggLess' => $variation['eggLess'],
                         'egg_preparation' => $variation['eggPreparation'],
                         'eggless_preparation' => $variation['egglessPreparation'],
                         'sku' => $variation['sku']
                     ]);

                     array_push($variationId, $productVariation->variation_id);
                     array_push($weightId, $productVariation->weight_id);
                 }
                 $weightId = implode(',',$weightId);

                 $create->update(['product_price'=> $input['variation'][0]['egg']]);
                 $create->update(['product_discount_price'=> $input['variation'][0]['egg']]);
                 $create->update(['variation_ids'=> $variationId]);
                 $create->update(['weight_ids'=> $weightId]);

            }

           //return $input['variation'][0]->egg;
            // $tags = new ProductTag;
            // $tags->product_id = $create['id'];
            // $tags->product_tags = $input['product_tags'];
            // $tags->save();
            // $this->stockCreate($input['product_sizes'], $create['id']);
            return $this->response->jsonResponse(false, $this->response->message('Product', 'store'), $create, 200);
        } else {
            return $validate;
        }
    }

    public function show($id)
    {
        return $this->response->jsonResponse(false, $this->response->message('Product', 'show'), Product::with('subCategory','images','unit', 'variation','category')->where('id', $id)->first(), 200);
    }

    public function update(Request $request, $id)
    {
        $validate = $this->response->validate($request->all(), [

            // 'product_name' => [
            //     'required',
            //     Rule::unique('products')->ignore($id)->whereNull('deleted_at'),
            // ],
            'product_name' => 'required',

            'category_id' => 'required',
            'region' => 'required',
            'hsn' => 'required',
            'tax_id' => 'required',
            'product_description' => 'required',
            'new_product' => 'required',
            'best_selling' => 'required',
            'variation'=>'required',
            'flavour'=> 'required',
            'unit_id'=> 'required',
            'short_description' => 'required',
            'product_price'=>'required',
            'COD_egg'=>'required',
            'COD_eggless'=>'required',

        ]);
        if($validate === true) {
            $input = $request->all();
            $productNameCheck = Product::where('product_name',$input['product_name'])->where('sub_category_id', $input['sub_category_id'])->whereNotNull('deleted_at');
            if($productNameCheck->exists()){
                 return $this->response->jsonResponse(true, 'Product Name Already Available', '', 205);
            }
            $update = $this->findProduct($id)->update($input);

            product_variation::where('product_id', $id)->delete();
            $variationId = [];
            $weightId = [];

            if($update){
                foreach ($input['variation'] as $key => $variation) {
                    $oldVariationId = $variation['id'];
                    $productVariation = product_variation::create([
                        'product_id' => $id,
                        'weight_id'=> $variation['weight'],
                        'egg' => $variation['egg'],
                        'eggLess' => $variation['eggLess'],
                        'egg_preparation' => $variation['eggPreparation'],
                        'eggless_preparation' => $variation['egglessPreparation'],
                        'sku' => $variation['sku']
                    ]);
                    $newVariationId = $productVariation->variation_id;

                    array_push($variationId, $productVariation->variation_id);
                    array_push($weightId, $productVariation->weight_id);
                    if(isset($oldVariationId)){
                        $cart = Cart::where('product_id', $id)->where('variation_ids', $oldVariationId);
                        if($cart->exists()){
                            $cart->update(['variation_ids'=>$newVariationId]);
                        }
                        $orderedProducts = OrderProducts::where('product_id', $id)->where('variation_id', $oldVariationId);
                        if($orderedProducts->exists()){
                            $orderedProducts->update(['variation_id'=>$newVariationId]);
                        }
                    }
                    

                }

                $weightId = implode(',',$weightId);

                Product::where('id', $id)->update(['variation_ids'=>$variationId]);
                Product::where('id', $id)->update(['weight_ids'=>$weightId]);

            }


            return $this->response->jsonResponse(false, $this->response->message('Product', 'update'), $update, 200);
        } else {
            return $validate;
        }
    }

    public function storeSuggested(Request $request){
        $value = $request->suggested;
        if( strpos($value, ',') !== false ) {
            // return explode(',', $value);
            foreach(explode(',', $value) as $productId) {
                Product::where('id', $productId)->update(['suggested'=> 1]);
            }
        } elseif ($value) {
            Product::where('id', $value)->update(['suggested'=> 1]);
        }

        return $this->response->jsonResponse(false, "Suggested Product updated", [], 200);;

    }

    public function updateSuggested(Request $request){
        product::where('suggested', 1)->update(['suggested'=> 0]);
        $value = $request->suggested;
        if( strpos($value, ',') !== false ) {
            foreach(explode(',', $value) as $productId) {
                 Product::where('id', $productId)->update(['suggested'=> 1]);
            }
        } elseif ($value) {
             Product::where('id', $value)->update(['suggested'=> 1]);
        }

        return $this->response->jsonResponse(false, "Suggested Product updated", [], 200);;

    }

    public function destroy($id)
    {
        product_variation::where('product_id', $id)->delete();
        $category = $this->findProduct($id);
        if($category) {

            return $this->response->jsonResponse(false, $this->response->message('Product', 'destroy'), $category->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Product Not Exists',[], 201);
    }

    public function productSwitch($id) {
        $sc = $this->findProduct($id);
        if($sc) {
            $value = $sc->active_status === 1 ? 0 : 1;
            $msg = $value === 0 ? 'Deactivated': 'Activated';
            return $this->response->jsonResponse(false, 'Product '.$msg.' SuccessFully', $sc->update(['active_status' => $value]), 200);
        }
        return $this->response->jsonResponse(true, 'Product Not Exists',[], 201);
    }

    public function getActiveProduct() {
        return $this->response->jsonResponse(false, $this->response->message('Product', 'getActive'), Product::with('subCategory','images','unit', 'variation','category')->where('active_status', 1)->get(), 200);
    }

    public function getActiveProductNoDiscount() {
        return $this->response->jsonResponse(false, $this->response->message('Product', 'getActive'), Product::with('subCategory','images','unit', 'variation','category')->where('active_status', 1)->where('product_discount', 0)->get(), 200);
    }

    public function getActiveSuggestedProduct() {
        return $this->response->jsonResponse(false, $this->response->message('Product', 'show'), Product::with('subCategory','images','unit', 'variation','category')->where(['active_status'=> 1, 'suggested'=> 1])->get(), 200);
    }

    public function searchProduct($search) {
        if($search === "null") {
            return $this->response->jsonResponse(false, $this->response->message('Product', 'search'), [], 200);
        }
        return $this->response->jsonResponse(false, $this->response->message('Product', 'search'), Product::where('product_name', 'LIKE', $search.'%')->with('subCategory','images')->get(), 201);
    }

    public function findProduct($id) {
        return Product::find($id);
    }

    //image update for product
    public function imageUpdateProduct(Request $request)
    {
        $getProductSlug = $this->findProduct($request['product_id'])->product_slug;
        $getImageCount = ProductImage::where('product_id', $request['product_id'])->count() + 1;
        if($request->hasFile('product_image')) {
            $name = $getProductSlug.'-'.$getImageCount;
            $uploadUrl = $this->response->cloudinaryImage($request->file('product_image'), 'products', $name);
            $image = new ProductImage();
            $image->product_id = $request['product_id'];
            $image->product_image = $uploadUrl;
            $image->save();
            return $this->response->jsonResponse(false, $this->response->message('Product', 'image'), [], 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Size is too high', [], 201);
        }
    }



    //Product Details
    public function productDetails($id)
    {
        return $this->response->jsonResponse(false, 'Product Details Listed Successfully', Product::with('subCategory', 'images','unit', 'variation','category')->where('id', $id)->get(), 201);
    }

    //related products for particular product
    public function relatedProducts($product_slug)
    {
        $categoryId = Product::where('product_slug', $product_slug)->select('sub_category_id')->first();
        $getRelatedProducts = Product::with('images')->where('sub_category_id', $categoryId->sub_category_id)->whereNotIn('product_slug', [$product_slug])->take(6)->get();
        return $this->response->jsonResponse(false, 'Related Products Listed Successfully', $getRelatedProducts, 201);
    }
    //Searching a Products
    // public function searchProduct($search)
    // {
    //     if ($search === "null") {
    //         return $this->response->jsonResponse(false, 'Product filtered Successfully', [], 201);
    //     }

    //     return $this->response->jsonResponse(false, 'Product filtered Successfully', Product::with('images')->where('product_name', 'LIKE', $search . '%')->get(), 201);
    // }

    public function getSCTags($sub_category_slug)
    {
        return $this->response->jsonResponse(false, 'Tags Listed Successfully', SubCategory::with('tags')->where('sub_category_slug', $sub_category_slug)->first()->tags, 201);
    }

    //get Sub Category's product
    public function getSCProducts($sub_category_slug)
    {
        return $this->response->jsonResponse(false, 'SubCategory Product Listed Successfully', SubCategory::with('tags', 'products.firstImage')->where('active_status', 1)->where('sub_category_slug', $sub_category_slug)->first(), 201);
    }

    public function getProducts($sub_category_slug, $tag_id = null, $from_price = null, $to_price = null) {
        $getSc = SubCategory::with('products.images')->where('active_status', 1)->where('sub_category_slug', $sub_category_slug)->first();
        $getProducts = Product::with('firstImage')->where('sub_category_id', $getSc->sub_category_id)->where('active_status', 1)->get();

        if($tag_id !== "null") {
            $productId = [];
            foreach ($getSc->products as $product) {
                foreach ($product->product_tags as $prodTag) {
                    if((int)$tag_id === (int)$prodTag['id']) {
                        array_push($productId, $product->product_id);
                    }
                }
            }
            $getProducts->whereIn('product_id', $productId);
        }

        if($from_price !== "null" || $to_price !== "null") {
            $getProducts->whereBetween('product_discount_price', [(int)$from_price, (int)$to_price]);
        }

        $data = [];
        $data['sub_category'] = SubCategory::with('tags')->where('active_status', 1)->where('sub_category_slug', $sub_category_slug)->select('sub_category_id', 'category_id', 'sub_category_name', 'sub_category_slug')->first();
        $data['products'] = $getProducts->get();

       // $data1 = ProductDiscont::where('active_status', 1)->get();

        return $this->response->jsonResponse(false, 'SubCategory Product Filtered Successfully', $data1, 201);
    }

    public function productQuickView($id) {
        return $this->response->jsonResponse(false, 'ProductQuickView Listed Successfully', Product::with('images')->where('product_id', $id)->where('active_status', 1)->first(), 201);
    }

    //deleting a Product image
    public function deleteImageProduct($product_image_id)
    {
        $data = ProductImage::find($product_image_id);
        if ($data) {
            // $image_path = public_path().'/'.$data->product_image;
            // unlink($image_path);
            $data->delete();
            return $this->response->jsonResponse(false, 'Product Image Deleted Successfully', [], 201);
        } else {
            return $this->response->jsonResponse(true, 'Couldnt find a image', [], 404);
        }
    }

    public function stockCreate($sizes, $id) {
        $sizeArray = explode(',', $sizes);
        foreach ($sizeArray as $size) {
            Stock::create([
                'product_id' => $id,
                'size_id' => $size,
                'stock_quantity' => 0
            ]);
        }
    }


    public function createAddons(Request $request){
        $validate = $this->response->validate($request->all(),
        [
            'product_name'=>'required',
            'price'=>'required',
            'hsn'=>'required',
            'tax_id'=>'required'
        ]);
        if($validate === true) {
            $input = $request->all();
            $create = Addons::create($input);
            return $this->response->jsonResponse(false, $this->response->message('Addons', 'store'), $create, 200);
        } else {
            return $validate;
        }
    }

    public function updateAddons(Request $request){

        $validate = $this->response->validate($request->all(), [
            'product_name'=>'required',
            'price'=>'required',
            'hsn'=>'required',
            'tax_id'=>'required',
            'region_id'=>'required'
        ]);

        if($validate === true) {
            $input = $request->all();
            $create = Addons::where('addon_id', $request->addon_id)->update($input);
            return $this->response->jsonResponse(false, $this->response->message('Addons', 'update'), $create, 200);
        } else {
            return $validate;
        }

        return $this->response->jsonResponse(false, "Suggested Product updated", [], 200);

    }

    public function getAddonsList(){
        return $this->response->jsonResponse(false, "Addons listed sucessfully", Addons::get(), 200);
    }

    public function deleteAddons($id){
        $size = Addons::where('addon_id', $id)->first();
        if($size) {
            return $this->response->jsonResponse(false, $this->response->message('Addons', 'destroy'), $size->delete(), 200);
        }
        return $this->response->jsonResponse(true, 'Addons Not Exists',[], 201);
    }

    public function imageUpdateAddons(Request $request){
        $getSC = Addons::where('addon_id', $request->id)->select('product_name')->first();
        if($request->hasFile('addon_image')) {
            $uploadUrl = $this->response->cloudinaryImage($request->file('addon_image'), 'Addons', $getSC->product_name);
            return $this->response->jsonResponse(false, $this->response->message('SubCategory', 'image'), Addons::where('addon_id', $request['id'])->update(['image' => $uploadUrl]), 201);
        } else {
            return $this->response->jsonResponse(true, 'Image Size is too high', [], 201);
        }
    }
}
