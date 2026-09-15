// resources/js/composables/usePoll.js
// Polling 5 detik via Inertia partial reload — PRD bagian 13.2
import { onMounted, onUnmounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function usePoll(only = [], interval = 5000) {
    const isConnected = ref(true)
    const failCount   = ref(0)
    let timer = null

    function poll() {
        // Perlambat polling jika tab tidak aktif — hemat baterai (PRD 13.1 langkah 6)
        const delay = document.hidden ? interval * 3 : interval

        router.reload({
            only,
            onSuccess: () => {
                failCount.value = 0
                isConnected.value = true
            },
            onError: () => {
                failCount.value++
                if (failCount.value >= 3) isConnected.value = false
            },
        })

        timer = setTimeout(poll, delay)
    }

    onMounted(() => { timer = setTimeout(poll, interval) })
    onUnmounted(() => clearTimeout(timer))

    return { isConnected }
}