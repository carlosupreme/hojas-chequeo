<div
    x-data="{
        offline: !navigator.onLine,
        showOnlineToast: false,
        toastTimer: null,
    }"
    @offline.window="
        offline = true;
        showOnlineToast = false;
        clearTimeout(toastTimer);
    "
    @online.window="
        offline = false;
        showOnlineToast = true;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => showOnlineToast = false, 3500);
    "
>
    {{-- Offline banner (fixed top, full-width) --}}
    <div
        x-show="offline"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-full"
        style="display: none;"
        class="fixed top-0 left-0 right-0 z-99999 flex items-center justify-center gap-2 bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-lg"
        role="alert"
        aria-live="assertive"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 shrink-0">
            <path fill-rule="evenodd" d="M1.606 6.08a10.5 10.5 0 0 1 10.788-2.906 1.5 1.5 0 0 1-.8 2.886A7.5 7.5 0 0 0 3.5 8.354l-.543.543a1.5 1.5 0 0 1-2.122-2.122l.771-.695ZM22.5 8.25a1.5 1.5 0 0 0-2.121 0l-.544.543A7.5 7.5 0 0 0 8.19 7.307a1.5 1.5 0 1 0 2.396 1.803 4.5 4.5 0 0 1 6.51.897l.543.543a1.5 1.5 0 0 0 2.122-2.3Zm-4.19 4.19a1.5 1.5 0 0 0-2.122 0l-.543.543A1.5 1.5 0 0 1 13.5 13.5a1.5 1.5 0 0 0-2.646 1.47 4.5 4.5 0 0 0 1.94 1.18 1.5 1.5 0 0 1 .207 2.7 1.5 1.5 0 0 0 1.5 2.6 4.5 4.5 0 0 0 3.3-3.3 1.5 1.5 0 0 0-1.5-2.6ZM3.75 12a8.25 8.25 0 0 1 .968-3.908 1.5 1.5 0 1 0-2.62-1.47A11.25 11.25 0 0 0 .75 12a1.5 1.5 0 0 0 3 0Z" clip-rule="evenodd" />
            <path d="M3.53 3.53a.75.75 0 0 0-1.06 1.06l16.5 16.5a.75.75 0 1 0 1.06-1.06L3.53 3.53Z" />
        </svg>
        <span>Sin conexión — Verifica tu red</span>
    </div>

    {{-- Back-online toast (bottom-right, auto-dismiss) --}}
    <div
        x-show="showOnlineToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        style="display: none;"
        class="fixed bottom-5 right-5 z-99999 flex items-center gap-2.5 rounded-xl bg-green-600 px-4 py-3 text-sm font-medium text-white shadow-xl"
        role="status"
        aria-live="polite"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 shrink-0">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
        </svg>
        <span>Conexión restaurada</span>
    </div>
</div>
