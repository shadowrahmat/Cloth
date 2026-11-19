/**
 * v-reveal — fade/slide elements in as they scroll into view.
 *
 *   <section v-reveal>                         fade up
 *   <div v-reveal="{ delay: i * 60 }">         staggered (e.g. product grid)
 *   <img v-reveal="{ variant: 'zoom' }">       variants: up (default) | fade | left | right | zoom
 *
 * Elements are only hidden once JS runs, never for users who prefer reduced
 * motion, and they reveal once (no re-hiding on scroll back).
 */
const reduceMotion = typeof window !== 'undefined'
  && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

let observer = null;
const getObserver = () => {
  if (observer || typeof IntersectionObserver === 'undefined') return observer;
  observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal-in');
        observer.unobserve(entry.target);
      }
    }
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
  return observer;
};

const setup = (el, value) => {
  const opts = typeof value === 'object' && value !== null ? value : {};
  el.classList.add('reveal', `reveal-${opts.variant || 'up'}`);
  if (opts.delay) el.style.setProperty('--reveal-delay', `${Math.min(opts.delay, 600)}ms`);
};

export default {
  mounted(el, binding) {
    if (reduceMotion || binding.value === false) return;
    const io = getObserver();
    if (!io) return;
    setup(el, binding.value);
    io.observe(el);
  },
  unmounted(el) {
    observer?.unobserve(el);
  },
};
