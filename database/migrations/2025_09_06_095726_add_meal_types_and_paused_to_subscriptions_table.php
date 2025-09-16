<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan_id'); // old
            $table->string('plan_name')->nullable()->after('user_id'); // new plan name
            $table->json('meal_types')->nullable()->after('plan_name');
            $table->boolean('is_paused')->default(false)->after('meals_remaining');
        });
    }
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan_name')->after('user_id');
            $table->dropColumn(['plan_id', 'meals_per_day', 'meal_types', 'is_paused']);
        });
    }
};