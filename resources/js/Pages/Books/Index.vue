
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    books: Object,
    search: {
        type: String,
        default: '',
    },
    flash: {
        type: Object,
        default: () => ({
            success: null,
            error: null,
        }),
    },
});

// Menyimpan kata kunci pencarian
const keyword = ref(props.search ?? '');

// Memperbarui kotak pencarian ketika props search berubah
watch(
    () => props.search,
    (value) => {
        keyword.value = value ?? '';
    }
);

// Fungsi pencarian buku
function cari() {
    router.get(
        route('books.index'),
        {
            search: keyword.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}

// Fungsi menghapus buku
function hapus(id) {
    if (confirm('Yakin ingin menghapus buku ini?')) {
        router.delete(route('books.destroy', id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="mb-4 text-xl font-semibold text-gray-800">
                Data Buku
            </h1>

            <!-- Flash message sukses -->
            <div
                v-if="flash?.success"
                class="mb-4 rounded-md border border-green-300 bg-green-100 px-4 py-3 text-green-800"
            >
                {{ flash.success }}
            </div>

            <!-- Flash message error -->
            <div
                v-if="flash?.error"
                class="mb-4 rounded-md border border-red-300 bg-red-100 px-4 py-3 text-red-800"
            >
                {{ flash.error }}
            </div>

            <!-- Tombol tambah buku -->
            <div class="mb-4 flex justify-end">
                <Link
                    :href="route('books.create')"
                    class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                >
                    + Tambah Buku
                </Link>
            </div>

            <!-- Form pencarian -->
            <form
                @submit.prevent="cari"
                class="mb-4 flex flex-wrap gap-2"
            >
                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Cari kode, judul, atau pengarang..."
                    class="w-full max-w-md rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                />

                <button
                    type="submit"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Cari
                </button>

                <Link
                    v-if="keyword"
                    :href="route('books.index')"
                    class="rounded-md bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"
                >
                    Reset
                </Link>
            </form>

            <!-- Tabel data buku -->
            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm"
                >
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Pengarang</th>
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="book in books.data"
                            :key="book.id"
                        >
                            <td class="px-4 py-3">
                                {{ book.kode_buku }}
                            </td>

                            <td class="px-4 py-3">
                                {{ book.judul }}
                            </td>

                            <td class="px-4 py-3">
                                {{ book.pengarang }}
                            </td>

                            <td class="px-4 py-3">
                                {{ book.tahun }}
                            </td>

                            <td class="px-4 py-3">
                                {{ book.stok }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <!-- Tombol edit -->
                                    <Link
                                        :href="route('books.edit', book.id)"
                                        class="rounded-md bg-yellow-500 px-3 py-1.5 text-white hover:bg-yellow-600"
                                    >
                                        Edit
                                    </Link>

                                    <!-- Tombol hapus -->
                                    <button
                                        type="button"
                                        @click="hapus(book.id)"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-white hover:bg-red-700"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Jika data kosong -->
                        <tr v-if="books.data.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-4 text-center text-gray-500"
                            >
                                Data buku tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <template
                    v-for="link in books.links"
                    :key="link.label"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded border bg-white px-3 py-1 hover:bg-gray-100"
                        :class="{
                            'bg-gray-800 text-white hover:bg-gray-800':
                                link.active
                        }"
                    />

                    <span
                        v-else
                        v-html="link.label"
                        class="rounded border bg-gray-100 px-3 py-1 text-gray-400"
                    />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
