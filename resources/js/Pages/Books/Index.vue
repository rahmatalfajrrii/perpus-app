<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    books: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-800">Data Buku</h1>
                <Link
                    href="/books/create"
                    class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                >
                    + Tambah Buku
                </Link>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Pengarang</th>
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="book in books.data" :key="book.id">
                            <td class="px-4 py-3">{{ book.kode_buku }}</td>
                            <td class="px-4 py-3">{{ book.judul }}</td>
                            <td class="px-4 py-3">{{ book.pengarang }}</td>
                            <td class="px-4 py-3">{{ book.tahun }}</td>
                            <td class="px-4 py-3">{{ book.stok }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2 text-sm">
                <template v-for="link in books.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded border bg-white px-3 py-1 hover:bg-gray-100"
                        :class="{ 'bg-gray-800 text-white hover:bg-gray-800': link.active }"
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