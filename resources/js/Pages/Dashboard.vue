
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// BarChart = komponen grafik yang baru dibuat (Components/BarChart.vue)
import BarChart from '@/Components/BarChart.vue';
import { Head } from '@inertiajs/vue3';

// Data dari DashboardController@index:
// statistik = angka-angka kartu, grafik = label dan jumlah per bulan
defineProps({
    statistik: Object,
    grafik: Object,
});

// Mengubah angka 5000 menjadi teks "Rp5.000"
function formatRupiah(angka) {
    return 'Rp' + Number(angka).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="p-4">
            <!-- Kotak kartu: 1 kolom di layar kecil, 2 kolom di layar sedang, 4 kolom di layar besar. -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <!-- KARTU 1: Judul Buku -->
                <div
                    class="rounded-lg border-l-4 border-blue-500 bg-white p-5 shadow-sm"
                >
                    <div class="text-sm text-gray-500">Judul Buku</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.total_buku }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Total stok: {{ statistik.total_stok }} eksemplar
                    </div>
                </div>

                <!-- KARTU 2: Anggota -->
                <div
                    class="rounded-lg border-l-4 border-green-500 bg-white p-5 shadow-sm"
                >
                    <div class="text-sm text-gray-500">Anggota</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.total_anggota }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Terdaftar di perpustakaan
                    </div>
                </div>

                <!-- KARTU 3: Sedang Dipinjam -->
                <div
                    class="rounded-lg border-l-4 border-yellow-500 bg-white p-5 shadow-sm"
                >
                    <div class="text-sm text-gray-500">Sedang Dipinjam</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.sedang_dipinjam }}
                    </div>

                    <!-- Merah jika ada yang terlambat, abu-abu jika tidak. -->
                    <div
                        class="text-xs"
                        :class="statistik.terlambat > 0
                            ? 'text-red-600'
                            : 'text-gray-500'"
                    >
                        {{ statistik.terlambat }} terlambat
                    </div>
                </div>

                <!-- KARTU 4: Total Denda -->
                <div
                    class="rounded-lg border-l-4 border-red-500 bg-white p-5 shadow-sm"
                >
                    <div class="text-sm text-gray-500">Total Denda</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ formatRupiah(statistik.total_denda) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Dari buku yang sudah dikembalikan
                    </div>
                </div>
            </div>

            <!-- Grafik Peminjaman -->
            <div class="mt-6 rounded-lg bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    Peminjaman 6 Bulan Terakhir
                </h3>

                <BarChart
                    :label="grafik.label"
                    :jumlah="grafik.jumlah"
                    judul="Jumlah Peminjaman"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
