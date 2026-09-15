<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import WCard from '@/Components/ui/WCard.vue'
import WBtn from '@/Components/ui/WBtn.vue'
import { Plus, Pencil, Trash2, QrCode, Users, Printer, RefreshCw, Copy, Check } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ tables: Array })

const showModal = ref(false)
const isEditing = ref(false)
const editId = ref(null)
const copiedId = ref(null)

const form = useForm({ name: '', capacity: 4 })

const openCreate = () => { form.reset(); form.capacity = 4; isEditing.value = false; editId.value = null; showModal.value = true }
const openEdit = (table) => { form.name = table.name; form.capacity = table.capacity; isEditing.value = true; editId.value = table.id; showModal.value = true }

const submit = () => {
    if (isEditing.value) {
        form.put(`/owner/tables/${editId.value}`, { onSuccess: () => closeModal() })
    } else {
        form.post('/owner/tables', { onSuccess: () => closeModal() })
    }
}
const deleteTable = (id) => { if (confirm('Hapus meja ini?')) router.delete(`/owner/tables/${id}`) }
const closeModal = () => { showModal.value = false; form.reset() }

// ✅ Fix bug: window tidak bisa diakses di template — hitung di script
const qrUrl = (table) => `${window.location.origin}/order/${table.qr_code}`

// Preview QR — generate lokal via endpoint owner
const qrImgSrc = (table) => `/owner/tables/${table.id}/qr`

const printQr = (table) => { window.open(`/owner/tables/${table.id}/qr/print`, '_blank') }

const regenerateQr = (table) => {
    if (confirm(`Generate ulang QR untuk ${table.name}? QR lama tidak akan berfungsi lagi.`)) {
        router.post(`/owner/tables/${table.id}/qr/regenerate`)
    }
}

const copyLink = async (table) => {
    try {
        await navigator.clipboard.writeText(qrUrl(table))
        copiedId.value = table.id
        setTimeout(() => { copiedId.value = null }, 2000)
    } catch (e) {
        console.error('Gagal menyalin:', e)
    }
}

// Warna status berdasarkan PRD 5.5
const statusConfig = {
    available: { color: 'var(--success)', label: 'Kosong', bg: 'rgba(45, 158, 117, 0.1)' },
    occupied: { color: 'var(--warning)', label: 'Ada Order', bg: 'rgba(217, 119, 6, 0.1)' },
    waiting_payment: { color: 'var(--danger)', label: 'Menunggu Bayar', bg: 'rgba(214, 59, 59, 0.1)' }
}
</script>

<template>
  <div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">Manajemen Meja</h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Kelola meja dan QR Code self-order</p>
      </div>
      <WBtn @click="openCreate"><Plus :size="18" /> Tambah Meja</WBtn>
    </div>

    <!-- Grid Meja -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <WCard
        v-for="table in tables" :key="table.id"
        class="!p-4 hoverable cursor-pointer relative overflow-hidden"
        @click="openEdit(table)"
      >
        <!-- Background Status Color -->
        <div class="absolute inset-0 opacity-50" :style="{ backgroundColor: statusConfig[table.status]?.bg }"></div>

        <div class="relative z-10 text-center">
          <div class="text-2xl font-extrabold mb-2" style="font-family: var(--font-heading); color: var(--ink);">
            {{ table.name }}
          </div>

          <!-- Kapasitas -->
          <div class="flex items-center justify-center gap-1 mb-3" style="color: var(--ink-muted);">
            <Users :size="14" />
            <span class="text-xs font-medium">{{ table.capacity }} orang</span>
          </div>

          <!-- QR Code — generate lokal, tanpa API eksternal -->
          <img
            :src="qrImgSrc(table)"
            :alt="`QR Code ${table.name}`"
            class="w-20 h-20 mx-auto rounded-lg border p-1 mb-3"
            style="border-color: var(--paper-inset); background: white;"
          />

          <!-- Aksi QR -->
          <div class="flex items-center justify-center gap-1 mb-3">
            <button @click.stop="printQr(table)" title="Print QR"
              class="p-1.5 rounded-md hover:bg-[var(--paper-muted)] transition-colors"
              style="color: var(--primary);">
              <Printer :size="14" />
            </button>
            <button @click.stop="copyLink(table)" title="Salin link"
              class="p-1.5 rounded-md hover:bg-[var(--paper-muted)] transition-colors"
              style="color: var(--ink-muted);">
              <Check v-if="copiedId === table.id" :size="14" style="color: var(--success);" />
              <Copy v-else :size="14" />
            </button>
            <button @click.stop="regenerateQr(table)" title="Generate ulang QR"
              class="p-1.5 rounded-md hover:bg-[var(--paper-muted)] transition-colors"
              style="color: var(--warning);">
              <RefreshCw :size="14" />
            </button>
          </div>

          <!-- Badge Status -->
          <span
            class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
            :style="{ backgroundColor: statusConfig[table.status]?.color, color: 'white' }"
          >
            {{ statusConfig[table.status]?.label }}
          </span>
        </div>

        <!-- Tombol Hapus (Muncul saat hover) -->
        <button @click.stop="deleteTable(table.id)" class="absolute top-2 right-2 p-1.5 rounded-md opacity-0 hover:opacity-100 transition-opacity bg-[var(--paper)] shadow-sm" style="color: var(--danger);">
          <Trash2 :size="14" />
        </button>
      </WCard>
    </div>

    <!-- Modal Tambah/Edit -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(10, 10, 10, 0.5);" @click.self="closeModal">
      <div class="w-full max-w-sm rounded-2xl p-6" style="background-color: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <h2 class="text-xl font-extrabold mb-6" style="font-family: var(--font-heading); color: var(--ink);">{{ isEditing ? 'Edit' : 'Tambah' }} Meja</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Nama Meja</label>
            <input v-model="form.name" type="text" class="input" placeholder="Meja 1" required />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Kapasitas (Orang)</label>
            <input v-model="form.capacity" type="number" class="input input--numeric" min="1" required />
          </div>
          <div class="flex gap-3 pt-2">
            <WBtn type="button" variant="secondary" class="flex-1" @click="closeModal">Batal</WBtn>
            <WBtn type="submit" variant="primary" class="flex-1" :disabled="form.processing">{{ isEditing ? 'Simpan' : 'Tambah' }}</WBtn>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>