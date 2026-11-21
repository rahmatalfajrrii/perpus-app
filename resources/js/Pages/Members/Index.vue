<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    members: Object,
    search: String,
});

// Nilai awal kotak pencarian diisi dari props.search
// agar kata kunci tidak hilang saat halaman dimuat ulang
const keyword = ref(props.search);

function cari() {
    router.get('/members', { search: keyword.value });
}

function hapus(id) {
    if (confirm('Yakin ingin menghapus anggota ini?')) {
        router.delete(route('members.destroy', id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Anggota</h1>

            <!-- Notifikasi sukses -->
            <div
                v-if="$page.props.flash.success"
                class="my-2 text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- Tombol tambah dan pencarian -->
            <div class="my-3 flex items-center gap-3">
                <Link href="/members/create">
                    + Tambah Anggota
                </Link>

                <input
                    v-model="keyword"
                    @keyup.enter="cari"
                    type="text"
                    placeholder="Cari nama/NIS/NIM/No.HP..."
                >

                <button
                    @click="cari"
                    type="button"
                >
                    Cari
                </button>
            </div>

            <!-- Tabel data anggota -->
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 p-2">Foto</th>
                        <th class="border border-gray-300 p-2">NIS/NIM</th>
                        <th class="border border-gray-300 p-2">Nama</th>
                        <th class="border border-gray-300 p-2">No. HP</th>
                        <th class="border border-gray-300 p-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="member in members.data"
                        :key="member.id"
                    >
                        <td class="border border-gray-300 p-2 text-center">
                            <img
                                v-if="member.foto"
                                :src="'/storage/' + member.foto"
                                alt="Foto anggota"
                                class="w-12 h-12 object-cover rounded mx-auto"
                            >

                            <span v-else>-</span>
                        </td>

                        <td class="border border-gray-300 p-2">
                            {{ member.nis_nim }}
                        </td>

                        <td class="border border-gray-300 p-2">
                            {{ member.nama }}
                        </td>

                        <td class="border border-gray-300 p-2">
                            {{ member.no_hp }}
                        </td>

                        <td class="border border-gray-300 p-2">
                            <Link
                                :href="route('members.edit', member.id)"
                                class="text-blue-600"
                            >
                                Edit
                            </Link>

                            <button
                                @click="hapus(member.id)"
                                type="button"
                                class="ml-2 text-red-600"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>

                    <tr v-if="members.data.length === 0">
                        <td
                            colspan="5"
                            class="border border-gray-300 p-3 text-center"
                        >
                            Belum ada data anggota.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tautan halaman (paginasi) bawaan hasil paginate(10) -->
            <div class="mt-3 flex gap-3">
                <template
                    v-for="link in members.links"
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
