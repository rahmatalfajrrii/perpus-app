<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Data anggota diterima dari MemberController@edit
const props = defineProps({
    member: Object,
});

// Form edit anggota
const form = useForm({
    _method: 'put',
    nama: props.member.nama,
    nis_nim: props.member.nis_nim,
    no_hp: props.member.no_hp,
    foto: null,
});

// Alamat sementara gambar untuk pratinjau
const pratinjau = ref(null);

// Memilih foto baru
function pilihFoto(event) {
    const berkas = event.target.files[0];

    // Hapus URL pratinjau sebelumnya
    if (pratinjau.value) {
        URL.revokeObjectURL(pratinjau.value);
    }

    form.foto = berkas ?? null;
    pratinjau.value = berkas ? URL.createObjectURL(berkas) : null;
}

// ---------- Validasi realtime NIS/NIM ----------

// Status: '' (belum diperiksa), 'memeriksa', 'tersedia',
// 'terpakai', atau 'gagal'
const statusNis = ref('');

// Timer debounce dan pengendali permintaan Axios
let timerNis = null;
let permintaanNis = null;

// Memeriksa apakah NIS/NIM sudah digunakan anggota lain
async function periksaNis() {
    // Batalkan permintaan sebelumnya jika masih berjalan
    if (permintaanNis) {
        permintaanNis.abort();
    }

    const controller = new AbortController();
    permintaanNis = controller;

    statusNis.value = 'memeriksa';

    try {
        const respons = await axios.get(route('members.cek-nis'), {
            params: {
                nis_nim: form.nis_nim.trim(),

                // Abaikan ID anggota yang sedang diedit
                // agar NIS/NIM miliknya sendiri tidak dianggap terpakai
                abaikan: props.member.id,
            },
            signal: controller.signal,
        });

        // Abaikan respons lama jika sudah ada permintaan baru
        if (permintaanNis !== controller) {
            return;
        }

        statusNis.value = respons.data.tersedia
            ? 'tersedia'
            : 'terpakai';

    } catch (error) {
        // Pembatalan permintaan bukan kesalahan
        if (controller.signal.aborted || axios.isCancel(error)) {
            return;
        }

        if (permintaanNis === controller) {
            statusNis.value = 'gagal';
        }
    } finally {
        if (permintaanNis === controller) {
            permintaanNis = null;
        }
    }
}

// Dijalankan setiap kali isi kolom NIS/NIM berubah
watch(() => form.nis_nim, (nilai) => {
    clearTimeout(timerNis);

    // Batalkan permintaan sebelumnya
    if (permintaanNis) {
        permintaanNis.abort();
        permintaanNis = null;
    }

    // Jika kolom kosong, hapus pesan pemeriksaan
    if (!nilai || nilai.trim() === '') {
        statusNis.value = '';
        return;
    }

    // Tunggu 500 milidetik setelah ketikan terakhir
    timerNis = setTimeout(periksaNis, 500);
});

// Mengirim perubahan anggota ke server
function submit() {
    // Menggunakan POST dengan _method: put
    // agar upload foto dapat diproses Laravel
    form.post(route('members.update', props.member.id));
}

// Membersihkan timer, permintaan, dan URL pratinjau
onBeforeUnmount(() => {
    clearTimeout(timerNis);

    if (permintaanNis) {
        permintaanNis.abort();
        permintaanNis = null;
    }

    if (pratinjau.value) {
        URL.revokeObjectURL(pratinjau.value);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Edit Anggota</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">

                <!-- Nama -->
                <div>
                    <label class="block">Nama</label>

                    <input
                        v-model="form.nama"
                        type="text"
                    >

                    <div v-if="form.errors.nama" class="text-red-600">
                        {{ form.errors.nama }}
                    </div>
                </div>

                <!-- NIS/NIM -->
                <div>
                    <label class="block">NIS/NIM</label>

                    <input
                        v-model="form.nis_nim"
                        type="text"
                    >

                    <!-- Pesan hasil pemeriksaan realtime -->
                    <div
                        v-if="statusNis === 'memeriksa'"
                        class="text-gray-500"
                    >
                        Memeriksa NIS/NIM…
                    </div>

                    <div
                        v-else-if="statusNis === 'tersedia'"
                        class="text-green-700"
                    >
                        NIS/NIM ini tersedia.
                    </div>

                    <div
                        v-else-if="statusNis === 'terpakai'"
                        class="text-red-600"
                    >
                        NIS/NIM ini sudah terdaftar. Gunakan nomor lain.
                    </div>

                    <div
                        v-else-if="statusNis === 'gagal'"
                        class="text-yellow-700"
                    >
                        Pemeriksaan otomatis gagal. Anda tetap dapat menyimpan;
                        server akan memeriksa ulang.
                    </div>

                    <div v-if="form.errors.nis_nim" class="text-red-600">
                        {{ form.errors.nis_nim }}
                    </div>
                </div>

                <!-- Nomor HP -->
                <div>
                    <label class="block">No. HP</label>

                    <input
                        v-model="form.no_hp"
                        type="text"
                    >

                    <div v-if="form.errors.no_hp" class="text-red-600">
                        {{ form.errors.no_hp }}
                    </div>
                </div>

                <!-- Foto saat ini -->
                <div>
                    <label class="block">Foto Saat Ini</label>

                    <div v-if="props.member.foto" class="mt-2">
                        <img
                            :src="`/storage/${props.member.foto}`"
                            alt="Foto anggota saat ini"
                            class="w-32 h-32 object-cover rounded"
                        >
                    </div>

                    <div v-else class="text-gray-500">
                        Belum ada foto.
                    </div>
                </div>

                <!-- Ganti foto -->
                <div>
                    <label class="block">
                        Ganti Foto (kosongkan jika tidak ingin mengganti)
                    </label>

                    <input
                        type="file"
                        accept="image/jpeg,image/png"
                        @change="pilihFoto"
                    >

                    <div v-if="form.errors.foto" class="text-red-600">
                        {{ form.errors.foto }}
                    </div>

                    <!-- Pratinjau foto baru -->
                    <div v-if="pratinjau" class="mt-3">
                        <p class="mb-2">Pratinjau:</p>

                        <img
                            :src="pratinjau"
                            alt="Pratinjau foto baru"
                            class="w-32 h-32 object-cover rounded"
                        >
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="flex gap-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
