<?php
namespace App\Http\Services;

use App\Http\Dto\Distance;
use App\Http\Dto\DistanceMatrixResponse;
use App\Http\Dto\Duration;
use App\Http\Dto\RestaurantResponse;
use App\Http\Repository\RestaurantRepository;
use App\Restaurant;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use Modules\DeliveryAreaPro\DeliveryArea;
use Nwidart\Modules\Facades\Module;

class LocationService{
    public function __construct(){
        //$this->repository = $restaurantRepository;
        //$this->hasItemsInStoreList = false;
    }


    public static function getGoogleDistance($latitudeFrom, $longitudeFrom, RestaurantResponse $restaurant)
    {
        Log::channel('orderlog')->info('Inside getGoogleDistance: location(' .$latitudeFrom .','.$longitudeFrom.') for store: '. $restaurant->name);
        $lat1 = $latitudeFrom;
        $long1 = $longitudeFrom;
        $lat2 = $restaurant->location->latitude;
        $long2 = $restaurant->location->longitude;
        $API_KEY = 'AIzaSyCt_14My2CYghVw6eZFSYFlFPBOK29lkww';
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&alternatives=true&mode=driving&language=de-DE&sensor=false&key=".$API_KEY;
        $curl = Curl::to($url)->get();

        $curl = json_decode($curl, true);
        //$response_a = json_decode($response, true);
        Log::channel('orderlog')->info("DISTANCE_MATRIX_CURL_RESPONSE: " .$curl);

        // $distance = $curl['rows'][0]['elements'][0]['distance'];
        // $time = $curl['rows'][0]['elements'][0]['duration'];
        // $distance = $curl->rows[0]->elements;

        $deliveryTimeInSecondText = (string) $curl['rows'][0]['elements'][0]['duration']['text'];
        $deliveryTimeInSecond = (int) $curl['rows'][0]['elements'][0]['duration']['value'];
        $deliveryTimeInMin = ($deliveryTimeInSecond /60) + ((int)$restaurant->delivery_time->value);

        $duration = new Duration();
        $duration->text = $deliveryTimeInMin . ' min';
        $duration->value = $deliveryTimeInMin;

        $distance = new Distance();
        $distance->text = (string) $curl['rows'][0]['elements'][0]['distance']['text'];
        $distance->value = (int) $curl['rows'][0]['elements'][0]['distance']['value'];

        $distanceMatrixResponse = new DistanceMatrixResponse();
        $distanceMatrixResponse->distance = $distance;
        $distanceMatrixResponse->duration = $duration;
        $distanceMatrixResponse->enGDM = true;

        return $distanceMatrixResponse;


//        return array(
//            'distance' => $curl['rows'][0]['elements'][0]['distance'],
//            'duration' => $curl['rows'][0]['elements'][0]['duration']
//        );


    }

    public static function getGeographicalDistance($latitudeFrom, $longitudeFrom, RestaurantResponse $restaurant)
    {
        Log::channel('orderlog')->info('Inside getGeographicalDistance: location(' .$latitudeFrom .','.$longitudeFrom.') for store: '. $restaurant->name);
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($restaurant->location->latitude);
        $lonTo = deg2rad($restaurant->location->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));


        $distanceInKm = $angle * 6371;
        //return $distanceInKm;
        Log::channel('orderlog')->info('Distance calculated: ' .$distanceInKm);



        $approxDeliveryTime = $distanceInKm * ((int)config('settings.approxDeliveryTimePerKm')) ;
        //$deliveryTimeInMin = $approxDeliveryTime + ((int)$restaurant->delivery_time);
        $deliveryTimeInMin = $approxDeliveryTime + ((int)$restaurant->delivery_time->value);


        $duration = new Duration();
        $duration->text = $deliveryTimeInMin . ' min';
        $duration->value = $deliveryTimeInMin;
        $distance = new Distance();
        $distance->text = (string) $distanceInKm .' mi';
        $distance->value = (int) $distanceInKm *  1000;

        $distanceMatrixResponse = new DistanceMatrixResponse();
        $distanceMatrixResponse->distance = $distance;
        $distanceMatrixResponse->duration = $duration;
        $distanceMatrixResponse->enGDM = false;

        return $distanceMatrixResponse;
    }


    /**
     * @param $latitudeFrom
     * @param $longitudeFrom
     * @param $restaurant
     * @return boolean
     */
    public static function isStoreOperational($latitudeFrom, $longitudeFrom, $restaurant)
    {
        Log::channel('orderlog')->info('Inside isStoreOperational: location(' .$latitudeFrom .','.$longitudeFrom.') for store: '. $restaurant->name);
        try{
            $latFrom = deg2rad($latitudeFrom);
            $lonFrom = deg2rad($longitudeFrom);
            $latTo = deg2rad($restaurant->location->latitude);
            $lonTo = deg2rad($restaurant->location->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

            $distance = $angle * 6371; //distance in km
            Log::channel('orderlog')->info('Distance calculated: ' .$distance);
            Log::channel('orderlog')->info('Delivery Radius: ' .$restaurant->delivery_radius);

            //if any delivery area assigned
            if (count($restaurant->delivery_areas) > 0) {
                //check if delivery pro module exists,
                if (Module::find('DeliveryAreaPro') && Module::find('DeliveryAreaPro')->isEnabled()) {
                    $dap = new DeliveryArea();
                    return $dap->checkArea($latitudeFrom, $longitudeFrom, $restaurant->delivery_areas);
                } else {
                    //else use geenral distance
                    if ($distance <= $restaurant->delivery_radius) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                //if no delivery areas, then use general distance
                Log::channel('orderlog')->info('no delivery areas, then use general distance');
                if ($distance <= $restaurant->delivery_radius) {
                    return true;
                } else {
                    return false;
                }
            }
        }catch (\Throwable $th) {
            Log::channel('orderlog')->error('Exception in isStoreOperational(): ' .$th->getMessage());
            return false;
        }
    }


}

?>