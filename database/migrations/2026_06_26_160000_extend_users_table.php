<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('phone')->unique()->nullable()->after('email');
            $table->string('country_phone_code', 10)->nullable()->after('phone');
            $table->string('role')->default('customer')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('profile_image')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'phone', 'country_phone_code', 'role', 'is_active', 'profile_image']);
        });
    }
};
