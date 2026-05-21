<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalNominalToURekapKekurangan extends Migration
{
    public function up()
    {
        Schema::table('u_rekap_kekurangan', function (Blueprint $table) {
            $table->decimal('total_nominal', 15, 2)->nullable()->after('excel');
        });
    }

    public function down()
    {
        Schema::table('u_rekap_kekurangan', function (Blueprint $table) {
            $table->dropColumn('total_nominal');
        });
    }
}
