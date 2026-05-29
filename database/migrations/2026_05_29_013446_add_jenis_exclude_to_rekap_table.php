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
        Schema::table('u_rekap_kekurangan', function (Blueprint $table) {
            if (!Schema::hasColumn('u_rekap_kekurangan', 'jenis')) {
                $table->string('jenis', 50)->nullable()->after('tipe');
            }
            if (!Schema::hasColumn('u_rekap_kekurangan', 'exclude_nidns')) {
                $table->longText('exclude_nidns')->nullable()->after('jenis');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('u_rekap_kekurangan', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'exclude_nidns']);
        });
    }
};
