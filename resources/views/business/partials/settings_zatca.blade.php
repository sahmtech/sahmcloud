<div class="pos-tab-content">
    <div class="row">
        <!-- Setting Fields -->
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('otp', __('zatca.otp') . ':') !!}
                {!! Form::text('zatca_settings[otp]', $zatca_settings['otp'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.otp'),
                    'id' => 'zatca.otp',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('emailAddress', __('zatca.emailAddress') . ':') !!}
                {!! Form::text('zatca_settings[emailAddress]', $zatca_settings['emailAddress'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.emailAddress'),
                    'id' => 'zatca.emailAddress',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('commonName', __('zatca.commonName') . ':') !!}
                {!! Form::text('zatca_settings[commonName]', $zatca_settings['commonName'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.commonName'),
                    'id' => 'zatca.commonName',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('organizationalUnitName', __('zatca.organizationalUnitName') . ':') !!}
                {!! Form::text('zatca_settings[organizationalUnitName]', $zatca_settings['organizationalUnitName'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.organizationalUnitName'),
                    'id' => 'zatca.organizationalUnitName',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('organizationName', __('zatca.organizationName') . ':') !!}
                {!! Form::text('zatca_settings[organizationName]', $zatca_settings['organizationName'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.organizationName'),
                    'id' => 'zatca.organizationName',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('taxNumber', __('zatca.taxNumber') . ':') !!}
                {!! Form::text('zatca_settings[taxNumber]', $zatca_settings['taxNumber'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.taxNumber'),
                    'id' => 'zatca.taxNumber',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('registeredAddress', __('zatca.registeredAddress') . ':') !!}
                {!! Form::text('zatca_settings[registeredAddress]', $zatca_settings['registeredAddress'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.registeredAddress'),
                    'id' => 'zatca.registeredAddress',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('businessCategory', __('zatca.businessCategory') . ':') !!}
                {!! Form::text('zatca_settings[businessCategory]', $zatca_settings['businessCategory'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.businessCategory'),
                    'id' => 'zatca.businessCategory',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('egsSerialNumber', __('zatca.egsSerialNumber') . ':') !!}
                {!! Form::text('zatca_settings[egsSerialNumber]', $zatca_settings['egsSerialNumber'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.egsSerialNumber'),
                    'id' => 'zatca.egsSerialNumber',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('registrationNumber', __('zatca.registrationNumber') . ':') !!}
                {!! Form::text('zatca_settings[registrationNumber]', $zatca_settings['registrationNumber'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.registrationNumber'),
                    'id' => 'zatca.registrationNumber',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">

                {!! Form::label('invoiceType', __('zatca.invoiceType') . ':') !!}
                {!! Form::select('zatca_settings[invoiceType]', $invoiceTypes, $zatca_settings['invoiceType'] ?? '', [
                    'class' => 'form-control',
                    'id' => 'zatca.invoiceType',
                ]) !!}
            </div>
        </div>

        <!-- Seller Fields -->
        <div class="clearfix"></div>
        <hr>
        <div class="clearfix"></div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('street_name', __('zatca.street_name') . ':') !!}
                {!! Form::text('zatca_seller[street_name]', $zatca_seller['street_name'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.street_name'),
                    'id' => 'zatca.street_name',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('building_number', __('zatca.building_number') . ':') !!}
                {!! Form::text('zatca_seller[building_number]', $zatca_seller['building_number'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.building_number'),
                    'id' => 'zatca.building_number',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('plot_identification', __('zatca.plot_identification') . ':') !!}
                {!! Form::text('zatca_seller[plot_identification]', $zatca_seller['plot_identification'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.plot_identification'),
                    'id' => 'zatca.plot_identification',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('city_sub_division', __('zatca.city_sub_division') . ':') !!}
                {!! Form::text('zatca_seller[city_sub_division]', $zatca_seller['city_sub_division'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.city_sub_division'),
                    'id' => 'zatca.city_sub_division',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('city', __('zatca.city') . ':') !!}
                {!! Form::text('zatca_seller[city]', $zatca_seller['city'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.city'),
                    'id' => 'zatca.city',
                ]) !!}
            </div>
        </div>
        <div class="col-xs-4">
            <div class="form-group">
                {!! Form::label('postal_number', __('zatca.postal_number') . ':') !!}
                {!! Form::text('zatca_seller[postal_number]', $zatca_seller['postal_number'] ?? '', [
                    'class' => 'form-control',
                    'placeholder' => __('zatca.postal_number'),
                    'id' => 'zatca.postal_number',
                ]) !!}
            </div>
        </div>
    </div>
</div>
