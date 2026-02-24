<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    mpkk_attendance: Array,
    upcoming_meetings: Array,
});

const selectedMpkk = ref('');

const selectedMpkkData = computed(() => {
    if (!selectedMpkk.value) return null;
    return props.mpkk_attendance.find(m => m.name === selectedMpkk.value);
});

const mpkkTotalHadir = computed(() => {
    return props.mpkk_attendance.reduce((sum, m) => sum + m.hadir, 0);
});

const mpkkTotalAll = computed(() => {
    return props.mpkk_attendance.reduce((sum, m) => sum + m.total, 0);
});

const statCards = [
    { label: 'Kehadiran Cabang', key: 'matc', color: 'bg-sky-400/15', iconColor: 'text-sky-300', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
    { label: 'Kehadiran Wanita Cabang', key: 'wanita', color: 'bg-rose-400/15', iconColor: 'text-rose-300', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
    { label: 'Kehadiran AMK', key: 'amk', color: 'bg-emerald-400/15', iconColor: 'text-emerald-300', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
    { label: 'Kehadiran MPKK', key: 'mpkk', color: 'bg-amber-400/15', iconColor: 'text-amber-300', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Dashboard</h2>
        </template>

        <div class="py-8 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4">
                    <div
                        v-for="card in statCards"
                        :key="card.key"
                        class="rounded-2xl bg-white/10 p-4 shadow-lg backdrop-blur-md ring-1 ring-white/15 sm:p-6"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg p-2" :class="card.color">
                                <svg class="h-5 w-5" :class="card.iconColor" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-sky-200/60 sm:text-sm">{{ card.label }}</span>
                        </div>
                        <div class="mt-3">
                            <span class="text-2xl font-bold text-white sm:text-3xl">{{ stats[card.key].hadir }}</span>
                            <span class="ml-1 text-sm text-emerald-300">Hadir</span>
                            <span class="mx-2 text-white/30">|</span>
                            <span class="text-2xl font-bold text-white sm:text-3xl">{{ stats[card.key].tidak_hadir }}</span>
                            <span class="ml-1 text-sm text-red-300">Tidak Hadir</span>
                        </div>
                    </div>
                </div>

                <!-- MPKK Attendance Card -->
                <div v-if="mpkk_attendance.length" class="mt-6 rounded-2xl bg-white/10 p-4 shadow-lg backdrop-blur-md ring-1 ring-white/15 sm:mt-8 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-white sm:text-lg">Kehadiran MPKK</h3>
                            <p class="mt-1 text-sm text-sky-200/50">Pilih MPKK untuk lihat status kehadiran.</p>
                        </div>
                        <div class="text-sm text-amber-300 font-medium">
                            Jumlah: {{ mpkkTotalHadir }} / {{ mpkkTotalAll }} orang
                        </div>
                    </div>

                    <div class="mt-4">
                        <select
                            v-model="selectedMpkk"
                            class="w-full rounded-lg border-0 bg-white/10 text-white ring-1 ring-white/15 focus:ring-2 focus:ring-amber-400 sm:max-w-md"
                        >
                            <option value="" class="bg-sky-900 text-white">-- Pilih MPKK --</option>
                            <option v-for="m in mpkk_attendance" :key="m.name" :value="m.name" class="bg-sky-900 text-white">
                                {{ m.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Selected MPKK Result -->
                    <div v-if="selectedMpkkData" class="mt-4 rounded-xl bg-amber-500/10 p-4 ring-1 ring-amber-400/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-amber-200">{{ selectedMpkkData.name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-3xl font-bold text-white">{{ selectedMpkkData.hadir }}</span>
                                <span class="text-lg text-amber-300/70"> / {{ selectedMpkkData.total }}</span>
                                <span class="ml-1 text-sm text-amber-300/70">orang</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="selectedMpkkData.hadir >= selectedMpkkData.total ? 'bg-emerald-400' : 'bg-amber-400'"
                                    :style="{ width: (selectedMpkkData.total > 0 ? Math.min(100, (selectedMpkkData.hadir / selectedMpkkData.total) * 100) : 0) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- QR Code -->
                <div class="mt-6 rounded-2xl bg-white/10 p-4 shadow-lg backdrop-blur-md ring-1 ring-white/15 sm:mt-8 sm:p-6">
                    <h3 class="text-base font-semibold text-white sm:text-lg">Jana QR Code</h3>
                    <p class="mt-1 text-sm text-sky-200/50">Jana kod QR untuk pengesahan kehadiran mengikut kategori.</p>
                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <Link
                            :href="route('admin.qr.matc')"
                            class="flex items-center gap-3 rounded-xl bg-sky-500/15 px-4 py-3 ring-1 ring-sky-400/20 transition hover:bg-sky-500/25"
                        >
                            <div class="rounded-lg bg-sky-400/15 p-2">
                                <svg class="h-5 w-5 text-sky-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75H16.5v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75H16.5v-.75z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">Cabang</div>
                                <div class="text-xs text-sky-200/50">QR kehadiran</div>
                            </div>
                        </Link>
                        <Link
                            :href="route('admin.qr.wanita')"
                            class="flex items-center gap-3 rounded-xl bg-rose-500/15 px-4 py-3 ring-1 ring-rose-400/20 transition hover:bg-rose-500/25"
                        >
                            <div class="rounded-lg bg-rose-400/15 p-2">
                                <svg class="h-5 w-5 text-rose-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75H16.5v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75H16.5v-.75z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">Wanita Cabang</div>
                                <div class="text-xs text-sky-200/50">QR kehadiran</div>
                            </div>
                        </Link>
                        <Link
                            :href="route('admin.qr.amk')"
                            class="flex items-center gap-3 rounded-xl bg-emerald-500/15 px-4 py-3 ring-1 ring-emerald-400/20 transition hover:bg-emerald-500/25"
                        >
                            <div class="rounded-lg bg-emerald-400/15 p-2">
                                <svg class="h-5 w-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75H16.5v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75H16.5v-.75z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">AMK</div>
                                <div class="text-xs text-sky-200/50">QR kehadiran</div>
                            </div>
                        </Link>
                        <Link
                            :href="route('admin.qr.mpkk')"
                            class="flex items-center gap-3 rounded-xl bg-amber-500/15 px-4 py-3 ring-1 ring-amber-400/20 transition hover:bg-amber-500/25"
                        >
                            <div class="rounded-lg bg-amber-400/15 p-2">
                                <svg class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75H16.5v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75H16.5v-.75z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">MPKK</div>
                                <div class="text-xs text-sky-200/50">QR kehadiran</div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Upcoming Meetings -->
                <div class="mt-6 rounded-2xl bg-white/10 p-4 shadow-lg backdrop-blur-md ring-1 ring-white/15 sm:mt-8 sm:p-6">
                    <h3 class="text-base font-semibold text-white sm:text-lg">Mesyuarat Akan Datang</h3>
                    <div v-if="upcoming_meetings.length" class="mt-4 divide-y divide-white/10">
                        <div v-for="meeting in upcoming_meetings" :key="meeting.id" class="flex items-center justify-between py-3">
                            <div>
                                <Link :href="route('meetings.show', meeting.id)" class="font-medium text-sky-300 hover:text-sky-200 transition">
                                    {{ meeting.title }}
                                </Link>
                                <p class="text-sm text-sky-200/50">{{ meeting.date }} &middot; {{ meeting.location }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-sky-400/15 px-2.5 py-0.5 text-xs font-medium text-sky-300 ring-1 ring-sky-400/20">
                                {{ meeting.status }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="mt-4 text-sm text-sky-200/50">Tiada mesyuarat akan datang.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
