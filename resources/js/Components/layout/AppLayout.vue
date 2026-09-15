<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard,
    UtensilsCrossed,
    ShoppingCart,
    Users,
    Settings,
    LogOut,
    Menu as MenuIcon,
    ChefHat,
    ShieldCheck,
    BarChart3,
    Power
} from '@lucide/vue'

const page = usePage()

// Role user aktif — dipakai untuk menyembunyikan menu khusus owner
const userRole = page.props.auth?.user?.role ?? 'owner'

// Toggle mobile menu (drawer — Fase 4)
const isMobileMenuOpen = ref(false)

// Status warung — fallback aman kalau props global belum di-share
const warung = ref({
    name: page.props.warung?.name || 'WarkuPos',
    is_open: page.props.auth?.user?.role === 'owner'
        ? (page.props.warung?.is_open ?? true)
        : true
})

const toggleWarung = () => {
    router.post(route('owner.settings.toggle'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            warung.value.is_open = !warung.value.is_open
        }
    })
}

// Menu navigasi — per-role (PRD 4: Pengguna & Role)
// owner: semua | kasir: dashboard, pos | dapur: queue | pelayan: (nanti)
const allMenuItems = [
    { name: 'Dashboard',   icon: LayoutDashboard, route: 'owner.dashboard', roles: ['owner', 'kasir', 'dapur', 'pelayan'] },
    { name: 'Kasir (POS)', icon: ShoppingCart,    route: 'kasir.pos',       roles: ['owner', 'kasir'] },
    { name: 'Dapur',       icon: ChefHat,         route: 'dapur.queue',     roles: ['owner', 'dapur'] },
    { name: 'Menu',        icon: UtensilsCrossed, route: 'owner.menus.index', roles: ['owner'] },
    { name: 'Meja & QR',   icon: Users,           route: 'owner.tables.index', roles: ['owner'] },
    { name: 'Laporan',     icon: BarChart3,       route: 'owner.reports.daily', roles: ['owner'] },
    { name: 'Audit Trail', icon: ShieldCheck,     route: 'owner.audit.index',  roles: ['owner'] },
    { name: 'Pengaturan',  icon: Settings,        route: '#', roles: ['owner'] },
]

// Filter menu sesuai role user aktif
const menuItems = computed(() =>
    allMenuItems.filter(item => item.roles.includes(userRole))
)

// Bottom nav mobile: 4 item pertama yang tersedia untuk role ini
const bottomNavItems = computed(() => menuItems.value.slice(0, 4))

// Highlight menu aktif — via helper Ziggy
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

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-inner">
        <div class="mb-8 px-4 flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--ink);">
            <UtensilsCrossed :size="18" color="white" />
          </div>
          <span class="sidebar-label text-lg font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;">WarkuPos</span>
        </div>

        <nav class="space-y-1 px-2">
          <Link
            v-for="item in menuItems"
            :key="item.name"
            :href="item.route === '#' ? '#' : route(item.route)"
            class="nav-item"
            :style="isActive(item.route)
                ? 'background: var(--paper-inset); color: var(--primary); font-weight: 700;'
                : ''"
          >
            <component :is="item.icon" :size="20" class="flex-shrink-0" />
            <span class="sidebar-label">{{ item.name }}</span>
          </Link>
        </nav>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <header class="topbar">
        <div class="flex items-center gap-3">
          <button class="md:hidden p-2 rounded-md" style="color: var(--ink-soft);" @click="isMobileMenuOpen = !isMobileMenuOpen">
            <MenuIcon :size="24" />
          </button>
          <h2 class="text-lg font-semibold" style="font-family: var(--font-heading); color: var(--ink-soft);">{{ warung.name }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <!-- TOGGLE BUKA/TUTUP WARUNG — hanya Owner (PRD 15.2, hindari 403 untuk role lain) -->
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

      <div class="p-6">
        <slot />
      </div>
    </main>

    <!-- BOTTOM NAV (Mobile) -->
    <nav class="bottom-nav">
      <!-- Tombol Buka/Tutup untuk Mobile — hanya Owner -->
      <button
        v-if="userRole === 'owner'"
        @click="toggleWarung"
        class="bottom-nav-item"
        :style="{ color: warung.is_open ? 'var(--success)' : 'var(--danger)' }"
      >
        <Power :size="22" />
        <span class="text-[10px] font-medium mt-1">{{ warung.is_open ? 'BUKA' : 'TUTUP' }}</span>
      </button>

      <Link
        v-for="item in bottomNavItems"
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
.sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 240px; background-color: var(--paper-muted); border-right: 1px solid var(--paper-inset); z-index: 30; transition: width 200ms ease; }
.sidebar-inner { height: 100%; display: flex; flex-direction: column; padding-top: 1.5rem; overflow-y: auto; }
@media (min-width: 641px) and (max-width: 1024px) { .sidebar { width: 64px; } .sidebar-label { display: none; } }
@media (max-width: 640px) { .sidebar { display: none; } }
.nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: var(--radius-md); color: var(--ink-muted); font-size: 0.875rem; font-weight: 500; transition: background-color 200ms, color 200ms; text-decoration: none; }
.nav-item:hover { background-color: var(--paper-inset); color: var(--ink-soft); }
.main-content { flex: 1; margin-left: 240px; display: flex; flex-direction: column; transition: margin-left 200ms ease; }
@media (min-width: 641px) and (max-width: 1024px) { .main-content { margin-left: 64px; } }
@media (max-width: 640px) { .main-content { margin-left: 0; margin-bottom: 70px; } }
.topbar { height: 64px; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; background-color: var(--paper); border-bottom: 1px solid var(--paper-inset); position: sticky; top: 0; z-index: 20; }
.bottom-nav { display: none; position: fixed; bottom: 0; left: 0; right: 0; height: 70px; background-color: var(--paper-muted); border-top: 1px solid var(--paper-inset); z-index: 30; justify-content: space-around; align-items: center; padding: 0 1rem; }
@media (max-width: 640px) { .bottom-nav { display: flex; } }
.bottom-nav-item { display: flex; flex-direction: column; align-items: center; color: var(--ink-muted); transition: color 150ms; }
.bottom-nav-item:hover { color: var(--primary); }
</style>