<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onBeforeUnmount } from 'vue';

const form = useForm({
    nama: '',
    nis_nim: '',
    no_hp: '',
    foto: null, // berisi berkas (File) yang dipilih, bukan teks
});

// Alamat sementara gambar di browser, hanya untuk pratinjau sebelum disimpan
const pratinjau = ref(null);

// Menyimpan URL pratinjau sebelumnya agar bisa dibersihkan
let urlSebelumnya = null;

// Dipanggil saat pengguna memilih berkas pada <input type="file">
function pilihFoto(event) {
    const berkas = event.target.files[0];

    form.foto = berkas ?? null;

    // Membersihkan URL pratinjau sebelumnya
    if (urlSebelumnya) {
        URL.revokeObjectURL(urlSebelumnya);
        urlSebelumnya = null;
    }

    pratinjau.value = berkas ? URL.createObjectURL(berkas) : null;
    urlSebelumnya = pratinjau.value;
}

// Membersihkan URL sementara ketika komponen dihancurkan
onBeforeUnmount(() => {
    if (urlSebelumnya) {
        URL.revokeObjectURL(urlSebelumnya);
    }
});

function submit() {
    // Karena form.foto berisi berkas, Inertia otomatis mengirim sebagai multipart/form-data
    form.post(route('members.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Tambah Anggota</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">

                <div>
                    <label class="block">Nama</label>
                    <input v-model="form.nama" type="text">

                    <div v-if="form.errors.nama" class="text-red-600">
                        {{ form.errors.nama }}
                    </div>
                </div>

                <div>
                    <label class="block">NIS/NIM</label>
                    <input v-model="form.nis_nim" type="text">

                    <div v-if="form.errors.nis_nim" class="text-red-600">
                        {{ form.errors.nis_nim }}
                    </div>
                </div>

                <div>
                    <label class="block">No. HP</label>
                    <input v-model="form.no_hp" type="text">

                    <div v-if="form.errors.no_hp" class="text-red-600">
                        {{ form.errors.no_hp }}
                    </div>
                </div>

                <div>
                    <label class="block">Foto (JPG/PNG, maksimal 2 MB)</label>
                    <input
                        type="file"
                        accept="image/jpeg,image/png"
                        @change="pilihFoto"
                    >

                    <div v-if="form.errors.foto" class="text-red-600">
                        {{ form.errors.foto }}
                    </div>
                </div>

                <div v-if="pratinjau">
                    <p class="block">Pratinjau foto</p>

                    <img
                        :src="pratinjau"
                        alt="Pratinjau foto anggota"
                        class="w-32 h-32 object-cover rounded"
                    >
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
