<style>
    .table .custom-bg {
        background-color: #f1f1f1 !important;
        -webkit-print-color-adjust: exact;
    }

    .table {
        margin-bottom: 5px;
    }

    .table-slim,
    .table-slim td,
    .table-slim th {
        border: 0.5px solid #d5d5d5 !important;
        padding: 2px 4px !important;
    }

    .page-footer,
    .page-footer-space {
        max-height: 60px !important;
    }

    .page-footer {
        position: fixed !important;
        bottom: 0px !important;
        margin-top: 10px;
        width: 50%;
    }

    @page {
        margin: 0px 0px 0px 0px !important;
    }

    table {
        font-size: 13px;
    }

    table:not(.main_table) {
        font-size: 13px;
        page-break-inside: avoid;
        page-break-after: avoid;
    }


    .main_table {
        width: 95%;
        display: flex;
    }

    .invoice-container {
        width: 100%;
        margin: auto;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
    }

    .flex {
        display: flex;
        justify-content: space-between;
        padding: 0;
        width: 96%;
        margin: 5px 0px !important;
    }

    .footer-text {
        width: 95%;
        margin-right: 5px;
    }

    .invoice-info-1 {
        display: flex;
        flex-direction: column;

    }

    .invoice-info-2 {
        width: 100%;
        margin: 0 20px;
    }

    .main-info {
        margin: auto;
    }

    .t-start {
        text-align: start;
    }

    @media print {
        .invoice-container thead {
            display: table-header-group !important;
        }

        tfoot {
            display: table-footer-group !important;
        }

        .invoice-wrapper {
            min-height: 100vh;
            padding-bottom: 60px;
        }

        .invoice-container {
            min-height: calc(100vh - 60px);
            margin: 0 !important;
        }

        .main {
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .margin-auto {
            margin: auto;
        }

        .page-footer {
            width: 100% !important;
        }

        .page-footer-space {
            max-height: 60px;
        }
    }
</style>
<table class="invoice-container">
    <tbody class="invoice-content">
        <tr>
            <td class="invoice-content-cell">
                <div class="main">
                    <table>
                        <thead>
                            <tr>
                                <td>
                                    @if (!empty($receipt_details->invoice_heading))
                                        <p class="text-muted-imp" style="font-weight: bold; font-size: 18px !important">
                                            {!! $receipt_details->invoice_heading !!}</p>
                                    @endif
                                    <p>
                                        @if (!empty($receipt_details->invoice_no_prefix))
                                            {!! $receipt_details->invoice_no_prefix !!}
                                        @endif

                                        {{ $receipt_details->invoice_no }}
                                    </p>

                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    @if (!empty($receipt_details->header_text))
                                        <div class="row invoice-info">
                                            <div class="col-xs-12">
                                                {!! $receipt_details->header_text !!}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- business information here -->
                                    <div class="row invoice-info-1">

                                        <div class="col-md-6 invoice-col width-25" style="margin-right: 20px;">

                                            <p class="t-start">
                                                @if (!empty($receipt_details->parent_invoice_no_prefix))
                                                    <span class="pull-left">{!! $receipt_details->parent_invoice_no_prefix !!}</span>
                                                @endif

                                                {{ $receipt_details->parent_invoice_no }}
                                            </p>

                                            <!-- Total Due-->
                                            @if (!empty($receipt_details->total_due))
                                                <p class="bg-light-blue-active t-start font-23 padding-5">
                                                    <span class="pull-left bg-light-blue-active">
                                                        {!! $receipt_details->total_due_label !!}
                                                    </span>

                                                    {{ $receipt_details->total_due }}
                                                </p>
                                            @endif

                                            <!-- Total Paid-->
                                            @if (!empty($receipt_details->total_paid))
                                                <p class="t-start font-23 color-555">
                                                    <span class="pull-left">{!! $receipt_details->total_paid_label !!}</span>
                                                    {{ $receipt_details->total_paid }}
                                                </p>
                                            @endif

                                            <!-- Date-->
                                            @if (!empty($receipt_details->date_label))
                                                <p class="t-start font-14 color-555">
                                                    <span class="pull-left">
                                                        {{ $receipt_details->date_label }}
                                                    </span>

                                                    {{ $receipt_details->invoice_date }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-6 invoice-col width-50 color-555 main-info">

                                            <!-- Logo -->
                                            @if (!empty($receipt_details->logo))
                                                <img style="max-height: 150px; width: auto;"
                                                    src="{{ $receipt_details->logo }}" class="img">
                                                <br />
                                            @endif

                                            <!-- Shop & Location Name  -->
                                            @if (!empty($receipt_details->display_name))
                                                <p>
                                                    {{ $receipt_details->display_name }}<br />
                                                    {!! $receipt_details->address !!}

                                                    @if (!empty($receipt_details->contact))
                                                        <br />{{ $receipt_details->contact }}
                                                    @endif

                                                    @if (!empty($receipt_details->website))
                                                        <br />{{ $receipt_details->website }}
                                                    @endif

                                                    @if (!empty($receipt_details->tax_info1))
                                                        <br />{{ $receipt_details->tax_label1 }}
                                                        {{ $receipt_details->tax_info1 }}
                                                    @endif

                                                    @if (!empty($receipt_details->tax_info2))
                                                        <br />{{ $receipt_details->tax_label2 }}
                                                        {{ $receipt_details->tax_info2 }}
                                                    @endif

                                                    @if (!empty($receipt_details->location_custom_fields))
                                                        <br />{{ $receipt_details->location_custom_fields }}
                                                    @endif
                                                </p>
                                            @endif

                                            <!-- Table information-->
                                            @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                                                <p>
                                                    @if (!empty($receipt_details->table_label))
                                                        {!! $receipt_details->table_label !!}
                                                    @endif
                                                    {{ $receipt_details->table }}
                                                </p>
                                            @endif

                                            <!-- Waiter info -->
                                            @if (!empty($receipt_details->waiter_label) || !empty($receipt_details->waiter))
                                                <p>
                                                    @if (!empty($receipt_details->waiter_label))
                                                        {!! $receipt_details->waiter_label !!}
                                                    @endif
                                                    {{ $receipt_details->waiter }}
                                                </p>
                                            @endif
                                        </div>


                                    </div>

                                    <div class="invoice-info-2 color-555">
                                        <div class="flex">
                                            <div class="width-100 t-start word-wrap">
                                                <b>{{ $receipt_details->customer_label ?? '' }}</b><br />

                                                <!-- customer info -->
                                                @if (!empty($receipt_details->customer_name))
                                                    {!! $receipt_details->customer_name !!}<br>
                                                @endif
                                                @if (!empty($receipt_details->customer_info))
                                                    {!! $receipt_details->customer_info !!}
                                                @endif
                                                @if (!empty($receipt_details->client_id_label))
                                                    <br />
                                                    {{ $receipt_details->client_id_label }}
                                                    {{ $receipt_details->client_id }}
                                                @endif
                                                @if (!empty($receipt_details->customer_tax_label))
                                                    <br />
                                                    {{ $receipt_details->customer_tax_label }}
                                                    {{ $receipt_details->customer_tax_number }}
                                                @endif
                                                @if (!empty($receipt_details->customer_custom_fields))
                                                    <br />{!! $receipt_details->customer_custom_fields !!}
                                                @endif
                                            </div>
                                            <div class="width-100 t-start word-wrap">
                                                <div style="width: 100%;display: flex;justify-content: space-around;">
                                                    <p>
                                                        @if (!empty($receipt_details->sub_heading_line1))
                                                            {{ $receipt_details->sub_heading_line1 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line2))
                                                            <br>{{ $receipt_details->sub_heading_line2 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line3))
                                                            <br>{{ $receipt_details->sub_heading_line3 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line4))
                                                            <br>{{ $receipt_details->sub_heading_line4 }}
                                                        @endif
                                                        @if (!empty($receipt_details->sub_heading_line5))
                                                            <br>{{ $receipt_details->sub_heading_line5 }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="width-100 color-555">
                                                <div class="col-xs-6">
                                                    {{ $receipt_details->additional_notes }}
                                                </div>


                                                @if ($receipt_details->show_qr_code && !empty($receipt_details->qr_code_text))
                                                    <div class="col-xs-6">
                                                        <img class="center-block mt-6"
                                                            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
                                                @endif
                                                <br />
                                                @if ($receipt_details->show_barcode)
                                                    <img class="center-block"
                                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    <div class="main_table">
                                        <table class="table table-responsive table-slim table-bordered  t-start">
                                            <thead>
                                                <tr class="t-start">
                                                    <td style=" width: 5% !important" class="custom-bg">
                                                        No</td>

                                                    @php
                                                        $p_width = 35;
                                                    @endphp
                                                    @if ($receipt_details->show_cat_code != 1)
                                                        @php
                                                            $p_width = 45;
                                                        @endphp
                                                    @endif
                                                    <td class="custom-bg"
                                                        style=" width: {{ $p_width }}% !important">
                                                        {{ $receipt_details->table_product_label }}
                                                    </td>

                                                    @if ($receipt_details->show_cat_code == 1)
                                                        <td class="custom-bg" style=" width: 10% !important">
                                                            {{ $receipt_details->cat_code_label }}</td>
                                                    @endif

                                                    <td class="custom-bg" style=" !important"; width: 15% !important>
                                                        {{ $receipt_details->table_qty_label }}
                                                    </td>
                                                    <td class="custom-bg" style=" width: 15% !important">
                                                        {{ $receipt_details->table_unit_price_label }}
                                                    </td>
                                                    <td class="custom-bg" style=" width: 20% !important">
                                                        {{ $receipt_details->table_subtotal_label }}
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($receipt_details->lines as $line)
                                                    <tr>
                                                        <td class="text-center">
                                                            {{ $loop->iteration }}
                                                        </td>
                                                        <td>
                                                            {{ $line['name'] }} {{ $line['variation'] }}
                                                            @if (!empty($line['sub_sku']))
                                                                , {{ $line['sub_sku'] }}
                                                                @endif @if (!empty($line['brand']))
                                                                    , {{ $line['brand'] }}
                                                                @endif
                                                                @if (!empty($line['sell_line_note']))
                                                                    ({{ $line['sell_line_note'] }})
                                                                @endif
                                                        </td>

                                                        @if ($receipt_details->show_cat_code == 1)
                                                            <td>
                                                                @if (!empty($line['cat_code']))
                                                                    {{ $line['cat_code'] }}
                                                                @endif
                                                            </td>
                                                        @endif

                                                        <td class="t-start">
                                                            {{ $line['quantity'] }} {{ $line['units'] }}
                                                        </td>
                                                        <td class="t-start">
                                                            {{ $line['unit_price_exc_tax'] }}
                                                        </td>
                                                        <td class="t-start">
                                                            {{ $line['line_total'] }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @php
                                                    $lines = count($receipt_details->lines);
                                                @endphp

                                                @for ($i = $lines; $i < 7; $i++)
                                                    <tr>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                @endfor

                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row invoice-info color-555" style="page-break-inside: avoid !important">
                                        <div class="col-md-6 invoice-col width-50">
                                            <b class="pull-left">Authorized Signatory</b>
                                        </div>

                                        <div class="col-md-6 invoice-col width-50">
                                            <table class="table-no-side-cell-border table-no-top-cell-border width-100">
                                                <tbody>
                                                    <tr class="color-555">
                                                        <td style="width:50%">
                                                            {!! $receipt_details->subtotal_label !!}
                                                        </td>
                                                        <td class="t-start">
                                                            {{ $receipt_details->subtotal }}
                                                        </td>
                                                    </tr>

                                                    <!-- Tax -->
                                                    @if (!empty($receipt_details->taxes))
                                                        @foreach ($receipt_details->taxes as $k => $v)
                                                            <tr class="color-555">
                                                                <td>{{ $k }}</td>
                                                                <td class="t-start">{{ $v }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif

                                                    <!-- Discount -->
                                                    @if (!empty($receipt_details->discount))
                                                        <tr class="color-555">
                                                            <td>
                                                                {!! $receipt_details->discount_label !!}
                                                            </td>

                                                            <td class="t-start">
                                                                (-) {{ $receipt_details->discount }}
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if (!empty($receipt_details->group_tax_details))
                                                        @foreach ($receipt_details->group_tax_details as $key => $value)
                                                            <tr class="color-555">
                                                                <td>
                                                                    {!! $key !!}
                                                                </td>
                                                                <td class="t-start">
                                                                    (+)
                                                                    {{ $value }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        @if (!empty($receipt_details->tax))
                                                            <tr class="color-555">
                                                                <td>
                                                                    {!! $receipt_details->tax_label !!}
                                                                </td>
                                                                <td class="t-start">
                                                                    (+) {{ $receipt_details->tax }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif


                                                    @if ($receipt_details->adjustment_amount)
                                                        <tr class="color-555">
                                                            <td>
                                                                {!! $receipt_details->adjustment_title !!}:
                                                            </td>

                                                            <td class="t-start">
                                                                {{ $receipt_details->adjustment_amount }}
                                                            </td>
                                                        </tr>
                                                    @endif


                                                    <!-- Total -->
                                                    <tr>
                                                        <th style="background-color: #357ca5 !important; !important"
                                                            class="font-23 padding-10">
                                                            {!! $receipt_details->total_label !!}
                                                        </th>
                                                        <td class="t-start font-23 padding-10"
                                                            style="background-color: #357ca5 !important; !important">
                                                            {{ $receipt_details->total }}
                                                        </td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @if (!empty($receipt_details->footer_text))
                                        <div class="row color-555 footer-text">
                                            <div class="col-xs-12">
                                                {!! $receipt_details->footer_text !!}
                                            </div>
                                        </div>
                                    @endif

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>
                <!--place holder for the fixed-position footer-->
                <div class="page-footer-space"></div>
            </td>
        </tr>
    </tfoot>
</table>
