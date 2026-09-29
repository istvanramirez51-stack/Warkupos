<script setup>
import { ref } from 'vue'
import AppLayout from '@/Components/layout/AppLayout.vue'
import { ShieldCheck, ChevronDown, LayoutGrid, List } from '@lucide/vue'

defineOptions({ layout: AppLayout })

defineProps({ logs: Object, filters: Object })

const actionConfig = {
    'order.created':        { label: 'Order Baru',   bg: 'var(--primary)' },
    'order.status_changed': { label: 'Status Order', bg: 'var(--ink-muted)' },
    'transaction.created':  { label: 'Pembayaran',   bg: 'var(--ink)' },
}

// View mode: list (mobile default) / table (desktop)
const viewMode = ref(window.innerWidth >= 768 ? 'table' : 'list')

const fmtTime = (t) => new Date(t).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit',
})
</script>

<template>
  <div class="w-full overflow-x-hidden">
    <div class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-extrabold flex items-center gap-2"
          style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
        <ShieldCheck :size="26" />
        Audit Trail
      </h1>
      <p class="text-sm mt-1" style="color: var(--ink-muted);">
        Riwayat semua aksi pada transaksi — log tidak dapat diubah atau dihapus (PRD 14.3)
      </p>
    </div>

    <!-- Filter aksi — chips scroll horizontal di mobile -->
    <div class="flex gap-2 mb-4 overflow-x-auto pb-1 -mx-1 px-1">
      <a
        :href="route('owner.audit.index')"
        class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap flex-shrink-0"
        :style="!filters.action ? 'background: var(--primary); color: white;' : 'background: var(--paper-muted); color: var(--ink-muted);'"
        style="transition: background-color 200ms, color 200ms;"
      >Semua</a>
      <a
        v-for="(cfg, action) in actionConfig" :key="action"
        :href="route('owner.audit.index', { action })"
        class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap flex-shrink-0"
        :style="filters.action === action ? 'background: var(--primary); color: white;' : 'background: var(--paper-muted); color: var(--ink-muted);'"
        style="transition: background-color 200ms, color 200ms;"
      >{{ cfg.label }}</a>
    </div>

    <!-- Toggle view — hanya mobile -->
    <div class="flex md:hidden mb-4">
      <div class="grid grid-cols-2 w-full rounded-xl overflow-hidden" style="border: 1px solid var(--paper-inset);">
        <button
          @click="viewMode = 'list'"
          class="py-2.5 text-xs font-bold flex items-center justify-center gap-1.5"
          :style="viewMode === 'list'
            ? 'background: var(--primary); color: white;'
            : 'background: var(--paper-muted); color: var(--ink-muted);'"
          style="transition: background-color 200ms, color 200ms;"
        >
          <List :size="14" /> Kartu
        </button>
        <button
          @click="viewMode = 'table'"
          class="py-2.5 text-xs font-bold flex items-center justify-center gap-1.5"
          :style="viewMode === 'table'
            ? 'background: var(--primary); color: white;'
            : 'background: var(--paper-muted); color: var(--ink-muted);'"
          style="transition: background-color 200ms, color 200ms;"
        >
          <LayoutGrid :size="14" /> Tabel
        </button>
      </div>
    </div>

    <!-- ============ VIEW KARTU (Mobile) ============ -->
    <div v-if="viewMode === 'list'" class="space-y-3">
      <div
        v-for="log in logs.data" :key="log.id"
        class="rounded-xl p-4"
        style="background: var(--paper); border: 1px solid var(--paper-inset);"
      >
        <!-- Baris atas: badge aksi + waktu -->
        <div class="flex items-start justify-between gap-2 mb-2">
          <span
            class="text-[10px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap flex-shrink-0"
            :style="{ backgroundColor: (actionConfig[log.action]?.bg ?? 'var(--ink-muted)'), color: 'white' }"
          >
            {{ actionConfig[log.action]?.label ?? log.action }}
          </span>
          <span class="text-xs stat whitespace-nowrap" style="color: var(--ink-muted);">
            {{ fmtTime(log.created_at) }}
          </span>
        </div>

        <!-- Oleh -->
        <div class="text-sm font-bold" style="color: var(--ink-soft);">
          {{ log.user?.name ?? 'Pelanggan (QR)' }}
        </div>

        <!-- Meta ringkas (jika ada) — tanpa perlu expand -->
        <div v-if="log.meta" class="mt-2">
          <details class="group">
            <summary class="cursor-pointer text-xs font-bold flex items-center gap-1" style="color: var(--primary);">
              <ChevronDown :size="13" class="group-open:rotate-180" style="transition: transform 200ms;" />
              Lihat Detail
            </summary>
            <pre class="text-[11px] mt-2 p-3 rounded-lg overflow-x-auto"
                 style="background: var(--paper-muted); color: var(--ink-soft);">{{ JSON.stringify(log.meta, null, 2) }}</pre>
          </details>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="logs.data.length === 0" class="text-center py-12">
        <p class="text-4xl mb-3">📜</p>
        <p class="text-sm" style="color: var(--ink-subtle);">Belum ada log. Lakukan order/pembayaran untuk menghasilkan log.</p>
      </div>
    </div>

    <!-- ============ VIEW TABEL (Desktop) ============ -->
    <div
      v-if="viewMode === 'table'"
      style="background: var(--paper); border: 1px solid var(--paper-inset); border-radius: 12px; overflow: hidden;"
    >
      <table class="w-full text-sm">
        <thead>
          <tr style="border-bottom: 1.5px solid var(--paper-inset); background: var(--paper-muted);">
            <th class="text-left px-4 py-3 text-xs font-bold uppercase" style="color: var(--ink-muted);">Waktu</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase" style="color: var(--ink-muted);">Aksi</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase" style="color: var(--ink-muted);">Oleh</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase" style="color: var(--ink-muted);">Detail</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs.data" :key="log.id" style="border-bottom: 1px solid var(--paper-inset);">
            <td class="px-4 py-3 stat whitespace-nowrap" style="color: var(--ink-soft);">
              {{ fmtTime(log.created_at) }}
            </td>
            <td class="px-4 py-3">
              <span
                class="text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap"
                :style="{ backgroundColor: (actionConfig[log.action]?.bg ?? 'var(--ink-muted)'), color: 'white' }"
              >
                {{ actionConfig[log.action]?.label ?? log.action }}
              </span>
            </td>
            <td class="px-4 py-3 font-semibold" style="color: var(--ink-soft);">
              {{ log.user?.name ?? 'Pelanggan (QR)' }}
            </td>
            <td class="px-4 py-3">
              <details class="group">
                <summary class="cursor-pointer text-xs font-bold flex items-center gap-1" style="color: var(--primary);">
                  <ChevronDown :size="13" class="group-open:rotate-180" style="transition: transform 200ms;" />
                  Lihat
                </summary>
                <pre class="text-xs mt-2 p-3 rounded-lg overflow-x-auto"
                     style="background: var(--paper-muted); color: var(--ink-soft);">{{ JSON.stringify(log.meta, null, 2) }}</pre>
              </details>
            </td>
          </tr>
          <tr v-if="logs.data.length === 0">
            <td colspan="4" class="px-4 py-10 text-center text-sm" style="color: var(--ink-subtle);">
              Belum ada log. Lakukan order/pembayaran untuk menghasilkan log.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination — scroll-x kalau halaman banyak -->
    <div v-if="logs.last_page > 1" class="flex gap-2 mt-4 justify-center overflow-x-auto pb-1">
      <a v-for="n in logs.last_page" :key="n"
         :href="route('owner.audit.index', { page: n, ...filters })"
         class="px-3 py-1.5 rounded-lg text-xs font-bold flex-shrink-0"
         :style="n === logs.current_page ? 'background: var(--primary); color: white;' : 'background: var(--paper-muted); color: var(--ink-muted);'"
      >{{ n }}</a>
    </div>
  </div>
</template>