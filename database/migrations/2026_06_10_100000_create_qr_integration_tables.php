<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qr_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Chứa Device ID nguyên (MAC-based)
            $table->string('name');
            $table->string('process_step')->nullable(); // cnc, pressing, edge_banding, finishing, qc
            $table->string('action_type')->nullable(); // complete, rollback, làm lệnh ép, ép đơn...
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('operator_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::create('qr_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('barcode');
            $table->timestamp('scanned_at')->nullable();
            $table->string('scanned_at_raw');
            $table->string('status'); // success, failed, duplicate, unmapped
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('device_id')
                ->references('id')
                ->on('qr_devices')
                ->onDelete('cascade');

            $table->unique(['device_id', 'scanned_at_raw', 'barcode'], 'qr_scan_logs_device_time_barcode_unique');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_scan_logs');
        Schema::dropIfExists('qr_devices');
    }
};
