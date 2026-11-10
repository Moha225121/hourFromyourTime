<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->after('password');
        });

        Schema::create('service_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('community_members')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('delivery_mode')->default('remote');
            $table->string('city')->nullable();
            $table->string('availability')->nullable();
            $table->string('status')->default('published');
            $table->timestamps();
            $table->index(['status', 'category_id']);
        });

        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('service_offers')->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('provider_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('requested_minutes');
            $table->unsignedInteger('actual_minutes')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('requester_confirmed')->default(false);
            $table->boolean('provider_confirmed')->default(false);
            $table->dateTime('settled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['requester_id', 'status']);
            $table->index(['provider_id', 'status']);
        });

        Schema::create('time_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('community_members')->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('service_bookings')->restrictOnDelete();
            $table->integer('minutes');
            $table->string('entry_type');
            $table->string('description');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['member_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_ledger');
        Schema::dropIfExists('service_bookings');
        Schema::dropIfExists('service_offers');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
