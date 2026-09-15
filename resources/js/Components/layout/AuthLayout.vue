<script setup>
// Murni layout presentasi, tidak ada logic bisnis
</script>

<template>
  <div class="flex min-h-screen items-center justify-center px-4 py-12" style="background-color: var(--paper)">
    <div class="w-full max-w-md">
      <slot />
    </div>
  </div>
</template>