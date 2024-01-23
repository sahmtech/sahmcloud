@extends('layouts.app')

@section('title', __('accounting::lang.automatedMigration'))

@section('content')

    @include('accounting::layouts.nav')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('accounting::lang.automatedMigration')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid'])
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-primary pull-right m-5 btn-modal"
                                href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@create') }}"
                                data-href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@create') }}"
                                data-container="#create_account_modal">
                                <i class="fas fa-plus"></i> @lang('messages.add')</a>
                        </div>
                    @endslot

                    <div class="col-sm-12">
                        <h4 style="text-align: start">قائمة الترحيلات</h4>

                        <table class="table table-bordered table-striped hide-footer" id="journal_table">
                            <thead>
                                <tr>
                                    <th class="col-md-1">#
                                    </th>
                                    <th class="col-md-3">اسم الترحيل</th>
                                    <th class="col-md-3">نوع العملية</th>
                                    <th class="col-md-3">حالة الدفع</th>
                                    <th class="col-md-3">طريقة الدفع</th>
                                    <th class="col-md-3">الحالة</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                @foreach ($mappingSetting as $row)
                                    <tr>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button id="btnGroupDrop1" type="button"
                                                    style="background-color: transparent;
                                                font-size: x-large;
                                                padding: 0px 20px;"
                                                    class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" style="margin: 2px;" title="@lang('messages.edit')"
                                                        href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@edit', $row->id) }}"
                                                        data-href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@edit', $row->id) }}">
                                                        <i class="fas fa-edit" style="padding: 2px;color:rgb(8, 158, 16);"></i>
                                                        @lang('messages.edit') </a>

                                                    <a class="dropdown-item" style="margin: 2px;" {{-- title="{{ $row->active ? @lang('accounting::lang.active') : @lang('accounting::lang.inactive') }}" --}}
                                                        href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@active_toggle', $row->id) }}"
                                                        data-href="{{ action('\Modules\Accounting\Http\Controllers\AutomatedMigrationController@active_toggle', $row->id) }}"
                                                        {{-- data-target="#active_auto_migration" data-toggle="modal" --}} {{-- id="delete_auto_migration" --}}>
                                                        @if (!$row->active)
                                                            <i class="fa fa-bullseye" style="padding: 2px;color: green;"
                                                                title="state of automated migration is active"
                                                                aria-hidden="true"></i>
                                                            @lang('accounting::lang.active')

                                                            <i class=""></i>
                                                        @else
                                                            <i class="fa fa-ban" style="padding: 2px;color:red;"
                                                                title="state of automated migration is inactive"></i>
                                                            @lang('accounting::lang.inactive')
                                                        @endif
                                                    </a>
                                                </div>
                                            </div>




                                        </td>
                                        <td>
                                            {{ $row->name }}

                                        </td>
                                        <td>
                                            @lang('accounting::lang.autoMigration.' . $row->type)

                                        </td>
                                        <td>

                                            @lang('accounting::lang.autoMigration.' . $row->payment_status)

                                        </td>
                                        <td>
                                            @lang('accounting::lang.autoMigration.' . $row->method)

                                        </td>

                                        <td>
                                            @if ($row->active)
                                                <i class="fa fa-bullseye" title="state of automated migration is active"
                                                    aria-hidden="true" style="color: green"></i>
                                            @else
                                                <i class="fa fa-ban" title="state of automated migration is inactive"
                                                    aria-hidden="true" style="color:red"></i>
                                            @endif

                                        </td>




                                    </tr>
                                @endforeach

                            </tbody>

                            {{-- <tfoot>
                                <tr>
                                    <th></th>
                                    <th class="text-center">@lang('accounting::lang.total')</th>
                                    <th><input type="hidden" class="total_debit_hidden"><span class="total_debit"></span></th>
                                    <th><input type="hidden" class="total_credit_hidden"><span class="total_credit"></span>
                                    </th>
                                </tr>
                            </tfoot> --}}
                        </table>

                    </div>
                @endcomponent
            </div>
        </div>
    </section>

    <div class="modal fade" id="create_account_modal" tabindex="-1" role="dialog"></div>
    <div class="modal fade" id="delete_auto_migration" tabindex="-1" role="dialog">
        @include('accounting::AutomatedMigration.deleteDialog')
    </div>
@stop


@section('javascript')
    @include('accounting::accounting.common_js')

    <script type="text/javascript">
        $(document).ready(function() {
            $('#journal_table tbody').append($(
                "<tr><td class=\"containter\"></td>td class=\"containter\"></td>td class=\"containter\"></td></tr>"
            ));
            $('.journal_add_btn').click(function(e) {
                //e.preventDefault();
                calculate_total();

                var is_valid = true;

                //check if same or not
                if ($('.total_credit_hidden').val() != $('.total_debit_hidden').val()) {
                    is_valid = false;
                    alert("@lang('accounting::lang.credit_debit_equal')");
                }

                //check if all account selected or not
                $('table > tbody  > tr').each(function(index, tr) {
                    var credit = __read_number($(tr).find('.credit'));
                    var debit = __read_number($(tr).find('.debit'));

                    if (credit != 0 || debit != 0) {
                        if ($(tr).find('.account_id').val() == '') {
                            is_valid = false;
                            alert("@lang('accounting::lang.select_all_accounts')");
                        }
                    }
                });

                if (is_valid) {
                    $('form#journal_add_form').submit();
                }

                return is_valid;
            });

            $('.credit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });
        });

        function calculate_total() {
            var total_credit = 0;
            var total_debit = 0;
            $('table > tbody  > tr').each(function(index, tr) {
                var credit = __read_number($(tr).find('.credit'));
                total_credit += credit;

                var debit = __read_number($(tr).find('.debit'));
                total_debit += debit;
            });

            $('.total_credit_hidden').val(total_credit);
            $('.total_debit_hidden').val(total_debit);

            $('.total_credit').text(__currency_trans_from_en(total_credit));
            $('.total_debit').text(__currency_trans_from_en(total_debit));
        }
        $(document).on('click', '.fa-plus-square', function() {
            var tbode_number = $(this).val();
            let counter = $('#journal_table' + tbode_number + ' tr').length - 1;
            $('#tbody' + tbode_number).append(
                '<tr><td><button type="button" class="fa fa-plus-square fa-2x text-primary cursor-pointer" data-id="' +
                counter +
                '" name="' + tbode_number + '" value="' + tbode_number +
                '" style="background: transparent; border: 0px;"></button></td><td><select class="form-control accounts-dropdown account_id" style="width: 100%;" name="account_id' +
                tbode_number + '[' +
                counter +
                ']"><option selected="selected" value="">يرجى الاختيار</option></select> </td> <td><label class="radio-inline"><input value="debit" type="radio" name="type' +
                tbode_number + '[' +
                counter +
                ']" checked>@lang('accounting::lang.debtor')</label><label class="radio-inline"><input value="credit" type="radio" name="type' +
                tbode_number + '[' +
                counter +
                ']">@lang('accounting::lang.creditor')</label></td><td><select class="form-control" name="amount_type' +
                tbode_number + '[' + counter + ']' + '" id="amount_type' + tbode_number + '' + counter +
                '"style="padding: 3px" required><option value="final_total">@lang('accounting::lang.autoMigration.final_total')</option><option value="total_before_tax">@lang('accounting::lang.autoMigration.total_before_tax')</option><option value="tax_amount">@lang('accounting::lang.autoMigration.tax_amount')</option><option value="shipping_charges">@lang('accounting::lang.autoMigration.shipping_charges')</option><option value="discount_amount">@lang('accounting::lang.autoMigration.discount_amount')</option></select></td></tr>'
            )
            $('select[name="account_id' + tbode_number + '[' + counter + ']"]').select2({
                ajax: {
                    url: '{{ route('accounts-dropdown') }}',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data
                        }
                    },
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.html;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });
            $('.credit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.debit').val('');
                }
                calculate_total();
            });
            $('.debit').change(function() {
                if ($(this).val() > 0) {
                    $(this).parents('tr').find('.credit').val('');
                }
                calculate_total();
            });

        });
    </script>
@endsection
