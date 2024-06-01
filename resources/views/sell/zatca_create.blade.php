<!DOCTYPE html>
<html>

<head>
    <title>Create Invoice</title>
    <link rel="stylesheet" href="{{ asset('/zatca_assets/zatca_css.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</head>

<body>
    <div class="zatca-container">
        <h2 class="zatca-title">Create Invoice</h2>
        <form action="{{ route('store_zatca') }}" method="POST" class="zatca-form">
            @csrf
            <!-- Seller Information -->
            <h3 class="zatca-section-title">Seller Information</h3>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="seller_name" class="zatca-label">Seller Name</label>
                    <input type="text" class="zatca-input" id="seller_name" name="seller_name"
                        value="{{ $seller->registration_name }}" readonly>
                </div>
                <div class="zatca-form-group">
                    <label for="seller_tax_number" class="zatca-label">Seller Tax Number</label>
                    <input type="text" class="zatca-input" id="seller_tax_number" name="seller_tax_number"
                        value="{{ $seller->tax_number }}" readonly>
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
                            <option value="{{ $buyer->id }}" data-name="{{ $buyer->registration_name }}"
                                data-tax="{{ $buyer->tax_number }}" data-address="{{ $buyer->address }}">
                                {{ $buyer->registration_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_name" class="zatca-label">Buyer Name</label>
                    <input type="text" class="zatca-input" id="buyer_name" name="buyer_name" readonly>
                </div>
            </div>
            <div class="zatca-row">
                <div class="zatca-form-group">
                    <label for="buyer_tax_number" class="zatca-label">Buyer Tax Number</label>
                    <input type="text" class="zatca-input" id="buyer_tax_number" name="buyer_tax_number" readonly>
                </div>
                <div class="zatca-form-group">
                    <label for="buyer_address" class="zatca-label">Buyer Address</label>
                    <input type="text" class="zatca-input" id="buyer_address" name="buyer_address" readonly>
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
                    <label for="invoice_date" class="zatca-label">Invoice Date</label>
                    <input type="date" class="zatca-input" id="invoice_date" name="invoice_date" required>
                </div>
                <div class="zatca-form-group">
                    <label for="invoice_time" class="zatca-label">Invoice Time</label>
                    <input type="time" class="zatca-input" id="invoice_time" name="invoice_time" required>
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
                                data-price="{{ $item->price }}" data-tax="{{ $item->tax }}"
                                data-total="{{ $item->total }}"
                                data-tax-percent="{{ ($item->tax / $item->price) * 100 }}">
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
                        </tr>
                        <tr>
                            <th>Total Tax</th>
                            <td id="total-tax">0.00</td>
                        </tr>
                        <tr>
                            <th>Total Price After Tax</th>
                            <td id="total-price-after-tax">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-success zatca-btn">Create Invoice</button>

        </form>
    </div>
    <script>
        $(document).ready(function() {
            let selectedItems = [];

            function calculateInvoiceSummary() {
                let totalPriceBeforeTax = 0;
                let totalTax = 0;
                let totalPriceAfterTax = 0;

                $('#invoice-items tr').each(function() {
                    const quantity = parseFloat($(this).find('[name="quantity[]"]').val()) || 0;
                    const price = parseFloat($(this).find('[name="price[]"]').val()) || 0;
                    const tax = parseFloat($(this).find('[name="tax[]"]').val()) || 0;
                    const taxPercent = parseFloat($(this).find('[name="tax_percent[]"]').val()) || 0;
                    const discount = parseFloat($(this).find('[name="discount[]"]').val()) || 0;
                    const total = (quantity * price) - discount;

                    totalPriceBeforeTax += total;
                    totalTax += (taxPercent / 100) * total;
                    totalPriceAfterTax += total + ((taxPercent / 100) * total);
                });

                $('#total-price-before-tax').text(totalPriceBeforeTax.toFixed(2));
                $('#total-tax').text(totalTax.toFixed(2));
                $('#total-price-after-tax').text(totalPriceAfterTax.toFixed(2));
            }

            $('#buyer_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const buyerName = selectedOption.data('name');
                const buyerTax = selectedOption.data('tax');
                const buyerAddress = selectedOption.data('address');

                $('#buyer_name').val(buyerName);
                $('#buyer_tax_number').val(buyerTax);
                $('#buyer_address').val(buyerAddress);
            });

            $('#item_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const item = {
                    id: selectedOption.val(),
                    name: selectedOption.data('name'),
                    price: selectedOption.data('price'),
                    tax: selectedOption.data('tax'),
                    taxPercent: selectedOption.data('tax-percent'),
                    total: selectedOption.data('total')
                };

                if (item.id && item.name && item.price !== undefined && item.tax !== undefined && item
                    .total !== undefined) {
                    selectedItems.push(item);

                    const row = `
                        <tr>
                            <input type="hidden" name="item_ids[]" value="${item.id}">
                            <td><input type="text" name="product_name[]" class="form-control zatca-input" value="${item.name}" readonly></td>
                            <td><input type="number" name="quantity[]" class="form-control zatca-input" value="1" required></td>
                            <td><input type="number" step="0.01" name="price[]" class="form-control zatca-input" value="${item.price}" readonly></td>
                            <td><input type="number" step="0.01" name="discount[]" class="form-control zatca-input"></td>
                            <td><input type="number" step="0.01" name="tax[]" class="form-control zatca-input" value="${item.tax}" readonly></td>
                            <td><input type="number" step="0.01" name="tax_percent[]" class="form-control zatca-input" value="${item.taxPercent}" readonly></td>
                            <td><input type="number" step="0.01" name="total[]" class="form-control zatca-input" value="${item.total}" readonly></td>
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
                const itemId = row.find('[name="product_name[]"]').val();

                // Remove item from the selectedItems array
                selectedItems = selectedItems.filter(item => item.name !== itemId);

                row.remove();
                calculateInvoiceSummary();
            });

            $('#invoice-items').on('input', '[name="quantity[]"], [name="discount[]"]', function() {
                calculateInvoiceSummary();
            });

            // Initialize Select2 on the item selection dropdown
            $('#item_id').select2();
        });
    </script>
</body>

</html>
