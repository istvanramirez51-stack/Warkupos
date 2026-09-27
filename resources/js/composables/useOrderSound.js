// resources/js/composables/useOrderSound.js
// Notifikasi bunyi (PRD DPR-04) — generate WAV di runtime + HTMLAudioElement.
// Tidak pakai Web Audio API, tidak perlu file audio eksternal.

import { ref } from 'vue'

export function useOrderSound() {
    const seenOrderIds = ref(new Set())

    /**
     * Buat WAV "ding-dong" secara programatik (PCM 16-bit, 22050 Hz).
     * Nada 880Hz 0.25s lalu 660Hz 0.3s, dengan fade-out per nada.
     */
    const buildWavDataUri = () => {
        const sampleRate = 22050
        const seg1 = Math.floor(sampleRate * 0.25) // nada 1: 880Hz
        const seg2 = Math.floor(sampleRate * 0.30) // nada 2: 660Hz
        const totalSamples = seg1 + seg2
        const dataSize = totalSamples * 2 // 16-bit = 2 byte per sample

        const buffer = new ArrayBuffer(44 + dataSize)
        const view = new DataView(buffer)

        // --- Header WAV (44 byte) ---
        const writeStr = (offset, str) => {
            for (let i = 0; i < str.length; i++) view.setUint8(offset + i, str.charCodeAt(i))
        }
        writeStr(0, 'RIFF')
        view.setUint32(4, 36 + dataSize, true)
        writeStr(8, 'WAVE')
        writeStr(12, 'fmt ')
        view.setUint32(16, 16, true)          // ukuran chunk fmt
        view.setUint16(20, 1, true)           // PCM
        view.setUint16(22, 1, true)           // mono
        view.setUint32(24, sampleRate, true)
        view.setUint32(28, sampleRate * 2, true) // byte rate
        view.setUint16(32, 2, true)           // block align
        view.setUint16(34, 16, true)          // bits per sample
        writeStr(36, 'data')
        view.setUint32(40, dataSize, true)

        // --- Sampel suara ---
        let offset = 44
        const fade = (i, len) => 1 - (i / len) // fade-out linear

        // Nada 1: 880 Hz
        for (let i = 0; i < seg1; i++) {
            const t = i / sampleRate
            const amp = 0.6 * fade(i, seg1)
            const sample = Math.round(Math.sin(2 * Math.PI * 880 * t) * amp * 32767)
            view.setInt16(offset, sample, true)
            offset += 2
        }

        // Nada 2: 660 Hz
        for (let i = 0; i < seg2; i++) {
            const t = i / sampleRate
            const amp = 0.6 * fade(i, seg2)
            const sample = Math.round(Math.sin(2 * Math.PI * 660 * t) * amp * 32767)
            view.setInt16(offset, sample, true)
            offset += 2
        }

        // Encode ke base64 → data URI
        const bytes = new Uint8Array(buffer)
        let binary = ''
        for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i])
        return 'data:audio/wav;base64,' + btoa(binary)
    }

    // Buat sekali, pakai berulang
    const dingDataUri = buildWavDataUri()

    /**
     * Putar bunyi — HTMLAudioElement, kompatibel semua browser.
     */
    const playDing = () => {
        try {
            const audio = new Audio(dingDataUri)
            audio.volume = 1.0
            const p = audio.play()
            if (p) {
                p.catch(e => console.warn('▶️ Play ditolak browser:', e.name, e.message))
            }
        } catch (e) {
            console.warn('Audio error:', e)
        }
    }

    /**
     * Dipanggil setiap polling selesai — deteksi order baru (PRD 13.1 langkah 5).
     */
    const detectNewOrders = (orders) => {
        if (!Array.isArray(orders)) return
        const currentIds = orders.map(o => o.id)

        // Deteksi pertama: catat ID tanpa bunyi (order lama tidak membunyikan)
        if (seenOrderIds.value.size === 0 && currentIds.length > 0) {
            currentIds.forEach(id => seenOrderIds.value.add(id))
            return
        }

        const newOrders = currentIds.filter(id => !seenOrderIds.value.has(id))
        if (newOrders.length > 0) {
            playDing()
            newOrders.forEach(id => seenOrderIds.value.add(id))
        }

        seenOrderIds.value = new Set(
            [...seenOrderIds.value].filter(id => currentIds.includes(id))
        )
    }

    const testSound = () => playDing()

    // initAudioUnlock tidak lagi wajib untuk <audio>, tapi tetap disediakan
    // agar signature kompatibel dengan Queue.vue
    const initAudioUnlock = () => { /* no-op untuk pendekatan HTMLAudio */ }

    return { detectNewOrders, initAudioUnlock, testSound }
}