@extends('layouts.guest')
@section('title', $business->name)

@section('content')


    <style>
        .navbar-default .navbar-nav>li>a:hover {
            background: #6495ed36;
            border-bottom: 2px solid #7f39bf;
            transition: 2;
            border-radius: 5px;

        }

        .navbar-default .navbar-nav>li>a:focus {
            color: #444;
            background: #6495ed36;
            border-bottom: 2px solid #7f39bf;
            transition: 2s ease-in-out;
            border-radius: 5px;

        }

        @media (min-width: 768px) {


            .navbar-default {
                background-color: #f8f8f800;
                border-color: #e7e7e700;

            }

            /* width: inherit; */
            .navbar-fixed-top {
                background-color: #fffcf5f2;
                border-color: #e7e7e7;
                padding-bottom: 13px;
            }
        }


        @media (max-width: 576px) {

            /* CSS styles for Extra Small screens */
            #product_container {
                /* width: inherit; */
            }

            #main_card_div {
                padding: 5px;
            }

            #business_logos_div {
                padding: 0px 1px;
                padding-top: 38px;
                padding-left: 32px;
            }

            #business_name {
                margin-top: 40px !important;
                font-size: larger !important;
            }
        }


        

        .page-header {
            text-align: center;
            font-weight: bold;
        }

        #product_description_section {
            color: #808180dd;
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>


    <div class="row">
        <div class="col-sm-2 col-xs-3" id="business_logos_div" style="padding-left: 41px;
        ">
            @if (!empty($business->logo))
                <img style="width: 100%;" src="{{ asset('uploads/business_logos/' . $business->logo) }}" alt="Logo"
                    width="30">
            @else
                <i class="fas fa-boxes"></i>
            @endif
        </div>
        <div class="col-sm-10 col-xs-9">
            <h3 style="margin-top: 56px" id="business_name">{{ $business->name }} - {{ $business_location->name }}</h3>

            <p style="color: #958e8e;    font-size: smaller;">{!! $business_location->location_address !!}</p>
        </div>

    </div>

    <hr style="border-top: 1px solid #5555554d;margin-top:0px" />

    <section class="no-print">

        <!-- Static navbar -->
        <nav class="navbar navbar-default" style="    
             
          ">
            <div class="container-fluid">

            </div>
            <div id="navbar" class="navbar " id="product_category_div" style="padding: 0px 26px;">
                <ul class="nav navbar-nav"
                    style="    display: -webkit-inline-box;
                        padding: 14px;
                    overflow-x: scroll;width: -webkit-fill-available;">
                    @foreach ($categories as $key => $value)
                        <li
                            style="border: 1px solid #bbbbbb17;
                                border-radius: 5px;
                                background: #f3f1f1;
                                margin: 3px;
                                ">
                            <a href="#category{{ $key }}" class="menu">{{ $value }}</a>
                        </li>
                    @endforeach
                    <li
                        style="border: 1px solid #bbbbbb17;
                            border-radius: 5px;
                            background: #f3f1f1;
                            margin: 3px;
                            ">
                        <a href="#category0" class="menu">Uncategorized</a>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
            </div><!--/.container-fluid -->
        </nav>


    </section>



    <!-- Main content -->
    <section class="content pt-0">
        <div class="container">
            @foreach ($products as $product_category)
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-header" id="category{{ $product_category->first()->category->id ?? 0 }}">
                            {{ $product_category->first()->category->name ?? 'Uncategorized' }}</h2>
                    </div>
                </div>
                <div class="row eq-height-row">
                    @foreach ($product_category as $product)
                        <div class="col-lg-3 col-md-12 eq-height-col col-xs-6 col-sm-4" id="main_card_div">

                            <div class="container" id="product_container">
                                <a href="#" class="show-product-details"
                                    data-href="{{ action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'show'], [$business->id, $product->id]) }}?location_id={{ $business_location->id }}">

                                    <div class="row"
                                        style="    border: 1px solid #b9b9b9ad; min-height: 19rem; border-radius: 13px; margin-bottom: 18px;  max-height: 28rem;height:28rem; ">
                                        <div class="col-sm-12 col-xs-12 col-md-12"
                                            style="background-image: url({{ $product->image_url }});    background-repeat: round;background-size: cover;height: 15rem;border-radius: 13px 13px 0px 0px;">

                                        </div>


                                        <div class="col-sm-12 col-xs-12 col-md-12" id="custom_product_card_info">

                                            @php
                                                $discount = $discounts->firstWhere('brand_id', $product->brand_id);
                                                if (empty($discount)) {
                                                    $discount = $discounts->firstWhere('category_id', $product->category_id);
                                                }
                                            @endphp


                                            @php
                                                $max_price = $product->variations->max('sell_price_inc_tax');
                                                $min_price = $product->variations->min('sell_price_inc_tax');
                                            @endphp
                                            <div class="row" style="text-align: right;padding-right: 18px;">
                                                <h5 style="color: #090329;padding-top:15px;font-weight: bold;">
                                                    ({{ $product->sku }})
                                                    -
                                                    {{ $product->name }}
                                                </h5>

                                                <p id="product_description_section"
                                                    style=" display: -webkit-box;
                                                            -webkit-line-clamp: 2;
                                                            -webkit-box-orient: vertical;">
                                                    @if ($product->product_description)
                                                        {{ $product->product_description }}
                                                    @else
                                                       @lang('lang_v1.no_description')
                                                        <br>
                                                    @endif
                                                </p>
                                                @if (!empty($discount))
                                                    <h4 class="display_currency" style="color: #090329;">

                                                        <span class="label label-warning discount-badge">-
                                                            {{ $discount->discount_amount }}%</span>
                                                    </h4>
                                                @endif

                                                <h5 style="color: #090329;font-weight: bold;">

                                                    <span class="display_currency"
                                                        data-currency_symbol="true">{{ $max_price }}</span>
                                                    @if ($max_price != $min_price)
                                                        - <span class="display_currency"
                                                            data-currency_symbol="true">{{ $min_price }}</span>
                                                    @endif

                                                </h5>

                                            </div>

                                        </div>

                                    </div>
                                </a>
                            </div>

                        </div>

                        @if ($loop->iteration % 4 == 0)
                            <div class="clearfix"></div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class='scrolltop no-print'>
            <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
        </div>
    </section>
    <!-- /.content -->
    <!-- Add currency related field-->
    <input type="hidden" id="__code" value="{{ $business->currency->code }}">
    <input type="hidden" id="__symbol" value="{{ $business->currency->symbol }}">
    <input type="hidden" id="__thousand" value="{{ $business->currency->thousand_separator }}">
    <input type="hidden" id="__decimal" value="{{ $business->currency->decimal_separator }}">
    <input type="hidden" id="__symbol_placement" value="{{ $business->currency->currency_symbol_placement }}">
    <input type="hidden" id="__precision" value="{{ $business->currency_precision }}">
    <input type="hidden" id="__quantity_precision" value="{{ $business->quantity_precision }}">
    <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@stop
@section('javascript')
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {

                //Set global currency to be used in the application
                __currency_symbol = $('input#__symbol').val();
                __currency_thousand_separator = $('input#__thousand').val();
                __currency_decimal_separator = $('input#__decimal').val();
                __currency_symbol_placement = $('input#__symbol_placement').val();
                if ($('input#__precision').length > 0) {
                    __currency_precision = $('input#__precision').val();
                } else {
                    __currency_precision = 2;
                }

                if ($('input#__quantity_precision').length > 0) {
                    __quantity_precision = $('input#__quantity_precision').val();
                } else {
                    __quantity_precision = 2;
                }

                //Set page level currency to be used for some pages. (Purchase page)
                if ($('input#p_symbol').length > 0) {
                    __p_currency_symbol = $('input#p_symbol').val();
                    __p_currency_thousand_separator = $('input#p_thousand').val();
                    __p_currency_decimal_separator = $('input#p_decimal').val();
                }

                __currency_convert_recursively($('.content'));
            });

            $(document).on('click', '.show-product-details', function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).data('href'),
                    dataType: 'html',
                    success: function(result) {
                        $('.product_modal')
                            .html(result)
                            .modal('show');
                        __currency_convert_recursively($('.product_modal'));
                    },
                });
            });

            $(document).on('click', '.menu', function(e) {
                e.preventDefault();
                $('.navbar-toggle').addClass('collapsed');
                $('.navbar-collapse').removeClass('in');

                var cat_id = $(this).attr('href');
                if ($(cat_id).length) {
                    $('html, body').animate({
                        scrollTop: $(cat_id).offset().top
                    }, 1000);
                }
            });

        })(jQuery);

        $(window).scroll(function() {
            var height = $(window).scrollTop();

            if (height > 180) {
                $('nav').addClass('navbar-fixed-top');
                $('.scrolltop:hidden').stop(true, true).fadeIn();
            } else {
                $('nav').removeClass('navbar-fixed-top');
                $('.scrolltop').stop(true, true).fadeOut();
            }
        });

        $(document).on('click', '.scroll', function(e) {
            $("html,body").animate({
                scrollTop: $("#top").offset().top
            }, "1000");
            return false;
        });
    </script>
@endsection
