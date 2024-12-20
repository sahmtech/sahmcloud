<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('zatca_invoice.refund_invoice') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }

        .invoice-box {
            max-width: 60vw;
            margin: auto;
            padding: 20px;
        }

        .header,
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            max-width: 150px;
        }

        .qr-code img {
            max-width: 100px;
        }

        .title {
            text-align: center;
            margin: 20px 0;
        }

        .title h1 {
            font-size: 35px;
            color: #333;
            margin: 5px 0;
            font-weight: bold;
        }

        .title h2 {
            font-size: 30px;
            color: #666;
            margin: 5px 0;
            font-weight: bold;
        }

        .section-title {
            background: #e2e2e2;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .colored_background {
            background: #f9f9f9;
        }

        .invoice-details,
        .seller-buyer,
        .invoice-items,
        .invoice-summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-details {
            width: 50%;
        }

        .invoice-details th,
        .invoice-details td,
        .seller-buyer th,
        .seller-buyer td,
        .invoice-items th,
        .invoice-items td,
        .invoice-summary th,
        .invoice-summary td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .invoice-details th,
        .seller-buyer th,
        .invoice-items th,
        .invoice-summary th {
            background: #f2f2f2;
            text-align: center;
        }

        .invoice-details td:first-child {
            text-align: left;
        }

        .invoice-details td:last-child {
            text-align: right;
        }

        .right-align {
            text-align: right;
        }

        .center-align {
            text-align: center;
        }

        .left-align {
            text-align: left;
        }

        .totals {
            background: #f9f9f9;
        }

        .totals td {
            border: none;
        }

        .separator {
            width: 20px;
        }

        .rtl-text {
            direction: rtl;
            text-align: right;
        }

        @media print {
            body {
                font-size: 12pt;
                color: #000;
            }

            .title h1 {
                font-size: 30pt;

            }

            .title h2 {
                font-size: 28pt;

            }

            .invoice-box {
                max-width: 100vw;
                margin: auto;
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }

        .seller-buyer th,
        .seller-buyer td {
            width: 27%;
        }

        .seller-buyer td:nth-child(2) {
            width: 46%;
            text-align: center;
            /* Center the value column text */
        }

        .invoice-summary th,
        .invoice-summary td {
            width: 30%;
        }

        .invoice-summary td:nth-child(2) {
            width: 40%;
            text-align: center;
        }

        .invoice-summary .left-align {
            text-align: left;
        }

        .invoice-summary .center-align {
            text-align: center;
        }

        .invoice-summary .rtl-text {
            direction: rtl;
            text-align: right;
        }

        .no_top_border {
            border-top: none !important;
        }

        .no_bottom_border {
            border-bottom: none !important;
        }

        .invoice-details td:first-child {
            width: 30%;
        }

        .invoice-details td:nth-child(2) {
            width: 40%;
            text-align: center;
        }

        .invoice-details td:last-child {
            width: 30%;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">

            <div class="title">
                <h1>{{ $debit_title ? $debit_title : __('zatca_invoice.refund_invoice') }}</h1>
                <h2>{{ __('zatca_invoice.refund_invoice', [], 'en') }}</h2>
            </div>
            <div class="logo">
                <img src="{{ asset('uploads/business_logos/' . $logo) }}" alt="Logo">
            </div>
        </div>
        <div class="section-title">
            <span>{{ __('zatca_invoice.invoice_information', [], 'en') }}</span>
            <span class="rtl-text">{{ __('zatca_invoice.invoice_information', [], 'ar') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <table class="invoice-details">
                <tr>
                    <td class="colored_background">{{ __('zatca_invoice.refund_invoice_number', [], 'en') }}:</td>
                    <td class="center-align">{{ $invoice->invoice_number ?? '' }}</td>
                    <td class="rtl-text colored_background">
                        {{ $debit_number ? $debit_number : __('zatca_invoice.refund_invoice_number', [], 'ar') }}
                    </td>
                </tr>
                <tr>
                    <td class="colored_background">{{ __('zatca_invoice.invoice_number', [], 'en') }}:</td>
                    <td class="center-align">{{ $parent_invoice_number ?? '' }}</td>
                    <td class="rtl-text colored_background">{{ __('zatca_invoice.invoice_number', [], 'ar') }}</td>
                </tr>

                <tr>
                    <td class="colored_background">{{ __('zatca_invoice.invoice_issue_date', [], 'en') }}:</td>
                    <td class="center-align">{{ $invoice->invoice_date ?? '' }}</td>
                    <td class="rtl-text colored_background">{{ __('zatca_invoice.invoice_issue_date', [], 'ar') }}</td>
                </tr>
                <tr>
                    <td class="colored_background">{{ __('zatca_invoice.from_date', [], 'en') }}:</td>
                    <td class="center-align">{{ $fromDate ?? '' }}</td>
                    <td class="rtl-text colored_background">{{ __('zatca_invoice.from_date', [], 'ar') }}</td>
                </tr>
                <tr>
                    <td class="colored_background">{{ __('zatca_invoice.to_date', [], 'en') }}:</td>
                    <td class="center-align">{{ $toDate ?? '' }}</td>
                    <td class="rtl-text colored_background">{{ __('zatca_invoice.to_date', [], 'ar') }}</td>
                </tr>
            </table>
            <div class="qr-code">
                {!! $Qr !!}
            </div>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 48%;">
                <div class="section-title">
                    <span>{{ __('zatca_invoice.seller_information', [], 'en') }}</span>
                    <span class="rtl-text">{{ __('zatca_invoice.seller_information', [], 'ar') }}</span>
                </div>
                <table class="seller-buyer">
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.name', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->registration_name ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.name', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.vat_number', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->tax_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.vat_number', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.building_number', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->building_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.building_number', [], 'ar') }}:
                        </td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.street_name', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->street_name ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.street_name', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.district', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->city_sub_division ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.district', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.city', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->city ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.city', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.country', [], 'en') }}:</td>
                        <td class="center-align">Saudi Arabia</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.country', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.postal_code', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->postal_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.postal_code', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">
                            {{ __('zatca_invoice.commercial_registration_number', [], 'en') }}:</td>
                        <td class="center-align">{{ $seller->registration_number ?? '' }}</td>
                        <td class="rtl-text colored_background">
                            {{ __('zatca_invoice.commercial_registration_number', [], 'ar') }}:</td>
                    </tr>
                </table>
            </div>
            <div style="width: 48%;">
                <div class="section-title">
                    <span>{{ __('zatca_invoice.buyer_information', [], 'en') }}</span>
                    <span class="rtl-text">{{ __('zatca_invoice.buyer_information', [], 'ar') }}</span>
                </div>
                <table class="seller-buyer">
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.name', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->registration_name ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.name', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.vat_number', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->tax_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.vat_number', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.building_number', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->building_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.building_number', [], 'ar') }}:
                        </td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.street_name', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->street_name ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.street_name', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.district', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->city_subdivision_name ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.district', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.city', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->city ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.city', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.country', [], 'en') }}:</td>
                        <td class="center-align">Saudi Arabia</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.country', [], 'ar') }}:</td>
                    </tr>
                    <tr>
                        <td class="colored_background">{{ __('zatca_invoice.postal_code', [], 'en') }}:</td>
                        <td class="center-align">{{ $client->postal_number ?? '' }}</td>
                        <td class="rtl-text colored_background">{{ __('zatca_invoice.postal_code', [], 'ar') }}:</td>
                    </tr>

                </table>
            </div>
        </div>

        <div class="section-title">
            <span>{{ __('zatca_invoice.invoice_items', [], 'en') }}</span>
            <span class="rtl-text">{{ __('zatca_invoice.invoice_items', [], 'ar') }}</span>
        </div>
        <table class="invoice-items">
            <thead>
                <tr>
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.serial_no', [], 'en') }}</th>
                    <th colspan="3" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.nature_of_goods', [], 'en') }}
                    </th>
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.item_net_amount', [], 'en') }}
                    </th>
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.discount', [], 'en') }}</th>
                    {{-- <th colspan="1" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.tax_category', [], 'en') }}
                    </th> --}}
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.tax_rate', [], 'en') }}</th>
                    {{-- <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.tax_amount', [], 'en') }}
                    </th> --}}
                    {{-- <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.total_amount', [], 'en') }}
                    </th> --}}
                    <th colspan="1" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.quantity', [], 'en') }}</th>
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.total_row_tax', [], 'en') }}
                    </th>
                    <th colspan="2" class="center-align no_bottom_border">
                        {{ __('zatca_invoice.total_row_amount', [], 'en') }}
                    </th>
                </tr>
                <tr>
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.serial_no', [], 'ar') }}
                    </th>
                    <th colspan="3" class="center-align no_top_border">
                        {{ __('zatca_invoice.nature_of_goods', [], 'ar') }}</th>
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.item_net_amount', [], 'ar') }}</th>
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.discount', [], 'ar') }}
                    </th>
                    {{-- <th colspan="1" class="center-align no_top_border">
                        {{ __('zatca_invoice.tax_category', [], 'ar') }}
                    </th> --}}
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.tax_rate', [], 'ar') }}
                    </th>
                    {{-- <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.tax_amount', [], 'ar') }}
                    </th> --}}
                    {{-- <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.total_amount', [], 'ar') }}
                    </th> --}}
                    <th colspan="1" class="center-align no_top_border">
                        {{ __('zatca_invoice.quantity', [], 'ar') }}
                    </th>
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.total_row_tax', [], 'ar') }}</th>
                    <th colspan="2" class="center-align no_top_border">
                        {{ __('zatca_invoice.total_row_amount', [], 'ar') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoice_items as $index => $item)
                    @if ($item->quantity > 0)
                        <tr>
                            <td colspan="2" class="center-align">{{ $index + 1 }}</td>
                            <td colspan="3" class="center-align">
                                {{ $item->product_name }}<br>{{ $notest[$item->id] ?? '' }}
                            </td>
                            <td colspan="2" class="center-align">
                                {{ number_format($item->price / $item->quantity, 2) }}</td>
                            <td colspan="2" class="center-align">
                                {{ number_format($item->discount / $item->quantity, 2) }}</td>
                            {{-- <td colspan="1" class="center-align">{{ $item->tax_category_code }}</td> --}}
                            <td colspan="2" class="center-align">{{ $item->tax_percent }}%</td>
                            {{-- <td colspan="2" class="center-align">{{ number_format($item->tax, 2) }}</td> --}}
                            {{-- <td colspan="2" class="center-align">{{ number_format($item->total, 2) }}</td> --}}
                            <td colspan="1" class="center-align">{{ $item->quantity }}</td>
                            <td colspan="2" class="center-align">{{ number_format($item->tax, 2) }}
                            </td>
                            <td colspan="2" class="center-align">
                                {{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="section-title">
            <span>{{ __('zatca_invoice.summary', [], 'en') }}</span>
            <span class="rtl-text">{{ __('zatca_invoice.summary', [], 'ar') }}</span>
        </div>
        <table class="invoice-summary">
            <tr>
                <th class="left-align">{{ __('zatca_invoice.total_excluding_vat', [], 'en') }}</th>
                <td class="center-align">{{ number_format($invoice->price - $invoice->discount, 2) }}</td>
                <th class="rtl-text">{{ __('zatca_invoice.total_excluding_vat', [], 'ar') }}</th>
            </tr>
            <tr>
                <th class="left-align">{{ __('zatca_invoice.total_discount', [], 'en') }}</th>
                <td class="center-align">{{ number_format($invoice->discount, 2) }}</td>
                <th class="rtl-text">{{ __('zatca_invoice.total_discount', [], 'ar') }}</th>
            </tr>
            <tr>
                <th class="left-align">{{ __('zatca_invoice.total_vat', [], 'en') }}</th>
                <td class="center-align">{{ number_format($invoice->tax, 2) }}</td>
                <th class="rtl-text">{{ __('zatca_invoice.total_vat', [], 'ar') }}</th>
            </tr>
            <tr>
                <th class="left-align">{{ __('zatca_invoice.total', [], 'en') }}</th>
                <td class="center-align">{{ number_format($invoice->total, 2) }}</td>
                <th class="rtl-text">{{ $debit_total ? $debit_total : __('zatca_invoice.total', [], 'ar') }}</th>
            </tr>
        </table>
    </div>
    {!! $footer_text !!}
    </div>
</body>

</html>
