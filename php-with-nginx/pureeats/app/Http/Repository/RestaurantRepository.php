<?php
namespace App\Http\Repository;

use App\Http\Mapper\ItemMapper;
use App\Http\Mapper\RestaurantMapper;
use App\Item;
use App\Restaurant;
use Illuminate\Support\Facades\DB;

class RestaurantRepository{

    public static function getAllStores($hasItems = false, $active = 1){
        $active = $active == '1' ? 1 : 0;
        $restaurants = Restaurant::where('is_active', $active)
            ->with(['coupons' => function($query){
                //$query->where('is_exclusive', 1);
                $query->select('name', 'code', 'description', 'min_subtotal', 'is_exclusive')->get();
                //$query->take(1);
                //$query->get();
            }])
            //->with('menuList')
            ->orderBy('is_accepted', 'DESC')
            ->get();

        $storeResult =[];


        //group the items based on category
        foreach ($restaurants as $restaurant) {
            $store = RestaurantMapper::mapTo($restaurant, $hasItems);
            array_push($storeResult, $store);
        }

        return $storeResult;
    }



    public static function getStoreById($id){
        $restaurant = Restaurant::where('id', $id)
            ->with(['coupons' => function($query){
                $query->select('name', 'code', 'description', 'min_subtotal', 'is_exclusive')->get();
                //$query->take(1);
                //$query->get();
            }])
            ->with('menuList')
            ->first();

        return RestaurantMapper::mapToDetails($restaurant, true);
    }

    public static function getStoreBySlug($slug){
        $restaurant = Restaurant::where('slug', $slug)
            ->with(['coupons' => function($query){
                $query->select('name', 'code', 'description', 'min_subtotal', 'is_exclusive')->get();
                //$query->take(1);
                //$query->get();
            }])
            ->with('menuList')
            ->first();
        return RestaurantMapper::mapToDetails($restaurant, true);
    }


}

?>