<?php
namespace App\Http\Dto;
use StoreChargeType;
use CalculationType;
use RatingType;


class DeliveryDetailsResponse{
    public bool $is_operational;
    public bool $is_serviceable;
    public string $operational_message;//'Due to heavy rain not possible to deliver the order';
    public Distance $distance;
    public Duration $delivery_time;
    public string $delivery_time_text;
    public float $min_order_price;
    public string $delivery_distance;
    public string $delivery_distance_text;
    public bool $enGDM;
    public string $errorcode;
}

?>