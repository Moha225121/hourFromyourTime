<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->text('skills')->nullable();
            $table->text('needs')->nullable();
            $table->string('initial', 5);
            $table->text('bio')->nullable();
            $table->decimal('rating', 3, 1)->default(5);
            $table->unsignedInteger('hours_contributed')->default(0);
            $table->unsignedInteger('available_hours')->default(0);
            $table->string('skill')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('place')->nullable();
            $table->string('times')->nullable();
            $table->timestamps();
        });
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('person');
            $table->string('initial', 5);
            $table->string('place')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
        Schema::create('time_activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('hours');
            $table->date('activity_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_activities');
        Schema::dropIfExists('help_requests');
        Schema::dropIfExists('community_members');
    }
};
