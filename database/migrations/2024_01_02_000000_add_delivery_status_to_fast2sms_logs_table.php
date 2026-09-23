<?php

declare(strict_types=1);

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
        Schema::table('fast2sms_logs', static function (Blueprint $table): void {
            if (! Schema::hasColumn('fast2sms_logs', 'status')) {
                $table->string('status')->nullable()->index()->after('is_success');
            }
            if (! Schema::hasColumn('fast2sms_logs', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('error_message');
            }
            if (! Schema::hasColumn('fast2sms_logs', 'amount_debited')) {
                $table->string('amount_debited')->nullable()->after('failure_reason');
            }
            if (! Schema::hasColumn('fast2sms_logs', 'delivery_timestamp')) {
                $table->unsignedBigInteger('delivery_timestamp')->nullable()->after('amount_debited');
            }
            if (! Schema::hasColumn('fast2sms_logs', 'post_attempt')) {
                $table->unsignedInteger('post_attempt')->nullable()->after('delivery_timestamp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fast2sms_logs', static function (Blueprint $table): void {
            $table->dropColumn([
                'status',
                'failure_reason',
                'amount_debited',
                'delivery_timestamp',
                'post_attempt',
            ]);
        });
    }
};
