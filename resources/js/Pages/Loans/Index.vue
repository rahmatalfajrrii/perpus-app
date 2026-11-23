
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    loans: Object,
});

// Status untuk mencegah tombol ditekan berulang kali.
const memproses = ref(false);

// Mengubah angka menjadi format Rupiah.
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(angka ?? 0);
}

// Memproses pengembalian buku.
function kembalikan(loan) {
    if (!confirm('Yakin ingin mengembalikan buku ini?')) {
        return;
    }

    // Kirim permintaan PATCH ke route loans.kembalikan.
    router.patch(route('loans.kembalikan', loan.id), {}, {
        // Posisi scroll halaman tidak kembali ke atas.
        preserveScroll: true,

        // Nonaktifkan tombol ketika permintaan dimulai.
        onStart: () => {
            memproses.value = true;
        },

        // Aktifkan kembali tombol setelah permintaan selesai.
        onFinish: () => {
            memproses.value = false;
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Peminjaman</h1>

            <!-- Pesan sukses dari session -->
            <div
                v-if="$page.props.flash?.success"
                class="my-2 text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Pesan error dari controller -->
            <div
                v-if="$page.props.errors.loan"
                class="my-2 text-red-600"
            >
                {{ $page.props.errors.loan }}
            </div>

            <div class="my-3">
                <!-- Link menuju form peminjaman -->
                <Link href="/loans/create">+ Pinjam Buku</Link>
            </div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Anggota</th>
                        <th class="border px-3 py-2">Buku</th>
                        <th class="border px-3 py-2">Tanggal Pinjam</th>
                        <th class="border px-3 py-2">Jatuh Tempo</th>
                        <th class="border px-3 py-2">Tanggal Kembali</th>
                        <th class="border px-3 py-2">Status</th>
                        <th class="border px-3 py-2">Denda</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Satu baris untuk setiap peminjaman -->
                    <tr
                        v-for="loan in loans.data"
                        :key="loan.id"
                    >
                        <td class="border px-3 py-2">
                            {{ loan.member?.nama ?? 'Anggota tidak ditemukan' }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ loan.book?.judul ?? 'Buku tidak ditemukan' }}
                        </td>

                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_pinjam }}
                        </td>

                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_kembali_rencana }}
                        </td>

                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_kembali || '-' }}
                        </td>

                        <td class="border px-3 py-2">
                            <span
                                :class="loan.status === 'dipinjam'
                                    ? 'text-yellow-700'
                                    : 'text-green-700'"
                            >
                                {{ loan.status }}
                            </span>
                        </td>

                        <td class="border px-3 py-2">
                            <!-- Buku sudah dikembalikan -->
                            <span v-if="loan.status === 'dikembalikan'">
                                {{ formatRupiah(loan.denda) }}
                            </span>

                            <!-- Buku masih dipinjam dan terlambat -->
                            <span
                                v-else-if="loan.hari_terlambat > 0"
                                class="text-red-600"
                            >
                                Terlambat {{ loan.hari_terlambat }} hari
                                ({{ formatRupiah(loan.perkiraan_denda) }})
                            </span>

                            <!-- Belum terlambat -->
                            <span v-else>-</span>
                        </td>

                        <td class="border px-3 py-2">
                            <!-- Tombol hanya muncul jika belum dikembalikan -->
                            <button
                                v-if="loan.status === 'dipinjam'"
                                type="button"
                                :disabled="memproses"
                                class="text-blue-700 underline"
                                @click="kembalikan(loan)"
                            >
                                Kembalikan
                            </button>

                            <span v-else>-</span>
                        </td>
                    </tr>

                    <!-- Pesan jika belum ada data -->
                    <tr v-if="loans.data.length === 0">
                        <td colspan="8" class="border px-3 py-2">
                            Belum ada data peminjaman.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tautan pagination -->
            <div class="mt-3 flex gap-3">
                <template
                    v-for="link in loans.links"
                    :key="link.label"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                    />

                    <span
                        v-else
                        v-html="link.label"
                        class="text-gray-400"
                    />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
