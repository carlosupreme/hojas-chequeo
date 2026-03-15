import { gsap } from 'gsap';

// Expose to inline Alpine x-data in blade components
window.gsap = gsap;

function prefersReducedMotion() {
    return window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
}

/**
 * Collapse all buttons except the selected one; fade in the label text.
 * @param {HTMLElement} container
 * @param {string|number} selectedId
 * @param {boolean} animate
 */
export function iconButtonsSelect(container, selectedId, animate) {
    if (prefersReducedMotion()) return;

    const btns  = [...container.querySelectorAll('.icon-btn')];
    const gaps  = [...container.querySelectorAll('.icon-gap')];
    const label = container.querySelector('.icon-label');

    const others = btns.filter(b => String(b.dataset.id) !== String(selectedId));

    if (animate) {
        gsap.to(others, { width: 0, opacity: 0, duration: 0.22, ease: 'power2.in', overwrite: true });
        gsap.to(gaps,   { width: 0, opacity: 0, duration: 0.2,  ease: 'power2.in', overwrite: true });
        gsap.fromTo(
            label,
            { width: 0, opacity: 0 },
            { width: 'auto', opacity: 1, duration: 0.28, delay: 0.2, ease: 'power2.out' }
        );
    } else {
        gsap.set(others, { width: 0, opacity: 0 });
        gsap.set(gaps,   { width: 0, opacity: 0 });
        gsap.set(label,  { width: 'auto', opacity: 1 });
    }
}

/**
 * Expand all buttons back; hide the label text.
 * @param {HTMLElement} container
 * @param {boolean} animate
 */
export function iconButtonsDeselect(container, animate) {
    if (prefersReducedMotion()) return;

    const btns  = [...container.querySelectorAll('.icon-btn')];
    const gaps  = [...container.querySelectorAll('.icon-gap')];
    const label = container.querySelector('.icon-label');

    if (animate) {
        gsap.to(label, { width: 0, opacity: 0, duration: 0.18, ease: 'power2.in', overwrite: true });
        gsap.to(btns,  { width: 40, opacity: 1, duration: 0.28, delay: 0.15, ease: 'back.out(1.4)', overwrite: true });
        gsap.to(gaps,  { width: 6,  opacity: 1, duration: 0.28, delay: 0.15, ease: 'back.out(1.4)', overwrite: true });
    } else {
        gsap.set(label, { width: 0, opacity: 0 });
        gsap.set(btns,  { width: 40, opacity: 1 });
        gsap.set(gaps,  { width: 6,  opacity: 1 });
    }
}

// Expose to global scope so Alpine inline x-data can call them directly
window.iconButtonsSelect   = iconButtonsSelect;
window.iconButtonsDeselect = iconButtonsDeselect;
