<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// Chart.js: pustaka JavaScript untuk menggambar grafik pada <canvas>.
import Chart from 'chart.js/auto';

// Data yang diterima dari komponen induk (Dashboard.vue).
const props = defineProps({
    // Teks sumbu X, contoh: ['Jun 2026', 'Jul 2026', ...].
    label: Array,

    // Angka tinggi batang, contoh: [3, 5, 2, ...].
    jumlah: Array,

    // Nama data yang tampil saat kursor diarahkan ke batang.
    judul: {
        type: String,
        default: 'Jumlah',
    },
});

// Penghubung ke elemen <canvas>.
const kanvas = ref(null);

// Menyimpan objek grafik.
let grafik = null;

// Dijalankan setelah elemen canvas tersedia.
onMounted(() => {
    grafik = new Chart(kanvas.value, {
        // Jenis grafik: batang.
        type: 'bar',

        data: {
            // Menyalin array agar tidak langsung menggunakan data reaktif Vue.
            labels: [...props.label],

            datasets: [
                {
                    label: props.judul,
                    data: [...props.jumlah],

                    // Warna batang biru.
                    backgroundColor: '#3b82f6',
                },
            ],
        },

        options: {
            // Ukuran grafik mengikuti ukuran pembungkusnya.
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                // Sembunyikan legenda karena hanya ada satu jenis data.
                legend: {
                    display: false,
                },
            },

            scales: {
                y: {
                    // Sumbu Y dimulai dari angka 0.
                    beginAtZero: true,

                    // Menampilkan angka bulat saja.
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    });
});

// Menghapus grafik sebelum komponen dilepas.
onBeforeUnmount(() => {
    if (grafik) {
        grafik.destroy();
    }
});
</script>

<template>
    <div class="relative w-full h-64">
        <canvas ref="kanvas"></canvas>
    </div>
</template>
