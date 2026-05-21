<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSTransaksi2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('s_transaksi_2', function (Blueprint $table) {
            $table->bigIncrements('No');
            $table->string('NIDN', 50)->nullable();
            $table->string('NUPTK', 50)->nullable();
            $table->string('NIK', 100)->nullable();
            $table->string('Nama', 255)->nullable();
            $table->string('TTL', 250)->nullable();
            $table->string('Tanggal_Lahir', 50)->nullable();
            $table->string('Usia', 2)->nullable();
            $table->string('Sertifikat_Dosen', 50)->nullable();
            $table->string('Tahun_Lulus', 50)->nullable();
            $table->string('PTS', 255)->nullable();
            $table->string('Kode_PT', 250)->nullable();
            $table->string('Jenis', 10)->nullable();
            $table->string('TMT_JAD_Pertama', 100)->nullable();
            $table->string('Inpassing', 100)->nullable();
            $table->string('TMT_Inpassing_Akhir', 50)->nullable();
            $table->string('TMT_JAD_Akhir', 50)->nullable();

            for ($i = 1; $i <= 12; $i++) {
                $table->string('Jabatan'.$i, 15)->nullable();
            }
            for ($i = 1; $i <= 12; $i++) {
                $table->string('Gol'.$i, 5)->nullable();
            }
            for ($i = 1; $i <= 12; $i++) {
                $table->string('Tahun'.$i, 2)->nullable();
            }

            $table->string('NPWP', 100)->nullable();
            $table->string('No_Rek', 100)->nullable();
            $table->string('No_Rekening', 250)->nullable();
            $table->string('Nama_Pegawai', 250)->nullable();
            $table->string('Nama_Rekening', 250)->nullable();
            $table->string('Nama_Penerima', 250)->nullable();
            $table->string('Bank', 250)->nullable();
            $table->string('Biaya_Per_Bulan', 250)->nullable();
            $table->char('Aktif', 1)->nullable();
            $table->string('Keterangan', 255)->nullable();
            $table->string('Eligible_span', 50)->nullable();
            $table->string('Tanggal_Update_Terakhir', 100)->nullable();
            $table->string('Pemegang_Wilayah', 50)->nullable();

            for ($i = 1; $i <= 12; $i++) {
                $table->string('Gaji'.$i, 50)->nullable();
            }
            for ($i = 1; $i <= 12; $i++) {
                $table->string('KodeUsulan'.$i, 30)->nullable();
            }

            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
            foreach ($months as $m) {
                $table->string($m, 20)->nullable();
            }

            for ($i = 1; $i <= 12; $i++) {
                $table->string('TPD'.$i, 30)->nullable();
                $table->string('TKGB'.$i, 30)->nullable();
            }

            for ($i = 1; $i <= 12; $i++) {
                $table->decimal('pajakTPD'.$i, 10, 2)->nullable();
                $table->decimal('pajakTKGB'.$i, 10, 2)->nullable();
                $table->decimal('nilaiPajakTPD'.$i, 10, 0)->nullable();
                $table->decimal('nilaiPajakTKGB'.$i, 10, 0)->nullable();
                $table->decimal('bersihTPD'.$i, 10, 0)->nullable();
                $table->decimal('bersihTKGB'.$i, 10, 0)->nullable();
            }

            for ($i = 1; $i <= 12; $i++) {
                $table->string('No_sp2d_'.$i, 100)->nullable();
                $table->string('Tgl_sp2d_'.$i, 30)->nullable();
            }

            $table->string('JmlTPD_Selisih', 50)->nullable();
            $table->string('JmlTKGB_Selisih', 50)->nullable();
            $table->string('Pajak_TPD_Selisih', 20)->nullable();
            $table->string('Pajak_TKGB_Selisih', 20)->nullable();
            $table->string('Bersih_TPD_Selisih', 20)->nullable();
            $table->string('Bersih_TKGB_Selisih', 20)->nullable();
            $table->string('No_SPM_TPD', 50)->nullable();
            $table->string('No_SPM_TKGB', 50)->nullable();
            $table->string('TglTPD', 50)->nullable();
            $table->string('TglTKGB', 50)->nullable();
            $table->string('Pengguna', 20)->nullable();
            $table->string('Tahun_Versi', 50)->nullable();

            $table->timestamps();

            $table->index('NIDN');

            // Table options: latin1 and MyISAM with compressed row format
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
        });

        // Set ROW_FORMAT=COMPRESSED and AUTO_INCREMENT to match original SQL
        DB::statement("ALTER TABLE `s_transaksi_2` ROW_FORMAT=COMPRESSED;");
        DB::statement("ALTER TABLE `s_transaksi_2` AUTO_INCREMENT = 41559;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s_transaksi_2');
    }
}
