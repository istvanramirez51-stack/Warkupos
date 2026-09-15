<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import { ShoppingCart, Banknote, QrCode, ArrowLeftRight, Clock } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ activeOrders: Array })

const selectedOrder = ref(null)
const showPayModal = ref(false)
const method = ref('tunai')
const paidAmount = ref(0)
const processing = ref(false)

const methodIcons = { tunai: Banknote, qris: QrCode, transfer: ArrowLeftRight }
const methodLabels = { tunai: 'Tunai', qris: 'QRIS', transfer: 'Transfer' }

const quickCash = computed(() => {
    if (!selectedOrder.value) return []
    const t = selectedOrder.value.total
    const options = [t, Math.ceil(t / 5000) * 5000, Math.ceil(t / 10000) * 10000, Math.ceil(t / 50000) * 50000]
    return [...new Set(options)].filter(v => v >= t)
})

const change = computed(() => Math.max(0, (paidAmount.value || 0) - (selectedOrder.value?.total ?? 0)))

const sourceBadge = (source) =>
    source === 'qr' ? { label: 'QR', bg: 'var(--primary)' }
    : source === 'pelayan' ? { label: 'Pelayan', bg: 'var(--ink-muted)' }
    : { label: 'Kasir', bg: 'var(--ink)' }

const openPay = (order) => {
    selectedOrder.value = order
    method.value = 'tunai'
    paidAmount.value = order.total
    showPayModal.value = true
}

const submitPay = () => {
    if (processing.value) return
    processing.value = true
    router.post(`/kasir/orders/${selectedOrder.value.id}/pay`, {
        payment_method: method.value,
        paid_amount: paidAmount.value,
    }, {
        onSuccess: () => { showPayModal.value = false },
        onFinish: () => { processing.value = false },
    })
}

const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">Kasir — POS</h1>
      <p class="text-sm mt-1" style="color: var(--ink-muted);">Pilih order untuk memproses pembayaran</p>
    </div>

    <!-- Empty state -->
    <div v-if="activeOrders.length === 0" class="text-center py-20">
      <p class="text-5xl mb-4">🧾</p>
      <p class="font-bold" style="color: var(--ink);">Tidak ada order aktif</p>
      <p class="text-sm mt-1" style="color: var(--ink-muted);">Order baru akan muncul di sini secara otomatis</p>
    </div>

    <!-- Grid order aktif -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <article
        v-for="order in activeOrders" :key="order.id"
        class="card card--hoverable cursor-pointer !p-5"
        @click="openPay(order)"
      >
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="font-extrabold text-lg" style="font-family: var(--font-heading); color: var(--ink);">
              {{ order.table_name }}
            </span>
            <!-- Badge sumber order -->
            <span
              class="text-[10px] font-bold px-2 py-0.5 rounded-full"
              :style="{ backgroundColor: sourceBadge(order.source).bg, color: 'white' }"
            >
              {{ sourceBadge(order.source).label }}
            </span>
          </div>
          <span class="flex items-center gap-1 text-xs stat" style="color: var(--ink-subtle);">
            <Clock :size="12" /> {{ order.time }}
          </span>
        </div>

        <!-- Item ringkas -->
        <div class="text-sm space-y-0.5 mb-3" style="color: var(--ink-soft);">
          <div v-for="(item, i) in order.items.slice(0, 3)" :key="i" class="truncate">
            <span class="font-bold stat">{{ item.qty }}×</span> {{ item.name }}
          </div>
          <div v-if="order.items.length > 3" class="text-xs" style="color: var(--ink-subtle);">
            +{{ order.items.length - 3 }} item lainnya
          </div>
        </div>

        <div class="flex items-center justify-between pt-3" style="border-top: 1px solid var(--paper-inset);">
          <span class="text-xs font-bold uppercase" style="color: var(--ink-muted);">Total</span>
          <span class="font-extrabold stat" style="color: var(--primary);">{{ fmt(order.total) }}</span>
        </div>
      </article>
    </div>

    <!-- Modal Pembayaran (KAS-03) -->
    <div
      v-if="showPayModal"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
      style="background: rgba(10,10,10,0.5);"
      @click.self="showPayModal = false"
    >
      <div
        class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-6 max-h-[90dvh] overflow-y-auto"
        style="background: var(--paper);"
      >
        <h2 class="text-xl font-extrabold mb-1" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;">
          Pembayaran — {{ selectedOrder.table_name }}
        </h2>
        <p class="text-sm mb-5" style="color: var(--ink-muted);">
          {{ selectedOrder.items.length }} item — Total
          <span class="font-extrabold stat" style="color: var(--primary);">{{ fmt(selectedOrder.total) }}</span>
        </p>

        <!-- Pilih metode -->
        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">Metode Bayar</label>
        <div class="grid grid-cols-3 gap-2 mb-5">
          <button
            v-for="(icon, m) in methodIcons" :key="m"
            @click="method = m; paidAmount = selectedOrder.total"
            class="flex flex-col items-center gap-1.5 py-3 rounded-xl font-bold text-xs"
            :style="method === m
              ? 'background: var(--primary); color: white; border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);'
              : 'background: var(--paper-muted); color: var(--ink-muted);'"
            style="transition: background-color 200ms, color 200ms;"
          >
            <component :is="icon" :size="20" />
            {{ methodLabels[m] }}
          </button>
        </div>

        <!-- Nominal bayar -->
        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">
          Nominal Dibayar
        </label>
        <input
          v-model.number="paidAmount"
          type="number"
          class="input w-full mb-3 stat text-lg"
          min="0"
        />

        <!-- Quick cash (untuk tunai) -->
        <div v-if="method === 'tunai'" class="flex flex-wrap gap-2 mb-5">
          <button
            v-for="amount in quickCash" :key="amount"
            @click="paidAmount = amount"
            class="px-3 py-1.5 rounded-full text-xs font-bold stat"
            :style="paidAmount === amount
              ? 'background: var(--primary); color: white;'
              : 'background: var(--paper-muted); color: var(--ink-soft);'"
            style="transition: background-color 200ms, color 200ms;"
          >
            {{ fmt(amount) }}
          </button>
        </div>

        <!-- Kembalian -->
        <div class="flex items-center justify-between p-4 rounded-xl mb-5" style="background: var(--paper-muted);">
          <span class="font-bold text-sm" style="color: var(--ink);">Kembalian</span>
          <span class="text-xl font-extrabold stat" style="color: var(--primary);">{{ fmt(change) }}</span>
        </div>

        <button
          @click="submitPay"
          :disabled="paidAmount < selectedOrder.total || processing"
          class="w-full py-4 rounded-xl font-extrabold text-sm disabled:opacity-50"
          style="
            background: var(--primary); color: white;
            border: 1.5px solid var(--brut-border);
            box-shadow: var(--brut-shadow);
          "
        >
          {{ processing ? 'Memproses...' : 'Proses Pembayaran' }}
        </button>
      </div>
    </div>
  </div>
</template>