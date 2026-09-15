<script setup>
defineProps({ warung: Object })
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center text-center p-6" style="background: var(--paper);">
    <div class="text-6xl mb-6">😴</div>
    <h1 class="text-3xl font-extrabold mb-2" style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
      Warung Sedang Tutup
    </h1>
    <p class="text-base mb-1" style="color: var(--ink-muted);">
      {{ warung?.name ?? 'Warung' }} sedang tidak menerima pesanan.
    </p>
    <p class="text-sm" style="color: var(--ink-subtle);">
      Silakan kembali lagi saat jam operasional ya. Terima kasih! 🙏
    </p>
  </div>
</template>