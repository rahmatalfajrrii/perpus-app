
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    book: Object,
});

const form = useForm({
    kode_buku: props.book.kode_buku ?? '',
    judul: props.book.judul ?? '',
    pengarang: props.book.pengarang ?? '',
    penerbit: props.book.penerbit ?? '',
    tahun: props.book.tahun ?? '',
    stok: props.book.stok ?? 0,
});

function submit() {
    form.put(route('books.update', props.book.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl p-6">
            <div class="rounded-lg bg-white p-6 shadow">
                <h1 class="mb-6 text-xl font-semibold text-gray-800">
                    Edit Buku
                </h1>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Kode Buku
                        </label>
                        <input
                            v-model="form.kode_buku"
                            type="text"
                            class="w-full rounded-md border-gray-300"
                            required
                        />
                        <p v-if="form.errors.kode_buku"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.kode_buku }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Judul
                        </label>
                        <input
                            v-model="form.judul"
                            type="text"
                            class="w-full rounded-md border-gray-300"
                            required
                        />
                        <p v-if="form.errors.judul"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.judul }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Pengarang
                        </label>
                        <input
                            v-model="form.pengarang"
                            type="text"
                            class="w-full rounded-md border-gray-300"
                            required
                        />
                        <p v-if="form.errors.pengarang"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.pengarang }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Penerbit
                        </label>
                        <input
                            v-model="form.penerbit"
                            type="text"
                            class="w-full rounded-md border-gray-300"
                        />
                        <p v-if="form.errors.penerbit"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.penerbit }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Tahun
                        </label>
                        <input
                            v-model="form.tahun"
                            type="number"
                            min="1000"
                            max="9999"
                            class="w-full rounded-md border-gray-300"
                        />
                        <p v-if="form.errors.tahun"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.tahun }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            Stok
                        </label>
                        <input
                            v-model="form.stok"
                            type="number"
                            min="0"
                            class="w-full rounded-md border-gray-300"
                            required
                        />
                        <p v-if="form.errors.stok"
                           class="mt-1 text-sm text-red-600">
                            {{ form.errors.stok }}
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Perubahan'
                            }}
                        </button>

                        <Link
                            :href="route('books.index')"
                            class="rounded-md bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300"
                        >
                            Kembali
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
