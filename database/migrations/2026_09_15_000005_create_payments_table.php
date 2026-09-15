<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online payment attempts, one row per checkout sent to a gateway (OneCard, SSLCommerz).
 *
 * The table was used by PaymentController, OneCardPaymentController,
 * SslCommerzPaymentController and PaymentApiController but never had a migration, so a
 * fresh database had no payments table and every online checkout failed. Columns follow
 * what that code writes and reads.
 *
 * Guarded with hasTable: databases where the table was created by hand or imported keep
 * theirs untouched.
 *
 * status: 0 pending, 1 successful, 2 failed, 3 cancelled.
 * reff_id is the transaction reference sent to the gateway; every callback finds the
 * row by it. It is indexed rather than unique so an imported table with an old
 * duplicate still loads. user_id carries no foreign key, like orders.user_id, so rows
 * whose user was later removed stay on record.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            return;
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->string('cus_name')->nullable();
            $table->string('cus_email')->nullable();
            $table->string('cus_address')->nullable();
            $table->string('cus_city')->nullable();
            $table->string('cus_state')->nullable();
            $table->string('cus_postcode', 20)->nullable();
            $table->string('cus_country')->nullable();
            $table->string('cus_phone', 50)->nullable();
            $table->string('response_type', 20)->nullable();
            $table->string('service_type', 50)->nullable();
            $table->string('getaway', 30)->nullable();
            $table->string('reff_id', 100)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            // The gateway's full response, stored as JSON.
            $table->longText('message')->nullable();
            $table->timestamps();

            $table->index('reff_id');
            $table->index(['user_id', 'getaway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
