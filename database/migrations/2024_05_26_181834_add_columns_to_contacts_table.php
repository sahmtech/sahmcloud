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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('registration_name')->nullable()->after('zip_code');
            $table->string('city_subdivision_name')->nullable()->after('zip_code');
            $table->string('plot_identification')->nullable()->after('zip_code');
            $table->string('building_number')->nullable()->after('zip_code');
            $table->string('street_name')->nullable()->after('zip_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            //
        });
    }
};
