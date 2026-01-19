import { gsap } from "gsap";

// Page-scoped GSAP animations for Filament "Create Chequeo".
// This file is conditionally loaded from resources/js/app.js when a
// [data-page="create-chequeo"] marker exists in the DOM.

function prefersReducedMotion() {
    return window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches;
}

function animateCreateChequeoPage(root) {
    if (!root || prefersReducedMotion()) return;

    // Ensure we don't re-run the same intro animation multiple times (e.g. Livewire updates).
    if (root.dataset.gsapInit === "1") return;
    root.dataset.gsapInit = "1";

    // Elements
    const headerTitle = root.querySelector("[data-animate=header-title]");
    const headerSubtitle = root.querySelector("[data-animate=header-subtitle]");
    const turnoCard = root.querySelector("[data-animate=turno-card]");

    const introTargets = [headerTitle, headerSubtitle, turnoCard].filter(Boolean);

    // Intro timeline
    const tl = gsap.timeline({
        defaults: { duration: 0.6, ease: "power2.out" },
    });

    if (introTargets.length) {
        tl.from(introTargets, {
            y: 12,
            opacity: 0,
            stagger: 0.08,
            clearProps: "transform,opacity",
        });
    }

    // Small hover affordance on the Turno card
    if (turnoCard) {
        turnoCard.addEventListener("mouseenter", () => {
            gsap.to(turnoCard, { scale: 1.01, duration: 0.2, ease: "power1.out" });
        });
        turnoCard.addEventListener("mouseleave", () => {
            gsap.to(turnoCard, { scale: 1, duration: 0.2, ease: "power1.out" });
        });
    }
}

function boot() {
    const root = document.querySelector('[data-page="create-chequeo"]');
    if (!root) return;

    animateCreateChequeoPage(root);

    // In Filament + Livewire, navigation can happen without a full reload.
    // Re-run on Livewire navigations if present.
    window.addEventListener?.("livewire:navigated", () => {
        const newRoot = document.querySelector('[data-page="create-chequeo"]');
        animateCreateChequeoPage(newRoot);
    });
}

boot();
