<?php declare(strict_types=1); 

namespace Galih\JurnalKopnus;

interface JurnalContract 
{
    public function getNomorNota(string $kode_cabang): string;

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
    ): mixed;
}
