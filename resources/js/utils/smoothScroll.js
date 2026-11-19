import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

let lenisInstance = null;
let tickHandler = null;

export function initSmoothScroll() {
  if (typeof window === 'undefined') return null;

  if (lenisInstance) {
    return lenisInstance;
  }

  // Respect the OS "reduce motion" preference: keep native scrolling.
  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
    return null;
  }

  // Initialize Lenis with luxury inertia physics
  lenisInstance = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    orientation: 'vertical',
    gestureOrientation: 'vertical',
    smoothWheel: true,
    wheelMultiplier: 0.95,
    touchMultiplier: 1.5,
    infinite: false,
  });

  // Sync with GSAP ScrollTrigger
  lenisInstance.on('scroll', ScrollTrigger.update);

  tickHandler = (time) => {
    if (lenisInstance) {
      lenisInstance.raf(time * 1000);
    }
  };

  gsap.ticker.add(tickHandler);
  gsap.ticker.lagSmoothing(0);

  // Expose to window for testing / debugging
  window.__lenis = lenisInstance;

  return lenisInstance;
}

export function getLenis() {
  return lenisInstance;
}

export function scrollTo(target, options = {}) {
  if (lenisInstance) {
    lenisInstance.scrollTo(target, {
      duration: 1.2,
      ...options,
    });
  } else if (typeof window !== 'undefined') {
    window.scrollTo({
      top: typeof target === 'number' ? target : 0,
      behavior: 'smooth',
    });
  }
}

export function refreshScrollTrigger() {
  if (typeof window !== 'undefined' && ScrollTrigger) {
    setTimeout(() => {
      ScrollTrigger.refresh();
    }, 150);
  }
}

export function destroySmoothScroll() {
  if (tickHandler) {
    gsap.ticker.remove(tickHandler);
    tickHandler = null;
  }
  if (lenisInstance) {
    lenisInstance.destroy();
    lenisInstance = null;
  }
}

export { gsap, ScrollTrigger };
