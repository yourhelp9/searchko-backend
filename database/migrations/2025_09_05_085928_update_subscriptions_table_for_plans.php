<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Purana plan_name column hata kar naye columns add karein
            $table->dropColumn('plan_name');
            $table->integer('plan_id')->after('user_id');
            $table->integer('meals_per_day')->after('plan_id');
        });
    }
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan_name')->after('user_id');
            $table->dropColumn(['plan_id', 'meals_per_day']);
        });
    }
};