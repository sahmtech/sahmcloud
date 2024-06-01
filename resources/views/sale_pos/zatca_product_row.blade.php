@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Invoice</h2>
        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="buyer">Select Buyer</label>
                <select name="buyer_id" id="buyer" class="form-control" required>
                    @foreach ($buyers as $buyer)
                        <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="invoice_date">Invoice Date</label>
                <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="invoice_time">Invoice Time</label>
                <input type="time" name="invoice_time" id="invoice_time" class="form-control" required>
            </div>

            <h4>Invoice Items</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="invoice-items">
                    <tr>
                        <td><input type="text" name="items[0][product_name]" class="form-control" required></td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                        <td><input type="number" name="items[0][price]" class="form-control" required></td>
                        <td><input type="number" name="items[0][discount]" class="form-control"></td>
                        <td><input type="number" name="items[0][tax]" class="form-control"></td>
                        <td><input type="number" name="items[0][total]" class="form-control" required></td>
                        <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-primary" id="add-item">Add Item</button>

            <button type="submit" class="btn btn-success">Create Invoice</button>
        </form>
    </div>

    <script>
        document.getElementById('add-item').addEventListener('click', function() {
            const index = document.querySelectorAll('#invoice-items tr').length;
            const row = `
            <tr>
                <td><input type="text" name="items[${index}][product_name]" class="form-control" required></td>
                <td><input type="number" name="items[${index}][quantity]" class="form-control" required></td>
                <td><input type="number" name="items[${index}][price]" class="form-control" required></td>
                <td><input type="number" name="items[${index}][discount]" class="form-control"></td>
                <td><input type="number" name="items[${index}][tax]" class="form-control"></td>
                <td><input type="number" name="items[${index}][total]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
            </tr>
        `;
            document.getElementById('invoice-items').insertAdjacentHTML('beforeend', row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endsection
