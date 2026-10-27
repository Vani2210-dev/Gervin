<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->default('manual')->comment('order_created, order_status_changed, order_updated, manual, broadcast');
            $table->string('title');
            $table->text('content');
            $table->string('link')->nullable();
            $table->string('target_type', 30)->default('all')->comment('all, market_group, role, user');
            $table->json('target_ids')->nullable()->comment('Danh sách ID nhóm/role/user nhận');
            $table->json('data')->nullable()->comment('Dữ liệu đính kèm (order_id, code, ...)');
            $table->timestamps();

            $table->index('type');
            $table->index('created_at');
        });

        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('system_notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('notification_settings');
    }
};
