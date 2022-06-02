<?php
namespace App\Http\Dto;


use App\Address;
use phpDocumentor\Reflection\Types\Collection;

class RestaurantResponse{
    public int $store_id;
    public string $name;
    public string $localized_name;
    public string $description;
    public string $localized_description;
    public string $slug;
    public float $contact_number;
    public string $image;
    public string $thumb;
    public string $store_type;
    public string $delivery_type;//[DELIVERY, TAKEAWAY, DELIVERY_AND_TAKEAWAY]
    public string $price_range = '';
    public string $average_cost_for_two = '';
    public bool $is_pureveg = false;
    public bool $open_table_support = false;
    public bool $is_table_reservation_supported = false;
    public bool $is_perm_closed = false;// is_accepted == 0; this store will be show as disabled, no need to show the opening/closing time
    public bool $is_temp_closed = false;// if result returned from time scheduling as closed, will show opening/closing time
    public bool $is_opening_soon = false;//true<==If open today otherwise it is false
    public string $disclaimer_text = '';
    public Duration $delivery_time;
    public DeliveryCharge $delivery_charge;
    public object $store_charge;
    public object $rating;
    public AddressResponse $location;//AddressResponse
    public DeliveryDetailsResponse $delivery_details;
    public object $takeaway_details;
    public object $timing;
    public array $coupons = [];
    public array $items = [];

    public $min_order_price;
    public $delivery_radius;//transient<==Require to compare the distance and to check whether operational or not
    public $delivery_areas;//transient<==Require to compare the distance and to check whether operational or not


    public function addCoupon($coupon){
        array_push($this->coupons, $coupon);
    }
    public function addCoupons($couponList){
        foreach ($couponList as $coupon){
            array_push($this->coupons, CouponResponse::getCoupon($coupon));
        }
    }

    public function addItems($itemGroupList){
        $groupedItems = array();

        foreach ($itemGroupList as $categoryName => $items) {
            $itemCategoryInfo = ItemCategoryInfo::createFromItemGroup($categoryName, $items);
            array_push($groupedItems, $itemCategoryInfo);
        }
    }
}



?>