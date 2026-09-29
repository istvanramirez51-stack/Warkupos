<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard,
    UtensilsCrossed,
    ShoppingCart,
    Users,
    Settings,
    LogOut,
    Menu as MenuIcon,
    X,
    ChefHat,
    ShieldCheck,
    BarChart3,
    Package,
    Power
} from '@lucide/vue'

const page = usePage()

// Role user aktif
const userRole = page.props.auth?.user?.role ?? 'owner'

// ===== Mobile drawer =====
const isMobileMenuOpen = ref(false)

// Tutup drawer otomatis setiap pindah halaman (klik link)
watch(() => page.url, () => {
    isMobileMenuOpen.value = false
})

// Status warung
const warung = ref({
    name: page.props.warung?.name || 'WarkuPos',
    is_open: page.props.warung?.is_open ?? true
})

const toggleWarung = () => {
    router.post(route('owner.settings.toggle'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            warung.value.is_open = !warung.value.is_open
        }
    })
}

// Menu per-role (PRD 4)
const allMenuItems = [
    { name: 'Dashboard',   icon: LayoutDashboard, route: 'owner.dashboard',     roles: ['owner', 'kasir', 'dapur', 'pelayan'] },
    { name: 'Kasir (POS)', icon: ShoppingCart,    route: 'kasir.pos',           roles: ['owner', 'kasir'] },
    { name: 'Dapur',       icon: ChefHat,         route: 'dapur.queue',         roles: ['owner', 'dapur'] },
    { name: 'Menu',        icon: UtensilsCrossed, route: 'owner.menus.index',   roles: ['owner'] },
    { name: 'Meja & QR',   icon: Users,           route: 'owner.tables.index',  roles: ['owner'] },
    { name: 'Laporan',     icon: BarChart3,       route: 'owner.reports.daily', roles: ['owner'] },
    { name: 'Audit Trail', icon: ShieldCheck,     route: 'owner.audit.index',   roles: ['owner'] },
    { name: 'Stok Bahan',  icon: Package,         route: 'owner.stock.index',   roles: ['owner'] },
    { name: 'Pengaturan',  icon: Settings,        route: '#',                   roles: ['owner'] },
]

const menuItems = computed(() =>
    allMenuItems.filter(item => item.roles.includes(userRole))
)

// Bottom nav: 4 item pertama per role
const bottomNavItems = computed(() => menuItems.value.slice(0, 4))

// Highlight menu aktif
const isActive = (routeName) => {
    if (routeName === '#') return false
    try {
        return route().current(routeName + '.*') || route().current(routeName)
    } catch {
        return false
    }
}
</script>

<template>
  <div class="app-layout-container">

    <!-- ===== OVERLAY (mobile — saat drawer terbuka) ===== -->
    <transition name="fade">
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 z-40 md:hidden"
        style="background: rgba(10, 10, 10, 0.5);"
        @click="isMobileMenuOpen = false"
      ></div>
    </transition>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" :class="{ 'sidebar--open': isMobileMenuOpen }">
      <div class="sidebar-inner">
        <!-- Logo + tombol close (mobile) -->
        <div class="mb-8 px-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--ink);">
              <UtensilsCrossed :size="18" color="white" />
            </div>
            <span class="sidebar-label text-lg font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;">WarkuPos</span>
          </div>
          <!-- Close drawer (hanya mobile) -->
          <button
            class="md:hidden p-2 rounded-md"
            style="color: var(--ink-muted);"
            @click="isMobileMenuOpen = false"
            aria-label="Tutup menu"
          >
            <X :size="22" />
          </button>
        </div>

        <!-- Info user + toggle warung (mobile — karena toggle topbar disembunyikan) -->
        <div class="px-4 mb-4 md:hidden">
          <div class="p-3 rounded-xl" style="background: var(--paper-inset);">
            <div class="text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--ink-muted);">Status Warung</div>
            <button
              v-if="userRole === 'owner'"
              @click="toggleWarung"
              class="w-full py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2"
              :style="{
                backgroundColor: warung.is_open ? 'var(--success)' : 'var(--danger)',
                color: 'white',
                transition: 'background-color 200ms'
              }"
            >
              <Power :size="14" />
              {{ warung.is_open ? 'BUKA — ketuk untuk Tutup' : 'TUTUP — ketuk untuk Buka' }}
            </button>
            <div v-else class="text-sm font-bold text-center"
                 :style="{ color: warung.is_open ? 'var(--success)' : 'var(--danger)' }">
              {{ warung.is_open ? '● BUKA' : '● TUTUP' }}
            </div>
          </div>
        </div>

        <!-- Navigasi -->
        <nav class="space-y-1 px-2">
          <Link
            v-for="item in menuItems"
            :key="item.name"
            :href="item.route === '#' ? '#' : route(item.route)"
            class="nav-item"
            :style="isActive(item.route)
                ? 'background: var(--paper-inset); color: var(--primary); font-weight: 700;'
                : ''"
            @click="isMobileMenuOpen = false"
          >
            <component :is="item.icon" :size="20" class="flex-shrink-0" />
            <span class="sidebar-label">{{ item.name }}</span>
          </Link>
        </nav>
      </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
      <header class="topbar">
        <div class="flex items-center gap-3">
          <!-- Hamburger — sekarang BERFUNGSI (buka drawer) -->
          <button
            class="md:hidden p-2 rounded-md"
            style="color: var(--ink-soft);"
            @click="isMobileMenuOpen = true"
            aria-label="Buka menu"
          >
            <MenuIcon :size="24" />
          </button>
          <h2 class="text-lg font-semibold truncate" style="font-family: var(--font-heading); color: var(--ink-soft);">{{ warung.name }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <!-- Toggle warung — desktop saja (mobile ada di drawer) -->
          <button
            v-if="userRole === 'owner'"
            @click="toggleWarung"
            class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold cursor-pointer"
            :style="{
              backgroundColor: warung.is_open ? 'var(--success)' : 'var(--danger)',
              color: 'white',
              transition: 'background-color 200ms'
            }"
          >
            <Power :size="14" />
            {{ warung.is_open ? 'BUKA' : 'TUTUP' }}
          </button>

          <Link :href="route('logout')" method="post" as="button" class="p-2 rounded-md hover:bg-[var(--paper-muted)]" style="color: var(--ink-subtle);">
            <LogOut :size="20" />
          </Link>
        </div>
      </header>

      <div class="p-4 sm:p-6">
        <slot />
      </div>
    </main>

    <!-- ===== BOTTOM NAV (mobile) ===== -->
    <nav class="bottom-nav">
      <!-- Toggle warung — hanya owner -->
      <button
        v-if="userRole === 'owner'"
        @click="toggleWarung"
        class="bottom-nav-item"
        :style="{ color: warung.is_open ? 'var(--success)' : 'var(--danger)' }"
      >
        <Power :size="22" />
        <span class="text-[10px] font-medium mt-1">{{ warung.is_open ? 'BUKA' : 'TUTUP' }}</span>
      </button>

      <!-- Tombol ☰ "Menu" — buka drawer (AKSES KE SEMUA MENU!) -->
      <button @click="isMobileMenuOpen = true" class="bottom-nav-item" style="color: var(--ink-muted);">
        <MenuIcon :size="22" />
        <span class="text-[10px] font-medium mt-1">Menu</span>
      </button>

      <!-- 3 item pertama per role -->
      <Link
        v-for="item in bottomNavItems.slice(0, 3)"
        :key="item.name"
        :href="item.route === '#' ? '#' : route(item.route)"
        class="bottom-nav-item"
        :style="isActive(item.route) ? 'color: var(--primary);' : ''"
      >
        <component :is="item.icon" :size="22" />
        <span class="text-[10px] font-medium mt-1">{{ item.name }}</span>
      </Link>
    </nav>

  </div>
</template>

<style scoped>
.app-layout-container { display: flex; min-height: 100vh; background-color: var(--paper); }

/* Sidebar — fixed. Di mobile: slide-in dari kiri (drawer) */
.sidebar {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: 240px;
  background-color: var(--paper-muted);
  border-right: 1px solid var(--paper-inset);
  z-index: 50;
  transition: transform 250ms ease, width 200ms ease;
}
.sidebar-inner { height: 100%; display: flex; flex-direction: column; padding-top: 1.5rem; overflow-y: auto; }

/* Tablet: sidebar mengecil, hanya ikon */
@media (min-width: 641px) and (max-width: 1024px) {
  .sidebar { width: 64px; }
  .sidebar-label { display: none; }
}

/* ✅ Mobile: sidebar default tersembunyi DI LUAR layar (bukan display:none),
   muncul slide-in saat .sidebar--open */
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar--open { transform: translateX(0); }
}

/* Overlay fade */
.fade-enter-active, .fade-leave-active { transition: opacity 250ms ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: var(--radius-md); color: var(--ink-muted); font-size: 0.875rem; font-weight: 500; transition: background-color 200ms, color 200ms; text-decoration: none; }
.nav-item:hover { background-color: var(--paper-inset); color: var(--ink-soft); }

.main-content { flex: 1; margin-left: 240px; display: flex; flex-direction: column; transition: margin-left 200ms ease; }
@media (min-width: 641px) and (max-width: 1024px) { .main-content { margin-left: 64px; } }
@media (max-width: 768px) { .main-content { margin-left: 0; margin-bottom: 70px; } }

.topbar { height: 64px; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; background-color: var(--paper); border-bottom: 1px solid var(--paper-inset); position: sticky; top: 0; z-index: 20; }
@media (min-width: 640px) { .topbar { padding: 0 1.5rem; } }

.bottom-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; height: 70px; background-color: var(--paper-muted); border-top: 1px solid var(--paper-inset); z-index: 30; justify-content: space-around; align-items: center; padding: 0 0.5rem; padding-bottom: env(safe-area-inset-bottom, 0px); }
@media (max-width: 768px) { .bottom-nav { display: flex; } }
.bottom-nav-item { display: flex; flex-direction: column; align-items: center; color: var(--ink-muted); transition: color 150ms; }
.bottom-nav-item:hover { color: var(--primary); }
</style>