<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;

class DashboardController extends Controller
{
    /**
     * Menyiapkan angka ringkasan dan data grafik untuk halaman Dashboard.
     */
    public function index()
    {
        // ---- Bagian 1: Angka untuk kartu ringkasan ----

        // Menghitung jumlah seluruh buku.
        $totalBuku = Book::count();

        // Menghitung jumlah seluruh stok buku.
        $totalStok = (int) Book::sum('stok');

        // Menghitung jumlah seluruh anggota.
        $totalAnggota = Member::count();

        // Menghitung peminjaman yang belum dikembalikan.
        $sedangDipinjam = Loan::where('status', 'dipinjam')->count();

        // Menghitung peminjaman yang sudah melewati tanggal jatuh tempo.
        $terlambat = Loan::where('status', 'dipinjam')
            ->where('tanggal_kembali_rencana', '<', today()->toDateString())
            ->count();

        // Menghitung total denda yang tercatat di database.
        $totalDenda = (int) Loan::sum('denda');

        // ---- Bagian 2: Data grafik peminjaman 6 bulan terakhir ----

        // Nama bulan untuk label grafik.
        $namaBulan = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];

        // Menyiapkan array untuk label dan jumlah peminjaman.
        $label = [];
        $jumlah = [];

        // Mengambil data dari 5 bulan lalu hingga bulan ini.
        for ($i = 5; $i >= 0; $i--) {

            // Tanggal pertama pada bulan yang dihitung.
            $awal = today()->startOfMonth()->subMonths($i);

            // Tanggal terakhir pada bulan tersebut.
            $akhir = $awal->copy()->endOfMonth();

            // Membuat label bulan dan tahun.
            $label[] = $namaBulan[$awal->month - 1] . ' ' . $awal->year;

            // Menghitung jumlah peminjaman pada bulan tersebut.
            $jumlah[] = Loan::whereBetween('tanggal_pinjam', [
                $awal->toDateString(),
                $akhir->toDateString(),
            ])->count();
        }

        // ---- Bagian 3: Mengirim data ke Dashboard.vue ----

        return inertia('Dashboard', [
            'statistik' => [
                'total_buku' => $totalBuku,
                'total_stok' => $totalStok,
                'total_anggota' => $totalAnggota,
                'sedang_dipinjam' => $sedangDipinjam,
                'terlambat' => $terlambat,
                'total_denda' => $totalDenda,
            ],

            'grafik' => [
                'label' => $label,
                'jumlah' => $jumlah,
            ],
        ]);
    }
}
