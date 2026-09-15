<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import WCard from '@/Components/ui/WCard.vue'
import WBtn from '@/Components/ui/WBtn.vue'
import { Plus, Pencil, Trash2, X, Check, Upload } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const page = usePage()
const props = defineProps({
    menus: Array,
    categories: Array
})

// Flash Message
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

// Modal State
const showModal = ref(false)
const isEditing = ref(false)
const editId = ref(null)
const imagePreview = ref(null)

// Form Menu
const menuForm = useForm({
    name: '',
    category_id: '',
    price: '',
    image: null, // Wajib null awalnya untuk file
    is_available: true
})

// Form Kategori (Inline)
const catForm = useForm({ name: '' })

// Format Rupiah Sementara
const formatRp = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)

// --- FUNCTIONS ---
const handleImageUpload = (e) => {
    const file = e.target.files[0]
    if (file) {
        menuForm.image = file
        imagePreview.value = URL.createObjectURL(file)
    }
}

const openCreateModal = () => {
    menuForm.reset()
    menuForm.image = null // Reset file
    menuForm.is_available = true
    imagePreview.value = null // Reset preview
    isEditing.value = false
    editId.value = null
    showModal.value = true
}

const openEditModal = (menu) => {
    menuForm.name = menu.name
    menuForm.category_id = menu.category_id
    menuForm.price = menu.price
    menuForm.image = null // Reset dulu, nanti isi baru jika ada upload baru
    menuForm.is_available = menu.is_available
    imagePreview.value = menu.image ? `/storage/${menu.image}` : null // Tampilkan gambar lama
    isEditing.value = true
    editId.value = menu.id
    showModal.value = true
}

const submitMenu = () => {
    // Inertia otomatis pakai multipart/form-data jika ada File
    if (isEditing.value) {
        menuForm.put(`/owner/menus/${editId.value}`, {
            onSuccess: () => closeModal()
        })
    } else {
        menuForm.post('/owner/menus', {
            onSuccess: () => closeModal()
        })
    }
}

const deleteMenu = (id) => {
    if (confirm('Yakin hapus menu ini?')) {
        router.delete(`/owner/menus/${id}`)
    }
}

const toggleMenu = (id) => {
    router.post(`/owner/menus/${id}/toggle`)
}

const submitCategory = () => {
    catForm.post('/owner/categories', {
        onSuccess: () => catForm.reset()
    })
}

const deleteCategory = (id) => {
    if (confirm('Yakin hapus kategori ini?')) {
        router.delete(`/owner/categories/${id}`)
    }
}

const closeModal = () => {
    showModal.value = false
    menuForm.reset()
    menuForm.image = null
    imagePreview.value = null
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">Manajemen Menu</h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Kelola item makanan dan minuman</p>
      </div>
      <WBtn type="button" @click="openCreateModal">
        <Plus :size="18" /> Tambah Menu
      </WBtn>
    </div>

    <!-- Alert Flash Message -->
    <div v-if="flashSuccess" class="mb-4 p-3 rounded-lg text-sm font-medium" style="background-color: var(--paper-inset); color: var(--success); border: 1px solid var(--success);">
      {{ flashSuccess }}
    </div>
    <div v-if="flashError" class="mb-4 p-3 rounded-lg text-sm font-medium" style="background-color: var(--paper-inset); color: var(--danger); border: 1px solid var(--danger);">
      {{ flashError }}
    </div>

    <!-- Section Kategori -->
    <WCard class="!p-4 mb-6">
      <form @submit.prevent="submitCategory" class="flex flex-col sm:flex-row gap-3 mb-3">
        <input v-model="catForm.name" type="text" placeholder="Nama kategori baru (misal: Paket)" class="input flex-1" required />
        <WBtn type="submit" variant="secondary" size="sm" :disabled="catForm.processing">Tambah Kategori</WBtn>
      </form>
      <div class="flex gap-2 flex-wrap">
        <span v-for="cat in categories" :key="cat.id" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--paper-inset); color: var(--ink-soft);">
          {{ cat.name }}
          <button @click="deleteCategory(cat.id)" class="hover:text-[var(--danger)] cursor-pointer"><X :size="12" /></button>
        </span>
      </div>
    </WCard>

    <!-- Tabel Menu -->
    <WCard class="!p-0 overflow-hidden">
      <div class="table-wrap overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead style="background-color: var(--paper-muted);">
            <tr style="border-bottom: 1px solid var(--paper-inset);">
              <th class="px-4 py-3 font-semibold uppercase tracking-wider" style="color: var(--ink-muted); font-size: 0.75rem;">Gambar</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider" style="color: var(--ink-muted); font-size: 0.75rem;">Nama Menu</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider" style="color: var(--ink-muted); font-size: 0.75rem;">Kategori</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-right" style="color: var(--ink-muted); font-size: 0.75rem;">Harga</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-center" style="color: var(--ink-muted); font-size: 0.75rem;">Status</th>
              <th class="px-4 py-3 font-semibold uppercase tracking-wider text-center" style="color: var(--ink-muted); font-size: 0.75rem;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="menu in menus" :key="menu.id" class="hover:bg-[var(--paper-muted)]" style="border-bottom: 1px solid var(--paper-inset); transition: background-color 150ms;">
              
              <!-- KOLOM GAMBAR BARU -->
              <td class="px-4 py-3">
                <img v-if="menu.image" :src="'/storage/' + menu.image" class="w-12 h-12 rounded-lg object-cover" style="border: 1px solid var(--paper-inset);" />
                <div v-else class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: var(--paper-inset); color: var(--ink-subtle);">
                  <Upload :size="16" />
                </div>
              </td>

              <td class="px-4 py-3 font-medium" style="color: var(--ink-soft);">{{ menu.name }}</td>
              <td class="px-4 py-3" style="color: var(--ink-muted);">{{ menu.category?.name }}</td>
              <td class="px-4 py-3 text-right font-semibold stat" style="color: var(--ink-soft); font-family: var(--font-mono); font-variant-numeric: tabular-nums;">
                {{ formatRp(menu.price) }}
              </td>
              <td class="px-4 py-3 text-center">
                <button @click="toggleMenu(menu.id)" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold cursor-pointer" :style="{ backgroundColor: menu.is_available ? 'var(--success)' : 'var(--danger)', color: 'white' }">
                  <Check v-if="menu.is_available" :size="12" />
                  <X v-else :size="12" />
                  {{ menu.is_available ? 'Tersedia' : 'Habis' }}
                </button>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1">
                  <button @click="openEditModal(menu)" class="p-2 rounded-md cursor-pointer hover:bg-[var(--paper-inset)]" style="color: var(--ink-muted);"><Pencil :size="16" /></button>
                  <button @click="deleteMenu(menu.id)" class="p-2 rounded-md cursor-pointer hover:bg-[var(--paper-inset)]" style="color: var(--danger);"><Trash2 :size="16" /></button>
                </div>
              </td>
            </tr>
            <tr v-if="menus.length === 0">
              <td colspan="6" class="px-6 py-8 text-center" style="color: var(--ink-subtle);">Belum ada menu. Klik "Tambah Menu" untuk memulai.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </WCard>

    <!-- Modal Tambah/Edit -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(10, 10, 10, 0.5);" @click.self="closeModal">
      <div class="w-full max-w-md rounded-2xl p-6" style="background-color: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <h2 class="text-xl font-extrabold mb-6" style="font-family: var(--font-heading); color: var(--ink);">
          {{ isEditing ? 'Edit Menu' : 'Tambah Menu Baru' }}
        </h2>

        <form @submit.prevent="submitMenu" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Nama Menu</label>
            <input v-model="menuForm.name" type="text" class="input" placeholder="Nasi Goreng Spesial" required />
          </div>

          <!-- UPLOAD GAMBAR BARU DI MODAL -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Gambar (Opsional)</label>
            <input 
              type="file" 
              accept="image/png, image/jpeg" 
              class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold cursor-pointer"
              style="color: var(--ink-soft); background-color: var(--paper-inset);"
              @change="handleImageUpload"
            />
            <p class="text-xs mt-1" style="color: var(--ink-subtle);">Maks. 2MB (JPG/PNG)</p>
            
            <!-- Preview Gambar -->
            <div v-if="imagePreview" class="mt-3">
              <img :src="imagePreview" class="w-24 h-24 rounded-lg object-cover" style="border: 1px solid var(--paper-inset);" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Kategori</label>
            <select v-model="menuForm.category_id" class="input" required>
              <option value="" disabled>Pilih Kategori</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Harga (Rp)</label>
            <input v-model="menuForm.price" type="number" class="input input--numeric" placeholder="25000" required />
          </div>

          <div class="flex items-center gap-3 pt-2">
            <button type="button" @click="menuForm.is_available = !menuForm.is_available" class="w-10 h-6 rounded-full relative cursor-pointer transition-colors 200ms" :style="{ backgroundColor: menuForm.is_available ? 'var(--success)' : 'var(--paper-inset)' }">
              <div class="w-4 h-4 bg-white rounded-full absolute top-1 transition-all 200ms" :style="{ left: menuForm.is_available ? '1.25rem' : '0.25rem' }"></div>
            </button>
            <span class="text-sm font-medium" style="color: var(--ink-soft);">Sedang Tersedia</span>
          </div>

          <div class="flex gap-3 pt-4">
            <WBtn type="button" variant="secondary" class="flex-1" @click="closeModal">Batal</WBtn>
            <WBtn type="submit" variant="primary" class="flex-1" :disabled="menuForm.processing">
              {{ isEditing ? 'Simpan' : 'Tambah' }}
            </WBtn>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>