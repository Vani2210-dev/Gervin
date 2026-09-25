<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('province', 100)->nullable()->after('longitude');
            $table->string('ward', 150)->nullable()->after('province');
            $table->string('status', 100)->nullable()->default('Đang đặt hàng')->after('ward');
            $table->string('partner_competitors', 255)->nullable()->after('status');
            $table->text('feedback')->nullable()->after('partner_competitors');
            $table->text('personality')->nullable()->after('feedback');
            $table->string('workshop_scale', 255)->nullable()->after('personality');
            $table->text('customer_proposal')->nullable()->after('workshop_scale');
            $table->text('sale_proposal')->nullable()->after('customer_proposal');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'province',
                'ward',
                'status',
                'partner_competitors',
                'feedback',
                'personality',
                'workshop_scale',
                'customer_proposal',
                'sale_proposal',
            ]);
        });
    }
};
