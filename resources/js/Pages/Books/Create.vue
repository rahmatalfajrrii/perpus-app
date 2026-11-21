<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const form = useForm({
    kode_buku: '',
    judul: '',
    pengarang: '',
    penerbit: '',
    tahun: '',
    stok: 0,
});

// ---------- Validasi realtime kode buku ----------

// Status: '' (belum diperiksa), 'memeriksa',
// 'tersedia', 'terpakai', atau 'gagal'
const statusKode = ref('');

// Timer debounce dan pengendali permintaan Axios
let timerKode = null;
let permintaanKode = null;

// Memeriksa apakah kode buku sudah digunakan
async function periksaKode() {
    // Batalkan permintaan sebelumnya
    if (permintaanKode) {
        permintaanKode.abort();
    }

    const controller = new AbortController();
    permintaanKode = controller;

    statusKode.value = 'memeriksa';

    try {
        const respons = await axios.get(route('books.cek-kode'), {
            params: {
                kode_buku: form.kode_buku.trim(),
            },
            signal: controller.signal,
        });

        // Pastikan respons berasal dari permintaan terbaru
        if (permintaanKode !== controller) {
            return;
        }

        statusKode.value = respons.data.tersedia
            ? 'tersedia'
            : 'terpakai';

    } catch (error) {
        // Pembatalan permintaan bukan kesalahan
        if (
            controller.signal.aborted ||
            axios.isCancel(error) ||
            error.code === 'ERR_CANCELED'
        ) {
            return;
        }

        if (permintaanKode === controller) {
            statusKode.value = 'gagal';
        }
    } finally {
        if (permintaanKode === controller) {
            permintaanKode = null;
        }
    }
}

// Memeriksa kode setiap kali pengguna mengetik
watch(() => form.kode_buku, (nilai) => {
    clearTimeout(timerKode);

    // Batalkan permintaan sebelumnya
    if (permintaanKode) {
        permintaanKode.abort();
        permintaanKode = null;
    }

    // Jika kolom kosong, hapus pesan pemeriksaan
    if (!nilai || nilai.trim() === '') {
        statusKode.value = '';
        return;
    }

    // Tunggu 500 milidetik setelah ketikan terakhir
    timerKode = setTimeout(periksaKode, 500);
});

// Membersihkan timer dan permintaan saat halaman ditinggalkan
onBeforeUnmount(() => {
    clearTimeout(timerKode);

    if (permintaanKode) {
        permintaanKode.abort();
        permintaanKode = null;
    }
});

// Menyimpan buku
function submit() {
    form.post(route('books.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <h1>Tambah Buku</h1>

        <form @submit.prevent="submit">
            <label>Kode Buku</label>
            <input v-model="form.kode_buku" type="text">

            <!-- Pesan hasil pemeriksaan realtime -->
            <div
                v-if="statusKode === 'memeriksa'"
                class="text-gray-500"
            >
                Memeriksa kode buku...
            </div>

            <div
                v-else-if="statusKode === 'tersedia'"
                class="text-green-700"
            >
                Kode buku ini tersedia.
            </div>

            <div
                v-else-if="statusKode === 'terpakai'"
                class="text-red-600"
            >
                Kode buku ini sudah terdaftar. Gunakan kode lain.
            </div>

            <div
                v-else-if="statusKode === 'gagal'"
                class="text-yellow-700"
            >
                Pemeriksaan otomatis gagal. Anda tetap dapat menyimpan;
                server akan memeriksa ulang.
            </div>

            <span v-if="form.errors.kode_buku" class="text-red-600">
                {{ form.errors.kode_buku }}
            </span>

            <label>Judul</label>
            <input v-model="form.judul" type="text">
            <span v-if="form.errors.judul">{{ form.errors.judul }}</span>

            <label>Pengarang</label>
            <input v-model="form.pengarang" type="text">
            <span v-if="form.errors.pengarang">{{ form.errors.pengarang }}</span>

            <label>Penerbit</label>
            <input v-model="form.penerbit" type="text">
            <span v-if="form.errors.penerbit">{{ form.errors.penerbit }}</span>

            <label>Tahun</label>
            <input v-model="form.tahun" type="text">
            <span v-if="form.errors.tahun">{{ form.errors.tahun }}</span>

            <label>Stok</label>
            <input v-model="form.stok" type="number">
            <span v-if="form.errors.stok">{{ form.errors.stok }}</span>

            <button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </form>
    </AuthenticatedLayout>
</template>
