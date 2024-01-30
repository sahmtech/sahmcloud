<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('business_id')->nullable();
            $table->unsignedBigInteger('business_location_id')->nullable();
            $table->string('ar_name');
            $table->string('en_name');
            $table->string('account_center_number');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_cost_centers');
    }
};