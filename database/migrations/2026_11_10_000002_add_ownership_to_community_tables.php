<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });
        Schema::table('help_requests', function (Blueprint $table) {
            $table->foreignId('requester_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('helper_id')->nullable()->after('requester_id')->constrained('users')->nullOnDelete();
        });
        Schema::table('time_activities', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('community_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_activities', fn (Blueprint $table) => $table->dropForeign(['member_id'])->dropColumn('member_id'));
        Schema::table('help_requests', fn (Blueprint $table) => $table->dropForeign(['requester_id'])->dropForeign(['helper_id'])->dropColumn(['requester_id', 'helper_id']));
        Schema::table('community_members', fn (Blueprint $table) => $table->dropForeign(['user_id'])->dropColumn('user_id'));
    }
};
