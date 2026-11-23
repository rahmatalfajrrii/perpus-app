<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

// 'members' dan 'books' diterima dari LoanController@create,
// digunakan untuk mengisi pilihan pada dua dropdown.
defineProps({
    members: Array,
    books: Array,
});

// Hanya dua isian yang dipilih petugas.
// Tanggal pinjam dan jatuh tempo diisi otomatis oleh server.
const form = useForm({
    member_id: '',
    book_id: '',
});

// Mengirim data ke route loans.store.
function submit() {
    form.post(route('loans.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Pinjam Buku</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">

                <div>
                    <label class="block">Anggota</label>

                    <!-- Dropdown pilihan anggota -->
                    <select v-model="form.member_id">
                        <option value="" disabled>
                            -- Pilih anggota --
                        </option>

                        <!-- Menampilkan nama dan NIS/NIM anggota -->
                        <option
                            v-for="member in members"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.nama }} ({{ member.nis_nim }})
                        </option>
                    </select>

                    <!-- Pesan error validasi anggota -->
                    <div v-if="form.errors.member_id" class="text-red-600">
                        {{ form.errors.member_id }}
                    </div>
                </div>

                <div>
                    <label class="block">Buku</label>

                    <!-- Dropdown pilihan buku -->
                    <select v-model="form.book_id">
                        <option value="" disabled>
                            -- Pilih buku --
                        </option>

                        <!-- Menampilkan judul dan stok buku -->
                        <option
                            v-for="book in books"
                            :key="book.id"
                            :value="book.id"
                        >
                            {{ book.judul }} (stok: {{ book.stok }})
                        </option>
                    </select>

                    <!-- Pesan error validasi buku -->
                    <div v-if="form.errors.book_id" class="text-red-600">
                        {{ form.errors.book_id }}
                    </div>

                    <!-- Pemberitahuan jika semua buku stoknya habis -->
                    <div v-if="books.length === 0" class="text-yellow-700">
                        Belum ada buku dengan stok tersedia.
                    </div>
                </div>

                <p class="text-gray-500">
                    Tanggal pinjam adalah hari ini. Jatuh tempo otomatis
                    14 hari setelah tanggal pinjam.
                </p>

                <button type="submit" :disabled="form.processing">
                    Simpan Peminjaman
                </button>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
