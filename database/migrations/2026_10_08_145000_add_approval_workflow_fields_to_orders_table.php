<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kế toán duyệt
            $table->unsignedBigInteger('accountant_approved_by')->nullable()->after('edit_reason');
            $table->dateTime('accountant_approved_at')->nullable()->after('accountant_approved_by');

            // Kỹ thuật duyệt (Chuyển sản xuất)
            $table->unsignedBigInteger('technical_approved_by')->nullable()->after('accountant_approved_at');
            $table->dateTime('technical_approved_at')->nullable()->after('technical_approved_by');

            // Người từ chối / yêu cầu sửa gần nhất
            $table->unsignedBigInteger('rejected_by')->nullable()->after('technical_approved_at');
            $table->dateTime('rejected_at')->nullable()->after('rejected_by');
            $table->string('rejected_step', 50)->nullable()->after('rejected_at'); // 'accountant' hoặc 'technical'

            $table->foreign('accountant_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('technical_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['accountant_approved_by']);
            $table->dropForeign(['technical_approved_by']);
            $table->dropForeign(['rejected_by']);

            $table->dropColumn([
                'accountant_approved_by',
                'accountant_approved_at',
                'technical_approved_by',
                'technical_approved_at',
                'rejected_by',
                'rejected_at',
                'rejected_step',
            ]);
        });
    }
};
