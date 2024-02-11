@extends('layouts.guest')
@section('title', $title)
@section('content')

    <div class="container">
        <div class="spacer"></div>
        <div class="row">
            <div class="col-md-12 text-right mb-12">
                @if (!empty($payment_link))
                    <a href="{{ $payment_link }}" class="btn btn-info no-print" style="margin-right: 20px;"><i
                            class="fas fa-money-check-alt" title="@lang('lang_v1.pay')"></i> @lang('lang_v1.pay')
                    </a>
                @endif
                <button type="button" class="btn btn-primary no-print btn-sm" id="print_invoice" aria-label="Print"><i
                        class="fas fa-print"></i> @lang('messages.print')
                </button>
                @auth
                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'index']) }}"
                        class="btn btn-success no-print btn-sm"><i class="fas fa-backward"></i>
                    </a>
                @endauth
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-12" style="border: 1px solid #ccc;" id="invoice_content">
                <div class="spacer"></div>
                <div>
                    {!! $receipt['html_content'] !!}
                </div>
                <div class="spacer"></div>
            </div>
        </div>
        <div class="spacer"></div>
    </div>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', '#print_invoice', function() {
                $('#invoice_content').printThis();
            });
        });
        const url = new URL(window.location.href);
        const print_on_load = url.searchParams.get('print_on_load');

       
        if (print_on_load != true) {
            $(window).on('load', function() {
                $('#invoice_content').printThis();
            });
        }
    </script>
@endsection
