@extends('admin.layouts.master')
@section("title") Order - Dashboard
@endsection
@section('content')
<style>
    .content-wrapper {
        overflow: hidden;
    }
    .bill-calc-table tr td {
        padding: 6px 80px;
    }
    .td-title {
        padding-left: 15px !important;
    }
    .td-data {
        padding-right: 15px !important;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-md-8 mt-4">
            @if(\Nwidart\Modules\Facades\Module::find('ThermalPrinter') && \Nwidart\Modules\Facades\Module::find('ThermalPrinter')->isEnabled())
            <a href="{{ route('thermalprinter.printInvoice', $order->unique_order_id) }}" class="btn btn-sm btn-primary my-2 ml-2" style="color: #fff; border: 1px solid #ccc; float: right;"><i class="icon-printer mr-1"></i> {{__('thermalPrinterLang.printWithThermalPrinter')}}</a>
            @endif

            <a href="javascript:void(0)" id="printButton" class="btn btn-sm btn-primary my-2" style="color: #fff; border: 1px solid #ccc; float: right;"><i class="icon-printer mr-1"></i> Print Bill</a>
            <div class="clearfix"></div>
            <div class="card" id="printThis">
                <div href="#" class="btn btn-block content-group" style="text-align: left; background-color: #8360c3; color: #fff; border-radius: 0;"><strong style="font-size: 1.2rem;">{{ $order->unique_order_id }}</strong>
                </div>
                <div class="p-3">

                    <div class="d-flex justify-content-between">
                        <div class="form-group mb-0">
                            <h3><strong>{{ $order->restaurant->name }}</strong></h3>
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label no-margin text-semibold mr-1"><strong>Order Date:</strong></label>
                            {{ $order->created_at->format('Y-m-d  - h:i A')}} 
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-center">
                        <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="control-label no-margin text-semibold mr-1"><strong><h5><u> <a href="{{route('admin.get.editUser', $order->user->id)}}"target="_blank" class="linked-item">Customer Details</a></u></h5></strong></label>
                                <br>
                                <p><b>Name: </b> {{ $order->user->name }}</p>
                                <p><b>Email: </b> {{ $order->user->email }}</p>
                                <p><b>Contact Number: </b> {{ $order->user->phone }}</p>

                                @if($order->delivery_type == 1)
                                <p><b>Delivery Address: </b> {{ $order->address }}</p>
                                @endif

                                <p><b>Comment/Suggestion: </b>
                                    @if($order->order_comment != null){{ $order->order_comment }}
                                    @else NULL
                                    @endif
                                </p>

                                @if($order->delivery_type != 2)
                                <p><b>Delivery PIN: </b>
                                    @if($order->delivery_pin != null){{ $order->delivery_pin }}
                                    @else NULL
                                    @endif
                                </p>
                                @endif

                                <label class="control-label no-margin text-semibold mr-1">
                                    <strong><h5>DeliveryGuy Details:
                                                    @if($order->orderstatus_id == 3 || $order->orderstatus_id == 10 || $order->orderstatus_id == 73 || $order->orderstatus_id == 710 || $order->orderstatus_id == 4 || $order->orderstatus_id == 11 || $order->orderstatus_id == 5)
                                                        @if($order->accept_delivery && $order->accept_delivery->user && $order->accept_delivery->user->name)
                                                            <u>
                                                                <a href="{{route('admin.get.editUser',  $order->accept_delivery->user->id)}}"
                                                                   target="_blank" class="linked-item">
                                                                    {{ $order->accept_delivery->user->name }}
                                                                </a>
                                                            </u>
                                                        @endif
                                                    @endif
                                                </h5>
                                    </strong>
                                </label>



                                <p><b>Delivery Time: </b>
                                    {{ $order->restaurant->delivery_time }} min
                                </p>
                                <p><b>Prepare Time: </b>
                                    {{ $order->prepare_time }} min
                                </p>

{{--                                <label class="control-label no-margin text-semibold mr-1">--}}
{{--                                    <strong><h5><u>--}}
{{--                                                <a href="{{route('admin.get.editUser',  $order->accept_delivery->user->id)}}"--}}
{{--                                                   target="_blank" class="linked-item">DeliveryGuy Details:--}}
{{--                                                    @if($order->orderstatus_id == 3 || $order->orderstatus_id == 10 || $order->orderstatus_id == 73 || $order->orderstatus_id == 710 || $order->orderstatus_id == 4 || $order->orderstatus_id == 11 || $order->orderstatus_id == 5)--}}
{{--                                                        @if($order->accept_delivery && $order->accept_delivery->user && $order->accept_delivery->user->name)--}}
{{--                                                            {{ $order->accept_delivery->user->name }}--}}
{{--                                                        @endif--}}
{{--                                                    @endif--}}
{{--                                                </a></u></h5>--}}
{{--                                    </strong>--}}
{{--                                </label>--}}
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <div class="form-group mb-1">
                                <div class="d-flex justify-content-center align-items-center flex-column mb-1" style="border: 1px solid #ddd;">
                                    <div class="py-1" style="font-weight: 900;">STATUS</div>
                                    <hr style="width: 100%;" class="m-0">
                                    <div class="py-1 text-success @if ($order->orderstatus_id == 6) text-danger @endif" style="font-weight: 500;">
                                        @if ($order->orderstatus_id == 1) Order Placed @endif
                                        @if ($order->orderstatus_id == 2) Order Accepted @endif
                                        @if ($order->orderstatus_id == 3) Delivery Assigned @endif
                                        @if ($order->orderstatus_id == 4) Picked Up @endif
                                        @if ($order->orderstatus_id == 5) Completed @endif
                                        @if ($order->orderstatus_id == 6) Canceled @endif
                                        @if ($order->orderstatus_id == 7) Ready to Pickup @endif
                                        @if ($order->orderstatus_id == 8) Awaiting Payment @endif
                                        @if ($order->orderstatus_id == 9) Payment Failed @endif
                                        @if ($order->orderstatus_id == 10) DeliveryGuy Reached Pickup Location @endif
                                        @if ($order->orderstatus_id == 11) DeliveryGuy Reached Drop Location @endif
                                        @if ($order->orderstatus_id == 73) ORDER_READY_AND_DELIVERY_ASSIGNED @endif
                                        @if ($order->orderstatus_id == 710) ORDER_READY_AND_DELIVERY_REACHED_TO_PICKUP @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-1 mt-2">
                                <div class="d-flex">
                                    <div class="col p-0 mr-2">
                                    <div class="d-flex justify-content-center align-items-center flex-column mb-1" style="border: 1px solid #ddd;">
                                        <div class="py-1" style="font-weight: 900;">Order Type</div>
                                        <hr style="width: 100%;" class="m-0">
                                        <div class="py-1 text-warning" style="font-weight: 500;">
                                            @if($order->delivery_type == 1)
                                                Delivery
                                            @else
                                                Self-pickup
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col p-0">
                                    <div class="d-flex justify-content-center align-items-center flex-column mb-1" style="border: 1px solid #ddd;">
                                        <div class="py-1" style="font-weight: 900;">Payment Mode</div>
                                        <hr style="width: 100%;" class="m-0">
                                        <div class="py-1 text-warning" style="font-weight: 500;">
                                            {{ $order->payment_mode }}
                                            @if($order->partial_wallet == 1)
                                                + WALLET
                                            @endif
                                        </div>

                                    </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        </div>
                    </div>
                    

                    @php
                    $subTotal = 0;
                    function calculateAddonTotal($addons) {
                        $total = 0;
                        foreach ($addons as $addon) {
                            $total += $addon->addon_price;
                        }
                        return $total;
                    }
                    @endphp
                    <div class="text-right mt-3">
                        <div class="form-group mb-2">
                            <div class="clearfix"></div>
                            <div class="row">
                                <div class="col-md-12 p-2 mb-3" style="background-color: #f7f8fb; float: right; text-align: left;">
                                    @foreach($order->orderitems as $item)
                                    <div>
                                        <div class="d-flex mb-1 align-items-start" style="font-size: 1.2rem;">
                                            <span class="badge badge-flat border-grey-800 text-default mr-2">{{ $item->quantity }}x</span>
                                            <strong class="mr-1" style="width: 100%;">{{ $item->name }}</strong>
                                            @php
                                            $itemTotal = ($item->price +calculateAddonTotal($item->order_item_addons)) * $item->quantity;
                                            $subTotal = $subTotal + $itemTotal;
                                            @endphp
                                            <span class="badge badge-flat border-grey-800 text-default">{{ config('settings.currencyFormat') }}{{ $itemTotal }}</span>
                                        </div>
                                        @if(count($item->order_item_addons))
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Category</th>
                                                        <th>Addon</th>
                                                        <th>Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($item->order_item_addons as $addon)
                                                    <tr>
                                                        <td>{{ $addon->addon_category_name }}</td>
                                                        <td>{{ $addon->addon_name }}</td>
                                                        <td>{{ config('settings.currencyFormat') }}{{ $addon->addon_price }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        @if(!$loop->last)
                                        <div class="mb-2" style="border-bottom: 2px solid #dcdcdc;"></div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <div class="clearfix"></div>

                        <div class="float-right">
                            <table class="table table-bordered table-striped bill-calc-table">

                                <tr>
                                    <td class="text-left td-title">SubTotal</td>
                                    <td class="td-data"> {{ config('settings.currencyFormat') }}{{ $subTotal }}</td>
                                </tr>

                                @if($order->coupon_name != NULL)
                                    <tr>
                                        <td class="text-left td-title">Coupon</td>
                                        <td class="td-data"> {{ $order->coupon_name }} @if($order->coupon_amount != NULL) ({{ config('settings.currencyFormat') }}{{ $order->coupon_amount }}) @endif </td>
                                    </tr>
                                @endif

                                @if($order->restaurant_charge != NULL)
                                <tr>
                                    <td class="text-left td-title">Store Charge</td>
                                    <td class="td-data"> {{ config('settings.currencyFormat') }}{{ $order->restaurant_charge }} </td>
                                </tr>
                                @endif

                                <tr>
                                    <td class="text-left td-title">Delivery Charge</td>
                                    <td class="td-data"> {{ config('settings.currencyFormat') }}{{ $order->delivery_charge }} </td>
                                </tr>

                                @if($order->tax != NULL)
                                    <tr>
                                        <td class="text-left td-title">Tax</td>
                                        <td class="td-data">{{ $order->tax }}% @if($order->tax_amount != NULL) ({{ config('settings.currencyFormat') }}{{ $order->tax_amount }}) @endif </td>
                                    </tr>
                                @endif

                                @if(!$order->tip_amount == NULL)
                                    <tr>
                                        <td class="text-left td-title">Tip</td>
                                        <td class="td-data">{{ config('settings.currencyFormat') }}{{ $order->tip_amount }}</td>
                                    </tr>
                                @endif

                                @if($order->wallet_amount != NULL)
                                    <tr>
                                        <td class="text-left td-title">Paid With {{ config('settings.walletName') }}</td>
                                        <td class="td-data"> {{ config('settings.currencyFormat') }}{{ $order->wallet_amount }} </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td class="text-left td-title"><b>TOTAL</b></td>
                                    <td class="td-data"> {{ config('settings.currencyFormat') }}{{ $order->total }} </td>
                                </tr>

                                @if($order->payable != NULL)
                                    <tr>
                                        <td class="text-left td-title">Payable</td>
                                        <td class="td-data"><b> {{ config('settings.currencyFormat') }}{{ $order->payable }}</b></td>
                                    </tr>
                                @endif

                            </table>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                    <!-- New By Arpan -->
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 d-flex justify-content-center">
                            <div class="form-group">
                                <h3><strong>Order Snapshots</strong></h3>
                                @if($order->screenshot != null)
                                    <a href="{{url("/images/bill")}}{{ "/".$order->screenshot }}" target="_blank" style="display: inline-block;">
                                        <img src="{{url("/images/bill")}}{{ "/".$order->screenshot }}"
                                             alt="{{ $order->$order }}" height="320px" width="240px"
                                             style="border-radius: 0.275rem;">
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 d-flex justify-content-center">
                            <div class="form-group">
                                <h3><strong>Bill Photos</strong></h3>
                                @if($order->bill_photos != null)
                                    @foreach (json_decode($order->bill_photos, true) as $billPhoto)
                                        <a href="{{url("/images/bill")}}{{ "/".$billPhoto }}" target="_blank" style="display: inline-block;">
                                            <img src="{{url("/images/bill")}}{{ "/".$billPhoto }}"
                                                 alt="{{ $order->$order }}" height="100%" width="100%"
                                                 style="border-radius: 0.275rem;">
                                        </a>
                                    @endforeach
                                @endif

                            </div>
                        </div>
                    </div>
                    <!-- ./ END -->
                </div>






            </div>
        </div>
        <div class="col-md-4 mb-5">
            <div style="margin-top: 5.2rem"></div>
            <div class="card">
                <div class="card-body">

                    <div class="text-center">
                        @if($order->orderstatus_id ==5 )
                            <h3 class="mb-0"><b>Order Completed in:</b></h3>
                            <span>{{ timeStrampDiffFormatted($order->created_at, $order->updated_at) }}</span>
                        @elseif($order->orderstatus_id ==6)
                            <h3 class="mb-0"><b>Order cancelled in:</b></h3>
                            <span>{{ timeStrampDiffFormatted($order->created_at, $order->updated_at) }}</span>
                        @else
                            <h3 class="mb-0"><b>Ongoing order</b></h3>
                            <span class="liveTimerNonCompleteOrder"></span>
                        @endif
                    </div>
                    <hr>

                    @if($order->orderstatus_id != 5 && $order->orderstatus_id != 6)
                        <h3 class="text-center"> <strong> Order Actions </strong> </h3>
                        <hr class="mt-1">
                    @endif

                    <div class="form-group d-flex justify-content-center" style="flex-direction: {{$order->delivery_type==2 ? 'column' : 'row' }}">
                        <!-- START AcceptOrder[2]-->
                        @if($order->orderstatus_id == 1) 
                        <form action="{{ route('admin.acceptOrderFromAdmin') }}" class="mr-1" method="POST">
                            <input type="hidden" name="id" value="{{ $order->id }}">
                            @csrf
                            <button 
                                class="btn btn-primary btn-labeled btn-labeled-left mr-1"> <b><i
                                class="icon-checkmark3 ml-1"></i> </b> Accept Order </button>
                        </form>
                        @endif
                        <!-- ./END AcceptOrder-->

                        <!-- START MarkReady Order[7/10]-->
                        @if($order->orderstatus_id == 2 || $order->orderstatus_id == 3 || ($order->orderstatus_id == 10))
                            <form action="{{ route('admin.readyOrderFromAdmin') }}" class="mr-2" method="POST">
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                @csrf
                                <button class="btn btn-primary btn-labeled btn-labeled-left mr-2"> <b><i class="icon-checkmark3 ml-1"></i> </b> Mark Ready </button>
                            </form>
                        @endif
                       <!-- ./END MarkReady Order-->

                        <br>
                        <!-- START ReachPickupLocation[10]-->
                        @if($order->orderstatus_id == 3 || $order->orderstatus_id == 73)
                            <form action="{{ route('admin.reaachedPickupLocationFromAdmin') }}" class="mr-2" method="POST">
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                @csrf
                                <button class="btn btn-primary btn-labeled btn-labeled-left mr-2"> <b><i class="icon-checkmark3 ml-1"></i> </b> Reached Pickup Location</button>
                            </form>
                        @endif
                       <!-- ./END ReachPickupLocation-->


                        <!-- START DeliverOrder[5] -->
                        @if(($order->delivery_type == 1 && $order->orderstatus_id == 11))
                            <form action="{{ route('admin.deliverOrder') }}" class="mr-2" method="POST">
                                <input type="hidden" name="delivery_pin" value="{{ $order->delivery_pin }}">
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-labeled btn-labeled-left mr-2">
                                    <b><i class="icon-checkmark3 ml-1"></i> </b> Deliver Order
                                </button>

                            </form>
                        @endif    
                        
                        @if($order->delivery_type==2 && $order->orderstatus_id == 7)
                        <form action="{{route('admin.markSelfPickupOrderAsCompletedFromAdmin')}}" class="mr-2"method="POST">
                            <input type="text" class="form-control form-control-lg"  name="delivery_pin" placeholder = "Delivery PIN">
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <br>
                            @csrf
                            <button type="submit" class="btn btn-primary btn-labeled btn-labeled-left mr-2">
                                <b><i class="icon-checkmark3 ml-1"></i> </b> Deliver Self Pickup Order
                            </button>
                        </form>                       
                        @endif
                       
                    <!-- END AssignDeliveryGuy [3/10]-->



                        <br>
                        <!-- START ReachDropLocation[11]-->
                        @if($order->orderstatus_id == 4)
                            <form action="{{ route('admin.reaachedDropLocationFromAdmin') }}" class="mr-2" method="POST">
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                @csrf
                                <button class="btn btn-primary btn-labeled btn-labeled-left mr-2"> <b><i class="icon-checkmark3 ml-1"></i> </b> Reached Drop Location</button>
                            </form>
                        @endif
                       <!-- ./END ReachDropLocation-->




                        <!-- START CancelOrder [6]-->
                        @if($order->orderstatus_id == 1 || $order->orderstatus_id == 2 || $order->orderstatus_id == 3 || $order->orderstatus_id == 4 || $order->orderstatus_id == 7 || $order->orderstatus_id == 8 || $order->orderstatus_id == 9 || $order->orderstatus_id == 10 || $order->orderstatus_id == 11 || $order->orderstatus_id == 73 || $order->orderstatus_id == 710)
                        <a href="javascript:void(0)" class="btn btn-danger btn-labeled dropdown-toggle" data-toggle="dropdown">
                        Cancel Order
                        </a>
                        <div class="dropdown-menu">
                            <form action="{{ route('admin.cancelOrderFromAdmin') }}" method="POST">
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <input type="hidden" name="refund_type" value="NOREFUND">
                                @csrf
                                <button class="dropdown-item" @if($order->wallet_amount) type="submit" data-popup="tooltip" data-placement="bottom" title="{{ config('settings.currencyFormat') }}{{ $order->wallet_amount }} will be refunded as user has paid @if($order->payment_mode != "WALLET") partially @endif with Wallet" @endif>
                                Cancel Order
                                </button>
                            </form>
                            <form action="{{ route('admin.cancelOrderFromAdmin') }}" method="POST">
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <input type="hidden" name="refund_type" value="FULL">
                                @csrf
                                <button class="dropdown-item" type="submit" data-popup="tooltip" data-placement="bottom" title="Full refund of {{ config('settings.currencyFormat') }}{{ $order->total }} will be refunded to users wallet. (Even if user has not made any payment)">
                                Cancel With Full Refund
                                </button>
                            </form>
                            <form action="{{ route('admin.cancelOrderFromAdmin') }}" method="POST">
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <input type="hidden" name="refund_type" value="HALF">
                                @csrf
                                <button class="dropdown-item" type="submit" data-popup="tooltip" data-placement="bottom" title="Half refund of {{ config('settings.currencyFormat') }}{{ $order->total/2 }} will be refunded to users wallet. (Even if user has not made any payment)">
                                Cancel With Half Refund
                                </button>
                            </form>
                        </div>
                        @endif
                        <!-- ./END CancelOrder-->

                        <!-- START PickUpOrder[4]-->
                        @if($order->orderstatus_id == 710 )
                            <form action="{{ route('admin.pickedupOrderFromAdmin') }}" class="mr-2" method="POST">
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                @csrf
                                <button
                                        class="btn btn-primary btn-labeled btn-labeled-left mr-2"> <b><i
                                                class="icon-checkmark3 ml-1"></i> </b> Pick Order From Restaurant</button>
                            </form>
                        @endif
                        <!-- ./END PickUpOrder-->


                    @if($order->orderstatus_id == 8)
                        <a href="{{ route('admin.approvePaymentOfOrder', $order->id) }}" class="btn btn-secondary ml-2 approvePayment" data-popup="tooltip" data-placement="bottom" title="Double Click to Approve Payment">
                            Approve Payment
                        </a>
                        @endif
                    </div>

                    @if($order->delivery_type==1)
                        @if($order->orderstatus_id ==  2 || ($order->orderstatus_id ==  7 && $order->rider_accept_at == null))
                            <label class="control-label no-margin text-semibold mr-1"><strong>Assign Delivery Guy</strong></label>
                            <form action="{{route('admin.assignDeliveryFromAdmin')}}" method="POST">
                                <input type="text" hidden value="{{$order->id}}" name="order_id">
                                <input type="text" hidden value="{{$order->user->id}}" name="customer_id">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-lg-12">
                                        <select class="form-control select" data-fouc  name="user_id" required="required">
                                            <option></option>
                                            @foreach ($users as $user)
                                            @if ($user->hasRole('Delivery Guy'))
                                            <option value="{{$user->id}}" @if(!$user->delivery_guy_detail) disabled="disabled" @endif>{{$user->name}}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <br>
                                    <div class="col-lg-12 mt-1 text-right float-right">
                                        <button type="submit" class="btn btn-secondary mr-1">
                                        Assign Delivery
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endif 
                    
                    @if($order->delivery_type==1)
                        @if($order->orderstatus_id == 3 || $order->orderstatus_id == 4 || $order->orderstatus_id == 10 || $order->orderstatus_id == 73 || $order->orderstatus_id == 710)
                           @if($order->accept_delivery && $order->accept_delivery->user && $order->accept_delivery->user->name)
                           <p class="text-center mb-3"> <strong>Assigned Delivery Guy: {{ $order->accept_delivery->user->name }}</strong></p>
                           @endif
                            <form action="{{route('admin.reAssignDeliveryFromAdmin')}}" method="POST">
                                <input type="text" hidden value="{{$order->id}}" name="order_id">
                                <input type="text" hidden value="{{$order->user->id}}" name="customer_id">
                                @csrf
                                <div class="form-group row">
                                    <div class="col-lg-12">
                                        <select class="form-control select" data-fouc name="user_id" required="required">
                                            <option></option>
                                            @foreach ($users as $user)
                                                @if($order->accept_delivery->user_id != $user->id)
                                                    <option value="{{$user->id}}">{{$user->name}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-12 mt-2 text-center">
                                        <button type="submit" class="btn btn-secondary">
                                        Re-Assign Delivery
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endif

                    @if($order->orderstatus_id == 5 || $order->orderstatus_id == 6)
                        @if($order->accept_delivery && $order->accept_delivery->user && $order->accept_delivery->user->name)
                            <p class="text-center mb-3"> <strong>Assigned Delivery Guy: {{ $order->accept_delivery->user->name }}</strong></p>
                        @endif
                    @endif

                   
                        
                </div>
            </div>

            



            <div class="card">
                <div class="card-body">
                    <legend class="font-weight-semibold text-uppercase font-size-sm">
                        <i class="icon-basket mr-2"></i>
                        <span class="badge badge-primary badge-pill animated flipInX">
                        @if ($order->delivery_type == 1)
                                Deliverable Order
                            @endif
                            @if ($order->delivery_type == 2)
                                Self Pickup Order
                            @endif
                        </span>
                    </legend>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Status</th>
                            <th scope="col">Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">Order Placed</th>
                            <td>
                                @if ($order->created_at !== null)
                                    {{ date("d-M h:i a", strtotime($order->created_at))}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Order Accepted</th>
                            <td>
                                @if ($order->restaurant_accept_at !== null)
                                    {{ date("h:i a", strtotime($order->restaurant_accept_at))}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Marked Ready</th>
                            <td>
                                @if ($order->restaurant_ready_at !== null)
                                    {{ date("h:i a", strtotime($order->restaurant_ready_at))}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Delivery Assigned</th>
                            <td>
                                @if ($order->rider_accept_at !== null)
                                    {{ date("h:i a", strtotime($order->rider_accept_at))}}
                                @endif
                            </td>
                        </tr>

                        @if ($order->rider_reassigned_at !== null)
                            <tr>
                                <th scope="row">Delivery Re-Assigned</th>
                                <td>
                                    {{ date("h:i a", strtotime($order->rider_reassigned_at))}}
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <th scope="row">DeliveryGuy Reached PickUpLocation</th>
                            <td>
                                @if ($order->rider_reached_pickup_location_at !== null)
                                    {{ date("h:i a", strtotime($order->rider_reached_pickup_location_at))}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Meal Picked Up</th>
                            <td>
                                @if ($order->rider_picked_at !== null)
                                    {{ date("h:i a", strtotime($order->rider_picked_at))}}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">DeliveryGuy Reached DropLocation</th>
                            <td>
                                @if ($order->rider_reached_drop_location_at !== null)
                                    {{ date("h:i a", strtotime($order->rider_reached_drop_location_at))}}
                                @endif
                            </td>
                        </tr>
                        @if($order->orderstatus_id !== 6)
                            <tr>
                                <th scope="row" style= "color : green">Delivered</th>
                                <td>
                                    @if ($order->rider_deliver_at !== null)
                                        <strong style= "color : green">{{ date("h:i a", strtotime($order->rider_deliver_at))}}</strong>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if($order->orderstatus_id !== 5)
                            <tr>
                                <th scope="row" style= "color : red">Cancelled At</th>
                                <td>
                                    @if ($order->orderstatus_id == 6)
                                        <strong style= "color : red">{{ date("d/m/y (H:i a)", strtotime($order->updated_at))}}</strong>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<script>

    $(function() {
        var orderCreatedData = "{{ $order->created_at }}";
        var startDateTime = new Date(orderCreatedData); 
        var startStamp = startDateTime.getTime();

        var newDate = new Date();
        var newStamp = newDate.getTime();

        var timer; // for storing the interval (to stop or pause later if needed)

        function updateClock() {
            newDate = new Date();
            newStamp = newDate.getTime();
            var diff = Math.round((newStamp-startStamp)/1000);
            
            var d = Math.floor(diff/(24*60*60)); /* though I hope she won't be working for consecutive days :) */
            diff = diff-(d*24*60*60);
            var h = Math.floor(diff/(60*60));
            diff = diff-(h*60*60);
            var m = Math.floor(diff/(60));
            diff = diff-(m*60);
            var s = diff;
            var checkDay = d > 0 ? true : false;
            var checkHour = h > 0 ? true : false;
            var checkMin = m > 0 ? true : false;
            var checkSec = s > 0 ? true : false;
            var formattedTime = checkDay ? d+ " day" : "";
            formattedTime += checkHour ? " " +h+ " hour" : "";
            formattedTime += checkMin ? " " +m+ " minute" : "";
            formattedTime += checkSec ? " " +s+ " second" : "";

            $('.liveTimerNonCompleteOrder').text(formattedTime);
        }

        timer = setInterval(updateClock, 1000);
            

            $('#printButton').on('click',function(){
                $('#printThis').printThis();
            })
            
             $('.select').select2({
                 placeholder: 'Select Delivery Guy',
                allowClear: true,
             }); 

             $('body').on("click", ".approvePayment", function(e) {
                 return false;
             });
             $('body').on("dblclick", ".approvePayment", function(e) {
                 window.location = this.href;
                 return false;
             });
    });
    
</script>
@endsection