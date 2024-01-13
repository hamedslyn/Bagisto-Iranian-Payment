<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zarinpal_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('message');
            $table->string('card_hash', 255);
            $table->string('card_pan', 20);
            $table->string('transaction_id', 50);
            $table->string('fee_type')->default("Merchant");
            $table->integer('fee');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['success', 'failed', 'other']);
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
        Schema::dropIfExists('zarinpal_transactions');
    }
};
