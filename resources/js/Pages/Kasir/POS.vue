<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import { ShoppingCart, Banknote, QrCode, ArrowLeftRight, Clock, Printer, MessageCircle } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
    activeOrders: Array,
    receipt: { type: Object, default: null }, // flash dari pay()
})

const selectedOrder = ref(null)
const showPayModal = ref(false)
const method = ref('tunai')
const paidAmount = ref(0)
const processing = ref(false)

// ===== Struk (KAS-04) =====
const showReceiptModal = ref(false)
const receiptData = ref(null)
const waPhone = ref('') // nomor HP pelanggan — opsional (PRD 12.3)

// Buka modal struk otomatis setelah pembayaran sukses (flash baru masuk)
watch(() => props.receipt, (val) => {
    if (val) {
        receiptData.value = val
        showReceiptModal.value = true
        waPhone.value = ''
    }
})

const printReceipt = () => {
    // Buka tab baru — Blade view auto window.print() (PRD 12.2)
    window.open(`/kasir/receipts/${receiptData.value.id}/print`, '_blank')
}

const sendWhatsApp = () => {
    const d = receiptData.value
    // Teks plain-text — format sesuai contoh PRD 12.3
    let text = ''
    text += `${'='.repeat(28)}\n`
    text += `${d.warung.name.toUpperCase().padStart(16, ' ').padStart(4)}\n`
    if (d.warung.address) text += `${d.warung.address}\n`
    if (d.warung.phone) text += `WA: ${d.warung.phone}\n`
    text += `${'='.repeat(28)}\n\n`
    text += `No  : ${d.number}\n`
    text += `Tgl : ${d.datetime}\n`
    text += `Meja: ${d.table}  Kasir: ${d.kasir}\n`
    text += `${'-'.repeat(28)}\n`
    d.items.forEach(i => {
        text += `${i.name} x${i.qty}\n`
        text += `${fmt(i.price)} = ${fmt(i.price * i.qty)}\n`
    })
    text += `${'-'.repeat(28)}\n`
    text += `TOTAL   ${fmt(d.total).padStart(20)}\n`
    text += `Bayar (${d.method}) ${fmt(d.paid).padStart(9)}\n`
    text += `Kembali ${fmt(d.change).padStart(20)}\n`
    text += `${'='.repeat(28)}\n`
    text += `Terima kasih atas kunjungan\nAnda! Sampai jumpa :)`

    // Nomor pelanggan opsional — kalau kosong, WA terbuka tanpa tujuan (PRD 12.3)
    const phone = waPhone.value.replace(/[^0-9]/g, '').replace(/^0/, '62')
    const url = phone
        ? `https://wa.me/${phone}?text=${encodeURIComponent(text)}`
        : `https://wa.me/?text=${encodeURIComponent(text)}`
    window.open(url, '_blank')
}

const closeReceipt = () => {
    showReceiptModal.value = false
    // Bersihkan flash dengan reload halaman (tanpa flash) — quiet
    router.reload({ only: ['activeOrders'] })
    receiptData.value = null
}

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

        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">
          Nominal Dibayar
        </label>
        <input
          v-model.number="paidAmount"
          type="number"
          class="input w-full mb-3 stat text-lg"
          min="0"
        />

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

    <!-- ============ MODAL STRUK (KAS-04) ============ -->
    <div
      v-if="showReceiptModal && receiptData"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
      style="background: rgba(10,10,10,0.5);"
      @click.self="closeReceipt"
    >
      <div
        class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-6 max-h-[90dvh] overflow-y-auto"
        style="background: var(--paper);"
      >
        <!-- Konfirmasi sukses -->
        <div class="text-center mb-5">
          <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center"
               style="background: var(--primary);">
            <span class="text-xl text-white font-extrabold">✓</span>
          </div>
          <h2 class="text-xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;">
            Pembayaran Berhasil
          </h2>
          <p class="text-sm stat mt-1" style="color: var(--ink-muted);">
            {{ receiptData.number }} — Kembalian
            <span class="font-bold" style="color: var(--primary);">{{ fmt(receiptData.change) }}</span>
          </p>
        </div>

        <!-- Ringkasan struk -->
        <div class="rounded-xl p-4 mb-5 text-sm" style="background: var(--paper-muted);">
          <div v-for="(item, i) in receiptData.items" :key="i"
               class="flex justify-between py-1"
               style="border-bottom: 1px dashed var(--paper-inset);">
            <span style="color: var(--ink);">
              <span class="font-bold stat">{{ item.qty }}×</span> {{ item.name }}
            </span>
            <span class="stat" style="color: var(--ink-soft);">{{ fmt(item.price * item.qty) }}</span>
          </div>
          <div class="flex justify-between pt-2 font-extrabold">
            <span style="color: var(--ink);">Total</span>
            <span class="stat" style="color: var(--primary);">{{ fmt(receiptData.total) }}</span>
          </div>
        </div>

        <!-- No HP pelanggan — opsional (PRD 12.3) -->
        <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">
          No. HP Pelanggan (opsional — untuk WhatsApp)
        </label>
        <input
          v-model="waPhone"
          type="tel"
          class="input w-full mb-5 stat"
          placeholder="0812xxxx — kosongkan untuk pilih manual"
        />

        <!-- Tombol aksi struk -->
        <div class="grid grid-cols-2 gap-3 mb-3">
          <button
            @click="printReceipt"
            class="py-4 rounded-xl font-extrabold text-sm flex items-center justify-center gap-2"
            style="background: var(--primary); color: white; border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);"
          >
            <Printer :size="18" />
            Cetak Struk
          </button>
          <button
            @click="sendWhatsApp"
            class="py-4 rounded-xl font-extrabold text-sm flex items-center justify-center gap-2"
            style="background: var(--ink); color: white;"
          >
            <MessageCircle :size="18" />
            WhatsApp
          </button>
        </div>

        <button
          @click="closeReceipt"
          class="w-full py-3 rounded-xl font-bold text-sm"
          style="background: var(--paper-muted); color: var(--ink-soft);"
        >
          Selesai
        </button>
      </div>
    </div>
  </div>
</template>