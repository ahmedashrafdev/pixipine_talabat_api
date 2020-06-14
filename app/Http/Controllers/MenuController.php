<?php

namespace App\Http\Controllers;

use App\Category;
use App\Item;
use App\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{

    public function getItem($id , Request $request)
    {
        // $optionsVals = [8,5];
        $item = new Item;
        $result = $item->details($id);
        // dd($result);
        return response()->json($result);
    }
    public function getMenus()
    {
        return response()->json(Menu::get());
    }

    public function getCategories(Request $request)
    {
        $categories = Category::orderBy('orderBy')->get();
        $params = ['offset' => $request->offset,'no' => $request->no, 'search' => $request->search];
        
        
        return $this->getCategoriesItems($categories , $params );
    }


  
    protected function getCategoriesItems($categories , $params)
    {
        $result = [];
        $menu = isset($params['menu']) ? $params['menu'] : 0;
        $search = isset($params['search']) ? $params['search'] : '';
        // dd($search);
        foreach($categories as $category){ 
            // dd($category->id);
            $products = DB::select("call getCategoryItems(? , ? , ? , ? , ? , ? , ?)",
            [
                $params['offset'],
                $params['no'],
                $category->id,
                'name',
                $search,
                'ASC',
                $menu,
            ]);
            $form = ['category' => $category , 'items' => $products];
            array_push($result , $form);
        }

        return $result;

    }
    public function getMenu($id , Request $request){
        $menu = Menu::find($id);
        // dd($menu);
        $categories = DB::select('CALL getMenuCategories(?)' , [$id]);
        $params = ['offset' => $request->offset,'no' => $request->no, 'search' => $request->search , 'menu' => $menu->id];
        $result = ['menu' => $menu , 'categories' => $this->getCategoriesItems($categories , $params )];
        return $result;
    }


}
