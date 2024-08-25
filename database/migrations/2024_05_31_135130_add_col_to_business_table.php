<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business', function (Blueprint $table) {
            $table->string('invoice_type')->nullable()->after('owner_id');
            $table->text('zatca_secret')->nullable()->after('owner_id');
            $table->text('zatca_certificate')->nullable()->after('owner_id');
            $table->text('zatca_private_key')->nullable()->after('owner_id');
            $table->string('city_sub_division')->nullable()->after('owner_id');
            $table->string('plot_identification')->nullable()->after('owner_id');
            $table->string('building_number')->nullable()->after('owner_id');
            $table->string('street_name')->nullable()->after('owner_id');
            $table->string('city')->nullable()->after('owner_id');
            $table->string('postal_number')->nullable()->after('owner_id');
            $table->string('egs_serial_number')->nullable()->after('owner_id');
            $table->string('common_name')->nullable()->after('owner_id');
            $table->string('registration_number')->nullable()->after('owner_id');
            $table->string('business_category')->nullable()->after('owner_id');
            $table->string('registered_address')->nullable()->after('owner_id');
            $table->string('organization_name')->nullable()->after('owner_id');
            $table->string('organizational_unit_name')->nullable()->after('owner_id');
            $table->string('email')->nullable()->after('owner_id');
            $table->string('fatoora_otp')->nullable()->after('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business', function (Blueprint $table) {
            //
        });
    }
};
