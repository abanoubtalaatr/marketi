<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway')->default('moyasar')->after('payment_method');
            $table->string('payment_url')->nullable()->after('transaction_id');
            $table->json('gateway_response')->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'payment_url', 'gateway_response']);
        });
    }
};
