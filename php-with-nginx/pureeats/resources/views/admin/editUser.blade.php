@extends('admin.layouts.master')
@section("title") Edit User - Dashboard
@endsection

@section('content')
<style>
    #showPassword {
    cursor: pointer;
    padding: 5px;
    border: 1px solid #E0E0E0;
    border-radius: 0.275rem;
    color: #9E9E9E;
    }
    #showPassword:hover {
    color: #616161;
    }
</style>
<div class="page-header">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4><i class="icon-circle-right2 mr-2"></i>
                <span class="font-weight-bold mr-2">Editing</span>
                <span class="badge badge-primary badge-pill animated flipInX">"{{ $user->email }}"</span>
            </h4>
            <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
        </div>
    </div>
</div>

<div class="content">
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.updateUser') }}" method="POST" enctype="multipart/form-data">
                        <legend class="font-weight-semibold text-uppercase font-size-sm">
                            <i class="icon-address-book mr-2"></i> User Details
                        </legend>
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Name:</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control form-control-lg" name="name"
                                    value="{{ $user->name }}" placeholder="Enter Full Name" required
                                    autocomplete="new-name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Email:</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control form-control-lg" name="email"
                                    value="{{ $user->email }}" placeholder="Emter Email Address" required
                                    autocomplete="new-email">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Phone:</label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control form-control-lg" name="phone" value="{{ $user->phone }}" 
                                    placeholder="Enter Phone Number" required autocomplete="new-phone">
                            </div>
                        </div>
                        <div class="form-group row form-group-feedback form-group-feedback-right">
                            <label class="col-lg-3 col-form-label">Password:</label>
                            <div class="col-lg-9">
                                <input id="passwordInput" type="password" class="form-control form-control-lg"
                                    name="password" placeholder="Enter Password (min 6 characters)"
                                    autocomplete="new-password">
                            </div>
                            <div class="form-control-feedback form-control-feedback-lg">
                                <span id="showPassword"><i class="icon-unlocked2"></i> Show</span>
                            </div>
                        </div>
                        <legend class="font-weight-semibold text-uppercase font-size-sm">
                            <i class="icon-tree7 mr-2"></i> Role Management
                        </legend>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Current Role:</label>
                            <div class="col-lg-9">
                                @foreach ($user->roles as $role)
                                <span class="badge badge-flat border-grey-800 text-default text-capitalize font-size-lg">
                                {{ $role->name }}
                                </span> @endforeach
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">New Role:</label>
                            <div class="col-lg-9">
                                @if($user->id == "1")
                                <span>Super Admin Role cannot be changed</span>
                                @else
                                <select class="form-control select" data-fouc name="roles">
                                    <option></option>
                                    @foreach ($roles as $key => $role)
                                    @if($key != 1)
                                        <option value="{{ $role->name }}" class="text-capitalize">{{ $role->name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                                @endif
                            </div>
                        </div>
                        @if($user->hasRole("Delivery Guy"))
                            <legend class="font-weight-semibold text-uppercase font-size-sm" id="deliveryGuyDetails">
                                <i class="icon-truck mr-2"></i> Delivery Guy Details
                            </legend>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Name or Nick Name:</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg" name="delivery_name"
                                        value="{{ !empty($user->delivery_guy_detail->name) ? $user->delivery_guy_detail->name : "" }}" placeholder="Enter Name or Nickname of Delivery Guy" required
                                        autocomplete="new-name">
                                        <span class="help-text text-muted">This name will be displayed to the user/customers</span>
                                </div>
                                
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Age</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg" name="delivery_age"
                                        value="{{ !empty($user->delivery_guy_detail->age) ? $user->delivery_guy_detail->age : "" }}" placeholder="Enter Delivery Guy's Age">
                                </div>
                            </div>
                            <div class="form-group row">
                                @if(!empty($user->delivery_guy_detail->photo))
                                <div class="col-lg-9 offset-lg-3">
                                    <img src="{{url("/")}}/assets/img/delivery/{{ $user->delivery_guy_detail->photo }}" alt="delivery-photo" class="img-fluid mb-2" style="width: 90px; border-radius: 50%">
                                </div>
                                @endif
                                <label class="col-lg-3 col-form-label">Delivery Guy's Photo:</label>
                                <div class="col-lg-9">
                                    <input type="file" class="form-control-uniform" name="delivery_photo" data-fouc>
                                    <span class="help-text text-muted">Image size 250x250</span>
                                </div>
                            </div>
                             <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Description</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg" name="delivery_description"
                                        value="{{ !empty($user->delivery_guy_detail->description) ? $user->delivery_guy_detail->description : "" }}" placeholder="Enter Short Description about this Delivery Guy">
                                </div>
                            </div>
                             <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Vehicle Number</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg" name="delivery_vehicle_number"
                                        value="{{ !empty($user->delivery_guy_detail->vehicle_number) ? $user->delivery_guy_detail->vehicle_number : "" }}" placeholder="Enter Delivery Guy's Vehicle Number">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">SMS Notification for New Orders?</label>
                                <div class="col-lg-9">
                                    <div class="checkbox checkbox-switchery mt-2">
                                        <label>
                                        <input value="true" type="checkbox" class="switchery-primary"
                                        @if(!empty($user->delivery_guy_detail->is_notifiable) && $user->delivery_guy_detail->is_notifiable) checked="checked" @endif name="is_notifiable">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Commission Rate %</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg commission_rate" name="delivery_commission_rate" placeholder="Commission Rate %" value="{{  !empty($user->delivery_guy_detail->commission_rate) ? $user->delivery_guy_detail->commission_rate : "0" }}" required="required">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Max Orders in Queue</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control form-control-lg max_orders" name="max_accept_delivery_limit" placeholder="Max Orders in Queue" value="{{  !empty($user->delivery_guy_detail->max_accept_delivery_limit) ? $user->delivery_guy_detail->max_accept_delivery_limit : "100" }}" required="required">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Tip Commission Rate %</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control form-control-lg commission_rate" name="tip_commission_rate" placeholder="Commission Rate %" value="{{  !empty($user->delivery_guy_detail->tip_commission_rate) ? $user->delivery_guy_detail->tip_commission_rate : "100" }}" required="required">
                                    </div>
                            </div>
                        @endif
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                            UPDATE
                            <i class="icon-database-insert ml-1"></i>
                            </button>
                        </div>
                        @csrf
                        <div class="text-left">
                            <div class="btn-group btn-group-justified" style="width: 150px">
                                @if($user->is_active)
                                <a class="btn btn-danger" href="{{ route('admin.banUser', $user->id) }}" data-popup="tooltip" title="User will not be able to place orders if banned">
                                    Ban User
                                    <i class="icon-switch2 ml-1"></i>
                                </a>
                                @else
                                <a class="btn btn-primary" href="{{ route('admin.banUser', $user->id) }}" data-popup="tooltip" title="Currently, {{ $user->name }} is banned from placing any orders">
                                    Reactivate User
                                    <i class="icon-switch2 ml-1"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <legend class="font-weight-semibold text-uppercase font-size-sm">
                            <i class="icon-piggy-bank mr-2"></i> {{ config("settings.walletName") }} Balance:  <span style="font-size: 1rem;">{{ config('settings.currencyFormat') }}{{ $user->balanceFloat }}</span>
                            <button class="btn btn-md btn-default" id="viewTransactions" style="float: right; border: 1px solid #ccc; line-height: 10px;">View Transactions</button>
                    </legend>
                    <button class="btn btn-primary btn-labeled btn-labeled-left mr-2" id="addAmountButton"><b><i class="icon-plus2"></i></b> Add Amount</button>
                    <button class="btn btn-secondary btn-labeled btn-labeled-left mr-2" id="substractAmountButton"><b><i class="icon-minus3"></i></b> Substract Amount</button>

                    <form action="{{ route('admin.addMoneyToWallet') }}" method="POST" id="addAmountForm" class="hidden" style="margin-top: 2rem;">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <div class="form-group row">
                            <label class="col-lg-4 col-form-label"><span class="text-danger">*</span>Add Money:</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control form-control-lg balance" name="add_amount"
                                    placeholder="Amount in {{ config('settings.currencyFormat') }}" required>
                            </div>
                        </div>
                         <div class="form-group row">
                            <label class="col-lg-4 col-form-label"><span class="text-danger">*</span>Message:</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control form-control-lg" name="add_amount_description"
                                    placeholder="Short Description or Message" required>
                            </div>
                        </div>
                        @csrf
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                            Update Balance
                            <i class="icon-database-insert ml-1"></i>
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('admin.substractMoneyFromWallet') }}" method="POST" id="substractAmountForm" class="hidden" style="margin-top: 2rem;">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                         <div class="form-group row">
                            <label class="col-lg-4 col-form-label"><span class="text-danger">*</span>Substract Money:</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control form-control-lg balance" name="substract_amount"
                                    placeholder="Amount in {{ config('settings.currencyFormat') }}" required>
                            </div>
                        </div>
                         <div class="form-group row">
                            <label class="col-lg-4 col-form-label"><span class="text-danger">*</span>Message:</label>
                            <div class="col-lg-8">
                                <input type="text" class="form-control form-control-lg" name="substract_amount_description"
                                    placeholder="Short Description or Message" required>
                            </div>
                        </div>
                        @csrf
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                            Update Balance
                            <i class="icon-database-insert ml-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($user->hasRole('Delivery Guy'))
                <div class="card" style="max-height: 360px;">
                    <div class="card-body">
                        <h4>Time Log</h4>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">LoginTime</th>
                                <th scope="col">LastSeen</th>
                                <th scope="col">Status</th>
                            </tr>
                            </thead>
                            @if(empty($sessions))
                                <div class="alert alert-danger p-2">
                                    <h6>No Data Available!</h6>
                                </div>
                            @endif
                            <tbody style="overflow: scroll">
                            @foreach($sessions as $session)
                                <tr>
                                    <td>
{{--                                        {{ date("d-m-y-(H:i a)", strtotime($session->login_at))}}--}}
{{--                                        {{ $session->created_at->format('d-m-y  - h:i A')}}--}}
                                        {{ $session->created_at->format('d-M  - h:i A')}}
                                    </td>
                                    <td>
                                        @if($session->last_checkout_at==NULL)
                                            Logout
                                        @else
                                            {{ date("d-M  - h:i A", strtotime($session->last_checkout_at))}}
                                        @endif
                                    </td>
                                    <th class="{{($session->is_online)?'text-success':'text-danger'}}" >{{($session->is_online)?'Online':'Offline'}}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($user->hasRole('Delivery Guy'))
                <div class="card">
                    <div class="card-body">
                        <h4>Live Location</h4>
                        <br>
                        <input type="text" id="currentLat"/>
                        <input type="text" id="currentLng"/>
                        <br />
                        <div style="height: 370px;" id="locationMap"></div>
                    </div>
                </div>
            @endif

        </div>
    </div>
    <div class="row" id="tansactionsDiv">
         <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                     <legend class="font-weight-semibold text-uppercase font-size-sm">
                            <i class="icon-transmission mr-2"></i> {{ config('settings.walletName') }} Transactions
                    </legend>
                    @if(count($user->transactions) > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        Type
                                    </th>
                                    <th width="20%">
                                        Amount
                                    </th>
                                    <th>
                                        Description
                                    </th>
                                    <th>
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->transactions->reverse() as $transaction)
                                <tr>
                                    <td>
                                        @if($transaction->type === "deposit")
                                            <span class="badge badge-flat border-grey-800 text-success text-capitalize">{{$transaction->type}}</span>
                                        @else
                                            <span class="badge badge-flat border-grey-800 text-danger text-capitalize">{{$transaction->type}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ config('settings.currencyFormat') }} {{ number_format($transaction->amount / 100, 2,'.', '') }}
                                    </td>
                                    <td>
                                        {{ $transaction->meta["description"] }}
                                    </td>
                                    <td>
                                       {{ $transaction->created_at->format('Y-m-d  - h:i A')}}  ({{ $transaction->created_at->diffForHumans() }})
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">No transactions has been made from {{ config('settings.walletName') }}</p>
                    @endif   
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="ordersDiv">
             <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                         <legend class="font-weight-semibold text-uppercase font-size-sm">
                                <i class="icon-basket mr-2"></i> Orders from user <strong>{{ $user->name }}</strong>
                        </legend>
                        @if(count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            Order ID
                                        </th>
                                        <th width="20%">
                                            Order Status
                                        </th>
                                        <th>
                                            Order Date
                                        </th>
                                        <th>
                                            Order Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders->reverse() as $order)
                                    <tr>
                                    <td>
                                    <a href="{{ route('admin.viewOrder', $order->unique_order_id ) }}">{{$order->unique_order_id}}</a>
                                    
                                    </td>
                                        <td>
                                            <span class="badge badge-flat border-grey-800 text-primary @if ($order->orderstatus_id == 6) text-danger @endif text-capitalize">
                                            @if ($order->orderstatus_id == 1) Order Placed @endif
                                            @if ($order->orderstatus_id == 2) Order Accepted @endif
                                            @if ($order->orderstatus_id == 3) Delivery Assigned @endif
                                            @if ($order->orderstatus_id == 4) Picked Up @endif
                                            @if ($order->orderstatus_id == 5) Completed @endif
                                            @if ($order->orderstatus_id == 6) Canceled @endif
                                            @if ($order->orderstatus_id == 7) Ready to Pickup @endif
                                            @if ($order->orderstatus_id == 8) Awaiting Payment @endif
                                            @if ($order->orderstatus_id == 9) Payment Failed @endif
                                            </span>
                                        </td>
                                      
                                        <td>
                                         {{ $order->created_at->format('Y-m-d  - h:i A')}}  ({{ $order->created_at->diffForHumans() }})
                                        </td>

                                        <td>
                                          {{ config('settings.currencyFormat') }}{{ $order->total }} 
                                        </td>
                                 </tr>
                                    @endforeach
                            </tbody>
                            </table>
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">No Orders Placed From This User</p>
                    @endif   
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    @if($user->hasRole('Delivery Guy') && sizeof($sessions) > 0)
    const currentLat = @json(json_encode($sessions[0]->location['lat']) ?? NULL);
    const currentLng = @json(json_encode($sessions[0]->location['lng']) ?? NULL);
    document.getElementById("currentLat").value = parseFloat(currentLat);
    document.getElementById("currentLng").value = parseFloat(currentLng);
    const deliveryGuyLoc = {lat: parseFloat(currentLat), lng: parseFloat(currentLng) };
    const locBelurMath = {lat: 22.6322, lng: 88.3559 };

    // const currentLocation = locBelurMath;// for default testing
    const currentLocation = deliveryGuyLoc;

    var map;


    function initMapNew() {
        const options = {
            zoom: 13,
            // center: {lat: -34.397, lng: 150.644 }
            center: currentLocation
        };

        map = new google.maps.Map(document.getElementById("locationMap"), options);
        addMarker({coords:currentLocation});
        addMarker({coords:currentLocation});
        addMarker({coords:currentLocation});
    }

    function addMarker(props){
        const marker = new google.maps.Marker({
            position: props.coords,
            map: map,
            //icon: 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png'
            icon: 'https://admin.pureeatstest.xyz/assets/img/delivery_guy.png'
        });

        const infoWindow = new google.maps.InfoWindow({
            content: '<h1>Delivery Guy</h1>'
        });

        marker.addListener('click', function(){
            infoWindow.open(map, marker);
        });

    }
    @endif
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCt_14My2CYghVw6eZFSYFlFPBOK29lkww&callback=initMapNew"></script>


<script>
    $(function () {

        if (Array.prototype.forEach) {
                 var elems = Array.prototype.slice.call(document.querySelectorAll('.switchery-primary'));
                 elems.forEach(function(html) {
                     var switchery = new Switchery(html, { color: '#2196F3' });
                 });
             }
             else {
                 var elems = document.querySelectorAll('.switchery-primary');
                 for (var i = 0; i < elems.length; i++) {
                     var switchery = new Switchery(elems[i], { color: '#2196F3' });
                 }
             }
             
        $('.form-control-uniform').uniform();

        $("#showPassword").click(function (e) { 
            $("#passwordInput").attr("type", "text");
        });
        $('.select').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Select Role (Old role will be revoked and the new role will be applied)',
        });
        $('.balance').numeric({allowThouSep:false, maxDecimalPlaces: 2 });

        $("#addAmountButton").click(function(event) {
            $('#addAmountButton').hide();
            $('#substractAmountButton').hide();
            $("#addAmountForm").removeClass('hidden');
            $("#substractAmountForm").addClass('hidden');
        });

        $("#substractAmountButton").click(function(event) {
            $('#addAmountButton').hide();
            $('#substractAmountButton').hide();
            $("#addAmountForm").addClass('hidden');
            $("#substractAmountForm").removeClass('hidden');
        });

        $("#viewTransactions").click(function(event) {
            var targetOffset = $('#tansactionsDiv').offset().top - 70;
            $('html, body').animate({scrollTop: targetOffset}, 500);
        });

        $('.commission_rate').numeric({ allowThouSep:false, maxDecimalPlaces: 2, max: 100, allowMinus: false });
        $('.max_orders').numeric({ allowThouSep:false, maxDecimalPlaces: 0, max: 99999, allowMinus: false });
    });
</script>

@endsection