<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTUraianPembayaranTable extends Migration
{
    public function up()
    {
        Schema::create('t_uraian_pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rekap_id')->nullable()->comment('FK ke u_rekap_kekurangan.id');
            $table->string('nidn', 50)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->tinyInteger('bulan')->nullable()->comment('1-12');
            $table->string('uraian_pembayaran', 255)->nullable();
            $table->decimal('nominal', 15, 2)->default(0)->comment('Jumlah kotor selisih');
            $table->decimal('pajak', 15, 2)->default(0)->comment('Pajak atas selisih');
            $table->decimal('bersih', 15, 2)->default(0)->comment('Nominal - Pajak');
            $table->string('nomor', 100)->nullable()->comment('No SP2D');
            $table->date('tanggal')->nullable()->comment('Tgl SP2D');
            $table->timestamps();

            $table->index(['nidn', 'tahun']);
            $table->index('rekap_id');

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_uraian_pembayaran');
    }
}
