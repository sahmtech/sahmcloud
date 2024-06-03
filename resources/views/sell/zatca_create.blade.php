@extends('layouts.app')

@section('title', 'Create Invoice')

<link rel="stylesheet" href="{{ asset('/zatca_assets/zatca_css.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@section('content')
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <div class="zatca-container">
        <h2 class="zatca-title">Create Invoice</h2>
        <form id="invoiceForm" action="{{ route('store_zatca') }}" method="POST" class="zatca-form">
            @csrf


            <input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{ $business_details->sell_price_tax }}">

            <input type="hidden" name="business_enable_inline_tax" id="business_enable_inline_tax"
                value="{{ session()->get('business.enable_inline_tax') }}">


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

            <!-- Seller Information -->
            <h3 class="zatca-section-title">Seller Information</h3>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="seller_name" class="zatca-label">Seller Name</label>
                    <input type="text" class="zatca-input" id="seller_name" name="seller_name"
                        value="{{ $seller->registration_name }}"required readonly>
                </div>
                <div class="zatca-form-group">
                    <label for="seller_tax_number" class="zatca-label">Seller Tax Number</label>
                    <input type="text" class="zatca-input" id="seller_tax_number" name="seller_tax_number"
                        value="{{ $seller->tax_number }}" required readonly>
                </div>
            </div>
            <!-- Buyer Information -->
            <h3 class="zatca-section-title">Buyer Information</h3>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="buyer_id" class="zatca-label">Select Buyer</label>
                    <select class="zatca-input" id="buyer_id" name="buyer_id">
                        <option value="">Select a buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->id }}" data-registration_name="{{ $buyer->registration_name }}"
                                data-tax_number="{{ $buyer->tax_number }}" data-city="{{ $buyer->city }}"
                                data-city_subdivision_name="{{ $buyer->city_subdivision_name }}"
                                data-zip_code="{{ $buyer->zip_code }}" data-street_name="{{ $buyer->street_name }}"
                                data-building_number="{{ $buyer->building_number }}"
                                data-plot_identification="{{ $buyer->plot_identification }}">

                                {{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_name" class="zatca-label">Buyer Registration Name</label>
                    <input type="text" class="zatca-input" id="buyer_registration_name" name="buyer_registration_name"
                        required>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_tax_number" class="zatca-label">Buyer Tax Number</label>
                    <input type="text" class="zatca-input" id="buyer_tax_number" name="buyer_tax_number" required>
                </div>

            </div>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">Postal Number</label>
                    <input type="text" class="zatca-input" id="buyer_postal_code" name="buyer_postal_code" required>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">Street</label>
                    <input type="text" class="zatca-input" id="buyer_street" name="buyer_street" required>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">Building Number</label>
                    <input type="text" class="zatca-input" id="buyer_building_number" name="buyer_building_number"
                        required>
                </div>
            </div>

            <div class="zatca-row">

                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">Plot Identification</label>
                    <input type="text" class="zatca-input" id="buyer_plot_identification"
                        name="buyer_plot_identification" required>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">City Subdivision Name</label>
                    <input type="text" class="zatca-input" id="buyer_city_subdivision_name"
                        name="buyer_city_subdivision_name" required>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">City</label>
                    <input type="text" class="zatca-input" id="buyer_city" name="buyer_city" required>
                </div>

            </div>
            <!-- Invoice Details -->
            <h3 class="zatca-section-title">Invoice Details</h3>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="invoice_number" class="zatca-label">Invoice Number</label>
                    <input type="text" class="zatca-input" id="invoice_number" name="invoice_number" required>
                </div>
                <div class="zatca-form-group">
                    <label for="invoice_type" class="zatca-label">Invoice Type</label>
                    <select class="zatca-input" id="invoice_type" name="invoice_type" required>
                        <option value="388">Tax Invoice</option>
                        <option value="383">Debit Note</option>
                        <option value="381">Credit Note</option>
                    </select>
                </div>
                <div class="zatca-form-group">
                    <label for="invoice_date" class="zatca-label">Invoice Date</label>
                    <input type="date" class="zatca-input" id="invoice_date" name="invoice_date" required>
                </div>

                <div class="zatca-form-group">
                    <label for="invoice_time" class="zatca-label">Invoice Time</label>
                    <input type="time" class="zatca-input" id="invoice_time" name="invoice_time" required>
                </div>

            </div>
            <div class="zatca-row">
                {{-- @if (!empty($status))
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
            @endif --}}
                @if (!empty($status))
                    <input type="hidden" name="status" id="status" value="{{ $status }}">

                    @if (in_array($status, ['draft', 'quotation']))
                        <input type="hidden" id="disable_qty_alert">
                    @endif
                @else
                    <div class="zatca-form-group">
                        <label for="status" class="zatca-label">{{ __('sale.status') }} </label>
                        <select class="zatca-input" id="status" name="status" required>
                            <option value="">{{ __('messages.please_select') }}</option>
                            @foreach ($statuses as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif


                <div class="zatca-form-group">
                    <label for="payment_type" class="zatca-label">Payment Type</label>
                    <select class="zatca-input" id="payment_type" name="payment_type" required>
                        <option value="10">Cash</option>
                        <option value="30">Credit</option>
                        <option value="42">Bank Account</option>
                        <option value="48">Bank Card</option>
                        <option value="1">Multiple</option>
                    </select>
                </div>
                <div class="zatca-form-group">
                    <label for="payment_note" class="zatca-label">Payment Note</label>
                    <input type="text" class="zatca-input" id="payment_note" name="payment_note">
                </div>
                <div class="zatca-form-group">
                    <label for="invoice_currency" class="zatca-label">Invoice Currency</label>
                    <input type="text" class="zatca-input" id="invoice_currency" name="invoice_currency"
                        value="SAR" required>
                </div>
            </div>
            <!-- Invoice Items -->
            <h3 class="zatca-section-title">Invoice Items</h3>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="item_id" class="zatca-label">Select Item</label>
                    <select class="zatca-input zatca-select" id="item_id" name="item_id">
                        <option selected disabled value="">Select an item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" data-name="{{ $item->name }}"
                                data-price="{{ $item->price }}" data-tax="{{ $item->total - $item->price }}"
                                data-total="{{ $item->total }}" data-tax-percent="{{ $item->tax }}">
                                {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <table class="table table-bordered zatca-table invoice-items">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Tax</th>
                        <th>Tax Percent</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="invoice-items" class="invoice-items"></tbody>
            </table>
            <!-- Invoice Summary -->
            <div class="invoice-summary">
                <h3 class="zatca-section-title">Invoice Summary</h3>
                <table class="table table-bordered zatca-table">
                    <tbody>
                        <tr>
                            <th>Total Price Before Tax</th>
                            <td id="total-price-before-tax">0.00</td>
                            <input type="hidden" name="total_before_tax" id="total_before_tax">
                        </tr>
                        <tr>
                            <th>Total Tax</th>
                            <td id="total-tax">0.00</td>
                            <input type="hidden" name="total_tax" id="total_tax">
                        </tr>
                        <tr>
                            <th>Total Price After Tax</th>
                            <td id="total-price-after-tax">0.00</td>
                            <input type="hidden" name="final_total" id="final_total">
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-success zatca-btn">Create Invoice</button>

        </form>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            let selectedItems = [];

            function calculateInvoiceSummary() {
                let totalPriceBeforeTax = 0;
                let totalTax = 0;
                let totalPriceAfterTax = 0;

                $('#invoice-items tr').each(function() {
                    const id = $(this).find('[name="item_ids[]"]').val();
                    const quantity = parseFloat($(this).find(`[name="selected_products[${id}][quantity]"]`)
                        .val()) || 0;
                    const price = parseFloat($(this).find(`[name="selected_products[${id}][price]"]`)
                    .val()) || 0;
                    const taxPercent = parseFloat($(this).find(
                        `[name="selected_products[${id}][tax_percent]"]`).val()) || 0;
                    const discount = parseFloat($(this).find(`[name="selected_products[${id}][discount]"]`)
                        .val()) || 0;

                    const total = ((quantity * price) - discount).toFixed(2);

                    totalPriceBeforeTax += parseFloat(total);
                    totalTax += parseFloat(((taxPercent / 100) * total).toFixed(2));
                    totalPriceAfterTax += parseFloat(total) + parseFloat(((taxPercent / 100) * total)
                        .toFixed(2));
                });

                $('#total-price-before-tax').text(totalPriceBeforeTax.toFixed(2));
                $('#total_before_tax').val(totalPriceBeforeTax);
                $('#total-tax').text(totalTax.toFixed(2));
                $('#total_tax').val(totalTax);
                $('#total-price-after-tax').text(totalPriceAfterTax.toFixed(2));
                $('#final_total').val(totalPriceAfterTax);
            }

            $('#buyer_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const buyer_registration_name = selectedOption.data('registration_name');
                const buyer_tax_number = selectedOption.data('tax_number');
                const buyer_city = selectedOption.data('city');
                const buyer_city_subdivision_name = selectedOption.data('city_subdivision_name');
                const buyer_postal_code = selectedOption.data('zip_code');
                const buyer_street = selectedOption.data('street_name');
                const buyer_building_number = selectedOption.data('building_number');
                const buyer_plot_identification = selectedOption.data('plot_identification');

                $('#buyer_registration_name').val(buyer_registration_name);
                $('#buyer_tax_number').val(buyer_tax_number);
                $('#buyer_city').val(buyer_city);
                $('#buyer_city_subdivision_name').val(buyer_city_subdivision_name);
                $('#buyer_postal_code').val(buyer_postal_code);
                $('#buyer_street').val(buyer_street);
                $('#buyer_building_number').val(buyer_building_number);
                $('#buyer_plot_identification').val(buyer_plot_identification);
            });

            $('#item_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const item = {
                    id: selectedOption.val(),
                    name: selectedOption.data('name'),
                    price: parseFloat(selectedOption.data('price')).toFixed(2),
                    tax: parseFloat(selectedOption.data('tax')).toFixed(2),
                    taxPercent: parseFloat(selectedOption.data('tax-percent')).toFixed(2),
                    total: parseFloat(selectedOption.data('total')).toFixed(2)
                };

                if (item.id && item.name && item.price !== undefined && item.tax !== undefined && item
                    .total !== undefined) {
                    selectedItems.push(item);

                    const row = `
                <tr>
                    <input type="hidden" name="item_ids[]" value="${item.id}">
                    <td><input type="text" name="selected_products[${item.id}][name]" class="form-control zatca-input" value="${item.name}" readonly></td>
                    <td><input type="number" name="selected_products[${item.id}][quantity]" class="form-control zatca-input" value="1" required></td>
                    <td><input type="number" step="0.01" name="selected_products[${item.id}][price]" class="form-control zatca-input" value="${item.price}" readonly></td>
                    <td><input type="number" step="0.01" name="selected_products[${item.id}][discount]" class="form-control zatca-input" value="0"></td>
                    <td><input type="number" step="0.01" name="selected_products[${item.id}][tax]" class="form-control zatca-input" value="${item.tax}" readonly></td>
                    <td><input type="number" step="0.01" name="selected_products[${item.id}][tax_percent]" class="form-control zatca-input" value="${item.taxPercent}" readonly></td>
                    <td><input type="number" step="0.01" name="selected_products[${item.id}][total]" class="form-control zatca-input" value="${item.total}" readonly></td>
                    <td><button type="button" class="btn btn-danger zatca-btn-remove">Remove</button></td>
                </tr>
            `;
                    $('#invoice-items').append(row);
                    calculateInvoiceSummary();

                    // Clear the selection and focus of the select element
                    $('#item_id').val(null).trigger('change'); // Select2 specific method to reset
                } else {
                    console.error('Selected item data is incomplete or undefined:', item);
                }
            });

            $('#invoice-items').on('click', '.zatca-btn-remove', function() {
                const row = $(this).closest('tr');
                const itemId = row.find('[name="item_ids[]"]').val();

                // Remove item from the selectedItems array
                selectedItems = selectedItems.filter(item => item.id !== itemId);

                row.remove();
                calculateInvoiceSummary();
            });

            $('#invoice-items').on('input', 'input', function() {
                calculateInvoiceSummary();
            });

            // Initialize Select2 on the item selection dropdown
            $('#item_id').select2();

            // Add form submission handler to round values to two decimal places
            $('#invoiceForm').on('submit', function() {
                $('#invoice-items').find(
                    '[name="selected_products[][price]"], [name="selected_products[][total]"]').each(
                    function() {
                        if (this.value !== '') {
                            this.value = parseFloat(this.value).toFixed(2);
                        }
                    });
                calculateInvoiceSummary(); // Ensure summary is updated before submission
            });
        });
    </script>
@endsection
