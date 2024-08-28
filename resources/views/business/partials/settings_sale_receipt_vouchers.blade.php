<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('start_reference_count', __('business.start_reference_count') . '') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-sort-numeric-up"></i>

                    </span>
                    {!! Form::text('start_reference_count',$business->start_reference_count, [
                        'class' => 'form-control input_number',
                    ]) !!}
                </div>
            </div>
        </div>












    </div>

</div>
