<?php
namespace App\Http\Dto;


class AddonItem{
    public string $addon_name;
    public int $addon_id;
    public float $price;
    public int $addon_category_id;
    public string $type='';
    public string $addon_category_name='';

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