<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  icon: { type: [Object, null], default: null }
})

const emit = defineEmits(['update:modelValue'])

const isNumeric = computed(() => ['tel', 'number'].includes(props.type))

const inputClasses = computed(() => [
  'input',
  { 'input--error': props.error, 'input--numeric': isNumeric.value }
])
</script>

<template>
  <div class="w-full">
    <div :class="{ 'input-group': icon }">
      <component v-if="icon" :is="icon" class="input-group-icon" :size="18" />
      <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :class="inputClasses"
        @input="emit('update:modelValue', $event.target.value)"
      />
    </div>
    <p v-if="error" class="mt-1.5 text-xs font-medium" style="color: var(--danger);">{{ error }}</p>
  </div>
</template>