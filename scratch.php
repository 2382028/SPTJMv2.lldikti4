<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('u_rekap_kekurangan', function (Blueprint $table) {
    if (!Schema::hasColumn('u_rekap_kekurangan', 'jenis')) {
        $table->string('jenis', 50)->nullable()->after('tipe');
    }
    if (!Schema::hasColumn('u_rekap_kekurangan', 'exclude_nidns')) {
        $table->longText('exclude_nidns')->nullable()->after('jenis');
    }
});

echo "Migration completed.\n";
