<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('user')->nullOnDelete();
            $table->string('title')->nullable()->after('user_id');
            $table->text('message')->nullable()->after('title');
            $table->boolean('is_read')->default(false)->after('message');
            $table->json('data')->nullable()->after('is_read');
        });
    }

    public function down(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['title', 'message', 'is_read', 'data']);
        });
    }
};
