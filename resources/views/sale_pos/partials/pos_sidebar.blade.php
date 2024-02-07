<div class="row" id="featured_products_box" style="display: none;">
    @if (!empty($featured_products))
        @include('sale_pos.partials.featured_products')
    @endif
</div>
<div class="row">

    @if (!empty($categories))
        <div class="col-md-12" id="product_category_div" style="padding: 0px 26px;">
            <select class="select-btn" id="product_category" multiple
               >

                <option class="select-options-btn" value="all" selected>
                    @lang('lang_v1.all_category')</option>

                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" class="select-options-btn">
                        {{ $category['name'] }}</option>
                @endforeach

                @foreach ($categories as $category)
                    @if (!empty($category['sub_categories']))
                        {{-- <optgroup label="{{ $category['name'] }}"> --}}
                        @foreach ($category['sub_categories'] as $sc)
                            {{-- <i class="fa fa-minus"></i> --}}
                            <option class="select-options-btn" value="{{ $sc['id'] }}">
                                {{ $sc['name'] }}</option>
                        @endforeach
                        {{-- </optgroup> --}}
                    @endif
                @endforeach
            </select>
        </div>
    @endif

    @if (!empty($brands))
        <div class="col-sm-12" id="product_brand_div" style="padding: 4px 26px;">
            {!! Form::select('size', $brands, null, [
                'id' => 'product_brand',
                'class' => 'select2',
                'name' => null,
                'style' => 'width:100% !important',
            ]) !!}
        </div>
    @endif

    <!-- used in repair : filter for service/product -->
    <div class="col-md-6 hide" id="product_service_div">
        {!! Form::select(
            'is_enabled_stock',
            ['' => __('messages.all'), 'product' => __('sale.product'), 'service' => __('lang_v1.service')],
            null,
            ['id' => 'is_enabled_stock', 'class' => 'select2', 'name' => null, 'style' => 'width:100% !important'],
        ) !!}
    </div>

    <div class="col-sm-4 @if (empty($featured_products)) hide @endif" id="feature_product_div">
        <button type="button" class="btn btn-primary btn-flat" id="show_featured_products">@lang('lang_v1.featured_products')</button>
    </div>
</div>
<br>
<div class="row">
    <input type="hidden" id="suggestion_page" value="1">
    <div class="col-md-12">
        <div class="eq-height-row" id="product_list_body" style="padding: 12px 0px;"></div>
    </div>
    <div class="col-md-12 text-center" id="suggestion_page_loader" style="display: none;">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
    </div>
</div>
