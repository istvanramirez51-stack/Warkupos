<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthSplitLayout from '@/Layouts/AuthSplitLayout.vue'
import { Eye, EyeOff, Coffee, Receipt, UtensilsCrossed, Store } from '@lucide/vue'
import { ref } from 'vue'

defineOptions({ layout: AuthSplitLayout })

const showPassword = ref(false)

const form = useForm({
  phone: '',
  password: '',
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ================= PANEL KIRI : ILUSTRASI (hidden di mobile) ================= -->
    <div
      class="hidden lg:flex lg:w-[45%] xl:w-[50%] relative items-center justify-center overflow-hidden"
      style="background-color: var(--primary);"
    >
      <!-- Dekorasi shape background -->
      <div class="absolute top-[12%] left-[10%] w-10 h-10 rotate-12 opacity-30"
           style="background-color: var(--paper); border-radius: 4px;"></div>
      <div class="absolute bottom-[14%] right-[12%] w-16 h-16 -rotate-6 opacity-20"
           style="background-color: var(--paper); border-radius: 50%;"></div>
      <svg class="absolute bottom-[10%] left-[8%] opacity-25" width="140" height="90" viewBox="0 0 140 90" fill="none">
        <path d="M2 88 L38 30 L70 66 L104 14 L138 50" stroke="var(--paper)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>

      <!-- Ilustrasi custom : Struk Pembayaran -->
      <div class="relative">
        <svg width="340" height="380" viewBox="0 0 340 380" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Uap / aksen atas -->
          <path d="M120 40 C114 28 126 22 120 10" stroke="var(--paper)" stroke-width="7" stroke-linecap="round" opacity="0.85"/>
          <path d="M150 34 C144 22 156 16 150 4" stroke="var(--paper)" stroke-width="7" stroke-linecap="round" opacity="0.85"/>
          <path d="M180 40 C174 28 186 22 180 10" stroke="var(--paper)" stroke-width="7" stroke-linecap="round" opacity="0.85"/>

          <!-- Badan struk -->
          <g style="filter: drop-shadow(8px 10px 0px rgba(0,0,0,0.25));">
            <path d="M70 70 H270 V300 L252 288 L234 300 L216 288 L198 300 L180 288 L162 300 L144 288 L126 300 L108 288 L90 300 L70 288 Z"
                  fill="var(--paper)" stroke="var(--ink)" stroke-width="7" stroke-linejoin="round"/>
          </g>

          <!-- Garis judul struk -->
          <rect x="100" y="95" width="140" height="12" rx="6" fill="var(--ink)"/>
          <rect x="115" y="120" width="110" height="8" rx="4" fill="var(--ink-subtle)"/>

          <!-- Item-item -->
          <rect x="100" y="150" width="90" height="8" rx="4" fill="var(--ink-subtle)"/>
          <rect x="215" y="150" width="25" height="8" rx="4" fill="var(--ink-subtle)"/>
          <rect x="100" y="172" width="70" height="8" rx="4" fill="var(--ink-subtle)"/>
          <rect x="215" y="172" width="25" height="8" rx="4" fill="var(--ink-subtle)"/>
          <rect x="100" y="194" width="105" height="8" rx="4" fill="var(--ink-subtle)"/>
          <rect x="215" y="194" width="25" height="8" rx="4" fill="var(--ink-subtle)"/>

          <!-- Garis pemisah total -->
          <rect x="100" y="222" width="140" height="4" rx="2" fill="var(--ink)" opacity="0.4"/>
          <rect x="100" y="240" width="55" height="12" rx="6" fill="var(--ink)"/>
          <rect x="190" y="240" width="50" height="12" rx="6" fill="var(--primary)"/>

          <!-- Badge check -->
          <circle cx="250" cy="115" r="32" fill="var(--ink)" stroke="var(--paper)" stroke-width="6"/>
          <path d="M236 115 L246 126 L266 103" stroke="var(--paper)" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

          <!-- Koin Rp -->
          <g style="filter: drop-shadow(5px 6px 0px rgba(0,0,0,0.2));">
            <circle cx="85" cy="335" r="30" fill="var(--paper)" stroke="var(--ink)" stroke-width="6"/>
            <text x="85" y="344" text-anchor="middle" font-family="var(--font-heading)" font-weight="800" font-size="22" fill="var(--ink)">Rp</text>
          </g>
          <g style="filter: drop-shadow(4px 5px 0px rgba(0,0,0,0.2));">
            <circle cx="255" cy="345" r="22" fill="var(--paper)" stroke="var(--ink)" stroke-width="5"/>
            <text x="255" y="352" text-anchor="middle" font-family="var(--font-heading)" font-weight="800" font-size="16" fill="var(--ink)">Rp</text>
          </g>
        </svg>

        <!-- Badge mengambang : kopi -->
        <div class="absolute -left-14 top-[45%] w-14 h-14 rounded-full flex items-center justify-center"
             style="background-color: var(--paper); border: 2px solid var(--brut-border); box-shadow: var(--brut-shadow); color: var(--primary);">
          <Coffee :size="26" stroke-width="2" />
        </div>
        <!-- Badge mengambang : menu -->
        <div class="absolute -right-12 top-[18%] w-14 h-14 rounded-full flex items-center justify-center"
             style="background-color: var(--paper); border: 2px solid var(--brut-border); box-shadow: var(--brut-shadow); color: var(--ink);">
          <UtensilsCrossed :size="26" stroke-width="2" />
        </div>
      </div>
    </div>

    <!-- ================= PANEL KANAN : FORM ================= -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10 lg:p-14">
      <div class="w-full max-w-md">

        <!-- HEADER BRAND (compact, tampil di mobile/tablet kecil) -->
        <div class="flex items-center gap-3 mb-8 lg:mb-10">
          <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
               style="background-color: var(--primary); border: 2px solid var(--brut-border); box-shadow: var(--brut-shadow);">
            <Store :size="26" stroke-width="2" color="white" />
          </div>
          <div>
            <h1 class="text-2xl font-extrabold leading-none"
                style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.03em;">
              WarkuPos
            </h1>
            <p class="text-xs font-semibold mt-1" style="color: var(--ink-muted);">Sistem POS UMKM</p>
          </div>
        </div>

        <!-- Heading form -->
        <div class="mb-8">
          <p class="text-sm" style="color: var(--ink-muted);">
            Catat, sajikan, lapor — semua dari satu layar.
          </p>
          <h2 class="text-3xl font-extrabold mt-5"
              style="font-family: var(--font-heading); color: var(--ink); letter-spacing: -0.02em;">
            Masuk ke Akun
          </h2>
          <p class="text-sm mt-2" style="color: var(--ink-subtle);">
            Selamat datang kembali! Silakan masukkan nomor HP warung Anda untuk melanjutkan.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <!-- NOMOR HP dengan prefix +62 -->
          <div>
            <label class="block text-sm font-bold mb-2" style="color: var(--ink);">Nomor HP</label>
            <div class="flex rounded-lg overflow-hidden"
                 style="background-color: var(--paper-inset); border: 1px solid var(--brut-border); transition: box-shadow 200ms;"
                 @focusin="$event.currentTarget.style.boxShadow='var(--brut-shadow)'"
                 @focusout="$event.currentTarget.style.boxShadow='none'">
                
              <input
                type="tel"
                v-model="form.phone"
                placeholder="812-3456-7890"
                class="flex-1 px-4 py-3.5 text-base outline-none input--numeric"
                style="background-color: var(--paper-inset); color: var(--ink-soft);"
              />
            </div>
            <p v-if="form.errors.phone" class="mt-1.5 text-xs font-medium" style="color: var(--danger);">
              {{ form.errors.phone }}
            </p>
          </div>

          <!-- PASSWORD -->
          <div>
            <label class="block text-sm font-bold mb-2" style="color: var(--ink);">Kata Sandi</label>
            <div class="relative rounded-lg overflow-hidden"
                 style="background-color: var(--paper-inset); border: 1px solid var(--brut-border); transition: box-shadow 200ms;"
                 @focusin="$event.currentTarget.style.boxShadow='var(--brut-shadow)'"
                 @focusout="$event.currentTarget.style.boxShadow='none'">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="Minimal 8 karakter"
                class="w-full px-4 py-3.5 pr-12 text-base outline-none"
                style="background-color: var(--paper-inset); color: var(--ink-soft);"
              />
              <button type="button" @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 p-1 cursor-pointer"
                      style="color: var(--ink-subtle);">
                <Eye v-if="!showPassword" :size="20" />
                <EyeOff v-else :size="20" />
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-1.5 text-xs font-medium" style="color: var(--danger);">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Error umum -->
          <div v-if="form.errors.error" class="p-3 rounded-lg text-sm font-semibold"
               style="background-color: var(--paper-inset); color: var(--danger); border: 1.5px solid var(--danger);">
            {{ form.errors.error }}
          </div>

          <!-- TOMBOL -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-4 rounded-xl text-base font-extrabold tracking-wide cursor-pointer"
            style="background-color: var(--primary); color: white; border: 2px solid var(--brut-border); box-shadow: var(--brut-shadow); transition: filter 200ms, transform 100ms, box-shadow 100ms;"
            onmouseover="this.style.filter='brightness(1.15)'"
            onmouseout="this.style.filter='brightness(1)'"
            onmousedown="this.style.transform='translate(2px, 2px)'; this.style.boxShadow='1px 1px 0px var(--brut-border)'"
            onmouseup="this.style.transform='translate(0,0)'; this.style.boxShadow='var(--brut-shadow)'"
          >
            {{ form.processing ? 'MEMPROSES...' : 'Masuk Sekarang' }}
          </button>

        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-sm" style="color: var(--ink-muted);">
            Lupa kata sandi? <span class="font-bold" style="color: var(--ink);">Hubungi Owner warung Anda.</span>
          </p>
          <p class="text-xs mt-3 font-medium" style="color: var(--ink-subtle);">
            &copy; 2026 WarkuPos. Sistem Point-of-Sale UMKM.
          </p>
        </div>

      </div>
    </div>

  </div>
</template>