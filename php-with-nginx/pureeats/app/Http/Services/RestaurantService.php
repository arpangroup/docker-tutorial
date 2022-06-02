<?php
namespace App\Http\Services;


use App\Http\Dto\DeliveryDetailsResponse;
use App\Http\Dto\RestaurantResponse;
use App\Restaurant;
use App\StoreWarning;
use Cache;
use App\Exceptions\ValidationException;
use App\Http\Repository\RestaurantRepository;
use ErrorCode;
use Illuminate\Support\Collection;
use Modules\DeliveryAreaPro\DeliveryArea;
use Modules\SuperCache\SuperCache;
use Nwidart\Modules\Facades\Module;

class RestaurantService{
    private $repository;
    private $isDistanceMatrixEnable;
    private $locationService;
    private $hasItemsInStoreList;
    private $isCachingEnabled;
    public function __construct(RestaurantRepository $restaurantRepository, LocationService  $locationService){
        $this->repository = $restaurantRepository;
        $this->isDistanceMatrixEnable = config('settings.enDistanceMatrixDeliveryTime') == 'true';
        $this->locationService = $locationService;
        $this->hasItemsInStoreList = false;
        $this->isCachingEnabled = false;
    }


    private function getAllActiveStores(){
        if (Cache::has('stores-all') && $this->isCachingEnabled) {
            $restaurants = Cache::get('stores-all');
        } else {
            $restaurants =  $this->repository->getAllStores($this->hasItemsInStoreList);
            $this->processSuperCache('stores-all', $restaurants);
        }
        return $restaurants;
    }

    public function getAllStores($lat = null, $lng = null){
        $restaurants = self::getAllActiveStores();
        if($lat == null || $lng == null) return $restaurants;

        $nearMe = array();

        foreach ($restaurants as $restaurant) {
            if($this->isDistanceMatrixEnable && !$restaurant->is_perm_closed){
                $distanceMatrixResponse = $this->locationService->getGoogleDistance($lat, $lng, $restaurant);
                if (($distanceMatrixResponse->distance->value / 1000) <= $restaurant->delivery_radius){// convert meter to km by dividing 1000
                    $restaurant->delivery_time = $distanceMatrixResponse->duration;
                    array_push($nearMe, self::filterResponse($restaurant));
                }
            }else {
                $check = $this->locationService->isStoreOperational($lat, $lng, $restaurant);
                if ($check) {
                    // Calculate the delivery time based on geographical distance
                    $distanceMatrixResponse = $this->locationService->getGeographicalDistance($lat, $lng, $restaurant);
                    $restaurant->delivery_time = $distanceMatrixResponse->duration;
                    array_push($nearMe, self::filterResponse($restaurant));
                }
            }
        }

        return $nearMe;
    }



    public function getStoreById($id, $lat = null, $lng = null){

        if (Cache::has('store-details-' . $id) && $this->isCachingEnabled) {
            $restaurant = Cache::get('store-details-' . $id);
        } else {
            $restaurant = $this->repository->getStoreById($id);
            $this->processSuperCache('store-details-' . $id, $restaurant);
        }

        if($lat != null && $lng != null){
            $restaurant->delivery_details = self::setDeliveryDetailsData($restaurant, $lat, $lng);
        }
        return self::filterResponse($restaurant);
    }

    public function getStoreBySlug($slug, $lat = null, $lng = null){
        if (Cache::has('store-details-' . $slug) && $this->isCachingEnabled) {
            $restaurant = Cache::get('store-details-' . $slug);
        } else {
            $restaurant =  $this->repository->getStoreBySlug($slug);
            $this->processSuperCache('store-details-' . $slug, $restaurant);
        }

        if($lat != null && $lng != null){
            $restaurant->delivery_details = self::setDeliveryDetailsData($restaurant, $lat, $lng);
        }
        return self::filterResponse($restaurant);

    }

    private function setDeliveryDetailsData($restaurant, $lat, $lng){
        if($this->isDistanceMatrixEnable && !$restaurant->is_perm_closed){
            $distanceMatrixResponse = $this->locationService->getGoogleDistance($lat, $lng, $restaurant);
        }else {
            $distanceMatrixResponse = $this->locationService->getGeographicalDistance($lat, $lng, $restaurant);
        }



        $response = new DeliveryDetailsResponse();
        $response->is_operational = false;
        $response->is_serviceable = false;
        $response->operational_message = 'operational';
        $response->delivery_time = $distanceMatrixResponse->duration;
        $response->distance = $distanceMatrixResponse->distance;
        $response->min_order_price = (float)$restaurant->min_order_price;
        $response->enGDM = $this->isDistanceMatrixEnable;


        /* Step1: First check if there exist any warning for the restaurant */
        $urgentWarning = StoreWarning::where('alert_type', 'STORE_OPERATIONAL')->where('is_active', '1')->first();
        if($urgentWarning){
            $warnedStores = explode(",", $urgentWarning['target_stores']);
            if(in_array($restaurant->id, $warnedStores)){
                $response->is_operational = false;
                $response->operational_message = $urgentWarning->message;
                $response->errorcode = ErrorCode::RESTAURANT_NOT_OPERATIONAL;
            }
        }

        if($restaurant->is_perm_closed){
            $response->is_serviceable = false;
            $response->operational_message = "Restaurant is closed";
            $response->errorcode = ErrorCode::RESTAURANT_NOT_OPERATIONAL;
        }

        if($response->is_operational) {
            /* Step2: Check Address is operational or not  */
            if($this->isDistanceMatrixEnable && !$restaurant->is_perm_closed){
                if (($distanceMatrixResponse->distance->value / 1000) > $restaurant->delivery_radius){// convert meter to km by dividing 1000
                    $response->is_operational = false;
                    $response->operational_message = "Restaurant is not operational";
                    $response->errorcode = ErrorCode::RESTAURANT_NOT_OPERATIONAL;
                }
            }else {
                $check = $this->locationService->isStoreOperational($lat, $lng, $restaurant);
                if (!$check) {
                    $response->is_operational = false;
                    $response->operational_message = "Restaurant is not operational";
                    $response->errorcode = ErrorCode::RESTAURANT_NOT_OPERATIONAL;
                }
            }
        }


        return $response;
    }

    private function filterResponse($restaurant){
        unset($restaurant->delivery_areas);
        unset($restaurant->delivery_radius);
        unset($restaurant->min_order_price);
        return $restaurant;
    }







    /**
     * @param $name
     * @param $data
     */
    private function processSuperCache($name, $data = null)
    {
        if (Module::find('SuperCache') && Module::find('SuperCache')->isEnabled()) {
            $superCache = new SuperCache();
            $superCache->cacheResponse($name, $data);
        }
    }

}

?>