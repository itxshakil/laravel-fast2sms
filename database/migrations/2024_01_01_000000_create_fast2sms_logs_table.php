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
        Schema::create('fast2sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->boolean('is_success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fast2sms_logs');
    }
};
