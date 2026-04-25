<?php

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
        Schema::table('laporan', function (Blueprint $table) {
            $table->enum('jenis_penanganan', ['lapangan', 'virtual'])->nullable()->after('status');
            $table->text('solusi')->nullable()->after('jenis_penanganan');
            $table->text('alasan_penolakan')->nullable()->after('solusi');
            $table->foreignId('validated_by')->nullable()->after('alasan_penolakan')->constrained('user')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn([
                'jenis_penanganan',
                'solusi',
                'alasan_penolakan',
                'validated_by',
                'validated_at'
            ]);
        });
    }
};
