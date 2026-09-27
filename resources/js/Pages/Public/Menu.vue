<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Minus, ShoppingCart, X, Trash2 } from '@lucide/vue'

const props = defineProps({
    table: Object,
    categories: Array,
    warung: Object,
})

// ---------- State ----------
const activeCategory = ref(props.categories[0]?.id ?? null)
const cart = ref([])
const showCart = ref(false)
const showItemModal = ref(false)
const selectedItem = ref(null)
const itemQty = ref(1)
const itemNotes = ref('')
const submitting = ref(false)

// ---------- Computed ----------
const cartTotal = computed(() => cart.value.reduce((sum, i) => sum + i.price * i.qty, 0))
const cartCount = computed(() => cart.value.reduce((sum, i) => sum + i.qty, 0))
const activeMenus = computed(() =>
    props.categories.find(c => c.id === activeCategory.value)?.menus ?? []
)

// ---------- Aksi ----------
const selectCategory = (id) => { activeCategory.value = id }

const openItem = (menu) => {
    const existing = cart.value.find(i => i.id === menu.id)
    selectedItem.value = menu
    itemQty.value = existing ? existing.qty : 1
    itemNotes.value = existing ? (existing.notes ?? '') : ''
    showItemModal.value = true
}

const addToCart = () => {
    const m = selectedItem.value
    const existing = cart.value.find(i => i.id === m.id)
    if (existing) {
        existing.qty = itemQty.value
        existing.notes = itemNotes.value || null
    } else {
        cart.value.push({ id: m.id, name: m.name, price: m.price, qty: itemQty.value, notes: itemNotes.value || null })
    }
    showItemModal.value = false
}

const removeFromCart = (id) => {
    if (!confirm('Hapus item ini dari keranjang?')) return
    cart.value = cart.value.filter(i => i.id !== id)
    if (cart.value.length === 0) showCart.value = false
}

const submitOrder = () => {
    if (submitting.value) return
    submitting.value = true
    router.post(`/order/${routeQrCode}`, {
        items: cart.value.map(i => ({ menu_id: i.id, qty: i.qty, notes: i.notes })),
    }, {
        onSuccess: () => { cart.value = []; showCart.value = false },
        onFinish: () => { submitting.value = false },
    })
}

const routeQrCode = window.location.pathname.split('/')[2]
const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID')
</script>

<template>
  <div class="min-h-screen pb-32 sm:pb-16" style="background: var(--paper); overflow-x: hidden;">

    <!-- ================= HEADER ================= -->
    <header class="sticky top-0 z-20" style="background: var(--paper); border-bottom: 1px solid var(--paper-inset);">
      <div class="flex items-center justify-between gap-3 px-4 py-3">
        <div class="min-w-0 flex-1">
          <h1
            class="text-lg font-extrabold truncate"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;"
          >
            {{ warung?.name }}
          </h1>
          <p class="text-xs truncate" style="color: var(--ink-muted);">
            Meja {{ table?.name }} — silakan pilih menu
          </p>
        </div>
        <!-- Badge meja: BIRU primary + putih -->
        <span
          class="text-xs font-bold px-3 py-1.5 rounded-full flex-shrink-0 whitespace-nowrap"
          style="background: var(--primary); color: white;"
        >
          {{ table?.name }}
        </span>
      </div>
    </header>

    <!-- ================= TAB KATEGORI ================= -->
    <div
      class="sticky top-[60px] z-10 overflow-x-auto"
      style="background: var(--paper); border-bottom: 1px solid var(--paper-inset);"
    >
      <div class="flex gap-2 px-4 py-2 w-max">
        <button
          v-for="cat in categories" :key="cat.id"
          @click="selectCategory(cat.id)"
          class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap"
          :style="activeCategory === cat.id
              ? 'background: var(--primary); color: white;'
              : 'background: var(--paper-muted); color: var(--ink-muted);'"
          style="transition: background-color 200ms, color 200ms;"
        >
          {{ cat.name }}
        </button>
      </div>
    </div>

        <!-- ================= DAFTAR MENU ================= -->
    <div class="px-4 pt-4">
      <div
        v-for="menu in activeMenus" :key="menu.id"
        class="flex items-center gap-3 p-3 mb-3 rounded-xl cursor-pointer overflow-hidden"
        style="background: var(--paper-muted); transition: background-color 200ms;"
        @click="openItem(menu)"
      >
        <!-- ✅ FOTO MENU (MNU-04) — fallback ikon kalau tidak ada foto -->
        <div class="w-16 h-16 rounded-lg flex-shrink-0 overflow-hidden flex items-center justify-center"
             style="background: var(--paper-inset);">
          <img
            v-if="menu.image"
            :src="`/storage/${menu.image}`"
            :alt="menu.name"
            class="w-full h-full object-cover"
            loading="lazy"
          />
          <span v-else class="text-2xl">🍽️</span>
        </div>

        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm leading-snug" style="color: var(--ink);">{{ menu.name }}</div>
          <div class="text-sm font-semibold mt-1 stat" style="color: var(--primary);">{{ fmt(menu.price) }}</div>
        </div>

        <button
          class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
          style="background: var(--primary);"
          aria-label="Tambah ke keranjang"
        >
          <Plus :size="18" color="white" />
        </button>
      </div>

      <p v-if="activeMenus.length === 0" class="text-center text-sm py-10" style="color: var(--ink-subtle);">
        Belum ada menu di kategori ini
      </p>
    </div>

    <!-- ================= BAR KERANJANG — BRUT #1 ================= -->
    <div v-if="cartCount > 0 && !showCart" class="fixed bottom-4 left-4 right-4 z-30">
      <button
        @click="showCart = true"
        class="w-full flex items-center justify-between px-5 py-4 rounded-xl"
        style="
          background: var(--primary); color: white;
          border: 1.5px solid var(--brut-border);
          box-shadow: var(--brut-shadow);
        "
      >
        <span class="flex items-center gap-2 font-bold text-sm">
          <ShoppingCart :size="18" />
          <span class="stat">{{ cartCount }}</span> item
        </span>
        <span class="font-extrabold stat">{{ fmt(cartTotal) }}</span>
      </button>
    </div>

    <!-- ================= BOTTOM SHEET KERANJANG ================= -->
    <div
      v-if="showCart"
      class="fixed inset-0 z-40 flex items-end justify-center"
      style="background: rgba(10,10,10,0.5);"
      @click.self="showCart = false"
    >
      <div
        class="w-full rounded-t-2xl flex flex-col"
        style="
          background: var(--paper);
          max-height: 85dvh;
          padding-bottom: env(safe-area-inset-bottom, 0px);
        "
      >
        <!-- Header sheet -->
        <div class="flex items-center justify-between px-5 pt-5 pb-3 flex-shrink-0">
          <h2
            class="text-lg font-extrabold"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;"
          >
            Keranjang
          </h2>
          <button
            @click="showCart = false"
            class="p-1 rounded-md"
            style="color: var(--ink-muted);"
            aria-label="Tutup keranjang"
          >
            <X :size="22" />
          </button>
        </div>

        <!-- Daftar item (scroll) -->
        <div class="flex-1 overflow-y-auto px-5">
          <div
            v-for="item in cart" :key="item.id"
            class="flex items-start gap-3 py-3"
            style="border-bottom: 1px solid var(--paper-inset);"
          >
            <div class="flex-1 min-w-0">
              <div class="font-bold text-sm" style="color: var(--ink);">{{ item.name }}</div>
              <div class="text-xs mt-0.5 stat" style="color: var(--ink-muted);">
                {{ item.qty }} × {{ fmt(item.price) }} =
                <span class="font-semibold" style="color: var(--ink-soft);">{{ fmt(item.qty * item.price) }}</span>
              </div>
              <div v-if="item.notes" class="text-xs italic mt-1" style="color: var(--ink-subtle);">"{{ item.notes }}"</div>
            </div>
            <!-- Hapus: netral ink-muted, ada confirm -->
            <button
              @click="removeFromCart(item.id)"
              class="p-1 flex-shrink-0"
              style="color: var(--ink-muted); transition: color 200ms;"
              aria-label="Hapus item"
            >
              <Trash2 :size="16" />
            </button>
          </div>

          <div class="flex items-center justify-between py-4">
            <span class="font-bold text-sm" style="color: var(--ink);">Total</span>
            <span class="text-lg font-extrabold stat" style="color: var(--ink);">{{ fmt(cartTotal) }}</span>
          </div>
        </div>

        <!-- Footer tombol (selalu terlihat) -->
        <div class="px-5 pb-5 pt-2 flex-shrink-0" style="border-top: 1px solid var(--paper-inset);">
          <button
            @click="submitOrder"
            :disabled="cartCount === 0 || submitting"
            class="w-full py-4 rounded-xl font-extrabold text-sm disabled:opacity-50"
            style="
              background: var(--primary); color: white;
              border: 1.5px solid var(--brut-border);
              box-shadow: var(--brut-shadow);
              transition: opacity 200ms;
            "
          >
            {{ submitting ? 'Mengirim...' : 'Kirim Pesanan' }}
          </button>
          <p class="text-center text-xs mt-2.5" style="color: var(--ink-subtle);">
            Pembayaran dilakukan di kasir setelah makan
          </p>
        </div>
      </div>
    </div>

    <!-- ================= MODAL DETAIL ITEM (QR-05) ================= -->
    <div
      v-if="showItemModal"
      class="fixed inset-0 z-50 flex items-end justify-center"
      style="background: rgba(10,10,10,0.5);"
      @click.self="showItemModal = false"
    >
      <div
        class="w-full rounded-t-2xl flex flex-col"
        style="
          background: var(--paper);
          max-height: 85dvh;
          padding-bottom: env(safe-area-inset-bottom, 0px);
        "
      >
        <div class="px-5 pt-5 pb-2 overflow-y-auto">
          <h3
            class="text-lg font-extrabold mb-0.5"
            style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;"
          >
            {{ selectedItem?.name }}
          </h3>
          <!-- Harga: biru primary -->
          <p class="text-base font-semibold stat mb-4" style="color: var(--primary);">
            {{ fmt(selectedItem?.price ?? 0) }}
          </p>

          <!-- Qty -->
          <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">
            Jumlah
          </label>
          <div class="flex items-center gap-5 mb-4">
            <button
              @click="itemQty > 1 && itemQty--"
              class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0"
              :style="`background: var(--paper-muted); color: var(--ink); opacity: ${itemQty > 1 ? 1 : 0.4};`"
              style="transition: opacity 200ms;"
              aria-label="Kurangi"
            >
              <Minus :size="18" />
            </button>
            <span class="text-xl font-extrabold stat w-8 text-center" style="color: var(--ink);">{{ itemQty }}</span>
            <button
              @click="itemQty < 99 && itemQty++"
              class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0"
              style="background: var(--paper-muted); color: var(--ink);"
              aria-label="Tambah"
            >
              <Plus :size="18" />
            </button>
          </div>

          <!-- Catatan -->
          <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">
            Catatan (opsional)
          </label>
          <input
            v-model="itemNotes"
            type="text"
            class="input w-full mb-4"
            placeholder="contoh: tidak pedas, tanpa bawang"
            maxlength="100"
          />
        </div>

        <div class="px-5 pb-5 pt-2 mt-auto flex-shrink-0">
          <button
            @click="addToCart"
            class="w-full py-4 rounded-xl font-extrabold text-sm"
            style="
              background: var(--primary); color: white;
              border: 1.5px solid var(--brut-border);
              box-shadow: var(--brut-shadow);
            "
          >
            Tambah — {{ fmt((selectedItem?.price ?? 0) * itemQty) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>