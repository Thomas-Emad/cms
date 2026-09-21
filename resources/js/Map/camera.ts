import { computed, onBeforeUnmount, ref } from 'vue';

export interface Bounds {
    x: number;
    y: number;
    w: number;
    h: number;
}

/**
 * Pan/zoom camera over a coordinate space, expressed as an SVG viewBox.
 *
 * - `zoom` is pixels per map unit; (cx, cy) is the map point at the CENTRE of the viewport.
 * - Animated moves (focus/fit/zoomBy) ease toward a target every frame with exponential
 *   smoothing, so they can be interrupted at any time without a jump (drag simply takes over).
 * - `padding` describes UI covering part of the viewport (side panel / bottom sheet) so
 *   "centre on this" centres in the VISIBLE area.
 * - The frame loop only runs while something is still moving.
 */
export function useCamera() {
    const width = ref(0);
    const height = ref(0);
    const cx = ref(500);
    const cy = ref(300);
    const zoom = ref(1);
    const minZoom = ref(0.2);
    const maxZoom = ref(8);
    /** Zoom at which the whole floor fits; set by fitHome(). Drives label density and zoom limits. */
    const fitZoom = ref(1);
    const padding = ref({ left: 0, right: 0, top: 0, bottom: 0 });

    let tx = cx.value;
    let ty = cy.value;
    let tz = zoom.value;
    let raf = 0;
    let last = 0;
    const STIFFNESS = 7; // higher = snappier

    const reduced = () => typeof window !== 'undefined' && !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    const clampZoom = (z: number) => Math.min(maxZoom.value, Math.max(minZoom.value, z));

    function frame(t: number) {
        const dt = Math.min((t - last) / 1000, 0.05);
        last = t;
        const k = 1 - Math.exp(-dt * STIFFNESS);
        cx.value += (tx - cx.value) * k;
        cy.value += (ty - cy.value) * k;
        zoom.value = Math.exp(Math.log(zoom.value) + (Math.log(tz) - Math.log(zoom.value)) * k);

        const settled = Math.abs(tx - cx.value) < 0.05 && Math.abs(ty - cy.value) < 0.05 && Math.abs(Math.log(tz / zoom.value)) < 0.0005;
        if (settled) {
            cx.value = tx;
            cy.value = ty;
            zoom.value = tz;
            raf = 0;
        } else {
            raf = requestAnimationFrame(frame);
        }
    }

    function animate() {
        if (reduced()) {
            stop();
            cx.value = tx;
            cy.value = ty;
            zoom.value = tz;
            return;
        }
        if (!raf) {
            last = performance.now();
            raf = requestAnimationFrame(frame);
        }
    }

    function stop() {
        if (raf) cancelAnimationFrame(raf);
        raf = 0;
    }

    /** Map point that should appear at the middle of the visible (un-covered) area. */
    function focus(x: number, y: number, z?: number, immediate = false) {
        tz = clampZoom(z ?? tz);
        const p = padding.value;
        const visibleCx = p.left + (width.value - p.left - p.right) / 2;
        const visibleCy = p.top + (height.value - p.top - p.bottom) / 2;
        tx = x + (width.value / 2 - visibleCx) / tz;
        ty = y + (height.value / 2 - visibleCy) / tz;
        if (immediate) {
            stop();
            cx.value = tx;
            cy.value = ty;
            zoom.value = tz;
        } else {
            animate();
        }
    }

    function fit(b: Bounds, margin = 24, immediate = false) {
        const p = padding.value;
        const availW = Math.max(50, width.value - p.left - p.right - margin * 2);
        const availH = Math.max(50, height.value - p.top - p.bottom - margin * 2);
        focus(b.x + b.w / 2, b.y + b.h / 2, Math.min(availW / b.w, availH / b.h), immediate);
    }

    /** Fit a floor into view AND remember it as the "home" zoom (limits + label thresholds derive from it). */
    function fitHome(b: Bounds, margin = 24, immediate = false) {
        const p = padding.value;
        const z = Math.min(Math.max(50, width.value - p.left - p.right - margin * 2) / b.w, Math.max(50, height.value - p.top - p.bottom - margin * 2) / b.h);
        fitZoom.value = z;
        minZoom.value = z * 0.6;
        maxZoom.value = z * 7;
        focus(b.x + b.w / 2, b.y + b.h / 2, z, immediate);
    }

    /** Direct manipulation: follows the finger exactly, cancelling any animation. */
    function panByPx(dx: number, dy: number) {
        stop();
        cx.value -= dx / zoom.value;
        cy.value -= dy / zoom.value;
        tx = cx.value;
        ty = cy.value;
        tz = zoom.value;
    }

    /** Zoom keeping the map point under screen position (sx, sy) fixed. Direct (pinch / wheel). */
    function zoomAt(factor: number, sx: number, sy: number) {
        stop();
        const z2 = clampZoom(zoom.value * factor);
        const wx = cx.value + (sx - width.value / 2) / zoom.value;
        const wy = cy.value + (sy - height.value / 2) / zoom.value;
        zoom.value = z2;
        cx.value = wx - (sx - width.value / 2) / z2;
        cy.value = wy - (sy - height.value / 2) / z2;
        tx = cx.value;
        ty = cy.value;
        tz = z2;
    }

    /** Animated zoom (buttons) about the visible centre. */
    function zoomBy(factor: number) {
        const p = padding.value;
        const vx = p.left + (width.value - p.left - p.right) / 2;
        const vy = p.top + (height.value - p.top - p.bottom) / 2;
        const wx = cx.value + (vx - width.value / 2) / zoom.value;
        const wy = cy.value + (vy - height.value / 2) / zoom.value;
        focus(wx, wy, tz * factor);
    }

    const viewBox = computed(() => {
        const w = width.value / zoom.value;
        const h = height.value / zoom.value;
        return `${cx.value - w / 2} ${cy.value - h / 2} ${w} ${h}`;
    });

    function toWorld(sx: number, sy: number) {
        return { x: cx.value + (sx - width.value / 2) / zoom.value, y: cy.value + (sy - height.value / 2) / zoom.value };
    }

    onBeforeUnmount(stop);

    return { width, height, cx, cy, zoom, minZoom, maxZoom, fitZoom, padding, viewBox, focus, fit, fitHome, panByPx, zoomAt, zoomBy, toWorld, stop };
}

export type Camera = ReturnType<typeof useCamera>;
