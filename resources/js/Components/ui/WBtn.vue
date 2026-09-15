<script setup>
defineProps({
  variant: { type: String, default: 'secondary' },
  size: { type: String, default: 'md' },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false }
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="['btn', `btn-${variant}`, `btn-${size}`]"
  >
    <slot />
  </button>
</template>