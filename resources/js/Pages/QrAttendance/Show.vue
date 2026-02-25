<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    qrCode: String,
    url: String,
});

function printQr() {
    window.print();
}
</script>

<template>
    <Head title="QR Code" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                QR Kehadiran
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white/10 shadow-lg backdrop-blur-md ring-1 ring-white/15">
                    <div class="p-6 text-center">
                        <p class="mb-6 text-sm text-sky-200/50">
                            Imbas kod QR ini untuk akses borang kehadiran.
                        </p>

                        <div class="mx-auto my-8 inline-block rounded-2xl border-4 border-white/15 bg-white p-6" v-html="qrCode"></div>

                        <p class="mt-4 text-xs text-sky-300/40 break-all">{{ url }}</p>

                        <button
                            @click="printQr"
                            class="mt-6 inline-flex items-center rounded-md bg-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 hover:bg-sky-400"
                        >
                            Cetak QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    nav, header, button, .no-print { display: none !important; }
    body { background: white !important; }
}
</style>
