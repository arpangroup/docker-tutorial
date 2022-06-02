<?php
namespace App\Http\Dto;


use phpDocumentor\Reflection\Types\Boolean;

class DistanceMatrixResponse{
    public Distance $distance; // meter
    public Duration $duration; // minute
    public bool $enGDM;

    public static function createAddonItem($rawAddOnItem, $type, $category_name){
        $response = new AddonItem();

        $response->addon_name = (string)$rawAddOnItem->name;
        $response->addon_id = $rawAddOnItem->id;
        $response->price = (float)$rawAddOnItem->price;
        $response->addon_category_id = (int)$rawAddOnItem->addon_category_id;
        $response->type = (string)$type;
        $response->addon_category_name = (string)$category_name;
        return $response;
    }
}

?>