<script setup>
// Mengambil layout dashboard yang sudah dibuat pada Pertemuan 5
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Menerima data dari BookController@index
const props = defineProps({
    books: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
    search: {
        type: String,
        default: '',
    },
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
// Jika data server belum tersedia, gunakan array kosong
const daftarBuku = computed(() => {
    return hasilCari.value ?? props.books?.data ?? [];
});

// Mengirim permintaan AJAX ke /books/search
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
        const respons = await axios.get(route('books.search'), {
            params: {
                q: keyword.value.trim(),
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
    } finally {
        if (permintaan === controller) {
            permintaan = null;
        }
    }
}

// Pencarian otomatis setiap kali isi kotak berubah
watch(keyword, (nilai) => {
    clearTimeout(timer);

    // Batalkan permintaan sebelumnya
    if (permintaan) {
        permintaan.abort();
        permintaan = null;
    }

    // Jika kotak pencarian dikosongkan
    if (nilai.trim() === '') {
        hasilCari.value = null;
        sedangMencari.value = false;
        pesanError.value = '';

        return;
    }

    // Tunggu 800 milidetik sebelum mengirim permintaan
    timer = setTimeout(ambilHasil, 800);
});

// Menghapus data buku
function hapus(id) {
    if (confirm('Yakin ingin menghapus buku ini?')) {
        router.delete(route('books.destroy', id), {
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
        permintaan = null;
    }
});
</script>


<template>
    <AuthenticatedLayout>
        <!-- Seluruh isi dibungkus div p-4 -->
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Buku</h1>

            <!-- Flash message -->
            <div
                v-if="$page.props.flash?.success"
                class="my-2 text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <div class="my-3 flex items-center gap-3">
                <!-- Link menuju form tambah buku -->
                <Link href="/books/create">+ Tambah Buku</Link>

                <!-- Pencarian otomatis melalui watch(keyword) -->
                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Ketik judul atau pengarang..."
                >

                <!-- Penanda AJAX sedang berjalan -->
                <span v-if="sedangMencari" class="text-gray-500">
                    Mencari...
                </span>
            </div>

            <!-- Pesan jika permintaan AJAX gagal -->
            <div v-if="pesanError" class="my-2 text-red-600">
                {{ pesanError }}
            </div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Kode</th>
                        <th class="border px-3 py-2">Judul</th>
                        <th class="border px-3 py-2">Pengarang</th>
                        <th class="border px-3 py-2">Stok</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Menampilkan hasil pencarian atau daftar bawaan -->
                    <tr
                        v-for="book in daftarBuku"
                        :key="book.id"
                    >
                        <td class="border px-3 py-2">
                            {{ book.kode_buku }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ book.judul }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ book.pengarang }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ book.stok }}
                        </td>

                        <td class="border px-3 py-2">
                            <!-- Link menuju form edit -->
                            <Link
                                :href="`/books/${book.id}/edit`"
                                class="mr-3"
                            >
                                Edit
                            </Link>

                            <!-- Tombol hapus buku -->
                            <button @click="hapus(book.id)">
                                Hapus
                            </button>
                        </td>
                    </tr>

                    <!-- Pesan jika tidak ada data -->
                    <tr v-if="daftarBuku.length === 0">
                        <td colspan="5" class="border px-3 py-2">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginasi hanya untuk daftar bawaan -->
            <div
                v-if="hasilCari === null"
                class="mt-3 flex gap-3"
            >
                <template
                    v-for="link in books.links"
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
