(window.webpackJsonp=window.webpackJsonp||[]).push([[24],{211:function(e,t,a){"use strict";function r(e,t){var a=t.left,r=t.right,n=t.mirror,o=t.opposite,l=(a?1:0)|(r?2:0)|(n?16:0)|(o?32:0)|(e?64:0);if(d.hasOwnProperty(l))return d[l];if(!n!=!(e&&o)){var i=[r,a];a=i[0],r=i[1]}var c=a?"-100%":r?"100%":"0",m=e?"from {\n        opacity: 1;\n      }\n      to {\n        transform: translate3d("+c+", 0, 0) skewX(30deg);\n        opacity: 0;\n      }\n    ":"from {\n        transform: translate3d("+c+", 0, 0) skewX(-30deg);\n        opacity: 0;\n      }\n      60% {\n        transform: skewX(20deg);\n        opacity: 1;\n      }\n      80% {\n        transform: skewX(-5deg);\n        opacity: 1;\n      }\n      to {\n        transform: none;\n        opacity: 1;\n      }";return d[l]=(0,s.animation)(m),d[l]}function n(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:s.defaults,t=e.children,a=(e.out,e.forever),n=e.timeout,o=e.duration,l=void 0===o?s.defaults.duration:o,c=e.delay,m=void 0===c?s.defaults.delay:c,d=e.count,u=void 0===d?s.defaults.count:d,p=function(e,t){var a={};for(var r in e)t.indexOf(r)>=0||Object.prototype.hasOwnProperty.call(e,r)&&(a[r]=e[r]);return a}(e,["children","out","forever","timeout","duration","delay","count"]),g={make:r,duration:void 0===n?l:n,delay:m,forever:a,count:u,style:{animationFillMode:"both"}};return p.left,p.right,p.mirror,p.opposite,(0,i.default)(p,g,g,t)}Object.defineProperty(t,"__esModule",{value:!0});var o,l=a(74),i=(o=l)&&o.__esModule?o:{default:o},c=a(2),s=a(57),m={out:c.bool,left:c.bool,right:c.bool,mirror:c.bool,opposite:c.bool,duration:c.number,timeout:c.number,delay:c.number,count:c.number,forever:c.bool},d={};n.propTypes=m,t.default=n,e.exports=t.default},212:function(e,t,a){"use strict";var r=a(4),n=a(5),o=a(8),l=a(7),i=a(9),c=a(0),s=a.n(c),m=a(23),d=a.n(m),u=a(211),p=a.n(u),g=function(e){function t(){return Object(r.a)(this,t),Object(o.a)(this,Object(l.a)(t).apply(this,arguments))}return Object(i.a)(t,e),Object(n.a)(t,[{key:"render",value:function(){var e=this;return s.a.createElement(s.a.Fragment,null,s.a.createElement("div",{className:"col-12 p-0 fixed",style:{zIndex:"9"}},s.a.createElement("div",{className:"block m-0"},s.a.createElement("div",{className:"block-content p-0 ".concat(this.props.dark&&"nav-dark")},s.a.createElement("div",{className:"input-group ".concat(this.props.boxshadow&&"search-box")},!this.props.disable_back_button&&s.a.createElement("div",{className:"input-group-prepend"},this.props.back_to_home&&s.a.createElement("button",{type:"button",className:"btn search-navs-btns",style:{position:"relative"},onClick:function(){setTimeout(function(){e.context.router.history.push("/")},200)}},s.a.createElement("i",{className:"si si-arrow-left"}),s.a.createElement(d.a,{duration:"500"})),this.props.goto_orders_page&&s.a.createElement("button",{type:"button",className:"btn search-navs-btns",style:{position:"relative"},onClick:function(){setTimeout(function(){e.context.router.history.push("/my-orders")},200)}},s.a.createElement("i",{className:"si si-arrow-left"}),s.a.createElement(d.a,{duration:"500"})),this.props.goto_accounts_page&&s.a.createElement("button",{type:"button",className:"btn search-navs-btns",style:{position:"relative"},onClick:function(){setTimeout(function(){e.context.router.history.push("/my-account")},200)}},s.a.createElement("i",{className:"si si-arrow-left"}),s.a.createElement(d.a,{duration:"500"})),!this.props.back_to_home&&!this.props.goto_orders_page&&!this.props.goto_accounts_page&&s.a.createElement("button",{type:"button",className:"btn search-navs-btns ".concat(this.props.dark&&"nav-dark"),style:{position:"relative"},onClick:function(){setTimeout(function(){e.context.router.history.goBack()},200)}},s.a.createElement("i",{className:"si si-arrow-left"}),s.a.createElement(d.a,{duration:"500"}))),s.a.createElement("p",{className:"form-control search-input d-flex align-items-center ".concat(this.props.dark&&"nav-dark")},this.props.logo&&s.a.createElement("img",{src:"/assets/img/logos/logo.png",alt:localStorage.getItem("storeName"),width:"120"}),this.props.has_title?s.a.createElement(s.a.Fragment,null,this.props.from_checkout?s.a.createElement("span",{className:"nav-page-title"},localStorage.getItem("cartToPayText")," ",s.a.createElement("span",{style:{color:localStorage.getItem("storeColor")}},"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),this.props.title,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"))):s.a.createElement("span",{className:"nav-page-title"},this.props.title)):null,this.props.has_delivery_icon&&s.a.createElement(p.a,{left:!0},s.a.createElement("img",{src:"/assets/img/various/delivery-bike.png",alt:this.props.title,className:"nav-page-title"}))),this.props.has_restaurant_info?s.a.createElement("div",{className:"fixed-restaurant-info hidden",ref:function(t){e.heading=t}},s.a.createElement("span",{className:"font-w700 fixedRestaurantName"},this.props.restaurant.name),s.a.createElement("br",null),s.a.createElement("span",{className:"font-w400 fixedRestaurantTime"},s.a.createElement("i",{className:"si si-clock"})," ",this.props.restaurant.delivery_time," ",localStorage.getItem("homePageMinsText"))):null,s.a.createElement("div",{className:"input-group-append"},!this.props.disable_search&&s.a.createElement("button",{type:"submit",className:"btn search-navs-btns",style:{position:"relative"}},s.a.createElement("i",{className:"si si-magnifier"}),s.a.createElement(d.a,{duration:"500"})),this.props.homeButton&&s.a.createElement("button",{type:"button",className:"btn search-navs-btns nav-home-btn",style:{position:"relative"},onClick:function(){setTimeout(function(){e.context.router.history.push("/")},200)}},s.a.createElement("i",{className:"si si-home fa-2x text-muted"}),s.a.createElement(d.a,{duration:"500"}))))))))}}]),t}(c.Component);g.contextTypes={router:function(){return null}},t.a=g},232:function(e,t,a){"use strict";a.d(t,"b",function(){return i}),a.d(t,"a",function(){return c});var r=a(80),n=a(11),o=a(6),l=a.n(o),i=function(e,t,a,o,i){return function(c){l.a.post(n.cb,{token:e,user_id:t,delivery_lat:a,delivery_long:o,heading:i}).then(function(e){var t=e.data;return c({type:r.b,payload:t})}).catch(function(e){console.log(e)})}},c=function(e,t){return function(a){l.a.post(n.p,{token:e,order_id:t}).then(function(e){var t=e.data;return a({type:r.a,payload:t})}).catch(function(e){console.log(e)})}}},248:function(e,t,a){"use strict";var r=a(4),n=a(5),o=a(8),l=a(7),i=a(9),c=a(0),s=a.n(c),m=a(17),d=a(232),u=function(e){function t(){var e,a;Object(r.a)(this,t);for(var n=arguments.length,i=new Array(n),c=0;c<n;c++)i[c]=arguments[c];return(a=Object(o.a)(this,(e=Object(l.a)(t)).call.apply(e,[this].concat(i)))).state={gpsAccessError:!1,lat:null,lng:null},a.__sendGpsLocation=function(e){a.props.sendDeliveryGuyGpsLocation(a.props.delivery_user.data.auth_token,a.props.delivery_user.data.id,e.coords.latitude,e.coords.longitude,e.coords.heading)},a}return Object(i.a)(t,e),Object(n.a)(t,[{key:"componentDidMount",value:function(){var e=this,t=navigator&&navigator.geolocation;t&&(this.refreshSetInterval=setInterval(function(){t.getCurrentPosition(function(t){e.__sendGpsLocation(t)},function(t){console.log("Inside error"),console.log(t),e.setState({gpsAccessError:!0})},{enableHighAccuracy:!0})},15e3))}},{key:"componentWillUnmount",value:function(){clearInterval(this.refreshSetInterval),console.log("Cleared API CALL")}},{key:"render",value:function(){return s.a.createElement(s.a.Fragment,null,s.a.createElement("div",{className:"pt-50"},this.state.gpsAccessError&&s.a.createElement("div",{className:"auth-error location-error",style:{bottom:"5rem"}},s.a.createElement("div",{className:"error-shake"},localStorage.getItem("allowLocationAccessMessage")))))}}]),t}(c.Component);t.a=Object(m.b)(function(e){return{delivery_user:e.delivery_user.delivery_user}},{sendDeliveryGuyGpsLocation:d.b})(u)},342:function(e,t,a){"use strict";a.r(t);var r=a(4),n=a(5),o=a(8),l=a(7),i=a(9),c=a(0),s=a.n(c),m=a(89),d=a(102),u=a(11),p=a(6),g=a.n(p),y=a(212),v=a(42),h=a(43),f=a(216),_=a.n(f),E=function(e){function t(){return Object(r.a)(this,t),Object(o.a)(this,Object(l.a)(t).apply(this,arguments))}return Object(i.a)(t,e),Object(n.a)(t,[{key:"render",value:function(){var e=this.props.item;return s.a.createElement(s.a.Fragment,null,s.a.createElement("div",{className:"d-flex justify-content-between"},s.a.createElement("div",null,s.a.createElement("span",{className:"delivery-order-item-quantity"},e.quantity,"x")),s.a.createElement("div",{style:{maxWidth:"200px",minWidth:"200px"}},s.a.createElement("strong",null,e.name)),"true"===localStorage.getItem("showPriceAndOrderCommentsDelivery")&&s.a.createElement("div",{className:"cart-item-price"},s.a.createElement(s.a.Fragment,null,"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),e.price,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat")))),"true"===localStorage.getItem("showOrderAddonsDelivery")&&s.a.createElement("div",{className:"delivery-items-addons-block"},e&&e.order_item_addons.map(function(t,a){return s.a.createElement(s.a.Fragment,{key:e.id+t.id+a},s.a.createElement("div",{className:"d-flex justify-content-between"},s.a.createElement("div",null,t.addon_name),s.a.createElement("div",null,"true"===localStorage.getItem("showPriceAndOrderCommentsDelivery")&&s.a.createElement(s.a.Fragment,null,"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat")," ",t.addon_price,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat")))))})),s.a.createElement("hr",{className:"single-item-division-hr"}))}}]),t}(c.Component),b=a(361),S=a(17),N=a(248),k=s.a.createRef(),I=s.a.createRef(),x="ontouchstart"in document.documentElement,w=function(e){function t(){var e,a;Object(r.a)(this,t);for(var n=arguments.length,i=new Array(n),c=0;c<n;c++)i[c]=arguments[c];return(a=Object(o.a)(this,(e=Object(l.a)(t)).call.apply(e,[this].concat(i)))).isDragging=!1,a.sliderLeft=0,a.state={},a.onDrag=function(e){a.unmounted||a.state.unlocked||a.isDragging&&(a.sliderLeft=x?Math.min(Math.max(0,e.touches[0].clientX-a.startX),a.containerWidth):Math.min(Math.max(0,e.clientX-a.startX),a.containerWidth),a.updateSliderStyle())},a.updateSliderStyle=function(){a.unmounted||a.state.unlocked||(k.current.style.left=a.sliderLeft+80+"px")},a.stopDrag=function(){a.unmounted||a.state.unlocked||a.isDragging&&(a.isDragging=!1,a.sliderLeft>.9*a.containerWidth?(a.sliderLeft=a.containerWidth,a.onSuccess(),a.props.onSuccess&&a.props.onSuccess()):(a.sliderLeft=0,a.props.onFailure&&a.props.onFailure()),a.updateSliderStyle())},a.startDrag=function(e){a.unmounted||a.state.unlocked||(a.isDragging=!0,a.startX=x?e.touches&&e.touches[0].clientX:e.clientX)},a.onSuccess=function(){I.current.style.width=I.current.clientWidth+"px",a.setState({unlocked:!0})},a.getText=function(){return a.state.unlocked?a.props.text_unlocked||s.a.createElement("i",{className:"si si-check"}):a.props.text||"Slide to Proceed"},a.reset=function(){a.unmounted||a.setState({unlocked:!1},function(){a.sliderLeft=0,a.updateSliderStyle()})},a}return Object(i.a)(t,e),Object(n.a)(t,[{key:"componentDidMount",value:function(){x?(document.addEventListener("touchmove",this.onDrag),document.addEventListener("touchend",this.stopDrag)):(document.addEventListener("mousemove",this.onDrag),document.addEventListener("mouseup",this.stopDrag)),this.containerWidth=I.current.clientWidth-80}},{key:"componentWillReceiveProps",value:function(e){e.reset&&this.reset()}},{key:"componentWillUnmount",value:function(){this.unmounted=!0}},{key:"render",value:function(){return s.a.createElement("div",{className:"ReactSwipeButton"},s.a.createElement("div",{className:"rsbContainer slider-bg-color ".concat(this.state.unlocked&&"rsbContainerUnlocked"),ref:I},s.a.createElement("div",{className:"rsbcSlider",ref:k,onMouseDown:this.startDrag,onTouchStart:this.startDrag},s.a.createElement("span",{className:"rsbcSliderText"},this.getText()),s.a.createElement("span",{className:"rsbcSliderArrow"})),s.a.createElement("div",{className:"rsbcText"},this.getText())))}}]),t}(c.Component),O=a(78),D=a.n(O),T=function(e){function t(){var e,a;Object(r.a)(this,t);for(var n=arguments.length,i=new Array(n),c=0;c<n;c++)i[c]=arguments[c];return(a=Object(o.a)(this,(e=Object(l.a)(t)).call.apply(e,[this].concat(i)))).state={initLoading:!0,loading:!1,already_accepted:!1,accepted_order:!1,picked_up:!1,delivered:!1,delivery_pin:"",delivery_pin_error:!1,reset:!1,max_order:!1},a.__acceptToDeliver=function(){a.props.acceptToDeliverOrder(a.props.delivery_user.data.auth_token,a.props.delivery_user.data.id,a.props.single_delivery_order.id),a.setState({loading:!0})},a.__pickedUp=function(){a.props.pickupOrder(a.props.delivery_user.data.auth_token,a.props.single_delivery_order.id),a.setState({loading:!0})},a.__delivered=function(){a.props.deliverOrder(a.props.delivery_user.data.auth_token,a.props.single_delivery_order.id,a.state.delivery_pin),a.setState({loading:!0})},a.__getDirectionToRestaurant=function(e,t){var a="http://maps.google.com/maps?q="+e+","+t;window.open(a,"_blank")},a.__getDirectionToUser=function(e){try{JSON.parse(e);var t="http://maps.google.com/maps?q="+JSON.parse(e).lat+","+JSON.parse(e).lng;window.open(t,"_blank")}catch(r){JSON.parse(e);var a="http://maps.google.com/maps?q="+e;window.open(a,"_blank")}},a.__handleDeliveryPinInput=function(e){a.setState({delivery_pin:e.target.value})},a.getDeliveryGuyTotalEarning=function(e){var t=0;return e.commission&&(t+=parseFloat(e.commission)),e.tip_amount&&(t+=parseFloat(e.tip_amount)),t},a}return Object(i.a)(t,e),Object(n.a)(t,[{key:"componentDidMount",value:function(){var e=this;document.getElementsByTagName("body")[0].classList.remove("bg-grey"),document.getElementsByTagName("body")[0].classList.add("delivery-dark-bg"),this.props.delivery_user.success&&this.props.getSingleDeliveryOrder(this.props.delivery_user.data.auth_token,this.props.match.params.unique_order_id).then(function(t){t&&t.payload&&t.payload.id&&e.setState({initLoading:!1})})}},{key:"componentWillReceiveProps",value:function(e){2===e.single_delivery_order.orderstatus_id&&(e.single_delivery_order.max_order&&this.setState({max_order:!0,reset:!0}),this.setState({loading:!1})),3===e.single_delivery_order.orderstatus_id&&(e.single_delivery_order.already_accepted&&this.setState({already_accepted:!0,reset:!0}),this.setState({accepted_order:!0}),this.setState({loading:!1})),4===e.single_delivery_order.orderstatus_id&&(this.setState({accepted_order:!0,picked_up:!0}),this.setState({loading:!1})),e.single_delivery_order.delivery_pin_error&&this.setState({delivery_pin_error:!0,reset:!0}),5===e.single_delivery_order.orderstatus_id&&(this.setState({loading:!1}),this.context.router.history.push("/delivery"))}},{key:"render",value:function(){var e=this;if(window.innerWidth>768)return s.a.createElement(b.a,{to:"/"});var t=this.props.single_delivery_order;return s.a.createElement(s.a.Fragment,null,s.a.createElement(h.a,{seotitle:"Delivery Orders",seodescription:localStorage.getItem("seoMetaDescription"),ogtype:"website",ogtitle:localStorage.getItem("seoOgTitle"),ogdescription:localStorage.getItem("seoOgDescription"),ogurl:window.location.href,twittertitle:localStorage.getItem("seoTwitterTitle"),twitterdescription:localStorage.getItem("seoTwitterDescription")}),s.a.createElement(y.a,{boxshadow:!0,has_title:!0,title:t.unique_order_id&&"#"+t.unique_order_id.substr(t.unique_order_id.length-8),disable_search:!0,dark:!0}),this.state.initLoading?s.a.createElement("div",{className:"pt-50"},s.a.createElement(v.a,{height:150,width:400,speed:1.2,primaryColor:"#192038",secondaryColor:"#222b45"},s.a.createElement("rect",{x:"20",y:"70",rx:"4",ry:"4",width:"80",height:"78"}),s.a.createElement("rect",{x:"144",y:"85",rx:"0",ry:"0",width:"115",height:"18"}),s.a.createElement("rect",{x:"144",y:"115",rx:"0",ry:"0",width:"165",height:"16"}))):s.a.createElement(s.a.Fragment,null,this.state.loading&&s.a.createElement("div",{className:"height-100 overlay-loading ongoing-payment-spin"},s.a.createElement("div",{className:"spin-load"})),!this.state.delivered&&s.a.createElement(s.a.Fragment,null,this.state.max_order&&s.a.createElement("div",{className:"delivery-error delivery-max-order-reached"},s.a.createElement("div",{className:"error-shake"},localStorage.getItem("deliveryMaxOrderReachedMsg"))),this.state.already_accepted?s.a.createElement("div",{className:"delivery-error delivery-already-accepted-error"},s.a.createElement("div",{className:"error-shake"},localStorage.getItem("deliveryAlreadyAccepted"))):s.a.createElement(s.a.Fragment,null,s.a.createElement("div",{className:"view-delivery-order",style:{paddingBottom:"20rem",paddingTop:"4rem"}},("true"===localStorage.getItem("enableDeliveryGuyEarning")||t.tip_amount>0)&&s.a.createElement("div",{className:"pt-20 px-15 pb-15"},s.a.createElement("div",{className:"p-15 single-order-earnings-block"},"true"===localStorage.getItem("enableDeliveryGuyEarning")&&s.a.createElement("div",{className:"d-flex justify-content-between"},s.a.createElement("div",null,localStorage.getItem("deliveryEarningCommissionText")),s.a.createElement("div",null,"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),t.commission,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"))),t.tip_amount>0&&s.a.createElement("div",{className:"d-flex justify-content-between"},s.a.createElement("div",null,localStorage.getItem("deliveryEarningTipText")),s.a.createElement("div",null,"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),t.tip_amount,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"))),s.a.createElement("hr",{className:"single-item-division-hr"}),s.a.createElement("div",{className:"d-flex justify-content-between align-items-center single-order-total-earnings"},s.a.createElement("div",null,localStorage.getItem("deliveryEarningTotalEarningText")),s.a.createElement("div",null,"left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),this.getDeliveryGuyTotalEarning(t),"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"))))),")}",s.a.createElement("div",{className:"p-15"},s.a.createElement("div",{className:"single-order-metas d-flex justify-content-between"},s.a.createElement("div",null,s.a.createElement("i",{className:"si si-clock mr-5"})," ",localStorage.getItem("deliveryOrderPlacedText"),":"," ",s.a.createElement(_.a,{fromNow:!0,interval:5e3},t.created_at))),s.a.createElement("ul",{className:"list list-timeline list-timeline-modern delivery-address-timeline pull-t pb-20"},s.a.createElement("li",null,s.a.createElement("i",{className:"list-timeline-icon si si-basket-loaded bg-del-timeline-icon"}),s.a.createElement(D.a,{bottom:!0,duration:500},s.a.createElement("div",{className:"list-timeline-content"},s.a.createElement("p",{className:"m-0 font-w700"},t.restaurant.name),s.a.createElement("p",{className:"m-0 single-order-restaurant-description"},t.restaurant.description),s.a.createElement("p",{className:"m-0"},t.restaurant.address),s.a.createElement("p",{className:"mb-2"},t.restaurant.pincode),s.a.createElement("div",{onClick:function(){return e.__getDirectionToRestaurant(t.restaurant.latitude,t.restaurant.longitude)}},s.a.createElement("button",{className:"btn btn-get-direction"},s.a.createElement("i",{className:"si si-action-redo mr-5"}),localStorage.getItem("deliveryGetDirectionButton")))))),s.a.createElement("li",{className:"mt-20"},s.a.createElement("i",{className:"list-timeline-icon si si-pointer bg-del-timeline-icon"}),s.a.createElement(D.a,{bottom:!0,duration:1e3},s.a.createElement("div",{className:"list-timeline-content"},s.a.createElement("p",{className:"font-w700"},t.user.name),s.a.createElement("p",{className:"mb-0"},t.user.phone),s.a.createElement("p",{className:"mb-2"},t.address),s.a.createElement("div",null,s.a.createElement("button",{className:"btn btn-get-direction mr-2",onClick:function(){return e.__getDirectionToUser(t.location)}},s.a.createElement("i",{className:"si si-action-redo mr-5"}),localStorage.getItem("deliveryGetDirectionButton")),s.a.createElement("a",{className:"btn btn-get-direction",href:"tel:"+t.user.phone},s.a.createElement("i",{className:"si si-call-out mr-5"}),localStorage.getItem("callNowButton")," "))))))),s.a.createElement("div",{className:"pt-15 px-15"},s.a.createElement("div",{className:"single-order-items-title"},s.a.createElement("i",{className:"si si-list mr-2"}),localStorage.getItem("deliveryOrderItems")),s.a.createElement("div",{className:"p-15 single-order-items-list"},t.orderitems.map(function(e){return s.a.createElement(E,{item:e,key:e.id})}),"true"===localStorage.getItem("showPriceAndOrderCommentsDelivery")&&s.a.createElement(s.a.Fragment,null,s.a.createElement("p",null,t.order_comment),s.a.createElement("p",{className:"pull-right font-w700 h4 text-white"},"Total:"," ","left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),t.total,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat")),s.a.createElement("div",{className:"clearfix"})))),this.state.picked_up&&s.a.createElement(s.a.Fragment,null,s.a.createElement("div",{className:"pt-15 px-15"},"COD"===t.payment_mode?s.a.createElement("button",{className:"btn btn-cod"},localStorage.getItem("deliveryCashOnDelivery"),":"," ","left"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat"),t.payable,"right"===localStorage.getItem("currencySymbolAlign")&&localStorage.getItem("currencyFormat")):s.a.createElement("button",{className:"btn btn-payed-online"},s.a.createElement("i",{className:"si si-check mr-5"})," ",localStorage.getItem("deliveryOnlinePayment"))),"true"===localStorage.getItem("enableDeliveryPin")&&s.a.createElement("div",{className:"pt-10 px-15 delivery-pin-block"},s.a.createElement("div",{className:"form-group"},s.a.createElement("div",{className:"row"},s.a.createElement("div",{className:"col-12"},s.a.createElement("input",{type:"text",className:"form-control",placeholder:localStorage.getItem("deliveryDeliveryPinPlaceholder"),onChange:this.__handleDeliveryPinInput}),this.state.delivery_pin_error&&s.a.createElement("div",{className:"delivery-pin-error",style:{zIndex:"9",marginBottom:"4rem"}},s.a.createElement("div",{className:"error-shake"},localStorage.getItem("deliveryInvalidDeliveryPin")))))))),s.a.createElement("div",{className:"delivery-action"},!this.state.accepted_order&&!this.state.picked_up&&!this.state.delivered&&s.a.createElement(s.a.Fragment,null,s.a.createElement(w,{text:localStorage.getItem("deliveryAcceptOrderButton"),color:localStorage.getItem("storeColor"),onSuccess:this.__acceptToDeliver,reset:this.state.reset})),this.state.accepted_order&&!this.state.picked_up&&!this.state.delivered&&s.a.createElement(w,{text:localStorage.getItem("deliveryPickedUpButton"),color:localStorage.getItem("storeColor"),onSuccess:this.__pickedUp,reset:this.state.reset}),this.state.accepted_order&&this.state.picked_up&&!this.state.delivered&&s.a.createElement(w,{text:localStorage.getItem("deliveryDeliveredButton"),color:localStorage.getItem("storeColor"),onSuccess:this.__delivered,reset:this.state.reset}),this.state.accepted_order&&this.state.picked_up&&this.state.delivered&&s.a.createElement("button",{type:"button",className:"btn btn-accept",style:{backgroundColor:localStorage.getItem("storeColor")}},s.a.createElement("i",{className:"si si-check mr-5"}),localStorage.getItem("deliveryOrderCompletedButton"))))))),s.a.createElement(N.a,null))}}]),t}(c.Component);T.contextTypes={router:function(){return null}};t.default=Object(S.b)(function(e){return{delivery_user:e.delivery_user.delivery_user,single_delivery_order:e.single_delivery_order.single_delivery_order,accepted_order:e.accepted_order.accepted_order}},{getSingleDeliveryOrder:function(e,t){return function(a){return g.a.post(u.I,{token:e,unique_order_id:t}).then(function(e){var t=e.data;return a({type:d.a,payload:t})}).catch(function(e){console.log(e)})}},acceptToDeliverOrder:function(e,t,a){return function(r){g.a.post(u.a,{token:e,delivery_guy_id:t,order_id:a}).then(function(e){var t=e.data;r({type:m.a,payload:t}),r({type:d.a,payload:t})}).catch(function(e){console.log(e)})}},pickupOrder:function(e,t){return function(a){g.a.post(u.W,{token:e,order_id:t}).then(function(e){var t=e.data;a({type:m.c,payload:t}),a({type:d.a,payload:t})}).catch(function(e){console.log(e)})}},deliverOrder:function(e,t,a){return function(r){g.a.post(u.k,{token:e,order_id:t,delivery_pin:a}).then(function(e){var t=e.data;r({type:m.b,payload:t}),r({type:d.a,payload:t})}).catch(function(e){console.log(e)})}}})(T)}}]);