<div class="box {{ $class ?? 'box-solid' }}" @if (!empty($id)) id="{{ $id }}" @endif>
    @if (empty($header))
        @if (!empty($title) || !empty($tool))
            <div class="box-header">

                <div class="col-md-12">
                    <div class="col-md-10" style="padding: 0px;">
                        {!! $icon ?? '' !!}
                        <h3 class="box-title">{{ $title ?? '' }}</h3>
                    </div>

                    <div class="col-md-2">
                        {!! $tool ?? '' !!}

                    </div>

                </div>
                @if (isset($help_text))
                    <br />
                    <small>{!! $help_text !!}</small>
                @endif
            </div>
        @endif
    @else
        <div class="box-header">
            {!! $header !!}
        </div>
    @endif

    <div class="box-body">
        {{ $slot }}
    </div>
    <!-- /.box-body -->
</div>
