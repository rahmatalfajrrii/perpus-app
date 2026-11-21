<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Menerima data dari MemberController@index
const props = defineProps({
    members: Object,
    search: String,
});

// Isi kotak pencarian
const keyword = ref(props.search ?? '');

// Hasil pencarian dari server
const hasilCari = ref(null);
const sedangMencari = ref(false);
const pesanError = ref('');

// Timer debounce dan pembatal permintaan Axios
let timer = null;
let permintaan = null;

// Data yang ditampilkan di tabel
const daftarAnggota = computed(() =>
    hasilCari.value ?? props.members.data
);

// Mengirim permintaan AJAX ke /members/search
async function ambilHasil() {
    // Batalkan permintaan sebelumnya
    if (permintaan) {
        permintaan.abort();
    }

    const controller = new AbortController();
    permintaan = controller;

    sedangMencari.value = true;
    pesanError.value = '';

    try {
        const respons = await axios.get(route('members.search'), {
            params: {
                q: keyword.value.trim()
            },
            signal: controller.signal,
        });

        // Pastikan respons berasal dari permintaan terbaru
        if (permintaan !== controller) {
            return;
        }

        hasilCari.value = respons.data;
        sedangMencari.value = false;

    } catch (error) {
        // Permintaan yang sengaja dibatalkan bukan kesalahan
        if (
            axios.isCancel(error) ||
            error.code === 'ERR_CANCELED'
        ) {
            return;
        }

        if (permintaan === controller) {
            sedangMencari.value = false;
            pesanError.value =
                'Pencarian gagal. Muat ulang halaman, lalu coba lagi.';
        }
    }
}

// Pencarian otomatis setiap kali isi kotak berubah
watch(keyword, (nilai) => {
    clearTimeout(timer);

    // Jika kotak pencarian dikosongkan
    if (nilai.trim() === '') {
        if (permintaan) {
            permintaan.abort();
            permintaan = null;
        }

        hasilCari.value = null;
        sedangMencari.value = false;
        pesanError.value = '';

        return;
    }

    // Tunggu 300 milidetik sebelum mengirim permintaan
    timer = setTimeout(ambilHasil, 300);
});

// Menghapus data anggota
function hapus(id) {
    if (confirm('Yakin ingin menghapus anggota ini?')) {
        router.delete(route('members.destroy', id), {
            onSuccess: () => {
                if (keyword.value.trim() !== '') {
                    ambilHasil();
                }
            },
        });
    }
}

// Membersihkan timer dan permintaan saat halaman ditinggalkan
onBeforeUnmount(() => {
    clearTimeout(timer);

    if (permintaan) {
        permintaan.abort();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">

            <h1 class="text-xl font-bold">Data Anggota</h1>

            <!-- Flash message -->
            <div
                v-if="$page.props.flash?.success"
                class="my-2 text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Tombol tambah dan pencarian -->
            <div class="my-3 flex items-center gap-3">

                <Link href="/members/create">
                    + Tambah Anggota
                </Link>

                <!-- Pencarian otomatis melalui watch(keyword) -->
                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Ketik nama atau NIS/NIM..."
                >

                <!-- Penanda pencarian -->
                <span
                    v-if="sedangMencari"
                    class="text-gray-500"
                >
                    Mencari...
                </span>

            </div>

            <!-- Pesan error -->
            <div
                v-if="pesanError"
                class="my-2 text-red-600"
            >
                {{ pesanError }}
            </div>

            <!-- Tabel data anggota -->
            <table class="border-collapse">

                <thead>
                    <tr>
                        <th class="border px-3 py-2">Foto</th>
                        <th class="border px-3 py-2">NIS/NIM</th>
                        <th class="border px-3 py-2">Nama</th>
                        <th class="border px-3 py-2">No. HP</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- Menampilkan hasil pencarian atau daftar bawaan -->
                    <tr
                        v-for="member in daftarAnggota"
                        :key="member.id"
                    >
                        <td class="border px-3 py-2">

                            <!-- Foto anggota -->
                            <img
                                v-if="member.foto"
                                :src="`/storage/${member.foto}`"
                                alt="Foto anggota"
                                class="h-16 w-16 object-cover"
                            >

                            <span v-else>-</span>

                        </td>

                        <td class="border px-3 py-2">
                            {{ member.nis_nim }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ member.nama }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ member.no_hp }}
                        </td>

                        <td class="border px-3 py-2">

                            <Link
                                :href="`/members/${member.id}/edit`"
                                class="mr-3"
                            >
                                Edit
                            </Link>

                            <button @click="hapus(member.id)">
                                Hapus
                            </button>

                        </td>
                    </tr>

                    <!-- Jika tidak ada data -->
                    <tr v-if="daftarAnggota.length === 0">
                        <td
                            colspan="5"
                            class="border px-3 py-2"
                        >
                            {{
                                keyword.trim() !== ''
                                    ? 'Tidak ada anggota yang ditemukan.'
                                    : 'Belum ada data anggota.'
                            }}
                        </td>
                    </tr>

                </tbody>

            </table>

            <!-- Pagination hanya untuk daftar bawaan -->
            <div
                v-if="hasilCari === null"
                class="mt-3 flex gap-3"
            >
                <template
                    v-for="(link, index) in props.members.links"
                    :key="index"
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
