<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named "app_notifications" because Laravel reserves "notifications" for its own notification system.
        Schema::create('app_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 120);
            $table->text('body');
            $table->string('image')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table): void {
            // Notifications created after this moment count as unread for the customer.
            $table->timestamp('notifications_seen_at')->nullable()->after('api_token');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn('notifications_seen_at');
        });

        Schema::dropIfExists('app_notifications');
    }
};
