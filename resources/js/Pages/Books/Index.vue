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
        <div class="container-fluid py-4">

            <h1>Data Buku</h1>

            <!-- Flash message -->
            <div
                v-if="$page.props.flash?.success"
                class="alert alert-success"
                style="color: red; margin-bottom: 10px;"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Tombol tambah buku -->
            <Link href="/books/create">
                + Tambah Buku
            </Link>

            <!-- Kotak pencarian -->
            <div class="my-3">
                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Ketik judul, pengarang, atau penerbit"
                >

                <span v-if="sedangMencari">
                    Mencari...
                </span>
            </div>

            <!-- Pesan error -->
            <p v-if="pesanError">
                {{ pesanError }}
            </p>

            <!-- Tabel data buku -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Kode Buku</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="book in daftarBuku"
                        :key="book.id"
                    >
                        <td>{{ book.kode_buku }}</td>
                        <td>{{ book.judul }}</td>
                        <td>{{ book.pengarang }}</td>
                        <td>{{ book.stok }}</td>

                        <td>
                            <Link
                                :href="route('books.edit', book.id)"
                                class="btn btn-warning btn-sm"
                            >
                                Edit
                            </Link>

                            <button
                                type="button"
                                class="btn btn-danger btn-sm"
                                @click="hapus(book.id)"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>

                    <!-- Jika tidak ada data -->
                    <tr v-if="daftarBuku.length === 0">
                        <td colspan="5" class="text-center">
                            Tidak ada buku yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination hanya untuk daftar bawaan -->
            <div
                v-if="hasilCari === null && props.books?.links"
                class="mt-3"
            >
                <Link
                    v-for="(link, index) in props.books.links"
                    :key="index"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="btn btn-sm mx-1"
                    :class="{
                        'btn-primary': link.active,
                        'btn-outline-secondary': !link.active,
                        'disabled': !link.url
                    }"
                    :disabled="!link.url"
                />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
