<script setup>
defineProps({ qrCode: String, order: Object })
const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')
</script>

<template>
  <div class="min-h-screen p-4 sm:p-6" style="background: var(--paper);">
    <div class="max-w-md mx-auto pt-8 sm:pt-12">
      <div class="text-center mb-8">
        <!-- Ikon sukses: biru primary, bukan hijau -->
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
          style="background: var(--primary); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);"
        >
          <span class="text-2xl" style="color: white;">✓</span>
        </div>

        <h1
          class="text-2xl font-extrabold mb-2"
          style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;"
        >
          Pesanan Terkirim!
        </h1>
        <p class="text-sm" style="color: var(--ink-muted);">
          Dapur sedang memproses pesanan kamu. Estimasi penyajian ±15 menit.
        </p>
      </div>

      <!-- Ringkasan — satu-satunya brut di halaman ini -->
      <div
        class="rounded-xl p-5 mb-6"
        style="background: var(--paper); border: 1.5px solid var(--brut-border); box-shadow: var(--brut-shadow);"
      >
        <div class="flex justify-between items-center gap-2 mb-3">
          <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--ink-muted);">
            Ringkasan
          </span>
          <!-- Badge meja: biru primary -->
          <span
            class="text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0 whitespace-nowrap"
            style="background: var(--primary); color: white;"
          >
            {{ qrCode }}
          </span>
        </div>

        <div
          v-for="(item, idx) in order.items" :key="idx"
          class="flex justify-between gap-3 py-2.5"
          style="border-bottom: 1px solid var(--paper-inset);"
        >
          <span class="text-sm min-w-0" style="color: var(--ink);">
            <span class="font-bold stat">{{ item.qty }}×</span> {{ item.name }}
            <span v-if="item.notes" class="block text-xs italic mt-0.5" style="color: var(--ink-subtle);">
              "{{ item.notes }}"
            </span>
          </span>
          <span class="text-sm stat font-semibold flex-shrink-0" style="color: var(--ink-soft);">
            {{ fmt(item.price * item.qty) }}
          </span>
        </div>

        <div class="flex justify-between items-center pt-4">
          <span class="font-extrabold" style="color: var(--ink);">Total</span>
          <!-- Total: biru primary -->
          <span class="text-xl font-extrabold stat" style="color: var(--primary);">
            {{ fmt(order.total) }}
          </span>
        </div>
      </div>

      <p class="text-center text-xs" style="color: var(--ink-subtle);">
        Pembayaran dilakukan di kasir. Terima kasih! 🙏
      </p>
    </div>
  </div>
</template>