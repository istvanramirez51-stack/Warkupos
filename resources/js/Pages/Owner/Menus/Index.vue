<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/layout/AppLayout.vue'
import WCard from '@/Components/ui/WCard.vue'
import WBtn from '@/Components/ui/WBtn.vue'
import { Plus, Pencil, Trash2, X, Check, Upload, LayoutGrid, List } from '@lucide/vue'

defineOptions({ layout: AppLayout })

const page = usePage()
const props = defineProps({
    menus: Array,
    categories: Array
})

// Flash Message
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

// View mode: list (mobile default) / table (desktop)
const viewMode = ref(window.innerWidth >= 768 ? 'table' : 'list')

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
    image: null,
    is_available: true
})

// Form Kategori
const catForm = useForm({ name: '' })

const formatRp = (val) => 'Rp ' + Number(val).toLocaleString('id-ID')

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
    menuForm.image = null
    menuForm.is_available = true
    imagePreview.value = null
    isEditing.value = false
    editId.value = null
    showModal.value = true
}

const openEditModal = (menu) => {
    menuForm.name = menu.name
    menuForm.category_id = menu.category_id
    menuForm.price = menu.price
    menuForm.image = null
    menuForm.is_available = menu.is_available
    imagePreview.value = menu.image ? `/storage/${menu.image}` : null
    isEditing.value = true
    editId.value = menu.id
    showModal.value = true
}

const submitMenu = () => {
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
  <div class="w-full overflow-x-hidden">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
      <div class="min-w-0">
        <h1 class="text-2xl sm:text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">Manajemen Menu</h1>
        <p class="text-sm mt-1" style="color: var(--ink-muted);">Kelola item makanan dan minuman</p>
      </div>
      <WBtn type="button" @click="openCreateModal" class="w-full sm:w-auto justify-center">
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
        <WBtn type="submit" variant="secondary" size="sm" :disabled="catForm.processing" class="w-full sm:w-auto justify-center">Tambah Kategori</WBtn>
      </form>
      <div class="flex gap-2 flex-wrap">
        <span v-for="cat in categories" :key="cat.id" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold" style="background-color: var(--paper-inset); color: var(--ink-soft);">
          {{ cat.name }}
          <button @click="deleteCategory(cat.id)" class="hover:text-[var(--danger)] cursor-pointer"><X :size="12" /></button>
        </span>
      </div>
    </WCard>

    <!-- Toggle view mode — hanya mobile -->
    <div class="flex justify-end mb-3 md:hidden">
      <div class="inline-flex rounded-lg overflow-hidden" style="border: 1px solid var(--paper-inset);">
        <button
          @click="viewMode = 'list'"
          class="px-3 py-1.5 text-xs font-bold flex items-center gap-1"
          :style="viewMode === 'list' ? 'background: var(--primary); color: white;' : 'background: var(--paper); color: var(--ink-muted);'"
          style="transition: background-color 200ms, color 200ms;"
        >
          <List :size="14" /> Kartu
        </button>
        <button
          @click="viewMode = 'table'"
          class="px-3 py-1.5 text-xs font-bold flex items-center gap-1"
          :style="viewMode === 'table' ? 'background: var(--primary); color: white;' : 'background: var(--paper); color: var(--ink-muted);'"
          style="transition: background-color 200ms, color 200ms;"
        >
          <LayoutGrid :size="14" /> Tabel
        </button>
      </div>
    </div>

    <!-- ============ VIEW KARTU (Mobile) — layout anti-overflow ============ -->
    <div v-if="viewMode === 'list'" class="space-y-3">

      <WCard v-for="menu in menus" :key="menu.id" class="!p-3 overflow-hidden">
        <!-- Baris 1: thumbnail + info + aksi -->
        <div class="flex items-start gap-3">
          <!-- Thumbnail -->
          <div class="w-14 h-14 rounded-lg flex-shrink-0 overflow-hidden flex items-center justify-center" style="background-color: var(--paper-inset);">
            <img v-if="menu.image" :src="'/storage/' + menu.image" class="w-full h-full object-cover" />
            <Upload v-else :size="18" style="color: var(--ink-subtle);" />
          </div>

          <!-- Info — min-w-0 agar truncate bekerja -->
          <div class="flex-1 min-w-0">
            <!-- ✅ Nama + badge status SEBARIS (badge kecil, tidak mendorong layout) -->
            <div class="flex items-center gap-2 min-w-0">
              <span class="font-bold text-sm truncate" style="color: var(--ink);">{{ menu.name }}</span>
              <span
                class="text-[9px] font-bold px-1.5 py-0.5 rounded-full flex-shrink-0"
                :style="{ backgroundColor: menu.is_available ? 'var(--success)' : 'var(--danger)', color: 'white' }"
              >
                {{ menu.is_available ? '✓' : '✕' }}
              </span>
            </div>

            <div class="text-xs mt-0.5 truncate" style="color: var(--ink-muted);">
              {{ menu.category?.name ?? '—' }}
            </div>

            <div class="font-bold stat text-sm mt-1" style="color: var(--primary);">
              {{ formatRp(menu.price) }}
            </div>
          </div>

          <!-- Aksi — ikon vertikal, kecil, flex-shrink-0 -->
          <div class="flex flex-col gap-1 flex-shrink-0">
            <button @click="openEditModal(menu)" class="p-1.5 rounded-md hover:bg-[var(--paper-inset)]" style="color: var(--ink-muted);" aria-label="Edit">
              <Pencil :size="15" />
            </button>
            <button @click="deleteMenu(menu.id)" class="p-1.5 rounded-md hover:bg-[var(--paper-inset)]" style="color: var(--danger);" aria-label="Hapus">
              <Trash2 :size="15" />
            </button>
          </div>
        </div>

        <!-- Baris 2 (bawah): toggle status full-width — mudah dijangkau jempol -->
        <button
          @click="toggleMenu(menu.id)"
          class="w-full mt-3 py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5"
          :style="{
            backgroundColor: menu.is_available ? 'rgba(45, 158, 117, 0.1)' : 'rgba(214, 59, 59, 0.1)',
            color: menu.is_available ? 'var(--success)' : 'var(--danger)',
            transition: 'background-color 200ms, color 200ms'
          }"
        >
          <Check v-if="menu.is_available" :size="13" />
          <X v-else :size="13" />
          {{ menu.is_available ? 'Tersedia — ketuk untuk set Habis' : 'Habis — ketuk untuk set Tersedia' }}
        </button>
      </WCard>

      <div v-if="menus.length === 0" class="text-center py-12">
        <p class="text-4xl mb-3">🍽️</p>
        <p class="text-sm" style="color: var(--ink-subtle);">Belum ada menu. Klik "Tambah Menu" untuk memulai.</p>
      </div>
    </div>

    <!-- ============ VIEW TABEL (Desktop/Tablet) ============ -->
    <WCard v-if="viewMode === 'table'" class="!p-0 overflow-hidden">
      <div class="overflow-x-auto">
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
              <td class="px-4 py-3">
                <img v-if="menu.image" :src="'/storage/' + menu.image" class="w-12 h-12 rounded-lg object-cover" style="border: 1px solid var(--paper-inset);" />
                <div v-else class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: var(--paper-inset); color: var(--ink-subtle);">
                  <Upload :size="16" />
                </div>
              </td>
              <td class="px-4 py-3 font-medium" style="color: var(--ink-soft);">{{ menu.name }}</td>
              <td class="px-4 py-3" style="color: var(--ink-muted);">{{ menu.category?.name }}</td>
              <td class="px-4 py-3 text-right font-semibold stat" style="color: var(--ink-soft);">{{ formatRp(menu.price) }}</td>
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

    <!-- ============ MODAL — bottom-sheet mobile / center desktop ============ -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
      style="background-color: rgba(10, 10, 10, 0.5);"
      @click.self="closeModal"
    >
      <div
        class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl p-5 sm:p-6 flex flex-col"
        style="
          background-color: var(--paper);
          border: 1.5px solid var(--brut-border);
          box-shadow: var(--brut-shadow);
          max-height: 90dvh;
          padding-bottom: env(safe-area-inset-bottom, 0px);
        "
      >
        <!-- Header modal -->
        <div class="flex items-center justify-between mb-5 flex-shrink-0">
          <h2 class="text-xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink);">
            {{ isEditing ? 'Edit Menu' : 'Tambah Menu Baru' }}
          </h2>
          <button @click="closeModal" class="p-1 rounded-md sm:hidden" style="color: var(--ink-muted);">
            <X :size="22" />
          </button>
        </div>

        <!-- Form — scrollable -->
        <form @submit.prevent="submitMenu" class="space-y-4 overflow-y-auto flex-1 pr-1">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Nama Menu</label>
            <input v-model="menuForm.name" type="text" class="input" placeholder="Nasi Goreng Spesial" required />
          </div>

          <!-- Upload — tap-to-pick area -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--ink-muted);">Gambar (Opsional)</label>
            <label
              class="flex flex-col items-center justify-center gap-2 py-6 rounded-xl cursor-pointer border-2 border-dashed"
              style="border-color: var(--paper-inset); background-color: var(--paper-inset);"
            >
              <Upload :size="24" style="color: var(--ink-subtle);" />
              <span class="text-xs font-medium" style="color: var(--ink-muted);">Ketuk untuk pilih foto</span>
              <span class="text-[10px]" style="color: var(--ink-subtle);">JPG/PNG — maks 2MB</span>
              <input type="file" accept="image/png, image/jpeg" class="hidden" @change="handleImageUpload" />
            </label>

            <div v-if="imagePreview" class="mt-3 flex items-center gap-3">
              <img :src="imagePreview" class="w-20 h-20 rounded-lg object-cover" style="border: 1px solid var(--paper-inset);" />
              <button
                type="button"
                @click="imagePreview = null; menuForm.image = null"
                class="text-xs font-bold flex items-center gap-1"
                style="color: var(--danger);"
              >
                <X :size="13" /> Hapus foto
              </button>
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
            <input v-model="menuForm.price" type="number" class="input input--numeric stat" placeholder="25000" required />
          </div>

          <div class="flex items-center gap-3 pt-1">
            <button
              type="button"
              @click="menuForm.is_available = !menuForm.is_available"
              class="w-10 h-6 rounded-full relative cursor-pointer"
              :style="{
                backgroundColor: menuForm.is_available ? 'var(--success)' : 'var(--paper-inset)',
                transition: 'background-color 200ms'
              }"
            >
              <div
                class="w-4 h-4 bg-white rounded-full absolute top-1"
                :style="{
                  left: menuForm.is_available ? '1.25rem' : '0.25rem',
                  transition: 'left 200ms'
                }"
              ></div>
            </button>
            <span class="text-sm font-medium" style="color: var(--ink-soft);">Sedang Tersedia</span>
          </div>
        </form>

        <!-- Footer tombol — selalu terlihat -->
        <div class="flex gap-3 pt-4 mt-4 flex-shrink-0" style="border-top: 1px solid var(--paper-inset);">
          <WBtn type="button" variant="secondary" class="flex-1" @click="closeModal">Batal</WBtn>
          <WBtn type="submit" variant="primary" class="flex-1" :disabled="menuForm.processing">
            {{ isEditing ? 'Simpan' : 'Tambah' }}
          </WBtn>
        </div>
      </div>
    </div>

  </div>
</template>