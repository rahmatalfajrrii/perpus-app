<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    // Mengaktifkan fitur factory.
    use HasFactory;

    // Denda Rp1.000 untuk setiap hari keterlambatan.
    public const DENDA_PER_HARI = 2000;

    // Kolom yang boleh diisi melalui create() dan update().
    protected $fillable = [
        'book_id',
        'member_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali',
        'status',
        'denda',
    ];

    // Atribut virtual yang ikut dikirim ke Vue dalam bentuk JSON.
    protected $appends = [
        'hari_terlambat',
        'perkiraan_denda',
    ];

    // Relasi peminjaman dengan buku.
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Relasi peminjaman dengan anggota.
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Menghitung jumlah hari keterlambatan.
     * Dipanggil melalui $loan->hari_terlambat.
     */
    protected function hariTerlambat(): Attribute
    {
        return Attribute::make(get: function () {

            // Mengubah tanggal jatuh tempo menjadi objek Carbon.
            $jatuhTempo = Carbon::parse($this->tanggal_kembali_rencana)
                ->startOfDay();

            // Jika sudah dikembalikan, gunakan tanggal kembali sebenarnya.
            // Jika belum, gunakan tanggal hari ini.
            $acuan = $this->tanggal_kembali
                ? Carbon::parse($this->tanggal_kembali)->startOfDay()
                : today();

            // Jika belum melewati jatuh tempo, tidak ada keterlambatan.
            if ($acuan->lessThanOrEqualTo($jatuhTempo)) {
                return 0;
            }

            // Menghitung selisih hari keterlambatan.
            return (int) $jatuhTempo->diffInDays($acuan);
        });
    }

    /**
     * Menghitung perkiraan denda.
     * Rumus: hari terlambat x tarif denda per hari.
     */
    protected function perkiraanDenda(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hari_terlambat * self::DENDA_PER_HARI
        );
    }
}
