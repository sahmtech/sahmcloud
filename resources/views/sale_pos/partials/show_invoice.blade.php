@extends('layouts.guest')
@section('title', $title)
@section('content')
    <style>
        @media print {
            .container {
                display: none;
            }
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-right mb-12">
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
    </div>
    {!! $receipt['html_content'] !!}
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', '#print_invoice', function() {
                window.print();
            });
            if (print_on_load != true) {
                $(window).on('load', function() {
                    window.print();
                });
            }
        });
        const url = new URL(window.location.href);
        const print_on_load = url.searchParams.get('print_on_load');
    </script>
@endsection
