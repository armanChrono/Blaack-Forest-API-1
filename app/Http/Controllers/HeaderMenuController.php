<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderMenu;
use App\Models\Category;
use App\Repositories\ResponseRepository;
use Illuminate\Support\Facades\Validator;

class HeaderMenuController extends Controller
{
    public function __construct(ResponseRepository $response)
    {
        $this->response = $response;
    }

    //Create HeaderMenu
    public function createHeaderMenu(Request $request)
    {
        $getCategory = Category::where('category_id', $request['category_id'])->first();
        $checkExisting = HeaderMenu::where('header_menu_name', $getCategory['category_name'])->exists();
        if ($checkExisting) {
            return $this->response->jsonResponse(true, $getCategory['header_menu_name'] . ' Already Exists', [], 201);
        }
        $create = new HeaderMenu;
        $create->header_menu_name = $getCategory['category_name'];
        $create->header_menu_slug = $getCategory['category_slug'];
        $create->save();

        return $this->response->jsonResponse(false, 'HeaderMenu Created Successfully', [], 201);
    }

    //deleting a HeaderMenu
    public function deleteHeaderMenu($header_menu_id)
    {
        return $this->response->jsonResponse(false, 'HeaderMenu Deleted Successfully', HeaderMenu::where('header_menu_id', $header_menu_id)->delete(), 201);
    }

    //activate a HeaderMenu will show a category in a panel
    public function activateHeaderMenu($header_menu_id)
    {
        $getHeaderMenu = HeaderMenu::where('header_menu_id', $header_menu_id);
        if ($getHeaderMenu->exists()) {
            return $this->response->jsonResponse(false, 'HeaderMenu Activated Successfully', $getHeaderMenu->update(['active_status' => 1]), 201);
        } else {
            return $this->response->jsonResponse(false, 'HeaderMenu Not Available', [], 201);
        }
    }

    //deactivate a HeaderMenu will show a category in a panel
    public function deActivateHeaderMenu($header_menu_id)
    {
        $getHeaderMenu = HeaderMenu::where('header_menu_id', $header_menu_id);
        if ($getHeaderMenu->exists()) {
            return $this->response->jsonResponse(false, 'HeaderMenu De-Activated Successfully', $getHeaderMenu->update(['active_status' => 0]), 201);
        } else {
            return $this->response->jsonResponse(false, 'HeaderMenu Not Available', [], 201);
        }
    }

    //listing all the listallHeaderMenus
    public function listAllHeaderMenu()
    {
        return $this->response->jsonResponse(false, 'HeaderMenu Listed Successfully', HeaderMenu::get(), 201);
    }

    //listing active HeaderMenus
    public function listActiveHeaderMenu()
    {
        return $this->response->jsonResponse(false, 'Active HeaderMenus Listed Successfully', HeaderMenu::where('active_status', 1)->get(), 201);
    }
}
