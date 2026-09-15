<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import { usePoll } from '@/composables/usePoll'
import { ChefHat, Clock, Wifi, WifiOff, Play, CheckCircle2, StickyNote } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ orders: Array })

// Polling 5 detik — refresh hanya prop 'orders' (PRD 13)
const { isConnected } = usePoll(['orders'], 5000)

const pendingOrders = computed(() => props.orders.filter(o => o.status === 'pending'))
const processingOrders = computed(() => props.orders.filter(o => o.status === 'diproses'))

const setStatus = (order, status) => {
    router.post(`/dapur/orders/${order.id}/status`, { status }, { preserveScroll: true })
}

// Animasi highlight order baru — pola wkp-motion.highlight (PRD 9.1)
// Untuk MVP: kartu pending diberi border berbeda agar menonjol
const fmtTime = (t) => t
</script>

<template>
  <div>
    <!-- Header + indikator koneksi (DPR-03) -->
    <div class="flex items-center justify-between mb-6 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold flex items-center gap-2"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
          <ChefHat :size="28" />
          Dapur — Antrian Pesanan
        </h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Auto-refresh setiap 5 detik</p>
      </div>

      <!-- Badge koneksi: hijau/merah (PRD DPR-03) -->
      <div
        class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold"
        :style="isConnected
            ? 'background: var(--success); color: white;'
            : 'background: var(--danger); color: white;'"
        style="transition: background-color 200ms;"
      >
        <component :is="isConnected ? Wifi : WifiOff" :size="14" />
        {{ isConnected ? 'Terhubung' : 'Koneksi Terputus' }}
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="orders.length === 0" class="text-center py-20">
      <p class="text-5xl mb-4">🍳</p>
      <p class="font-bold" style="color: var(--ink);">Tidak ada pesanan</p>
      <p class="text-sm mt-1" style="color: var(--ink-muted);">
        Pesanan baru akan muncul otomatis dalam ≤ 5 detik
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" v-else>
      <!-- ===== KOLOM 1: MENUNGGU DIPROSES ===== -->
      <section>
        <h2 class="text-sm font-bold uppercase tracking-wider mb-3 flex items-center gap-2"
            style="color: var(--ink-muted);">
          Menunggu
          <span class="stat px-2 py-0.5 rounded-full text-xs"
                style="background: var(--paper-muted); color: var(--ink);">
            {{ pendingOrders.length }}
          </span>
        </h2>

        <article
          v-for="order in pendingOrders" :key="order.id"
          class="card !p-5 mb-3"
          style="border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="font-extrabold text-xl" style="font-family: var(--font-heading); color: var(--ink);">
              {{ order.table_name }}
            </span>
            <span class="flex items-center gap-1 text-xs stat" style="color: var(--ink-muted);">
              <Clock :size="12" /> {{ order.created_at }}
            </span>
          </div>

          <!-- Item + qty besar -->
          <div class="space-y-1.5 mb-3">
            <div v-for="(item, i) in order.items" :key="i">
              <span class="font-extrabold stat text-base" style="color: var(--primary);">{{ item.qty }}×</span>
              <span class="font-bold text-sm ml-1" style="color: var(--ink);">{{ item.name }}</span>
              <!-- Catatan per item — mencolok, dapur wajib lihat (QR-05) -->
              <div v-if="item.notes" class="flex items-center gap-1 ml-8 text-xs italic mt-0.5"
                   style="color: var(--primary);">
                <StickyNote :size="11" />
                "{{ item.notes }}"
              </div>
            </div>
          </div>

          <div v-if="order.notes" class="flex items-center gap-1 text-xs italic mb-3"
               style="color: var(--ink-muted);">
            <StickyNote :size="11" /> Catatan: "{{ order.notes }}"
          </div>

          <button
            @click="setStatus(order, 'diproses')"
            class="w-full py-3 rounded-xl font-extrabold text-sm flex items-center justify-center gap-2"
            style="background: var(--primary); color: white; border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);"
          >
            <Play :size="16" />
            Mulai Proses
          </button>
        </article>
      </section>

      <!-- ===== KOLOM 2: SEDANG DIPROSES ===== -->
      <section>
        <h2 class="text-sm font-bold uppercase tracking-wider mb-3 flex items-center gap-2"
            style="color: var(--ink-muted);">
          Sedang Diproses
          <span class="stat px-2 py-0.5 rounded-full text-xs"
                style="background: var(--paper-muted); color: var(--ink);">
            {{ processingOrders.length }}
          </span>
        </h2>

        <article
          v-for="order in processingOrders" :key="order.id"
          class="card !p-5 mb-3"
        >
          <div class="flex items-center justify-between mb-3">
            <span class="font-extrabold text-xl" style="font-family: var(--font-heading); color: var(--ink-soft);">
              {{ order.table_name }}
            </span>
            <span class="flex items-center gap-1 text-xs stat" style="color: var(--ink-muted);">
              <Clock :size="12" /> {{ order.created_at }}
            </span>
          </div>

          <div class="space-y-1.5 mb-3">
            <div v-for="(item, i) in order.items" :key="i">
              <span class="font-extrabold stat text-base" style="color: var(--ink-muted);">{{ item.qty }}×</span>
              <span class="font-bold text-sm ml-1" style="color: var(--ink-soft);">{{ item.name }}</span>
              <div v-if="item.notes" class="flex items-center gap-1 ml-8 text-xs italic mt-0.5"
                   style="color: var(--primary);">
                <StickyNote :size="11" />
                "{{ item.notes }}"
              </div>
            </div>
          </div>

          <button
            @click="setStatus(order, 'selesai')"
            class="w-full py-3 rounded-xl font-extrabold text-sm flex items-center justify-center gap-2"
            style="background: var(--success); color: white;"
          >
            <CheckCircle2 :size="16" />
            Selesai — Siap Diantar
          </button>
        </article>
      </section>
    </div>
  </div>
</template>