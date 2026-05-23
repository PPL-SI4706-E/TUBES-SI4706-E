<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename the incorrectly named table 'user' to the conventional 'users'
        if (Schema::hasTable('user')) {
            Schema::rename('user', 'users');
        }
    }

    public function down(): void
    {
        // Revert the rename if needed
        if (Schema::hasTable('users')) {
            Schema::rename('users', 'user');
        }
    }
};
