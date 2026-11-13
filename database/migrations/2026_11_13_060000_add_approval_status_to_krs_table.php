<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->string('status')->default('disetujui');
            $table->index(['tahun_ajaran', 'semester', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropIndex(['tahun_ajaran', 'semester', 'status']);
            $table->dropColumn('status');
        });
    }
};
