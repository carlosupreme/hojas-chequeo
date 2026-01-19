import { gsap } from "gsap";

function prefersReducedMotion() {
    return window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches;
}

function animateSavingIndicator() {
    if (prefersReducedMotion()) return;

    const el = document.querySelector('[data-animate="saving-indicator"]');
    if (!el) return;

    // Subtle modern pop: fade+scale in (works nicely with the pill + spinner)
    gsap.fromTo(
        el,
        { opacity: 0, scale: 0.98, y: -2 },
        {
            opacity: 1,
            scale: 1,
            y: 0,
            duration: 0.22,
            ease: "power2.out",
            clearProps: "transform",
            overwrite: true,
        }
    );
}

function boot() {
    // Listen globally because the component may be swapped/re-rendered by Livewire.
    window.addEventListener("chequeo-items:form-changed", () => {
        animateSavingIndicator();
    });
}

boot();
