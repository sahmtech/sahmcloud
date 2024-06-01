@extends('layouts.app')


@php
    if (!empty($status) && $status == 'quotation') {
        $title = __('lang_v1.add_quotation');
    } elseif (!empty($status) && $status == 'draft') {
        $title = __('lang_v1.add_draft');
    } else {
        $title = __('sale.add_sale');
    }

    if ($sale_type == 'sales_order') {
        $title = __('lang_v1.sales_order');
    }
@endphp
@section('title', $title)

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>{{ $title }}</h1>
    </section>
    <!-- Main content -->
    <section class="content no-print">
        @if (count($business_locations) > 0)
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select(
                                'select_location_id',
                                $business_locations,
                                $default_location->id ?? null,
                                ['class' => 'form-control input-sm', 'id' => 'select_location_id', 'required', 'autofocus'],
                                $bl_attributes,
                            ) !!}
                            <span class="input-group-addon">
                                @show_tooltip(__('tooltip.sale_location'))
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellController::class, 'storeZatca']),
            'method' => 'post',
            'id' => 'zatca_form',
        ]) !!}
        {!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null, [
            'id' => 'location_id',
            'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                ? $default_location->receipt_printer_type
                : 'browser',
            'data-default_payment_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '',
        ]) !!}
        <div class="row">
            <div class="col-md-12 col-sm-12">

                @component('components.widget', ['class' => 'box-solid'])
                    <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('contact.customer') . '  ') !!} <span style="color: red; font-size:15px">*</span>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="hidden" id="default_customer_id" value="{{ $walk_in_customer['id'] }}">
                                <input type="hidden" id="default_customer_name" value="{{ $walk_in_customer['name'] }}">
                                <input type="hidden" id="default_customer_balance"
                                    value="{{ $walk_in_customer['balance'] ?? '' }}">
                                <input type="hidden" id="default_customer_address"
                                    value="{{ $walk_in_customer['shipping_address'] ?? '' }}">
                                @if (
                                    !empty($walk_in_customer['price_calculation_type']) &&
                                        $walk_in_customer['price_calculation_type'] == 'selling_price_group')
                                    <input type="hidden" id="default_selling_price_group"
                                        value="{{ $walk_in_customer['selling_price_group_id'] ?? '' }}">
                                @endif
                                {!! Form::select('contact_id', [], null, [
                                    'class' => 'form-control mousetrap',
                                    'id' => 'customer_id',
                                    'placeholder' => 'Enter Customer name / phone',
                                    'required',
                                ]) !!}
                                {{-- <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_customer"
                                        data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                </span> --}}
                            </div>
                            <small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong>
                                <span></span></small>
                        </div>
                        {{-- <small>
                        <strong style="font-size: small;
                        color: #221162;">
                            @lang('lang_v1.billing_address'):
                        </strong>
                        <div id="billing_address_div">
                            {!! $walk_in_customer['contact_address'] ?? '' !!}
                        </div>
                        <br>
                        <strong style="font-size: small;
                        color: #221162;">
                            @lang('lang_v1.shipping_address'):
                        </strong>
                        <div id="shipping_address_div">
                            {{ $walk_in_customer['supplier_business_name'] ?? '' }},<br>
                            {{ $walk_in_customer['name'] ?? '' }},<br>
                            {{ $walk_in_customer['shipping_address'] ?? '' }}
                        </div>
                    </small> --}}
                    </div>
                    <div class="row" style="padding: 0px 15px;">
                        @if (!empty($status))
                            <input type="hidden" name="status" id="status" value="{{ $status }}">

                            @if (in_array($status, ['draft', 'quotation']))
                                <input type="hidden" id="disable_qty_alert">
                            @endif
                        @else
                            <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                                <div class="form-group">
                                    {!! Form::label('status', __('sale.status') . ' ') !!} <span style="color: red; font-size:15px">*</span>
                                    {!! Form::select('status', $statuses, null, [
                                        'class' => 'form-control ',
                                        'placeholder' => __('messages.please_select'),
                                        'required',
                                        'style' => 'border-radius:5px;padding: 0px 5px;',
                                    ]) !!}
                                </div>
                            </div>
                        @endif


                        <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                            <div class="form-group">
                                {!! Form::label('transaction_date', __('sale.sale_date') . ' ') !!} <span style="color: red; font-size:15px">*</span>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="padding: 0px 15px;">
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label(
                                    'invoice_no',
                                    $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no') . ':',
                                ) !!}
                                {!! Form::text('invoice_no', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no'),
                                ]) !!}
                                <p class="help-block">@lang('lang_v1.keep_blank_to_autogenerate')</p>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('invoice_type', __('zatca.invoiceType') . ':') !!}
                                {!! Form::select('invoice_type', $invoiceTypes, null, [
                                    'class' => 'form-control',
                                    'id' => 'zatca.invoiceType',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('payment_type', __('zatca.zatca_Invoice.payment_type') . ':') !!}
                                {!! Form::select('payment_type', $paymentTypes, null, [
                                    'class' => 'form-control',
                                    'id' => 'zatca.invoicePayment',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row" style="padding: 0px 15px;">
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('invoice_note', __('zatca.invoice_notes.invoice')) !!}
                                {!! Form::textarea('invoice_note', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('zatca.invoice_notes.invoice'),
                                    'rows' => '3',
                                    'cols' => '30',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('payment_note', __('zatca.invoice_notes.payment')) !!}
                                {!! Form::textarea('payment_note', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('zatca.invoice_notes.payment'),
                                    'rows' => '3',
                                    'cols' => '30',
                                ]) !!}
                            </div>
                        </div>

                    </div>
                @endcomponent
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal"
                                        data-target="#configure_search_modal"
                                        title="{{ __('lang_v1.configure_product_search') }}"><i
                                            class="fas fa-search-plus"></i></button>
                                </div>
                                {!! Form::text('search_product', null, [
                                    'class' => 'form-control mousetrap',
                                    'id' => 'search_product',
                                    'placeholder' => __('lang_v1.search_product_placeholder'),
                                    'disabled' => is_null($default_location) ? true : false,
                                    'autofocus' => is_null($default_location) ? false : true,
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product"
                                        data-href="{{ action([\App\Http\Controllers\ProductController::class, 'quickAdd']) }}"
                                        data-container=".quick_add_product_modal"><i
                                            class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row col-sm-12 pos_product_div" style="min-height: 0">

                        <input type="hidden" name="sell_price_tax" id="sell_price_tax"
                            value="{{ $business_details->sell_price_tax }}">

                        <input type="hidden" name="business_enable_inline_tax" id="business_enable_inline_tax"
                            value="{{ session()->get('business.enable_inline_tax') }}">

                        <!-- Keeps count of product rows -->
                        <input type="hidden" id="product_row_count" value="0">
                        @php
                            $hide_tax = '';
                            if (session()->get('business.enable_inline_tax') == 0) {
                                $hide_tax = 'hide';
                            }
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                                <thead
                                    style="    background: #afddd32e;
                            font-size: medium;
                            color: #1b0544;">
                                    <tr>
                                        <th class="text-center" style="border: none;">
                                            @lang('sale.product')
                                        </th>
                                        <th class="text-center" style="border: none;">
                                            @lang('sale.qty')
                                        </th>
                                        @if (!empty($pos_settings['inline_service_staff']))
                                            <th class="text-center" style="border: none;">
                                                @lang('restaurant.service_staff')
                                            </th>
                                        @endif
                                        <th class="@if (!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif" style="border: none;">
                                            @lang('sale.unit_price')
                                        </th>
                                        <th class="@if (!auth()->user()->can('edit_product_discount_from_sale_screen')) hide @endif" style="border: none;">
                                            @lang('receipt.discount')
                                        </th>
                                        <th class="text-center {{ $hide_tax }}" style="border: none;">
                                            @lang('sale.tax')
                                        </th>
                                        <th class="text-center {{ $hide_tax }}" style="border: none;">
                                            @lang('sale.price_inc_tax')
                                        </th>
                                        @if (!empty($common_settings['enable_product_warranty']))
                                            <th style="border: none;">@lang('lang_v1.warranty')</th>
                                        @endif
                                        <th class="text-center" style="border: none;">
                                            @lang('sale.subtotal')
                                        </th>
                                        <th class="text-center"
                                            style="    font-size: x-small;border: none;
                                color: #c90000;">
                                            <i class="fa fa-trash fa-2x cursor-pointer" aria-hidden="true"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <hr style="width:100%;text-align:left;margin:0; border-top: 1px solid #ddd;margin:15px;">

                    </div>
                @endcomponent
            </div>
        </div>
        {!! Form::close() !!}
        {{-- <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            @include('contact.create', ['quick_add' => true])
        </div> --}}
    </section>
    @include('sale_pos.partials.configure_search_modal')
@stop

@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            $('#status').change(function() {
                if ($(this).val() == 'final') {
                    $('#payment_rows_div').removeClass('hide');
                } else {
                    $('#payment_rows_div').addClass('hide');
                }
            });
            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
                showUpload: false,
                showPreview: false,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
            });

            $(document).on('change', '#prefer_payment_method', function(e) {
                var default_accounts = $('select#select_location_id').length ?
                    $('select#select_location_id')
                    .find(':selected')
                    .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
                var payment_type = $(this).val();
                if (payment_type) {
                    var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                        default_accounts[payment_type]['account'] : '';
                    var account_dropdown = $('select#prefer_payment_account');
                    if (account_dropdown.length && default_accounts) {
                        account_dropdown.val(default_account);
                        account_dropdown.change();
                    }
                }
            });

            function setPreferredPaymentMethodDropdown() {
                var payment_settings = $('#location_id').data('default_payment_accounts');
                payment_settings = payment_settings ? payment_settings : [];
                enabled_payment_types = [];
                for (var key in payment_settings) {
                    if (payment_settings[key] && payment_settings[key]['is_enabled']) {
                        enabled_payment_types.push(key);
                    }
                }
                if (enabled_payment_types.length) {
                    $("#prefer_payment_method > option").each(function() {
                        if (enabled_payment_types.indexOf($(this).val()) != -1) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    });
                }
            }

            setPreferredPaymentMethodDropdown();

            $('#is_export').on('change', function() {
                if ($(this).is(':checked')) {
                    $('div.export_div').show();
                } else {
                    $('div.export_div').hide();
                }
            });

            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }

        });
    </script>
@endsection
