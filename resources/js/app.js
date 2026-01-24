import './bootstrap';

// Conditionally load page scripts (Filament pages).
// This keeps the main bundle small and avoids running animations on unrelated pages.
if (document.querySelector('[data-page="create-chequeo"]')) {
    import('./filament/pages/create-chequeo.gsap.js');
}

// Livewire component helpers
if (document.querySelector('[data-animate="saving-indicator"]')) {
    import('./filament/components/chequeo-items.gsap.js');
}

// Livewire component helpers
if (document.querySelector('[data-animate="chequeo-items"]')) {
    import('./filament/components/chequeo-items.gsap.js');
}
