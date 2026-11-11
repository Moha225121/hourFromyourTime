<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@hfyt.ly')
            ->update(['role' => 'admin', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@hfyt.ly')
            ->where('role', 'admin')
            ->update(['role' => 'member', 'updated_at' => now()]);
    }
};
