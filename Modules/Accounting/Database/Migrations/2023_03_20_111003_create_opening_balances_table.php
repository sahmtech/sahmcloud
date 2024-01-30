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
        Schema::create('accounting_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->date('year');
            $table->integer('business_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->unsignedBigInteger('accounting_account_id');
            $table->enum('type',['creditor', 'debtor']);
            $table->float('value');
            $table->dropColumn('accounting_account_id');
            $table->dropColumn('value');
            $table->unsignedBigInteger('accounts_account_transaction_id');
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
        Schema::dropIfExists('accounting_opening_balances');
    }
};