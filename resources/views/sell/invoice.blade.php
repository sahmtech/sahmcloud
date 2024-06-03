<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;

            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }

        .invoice-box {
            max-width: 90vw;
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
            max-width: 250px;
        }

        .qr-code img {
            max-width: 100px;
        }

        .title {
            text-align: center;
            margin: 20px 0;
        }

        .title h1 {
            font-size: 20px;
            color: #333;
            margin: 5px 0;
        }

        .title h2 {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }

        .section-title {
            background: #e2e2e2;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
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
            text-align: left;
        }

        .right-align {
            text-align: right;
        }

        .center-align {
            text-align: center;
        }

        .totals {
            background: #f9f9f9;
        }

        .totals td {
            border: none;
        }

        @media print {
            body {
                font-size: 12pt;
                color: #000;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('uploads/business_logos/' . $logo) }}" alt="Logo">
            </div>
        </div>
        <div class="title">
            <h1>فاتورة ضريبية</h1>
            <h2>Tax Invoice</h2>
        </div>
        <div class="section-title">Invoice Information</div>
        <div style="  display: flex;
        justify-content: space-between;
        align-items: center;">
            <table class="invoice-details">
                <tr>
                    <th>نوع الفاتورة<br>Invoice Type:</th>
                    <td>Standard</td>
                </tr>
                <tr>
                    <th>رمز نوع الفاتورة<br>Invoice Type Code:</th>
                    <td>{{ $invoiceTypeCode }}</td>
                </tr>
                <tr>
                    <th>رقم الفاتورة<br>Invoice Number:</th>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <th>تاريخ إصدار الفاتورة<br>Invoice Issue Date:</th>
                    <td>{{ $invoice->invoice_date }}</td>
                </tr>
                <tr>
                    <th>تاريخ التوريد<br>Delivery Date:</th>
                    <td>{{ $invoice->delivery_date }}</td>
                </tr>
                <tr>
                    <th>سبب الإعفاء الضريبي<br>Tax Exemption Reason:</th>
                    <td>{{ $invoice->invoice_note }}</td>
                </tr>
            </table>
            <div class="qr-code">
                {!! $Qr !!}
            </div>
        </div>
        <div class="section-title">Seller and Buyer Information</div>
        <table class="seller-buyer">
            <tr>
                <th colspan="2">البائع<br>Seller</th>
                <th colspan="2">المشتري<br>Buyer</th>
            </tr>
            <tr>
                <th>الاسم:<br>Name:</th>
                <td>{{ $seller->registration_name }}</td>
                <th>الاسم:<br>Name:</th>
                <td>{{ $client->registration_name }}</td>
            </tr>
            <tr>
                <th>رقم ضريبة القيمة المضافة:<br>VAT Number:</th>
                <td>{{ $seller->tax_number }}</td>
                <th>رقم ضريبة القيمة المضافة:<br>VAT Number:</th>
                <td>{{ $client->tax_number }}</td>
            </tr>
            <tr>
                <th>رقم المبنى:<br>Building No:</th>
                <td>{{ $seller->building_number }}</td>
                <th>رقم المبنى:<br>Building No:</th>
                <td>{{ $client->building_number }}</td>
            </tr>
            <tr>
                <th>اسم الشارع:<br>Street Name:</th>
                <td>{{ $seller->street_name }}</td>
                <th>اسم الشارع:<br>Street Name:</th>
                <td>{{ $client->street_name }}</td>
            </tr>
            <tr>
                <th>الحي:<br>District:</th>
                <td>{{ $seller->city_sub_division }}</td>
                <th>الحي:<br>District:</th>
                <td>{{ $client->city_subdivision_name }}</td>
            </tr>
            <tr>
                <th>المدينة:<br>City:</th>
                <td>{{ $seller->city }}</td>
                <th>المدينة:<br>City:</th>
                <td>{{ $client->city }}</td>
            </tr>
            <tr>
                <th>البلد:<br>Country:</th>
                <td>Saudi Arabia</td>
                <th>البلد:<br>Country:</th>
                <td>Saudi Arabia</td>
            </tr>
            <tr>
                <th>الرمز البريدي:<br>Postal Code:</th>
                <td>{{ $seller->postal_number }}</td>
                <th>الرمز البريدي:<br>Postal Code:</th>
                <td>{{ $client->postal_number }}</td>
            </tr>
            <tr>
                <th>رقم السجل التجاري:<br>Commercial Registration Number:</th>
                <td>{{ $seller->registration_number }}</td>
                <th>رقم المشتري الآخر:<br>Other Buyer ID:</th>
                <td>10106056711</td>
            </tr>
        </table>

        <div class="section-title">Invoice Items</div>
        <table class="invoice-items">
            <thead>
                <tr>
                    <th>رقم التسلسل<br>Serial No</th>
                    <th>طبيعة السلع أو الخدمات<br>Nature of Goods</th>
                    <th class="right-align">صافي مبلغ العنصر (بالريال السعودي)<br>Item Net Amount (SAR)</th>
                    <th class="right-align">الكمية<br>Quantity</th>
                    <th class="right-align">الخصومات<br>Discount</th>
                    <th class="right-align">فئة الضريبة<br>Tax Category</th>
                    <th class="right-align">معدل الضريبة<br>Tax Rate</th>
                    <th class="right-align">مبلغ الضريبة<br>Tax Amount</th>
                    <th class="right-align">المبلغ الإجمالي (بالريال السعودي)<br>Total Amount (SAR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoice_items as $index => $item)
                    <tr>
                        <td class="center-align">{{ $index + 1 }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="right-align">{{ number_format($item->price, 2) }}</td>
                        <td class="right-align">{{ $item->quantity }}</td>
                        <td class="right-align">{{ number_format($item->discount, 2) }}</td>
                        <td class="center-align">{{ $item->tax_category_code }}</td>
                        <td class="right-align">{{ $item->tax_percent }}%</td>
                        <td class="right-align">{{ number_format($item->tax, 2) }}</td>
                        <td class="right-align">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Summary</div>
        <table class="invoice-summary">
            <tr class="totals">
                <th>الإجمالي (باستثناء ضريبة القيمة المضافة):<br>Total (Excluding VAT):</th>
                <td class="right-align">{{ number_format($invoice->price, 2) }}</td>
            </tr>
            <tr class="totals">
                <th>إجمالي ضريبة القيمة المضافة:<br>Total VAT:</th>
                <td class="right-align">{{ number_format($invoice->tax, 2) }}</td>
            </tr>
            <tr class="totals">
                <th>المبلغ الإجمالي:<br>Total Amount:</th>
                <td class="right-align">{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
