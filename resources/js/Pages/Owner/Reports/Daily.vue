<script setup>
import AppLayout from '@/Components/layout/AppLayout.vue'
import { TrendingUp, TrendingDown, Receipt, Coins, Calculator, CalendarDays } from '@lucide/vue'

defineOptions({ layout: AppLayout })

defineProps({ report: Object, topMenus: Array })

const fmtRp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')

// Grafik batang per jam — pure CSS/SVG, zero dependency
const maxHourly = (report) => Math.max(...report.hourly.map(h => Number(h.revenue)), 1)
const barHeight = (revenue, report) => Math.max(4, (Number(revenue) / maxHourly(report)) * 100)
</script>

<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-extrabold"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
          Laporan Harian
        </h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Ringkasan penjualan per hari</p>
      </div>

      <!-- Pemilih tanggal -->
      <label class="flex items-center gap-2">
        <CalendarDays :size="16" style="color: var(--ink-muted);" />
        <input
          type="date"
          :value="report.date"
          class="input"
          @change="$inertia.get(route('owner.reports.daily'), { date: $event.target.value })"
        />
      </label>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="card !p-5"
           style="border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase" style="color: var(--ink-muted);">Pendapatan</span>
          <Coins :size="18" style="color: var(--primary);" />
        </div>
        <div class="text-3xl font-extrabold stat" style="color: var(--ink);">
          {{ fmtRp(report.revenue) }}
        </div>
        <!-- vs kemarin -->
        <div v-if="report.growth_percent !== null" class="flex items-center gap-1 mt-2 text-xs font-bold">
          <component :is="report.growth_percent >= 0 ? TrendingUp : TrendingDown" :size="13" />
          <span :style="{ color: report.growth_percent >= 0 ? 'var(--success)' : 'var(--danger)' }">
            {{ report.growth_percent >= 0 ? '+' : '' }}{{ report.growth_percent }}%
          </span>
          <span style="color: var(--ink-subtle);">vs kemarin ({{ fmtRp(report.yesterday_revenue) }})</span>
        </div>
      </div>

      <div class="card !p-5">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase" style="color: var(--ink-muted);">Total Transaksi</span>
          <Receipt :size="18" style="color: var(--primary);" />
        </div>
        <div class="text-3xl font-extrabold stat" style="color: var(--ink);">
          {{ report.total_transactions }}
        </div>
        <div class="text-xs mt-2" style="color: var(--ink-subtle);">transaksi selesai hari ini</div>
      </div>

      <div class="card !p-5">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase" style="color: var(--ink-muted);">Rata-rata / Transaksi</span>
          <Calculator :size="18" style="color: var(--primary);" />
        </div>
        <div class="text-3xl font-extrabold stat" style="color: var(--primary);">
          {{ fmtRp(report.avg_per_transaction) }}
        </div>
        <div class="text-xs mt-2" style="color: var(--ink-subtle);">nilai per transaksi</div>
      </div>
    </div>

    <!-- Grafik pendapatan per jam -->
    <div class="card !p-5 mb-6">
      <h2 class="font-extrabold mb-4" style="font-family: var(--font-heading); color: var(--ink);">
        Pendapatan per Jam
      </h2>

      <div v-if="report.hourly.length === 0" class="text-center py-10 text-sm" style="color: var(--ink-subtle);">
        Belum ada transaksi pada tanggal ini
      </div>

      <div v-else class="flex items-end gap-1.5 sm:gap-2 h-40">
        <div v-for="h in report.hourly" :key="h.hour" class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
          <span class="text-[10px] font-bold stat" style="color: var(--ink-muted);">{{ fmtRp(h.revenue) }}</span>
          <div
            class="w-full rounded-t-md"
            :style="{ height: barHeight(h.revenue, report) + 'px', background: 'var(--primary)' }"
            style="transition: height 300ms; min-height: 4px;"
          ></div>
          <span class="text-[10px] font-bold stat" style="color: var(--ink-muted);">{{ h.hour }}</span>
        </div>
      </div>
    </div>

    <!-- Menu terlaris hari ini -->
    <div class="card !p-5">
      <h2 class="font-extrabold mb-4" style="font-family: var(--font-heading); color: var(--ink);">
        Menu Terlaris — Hari Ini
      </h2>

      <div v-if="topMenus.length === 0" class="text-center py-6 text-sm" style="color: var(--ink-subtle);">
        Belum ada penjualan menu
      </div>

      <div v-for="(m, i) in topMenus" :key="i"
           class="flex items-center justify-between py-2.5"
           style="border-bottom: 1px solid var(--paper-inset);">
        <div class="flex items-center gap-3">
          <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold stat"
                :style="i === 0 ? 'background: var(--primary); color: white;' : 'background: var(--paper-muted); color: var(--ink-soft);'">
            {{ i + 1 }}
          </span>
          <span class="font-bold text-sm" style="color: var(--ink);">{{ m.name }}</span>
        </div>
        <div class="text-right">
          <span class="text-sm font-bold stat" style="color: var(--ink);">{{ m.total_qty }} porsi</span>
          <span class="block text-xs stat" style="color: var(--ink-muted);">{{ fmtRp(m.revenue) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>