<?php 

declare(strict_types=1);

namespace Galih\JurnalKopnus;

use Galih\JurnalKopnus\JurnalContract;

class JurnalService implements JurnalContract
{
    /**
     * Create nomor nota jurnal
     *
     * @return string
     */
    public function getNomorNota(string $kode_cabang): string 
    {
        $counter = DB::select(DB::raw("
                    UPDATE tbl_counter
                    SET counter_nota = substring('0000000',1,length('0000000')-length((counter_nota::NUMERIC+1)::character varying)) || (counter_nota::NUMERIC+1)::character varying
                    WHERE kode_cabang = '$kode_cabang'
                    RETURNING counter_nota
                "));

        $result = $kode_cabang . str_pad($counter[0]->counter_nota, 7, '0', STR_PAD_LEFT);

        Log::info('Create Nomor Nota Jurnal', [['Nomor Nota' => $result]]);
        return $result;
    }

    private function getTglEntry(): string
    {
        $result = DB::select(
            DB::raw("
                SELECT (tanggal_hari_ini::date || ' ' || to_char(now(), 'hh24:mi:ss'))::TIMESTAMP WITHOUT TIME ZONE AS tgl_entry FROM tbl_sistem
            ")
        );

        return $result[0]->tgl_entry;
    }

    public function insertTrxHarian(
        string $no_rekening,
        string $no_nota,
        int|float $jumlah,
        string $user_id,
        string $keterangan,
        string $no_perkiraan,
        string $kode_cabang_asal,
        string $kode_cabang_tujuan,
        string $jenis_dk,
        string $kode_transaksi
    ): mixed 
    {
        if (strtolower($jenis_dk) == "kredit") {
            $jumlah = $jumlah * -1;
        }

        $tgl_entry = $this->getTglEntry();

        DB::enableQueryLog();
        try {
            $insert_trx = DB::table('tbl_transaksi_harian')
                            ->insert([
                                'no_rekening'       => $no_rekening,
                                'no_nota'           => $no_nota,
                                'kode_transaksi'    => $kode_transaksi,
                                'jumlah_transaksi'  => $jumlah,
                                'waktu_transaksi'   => "now()",
                                'user_id'           => $user_id,
                                'user_id_supervisor'=> $user_id,
                                'keterangan'        => $keterangan,
                                'no_perkiraan'      => $no_perkiraan,
                                'jenis_rekening'    => "1",
                                'kode_cabang_asal'  => $kode_cabang_asal,
                                'kode_cabang_tujuan'=> $kode_cabang_tujuan,
                                'tgl_entry'         => $tgl_entry,
                                'flag_rekening'     => "1",
                                'jumlah_valas'      => "0",
                                'kurs'              => "0",
                                'kode_mata_uang'    => "001",
                                'no_nasabah'        => "-",
                            ]);

            $this->updateMasterPerkiraan($jenis_dk, $jumlah, $kode_cabang_tujuan, $no_perkiraan);

            return $insert_trx;
        } catch (\Exception $e) {
            Log::error('Insert Transaksi Harian] error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    private function updateMasterPerkiraan(string $jenis_dk, string|int|float $jumlah_trx, string $kode_cabang, string $no_perkiraan): mixed
    {
        DB::enableQueryLog();

        try {
            $update = DB::table('tbl_master_perkiraan')
                        ->where('kode_cabang', '=', $kode_cabang)
                        ->where('no_perkiraan', '=', $no_perkiraan)
                        ->update([
                            'tgl_trans_terakhir' => DB::raw('now()'),
                            'saldo_akhir'        => DB::raw('cast(saldo_akhir as decimal(20,2)) + ' . $jumlah_trx),
                            'mutasi_'.$jenis_dk  => DB::raw('cast(mutasi_'. $jenis_dk .' as decimal(20,2)) + ' . $jumlah_trx)
                        ]);

            return $update;
        } catch (\Exception $e) {
            Log::error('[Update Master Perkiraan] error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }
}