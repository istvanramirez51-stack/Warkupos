<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import WCard from '@/Components/ui/WCard.vue'
import WBtn from '@/Components/ui/WBtn.vue'
import { Plus, Package, AlertTriangle, Pencil, Trash2, X, Check } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const page = usePage()
const props = defineProps({
    ingredients: Array,
    lowStockCount: Number,
})

const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

// ===== Tambah bahan =====
const showAddModal = ref(false)
const addForm = ref({ name: '', unit: 'kg', stock_qty: 0, min_stock: 0 })

const submitAdd = () => {
    router.post('/owner/stock', addForm.value, {
        onSuccess: () => { showAddModal.value = false; addForm.value = { name: '', unit: 'kg', stock_qty: 0, min_stock: 0 } },
    })
}

// ===== Restok / set stok (STK-02) =====
const restockTarget = ref(null)
const restockMode = ref('add')
const restockQty = ref(0)

const openRestock = (ingredient) => {
    restockTarget.value = ingredient
    restockMode.value = 'add'
    restockQty.value = 0
}
const submitRestock = () => {
    router.post(`/owner/stock/${restockTarget.value.id}/stock`, {
        mode: restockMode.value,
        qty: restockQty.value,
    }, {
        onSuccess: () => { restockTarget.value = null },
    })
}

// ===== Edit bahan =====
const editTarget = ref(null)
const editForm = ref({ name: '', unit: 'kg', min_stock: 0 })

const openEdit = (ingredient) => {
    editTarget.value = ingredient
    editForm.value = {
        name: ingredient.name,
        unit: ingredient.unit,
        min_stock: ingredient.min_stock,
    }
}
const submitEdit = () => {
    router.put(`/owner/stock/${editTarget.value.id}`, editForm.value, {
        onSuccess: () => { editTarget.value = null },
    })
}

const deleteIngredient = (id) => {
    if (confirm('Hapus bahan ini?')) router.delete(`/owner/stock/${id}`)
}

// ===== Helper =====
const fmtQty = (qty, unit) => `${Number(qty).toLocaleString('id-ID')} ${unit}`
const isLow = (ing) => Number(ing.stock_qty) <= Number(ing.min_stock)
const stockPercent = (ing) => {
    const min = Number(ing.min_stock)
    if (min <= 0) return 100
    return Math.min(100, Math.round((Number(ing.stock_qty) / (min * 3)) * 100))
}
const units = ['kg', 'gram', 'liter', 'ml', 'pcs']
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
          Stok Bahan Baku
        </h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Kelola ketersediaan bahan — update stok harian</p>
      </div>
      <WBtn @click="showAddModal = true"><Plus :size="18" /> Tambah Bahan</WBtn>
    </div>

    <!-- Flash -->
    <div v-if="flashSuccess" class="mb-4 p-3 rounded-lg text-sm font-medium" style="background: var(--paper-inset); color: var(--success); border: 1px solid var(--success);">
      {{ flashSuccess }}
    </div>
    <div v-if="flashError" class="mb-4 p-3 rounded-lg text-sm font-medium" style="background: var(--paper-inset); color: var(--danger); border: 1px solid var(--danger);">
      {{ flashError }}
    </div>

    <!-- ALERT kritis (STK-03) -->
    <div
      v-if="lowStockCount > 0"
      class="mb-6 p-4 rounded-xl flex items-start gap-3"
      style="background: rgba(214, 59, 59, 0.08); border: 1.5px solid var(--danger);"
    >
      <AlertTriangle :size="22" style="color: var(--danger); flex-shrink: 0; margin-top: 2px;" />
      <div>
        <p class="font-extrabold text-sm" style="color: var(--danger);">
          {{ lowStockCount }} bahan dalam status KRITIS
        </p>
        <p class="text-xs mt-0.5" style="color: var(--ink-muted);">
          Stok bahan berikut sudah di bawah / sama dengan batas minimum — segera restok:
        </p>
        <div class="flex flex-wrap gap-2 mt-2">
          <span
            v-for="ing in ingredients.filter(i => isLow(i))" :key="ing.id"
            class="text-xs font-bold px-2.5 py-1 rounded-full"
            style="background: var(--danger); color: white;"
          >
            {{ ing.name }} — {{ fmtQty(ing.stock_qty, ing.unit) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Tabel bahan -->
    <WCard class="!p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead style="background: var(--paper-muted);">
            <tr style="border-bottom: 1px solid var(--paper-inset);">
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs" style="color: var(--ink-muted);">Bahan</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs" style="color: var(--ink-muted);">Stok Saat Ini</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs" style="color: var(--ink-muted);">Min. Stok</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs" style="color: var(--ink-muted);">Status</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-center text-xs" style="color: var(--ink-muted);">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ing in ingredients" :key="ing.id" style="border-bottom: 1px solid var(--paper-inset);" class="hover:bg-[var(--paper-muted)]">
              <td class="px-4 py-3 font-bold" style="color: var(--ink);">{{ ing.name }}</td>

              <!-- Stok + bar visual -->
              <td class="px-4 py-3">
                <div class="font-extrabold stat" :style="{ color: isLow(ing) ? 'var(--danger)' : 'var(--ink)' }">
                  {{ fmtQty(ing.stock_qty, ing.unit) }}
                </div>
                <div class="w-28 h-1.5 rounded-full mt-1" style="background: var(--paper-inset);">
                  <div class="h-full rounded-full" style="transition: width 300ms;"
                       :style="{
                         width: stockPercent(ing) + '%',
                         background: isLow(ing) ? 'var(--danger)' : 'var(--primary)'
                       }"></div>
                </div>
              </td>

              <td class="px-4 py-3 stat" style="color: var(--ink-muted);">{{ fmtQty(ing.min_stock, ing.unit) }}</td>

              <td class="px-4 py-3">
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"
                  :style="isLow(ing)
                    ? 'background: var(--danger); color: white;'
                    : 'background: var(--success); color: white;'"
                >
                  <AlertTriangle v-if="isLow(ing)" :size="11" />
                  {{ isLow(ing) ? 'Kritis' : 'Aman' }}
                </span>
              </td>

              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1">
                  <button @click="openRestock(ing)"
                          class="px-3 py-1.5 rounded-lg text-xs font-bold"
                          style="background: var(--primary); color: white;">
                    Update Stok
                  </button>
                  <button @click="openEdit(ing)" class="p-2 rounded-md hover:bg-[var(--paper-inset)]" style="color: var(--ink-muted);">
                    <Pencil :size="15" />
                  </button>
                  <button @click="deleteIngredient(ing.id)" class="p-2 rounded-md hover:bg-[var(--paper-inset)]" style="color: var(--danger);">
                    <Trash2 :size="15" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="ingredients.length === 0">
              <td colspan="5" class="px-6 py-10 text-center" style="color: var(--ink-subtle);">
                Belum ada bahan. Klik "Tambah Bahan" untuk memulai.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </WCard>

    <!-- Modal Tambah Bahan -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(10,10,10,0.5);" @click.self="showAddModal = false">
      <div class="w-full max-w-md rounded-2xl p-6" style="background: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <h2 class="text-xl font-extrabold mb-5" style="font-family: var(--font-heading); color: var(--ink);">Tambah Bahan Baru</h2>
        <form @submit.prevent="submitAdd" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Nama Bahan</label>
            <input v-model="addForm.name" type="text" class="input w-full" placeholder="Beras" required />
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Satuan</label>
              <select v-model="addForm.unit" class="input">
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Stok Awal</label>
              <input v-model="addForm.stock_qty" type="number" step="0.01" min="0" class="input stat" required />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Min. Stok</label>
              <input v-model="addForm.min_stock" type="number" step="0.01" min="0" class="input stat" required />
            </div>
          </div>
          <p class="text-xs" style="color: var(--ink-subtle);">
            Min. stok = batas minimum. Jika stok menyentuh angka ini, status berubah "Kritis" dan muncul alert di dashboard.
          </p>
          <div class="flex gap-3 pt-2">
            <WBtn type="button" variant="secondary" class="flex-1" @click="showAddModal = false">Batal</WBtn>
            <WBtn type="submit" variant="primary" class="flex-1">Tambah</WBtn>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Update Stok (STK-02) -->
    <div v-if="restockTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(10,10,10,0.5);" @click.self="restockTarget = null">
      <div class="w-full max-w-sm rounded-2xl p-6" style="background: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <h2 class="text-xl font-extrabold mb-1" style="font-family: var(--font-heading); color: var(--ink);">
          Update Stok — {{ restockTarget.name }}
        </h2>
        <p class="text-sm stat mb-5" style="color: var(--ink-muted);">
          Stok saat ini: <span class="font-bold" style="color: var(--ink);">{{ fmtQty(restockTarget.stock_qty, restockTarget.unit) }}</span>
        </p>

        <!-- Mode -->
        <div class="grid grid-cols-2 gap-2 mb-4">
          <button
            @click="restockMode = 'add'"
            class="py-2.5 rounded-xl text-xs font-bold"
            :style="restockMode === 'add'
              ? 'background: var(--primary); color: white;'
              : 'background: var(--paper-muted); color: var(--ink-muted);'"
            style="transition: background-color 200ms, color 200ms;"
          >➕ Tambah Stok</button>
          <button
            @click="restockMode = 'set'"
            class="py-2.5 rounded-xl text-xs font-bold"
            :style="restockMode === 'set'
              ? 'background: var(--primary); color: white;'
              : 'background: var(--paper-muted); color: var(--ink-muted);'"
            style="transition: background-color 200ms, color 200ms;"
          >📦 Set Langsung</button>
        </div>

        <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">
          {{ restockMode === 'add' ? 'Jumlah Ditambahkan' : 'Stok Baru' }} ({{ restockTarget.unit }})
        </label>
        <input v-model="restockQty" type="number" step="0.01" min="0" class="input stat w-full text-lg mb-5" required />

        <div class="flex gap-3">
          <button @click="restockTarget = null" class="flex-1 py-3 rounded-xl font-bold text-sm" style="background: var(--paper-muted); color: var(--ink-soft);">Batal</button>
          <button @click="submitRestock" class="flex-1 py-3 rounded-xl font-extrabold text-sm"
                  style="background: var(--primary); color: white; border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Edit Bahan -->
    <div v-if="editTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(10,10,10,0.5);" @click.self="editTarget = null">
      <div class="w-full max-w-md rounded-2xl p-6" style="background: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <h2 class="text-xl font-extrabold mb-5" style="font-family: var(--font-heading); color: var(--ink);">Edit Bahan</h2>
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Nama Bahan</label>
            <input v-model="editForm.name" type="text" class="input w-full" required />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Satuan</label>
              <select v-model="editForm.unit" class="input">
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Min. Stok</label>
              <input v-model="editForm.min_stock" type="number" step="0.01" min="0" class="input stat" required />
            </div>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="editTarget = null" class="flex-1 py-3 rounded-xl font-bold text-sm" style="background: var(--paper-muted); color: var(--ink-soft);">Batal</button>
            <button type="submit" class="flex-1 py-3 rounded-xl font-extrabold text-sm" style="background: var(--primary); color: white;">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>