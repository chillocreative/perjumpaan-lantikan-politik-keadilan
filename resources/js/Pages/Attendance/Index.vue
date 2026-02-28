<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useDragScroll } from '@/composables/useDragScroll.js';
import axios from 'axios';

const props = defineProps({
    meetings: Object,
    meeting: Object,
    attendances: Array,
    filters: Object,
    mpkkList: Array,
});

const page = usePage();
const selectedMeeting = ref(props.filters.meeting_id || '');
const selectedCategory = ref(props.filters.category || '');
const selectedMpkk = ref(props.filters.mpkk || '');

const { scrollEl, onMouseDown, onMouseLeave, onMouseUp, onMouseMove } = useDragScroll();

function applyFilters() {
    const params = { meeting_id: selectedMeeting.value };
    if (selectedCategory.value) {
        params.category = selectedCategory.value;
    }
    if (selectedCategory.value === 'mpkk' && selectedMpkk.value) {
        params.mpkk = selectedMpkk.value;
    }
    router.get(route('attendances.index'), params, {
        preserveState: true,
        replace: true,
    });
}

function onMeetingChange() {
    selectedCategory.value = '';
    selectedMpkk.value = '';
    applyFilters();
}

function onCategoryChange() {
    selectedMpkk.value = '';
    applyFilters();
}

function onMpkkChange() {
    applyFilters();
}

watch(selectedCategory, (val) => {
    if (val !== 'mpkk') selectedMpkk.value = '';
});

const categoryLabels = {
    matc: 'Cabang',
    amk: 'AMK',
    wanita: 'Wanita Cabang',
    mpkk: 'MPKK',
};

const statusLabels = {
    present: 'Hadir',
    absent: 'Tidak Hadir',
    late: 'Lewat',
    excused: 'Dimaafkan',
};

const statusColors = {
    present: 'bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/20',
    absent: 'bg-red-400/15 text-red-300 ring-1 ring-red-400/20',
    late: 'bg-yellow-400/15 text-yellow-300 ring-1 ring-yellow-400/20',
    excused: 'bg-white/10 text-sky-200 ring-1 ring-white/15',
};

const downloading = ref(false);
const downloadError = ref('');

async function downloadPdf() {
    downloading.value = true;
    downloadError.value = '';

    const params = new URLSearchParams({
        meeting_id: selectedMeeting.value,
        category: selectedCategory.value,
    });
    if (selectedMpkk.value) {
        params.set('mpkk', selectedMpkk.value);
    }
    const url = '/export/attendance-pdf?' + params.toString();

    try {
        const response = await axios.get(url, { responseType: 'blob' });

        const contentType = response.headers['content-type'] || '';
        if (!contentType.includes('application/pdf')) {
            const text = await response.data.text();
            downloadError.value = 'Respons bukan PDF. Sila muat semula halaman dan cuba lagi.';
            return;
        }

        const disposition = response.headers['content-disposition'] || '';
        let filename = 'kehadiran.pdf';
        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (match) filename = match[1].replace(/['"]/g, '');

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(blobUrl);
    } catch (error) {
        const status = error.response?.status;
        if (status === 401 || status === 419) {
            downloadError.value = 'Sesi anda telah tamat. Sila muat semula halaman dan log masuk semula.';
        } else if (status === 403) {
            downloadError.value = 'Anda tidak dibenarkan memuat turun PDF ini.';
        } else if (status === 422) {
            downloadError.value = 'Parameter tidak sah. Sila pilih mesyuarat dan kategori.';
        } else {
            downloadError.value = 'Gagal muat turun PDF (Ralat ' + (status || 'rangkaian') + '). Sila cuba lagi.';
        }
    } finally {
        downloading.value = false;
    }
}
</script>

<template>
    <Head title="Kehadiran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Rekod Kehadiran</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.flash.success" class="mb-4 rounded-md bg-emerald-400/15 p-4 ring-1 ring-emerald-400/20">
                    <p class="text-sm text-emerald-300">{{ page.props.flash.success }}</p>
                </div>
                <div v-if="page.props.flash.error" class="mb-4 rounded-md bg-red-400/15 p-4 ring-1 ring-red-400/20">
                    <p class="text-sm text-red-300">{{ page.props.flash.error }}</p>
                </div>

                <div class="mb-4 flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-sky-100/80 mb-1">Pilih Mesyuarat</label>
                        <select
                            v-model="selectedMeeting"
                            @change="onMeetingChange"
                            class="w-full rounded-md border-0 bg-white/10 text-white ring-1 ring-white/15 focus:ring-2 focus:ring-sky-400 sm:min-w-[20rem]"
                        >
                            <option value="" class="bg-sky-900 text-white">-- Pilih Mesyuarat --</option>
                            <option v-for="m in meetings.data" :key="m.id" :value="m.id" class="bg-sky-900 text-white">
                                {{ m.title }} ({{ m.date }})
                            </option>
                        </select>
                    </div>

                    <div v-if="selectedMeeting" class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-sky-100/80 mb-1">Kategori</label>
                        <select
                            v-model="selectedCategory"
                            @change="onCategoryChange"
                            class="w-full rounded-md border-0 bg-white/10 text-white ring-1 ring-white/15 focus:ring-2 focus:ring-sky-400 sm:w-48"
                        >
                            <option value="" class="bg-sky-900 text-white">Semua</option>
                            <option value="matc" class="bg-sky-900 text-white">Cabang</option>
                            <option value="wanita" class="bg-sky-900 text-white">Wanita Cabang</option>
                            <option value="amk" class="bg-sky-900 text-white">AMK</option>
                            <option value="mpkk" class="bg-sky-900 text-white">MPKK</option>
                        </select>
                    </div>

                    <div v-if="selectedCategory === 'mpkk'" class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-sky-100/80 mb-1">MPKK</label>
                        <select
                            v-model="selectedMpkk"
                            @change="onMpkkChange"
                            class="w-full rounded-md border-0 bg-white/10 text-white ring-1 ring-white/15 focus:ring-2 focus:ring-sky-400 sm:w-64"
                        >
                            <option value="" class="bg-sky-900 text-white">Semua MPKK</option>
                            <option v-for="mpkk in mpkkList" :key="mpkk" :value="mpkk" class="bg-sky-900 text-white">
                                {{ mpkk }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="downloadError" class="mb-4 rounded-md bg-red-400/15 p-4 ring-1 ring-red-400/20">
                    <p class="text-sm text-red-300">{{ downloadError }}</p>
                </div>

                <div v-if="meeting" class="overflow-hidden rounded-2xl bg-white/10 shadow-lg backdrop-blur-md ring-1 ring-white/15">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <h3 class="text-lg font-medium text-white">{{ meeting.title }}</h3>
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="selectedCategory"
                                    @click="downloadPdf"
                                    :disabled="downloading"
                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-wait"
                                >
                                    {{ downloading ? 'Memuat turun...' : 'Muat Turun PDF' }}
                                </button>
                                <Link
                                    v-if="page.props.auth.user.is_admin"
                                    :href="route('attendances.mark', meeting.id)"
                                    class="inline-flex items-center rounded-md bg-sky-500 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 hover:bg-sky-400"
                                >
                                    Rekod Kehadiran
                                </Link>
                            </div>
                        </div>

                        <div
                            ref="scrollEl"
                            class="overflow-x-auto cursor-grab"
                            @mousedown="onMouseDown"
                            @mouseleave="onMouseLeave"
                            @mouseup="onMouseUp"
                            @mousemove="onMouseMove"
                        >
                            <table v-if="attendances.length" class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Nama Ahli</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">No. IC</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Kategori</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Jawatan</th>
                                        <th v-if="selectedCategory === 'mpkk'" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">MPKK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Alamat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-sky-200/60 whitespace-nowrap">Sebab</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">
                                    <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-white/5">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-white uppercase">{{ attendance.member?.name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-sky-200/50 uppercase">{{ attendance.member?.ic_number }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-sky-200/80 uppercase">{{ attendance.member?.category_type === 'mpkk' ? (attendance.member?.position_name || 'MPKK') : (categoryLabels[attendance.member?.category_type] || '-') }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-sky-200/50 uppercase">{{ attendance.member?.position_type || '-' }}</td>
                                        <td v-if="selectedCategory === 'mpkk'" class="whitespace-nowrap px-6 py-4 text-sm text-sky-200/50 uppercase">{{ attendance.member?.position_name || '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-sky-200/80 uppercase min-w-[12rem]">{{ attendance.member?.address || '-' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                :class="statusColors[attendance.status] || 'bg-white/10 text-sky-200 ring-1 ring-white/15'"
                                            >
                                                {{ statusLabels[attendance.status] || attendance.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-sky-200/50 uppercase min-w-[10rem]">{{ attendance.status === 'absent' ? (attendance.absence_reason || '-') : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-if="!attendances.length" class="text-sm text-sky-200/50 mt-4">Tiada rekod kehadiran untuk mesyuarat ini.</p>
                    </div>
                </div>

                <div v-else class="overflow-hidden rounded-2xl bg-white/10 shadow-lg backdrop-blur-md ring-1 ring-white/15">
                    <div class="p-6 text-center text-sm text-sky-200/50">
                        Sila pilih mesyuarat untuk melihat rekod kehadiran.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
