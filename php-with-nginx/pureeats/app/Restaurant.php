<?php

namespace App;

use Event;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use willvincent\Rateable\Rateable;

class Restaurant extends Model implements Sortable
{
    use Rateable, SortableTrait;

    /**
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    /**
     * @var array
     */
    protected $casts = ['is_active' => 'integer', 'is_accepted' => 'integer', 'is_featured' => 'integer', 'delivery_type' => 'integer', 'delivery_radius' => 'integer', 'base_delivery_distance' => 'integer', 'extra_delivery_distance' => 'integer', 'distance' => 'float', 'is_operational' => 'boolean'];

    /**
     * @var array
     */
     protected $hidden = array('created_at', 'updated_at');
                
        /*addisRatingStoreCode*/
        // public function getRatingAttribute()
        // {
        //     return \Modules\RatingSystemPro\Entities\RatingStore::where('restaurant_id', $this->id)->average('rating');
        // }
        /*endaddisRatingStoreCode*/

    public static function boot()
    {
        parent::boot();

        static::created(function ($restaurant) {
            Event::dispatch('store.created', $restaurant);
        });

        static::updated(function ($restaurant) {
            Event::dispatch('store.updated', $restaurant);
        });

        static::deleted(function ($restaurant) {
            Event::dispatch('store.deleted', $restaurant);
        });
    }

    /**
     * @return mixed
     */
    public function items()
    {
        return $this->hasMany('App\Item');
    }

    /**
     * @return mixed
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return mixed
     */
    public function restaurant_categories()
    {
        return $this->belongsToMany('App\RestaurantCategory', 'restaurant_category_restaurant');
    }

    /**
     * @return mixed
     */
    public function toggleActive()
    {
        $this->is_active = !$this->is_active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function toggleAcceptance()
    {
        $this->is_accepted = !$this->is_accepted;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        $this->where('is_active', 1);
        return $this;
    }
    /**
     * @return mixed
     */
    public function isNotActive()
    {
        $this->where('is_active', 0);
        return $this;
    }

    /**
     * @return mixed
     */
    public function delivery_areas()
    {
        if (class_exists("\Modules\DeliveryAreaPro\Entities\DeliveryArea")) {
            return $this->belongsToMany(\Modules\DeliveryAreaPro\Entities\DeliveryArea::class);
        }
        return $this->belongsToMany(User::class);
    }

    /*addRelationshipCode*/ 
        // public function ratings()
        // {
        //     return $this->belongsToMany(\Modules\RatingSystemPro\Entities\RatingStore::class);
        // }
        /*endaddRelationshipCode*/
public function payment_gateways()
    {
        return $this->belongsToMany(\App\PaymentGateway::class);
    }

    public function payment_gateways_active()
    {
        return $this->belongsToMany(\App\PaymentGateway::class)->where('payment_gateways.is_active', '1');
    }

    public function store_payout_details()
    {
        return $this->hadMany('App\StorePayoutDetail');
    }

    /**
     * @return mixed
     */
    public function coupons()
    {        
        // return $this->belongsToMany(\App\Coupon::class)
        // ->select(['name', 'description', 'code'])
        // ->orderBy('coupon_restaurant.coupon_id', 'desc');
        $today = today()->format('Y-m-d');
        return $this->belongsToMany(\App\Coupon::class)                  
            ->orderBy('coupon_id', 'desc')
            //->select(['name', 'description', 'code'])  
            ->where('is_active', '1')
            //->where('is_exclusive', '1')
            ->where('expiry_date', '>=', $today) ;
            //->take(1);
    }

    public function menuList()
    {
        //return $this->hasMany('App\Item');
        //return $this->belongsToMany(\App\RestaurantCategory::class, 'item_categories');
        $result = $this->hasMany('App\Item')
            ->join('item_categories', 'item_categories.id', '=', 'items.item_category_id')
            /*################ NEW ###########################*/
            ->with('addon_categories')
            ->with(array('addon_categories.addons' => function ($query) {
                $query->where('is_active', 1);
                //$query->select('name', 'type', 'description', 'addons')->get();
            }))
            /*################################################*/
            ->where('is_enabled', '=', '1')
            ->select('items.*', 'item_categories.name AS category_name', 'item_categories.is_enabled AS is_enabled');
        return $result;
    }


     /**
     * @return mixed
     */
    public function location()
    {
        //return $this->belongsTo('App\Location');
        return null;
    }


}
