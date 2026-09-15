<script setup>
import AppLayout from '@/Components/layout/AppLayout.vue'
import { TrendingUp, TrendingDown, Receipt, Coins } from '@lucide/vue'

defineOptions({ layout: AppLayout })

defineProps({ report: Object, topMenus: Array })

const fmtRp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')
const fmtDay = (d) => new Date(d).getDate()

// Grafik garis SVG — zero dependency
const linePoints = (report) => {
    if (!report.daily.length) return ''
    const max = Math.max(...report.daily.map(d => Number(d.revenue)), 1)
    const w = 100, h = 40
    return report.daily
        .map((d, i) => {
            const x = (i / Math.max(report.daily.length - 1, 1)) * w
            const y = h - (Number(d.revenue) / max) * h
            return `${x},${y}`
        })
        .join(' ')
}
</script>

<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-extrabold"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
          Laporan Bulanan
        </h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Tren penjualan & perbandingan bulanan</p>
      </div>

      <label class="flex items-center gap-2">
        <input
          type="month"
          :value="report.month"
          class="input"
          @change="$inertia.get(route('owner.reports.monthly'), { month: $event.target.value })"
        />
      </label>
    </div>

    <!-- KPI + perbandingan bulan lalu -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
      <div class="card !p-5"
           style="border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase" style="color: var(--ink-muted);">Pendapatan Bulan Ini</span>
          <Coins :size="18" style="color: var(--primary);" />
        </div>
        <div class="text-3xl font-extrabold stat" style="color: var(--ink);">
          {{ fmtRp(report.revenue) }}
        </div>
        <div v-if="report.growth_percent !== null" class="flex items-center gap-1 mt-2 text-xs font-bold">
          <component :is="report.growth_percent >= 0 ? TrendingUp : TrendingDown" :size="13" />
          <span :style="{ color: report.growth_percent >= 0 ? 'var(--success)' : 'var(--danger)' }">
            {{ report.growth_percent >= 0 ? '+' : '' }}{{ report.growth_percent }}%
          </span>
          <span style="color: var(--ink-subtle);">vs bulan lalu ({{ fmtRp(report.prev_revenue) }})</span>
        </div>
        <div v-else class="text-xs mt-2" style="color: var(--ink-subtle);">
          Bulan lalu: {{ fmtRp(report.prev_revenue) }}
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
        <div class="text-xs mt-2" style="color: var(--ink-subtle);">
          bulan lalu: {{ report.prev_transactions }} transaksi
        </div>
      </div>
    </div>

    <!-- Grafik garis: pendapatan harian sebulan -->
    <div class="card !p-5 mb-6">
      <h2 class="font-extrabold mb-4" style="font-family: var(--font-heading); color: var(--ink);">
        Tren Pendapatan Harian
      </h2>

      <div v-if="report.daily.length === 0" class="text-center py-10 text-sm" style="color: var(--ink-subtle);">
        Belum ada transaksi pada bulan ini
      </div>

      <svg v-else viewBox="0 0 100 45" class="w-full h-48" preserveAspectRatio="none">
        <!-- Grid garis bantu -->
        <line v-for="n in 4" :key="n" :x1="0" :y1="(n - 1) * (40 / 3)" :x2="100" :y2="(n - 1) * (40 / 3)"
              stroke="var(--paper-inset)" stroke-width="0.3" />
        <!-- Garis tren -->
        <polyline
          :points="linePoints(report)"
          fill="none"
          stroke="var(--primary)"
          stroke-width="1"
          stroke-linejoin="round"
          stroke-linecap="round"
          vector-effect="non-scaling-stroke"
        />
      </svg>

      <!-- Label tanggal awal/akhir -->
      <div class="flex justify-between text-[10px] font-bold stat mt-2" style="color: var(--ink-muted);">
        <span>{{ report.daily[0] ? fmtDay(report.daily[0].date) : '' }}</span>
        <span>{{ report.daily.length ? fmtDay(report.daily[report.daily.length - 1].date) : '' }}</span>
      </div>
    </div>

    <!-- Rekap metode bayar (LAP-06 bonus) -->
    <div class="card !p-5 mb-6">
      <h2 class="font-extrabold mb-4" style="font-family: var(--font-heading); color: var(--ink);">
        Rekap Metode Pembayaran
      </h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div v-for="m in report.methods" :key="m.payment_method"
             class="p-4 rounded-xl" style="background: var(--paper-muted);">
          <div class="text-xs font-bold uppercase" style="color: var(--ink-muted);">{{ m.payment_method }}</div>
          <div class="text-xl font-extrabold stat mt-1" style="color: var(--ink);">{{ fmtRp(m.revenue) }}</div>
          <div class="text-xs stat mt-0.5" style="color: var(--ink-subtle);">{{ m.count }} transaksi</div>
        </div>
        <div v-if="report.methods.length === 0" class="col-span-full text-center py-4 text-sm" style="color: var(--ink-subtle);">
          Belum ada data pembayaran
        </div>
      </div>
    </div>

    <!-- Menu terlaris bulan ini -->
    <div class="card !p-5">
      <h2 class="font-extrabold mb-4" style="font-family: var(--font-heading); color: var(--ink);">
        Menu Terlaris — Bulan Ini
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