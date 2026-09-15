<script setup>
import AppLayout from '@/Components/layout/AppLayout.vue'
import WCard from '@/Components/ui/WCard.vue'
import WBtn from '@/Components/ui/WBtn.vue'
import { UtensilsCrossed, LayoutGrid, Armchair } from '@lucide/vue'

defineOptions({ layout: AppLayout })

// PENGAMAN: Kasih default value agar tidak crash
const props = defineProps({
    stats: { 
        type: Object, 
        default: () => ({ totalMenus: 0, activeMenus: 0, totalTables: 0, availableTables: 0 }) 
    },
    warung: { 
        type: Object, 
        default: () => ({ name: 'WarkuPos', is_open: false }) 
    }
})
</script>

<template>
  <div>
    <!-- Header Halaman -->
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
        Dashboard
      </h1>
      <p class="mt-1 text-sm" style="color: var(--ink-muted);">
        Selamat datang, <span class="font-semibold" style="color: var(--ink-soft);">{{ warung.name }}</span>
      </p>
    </div>

    <!-- Grid Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- KARTU 1: STATUS WARUNG (SHADOW BRUTAL) -->
      <WCard class="!p-6" style="border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <div class="flex items-center justify-between mb-4">
          <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--ink-muted);">Status Warung</span>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: var(--ink);">
            <UtensilsCrossed :size="20" color="white" />
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: warung.is_open ? 'var(--success)' : 'var(--danger)' }"></div>
          <span class="text-2xl font-extrabold" style="font-family: var(--font-heading); color: var(--ink);">
            {{ warung.is_open ? 'BUKA' : 'TUTUP' }}
          </span>
        </div>
        <WBtn variant="accent" size="sm" class="w-full mt-4">
          {{ warung.is_open ? 'Tutup Warung' : 'Buka Warung' }}
        </WBtn>
      </WCard>

      <!-- KARTU 2: TOTAL MENU (SHADOW BRUTAL) -->
      <WCard class="!p-6" style="border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);">
        <div class="flex items-center justify-between mb-4">
          <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--ink-muted);">Total Menu</span>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: var(--ink);">
            <LayoutGrid :size="20" color="white" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-4xl font-extrabold stat" style="font-family: var(--font-heading); color: var(--ink);">{{ stats.activeMenus }}</span>
          <span class="text-sm font-medium" style="color: var(--ink-subtle);">/ {{ stats.totalMenus }} aktif</span>
        </div>
      </WCard>

      <!-- KARTU 3: MEJA -->
      <WCard class="!p-6" style="box-shadow: var(--shadow-sm); border: 1px solid var(--paper-inset);">
        <div class="flex items-center justify-between mb-4">
          <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--ink-muted);">Meja Tersedia</span>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: var(--paper-inset); color: var(--ink-muted);">
            <Armchair :size="20" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-4xl font-extrabold stat" style="font-family: var(--font-heading); color: var(--ink);">{{ stats.availableTables }}</span>
          <span class="text-sm font-medium" style="color: var(--ink-subtle);">/ {{ stats.totalTables }} meja</span>
        </div>
      </WCard>

    </div>
  </div>
</template>