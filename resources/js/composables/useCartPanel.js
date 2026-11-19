import { ref, reactive, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { useCartStore } from '../stores/cart';
import { getLenis } from '../utils/smoothScroll';

/* ---------------------------------------------------------------------------
   Page scroll lock that also works on iPhone Safari (where overflow:hidden on
   <html> is ignored): pin <body> at the current offset, restore it on unlock.
--------------------------------------------------------------------------- */
let lockedY = null;
export function lockPageScroll() {
  if (lockedY !== null || typeof window === 'undefined') return;
  lockedY = window.scrollY;
  getLenis()?.stop();
  const b = document.body.style;
  Object.assign(b, { position: 'fixed', top: `-${lockedY}px`, left: '0', right: '0', width: '100%' });
  document.documentElement.style.overflow = 'hidden';
}
export function unlockPageScroll() {
  if (lockedY === null) return;
  const y = lockedY;
  lockedY = null;
  const b = document.body.style;
  Object.assign(b, { position: '', top: '', left: '', right: '', width: '' });
  document.documentElement.style.overflow = '';
  // Lenis measured the page as 0px tall while <body> was pinned (and blocks native scroll
  // while stopped), so restart it and re-measure BEFORE restoring the position.
  const lenis = getLenis();
  const restore = () => {
    if (lenis) { lenis.resize?.(); lenis.scrollTo(y, { immediate: true, force: true }); }
    window.scrollTo({ top: y, behavior: 'instant' });
  };
  lenis?.start();
  restore();
  requestAnimationFrame(restore);   // after layout settles
}

/**
 * Shared behaviour for every cart surface (desktop drawer, mobile sheet):
 * quantity / remove with per-line busy + error state, screen-reader
 * announcements, focus trap, focus return, scroll lock.
 */
export function useCartPanel({ panelRef, closeBtnRef, onOpen, onClose } = {}) {
  const cart = useCartStore();
  const busy = reactive({});
  const errors = reactive({});
  const announcement = ref('');

  // ---------------------------------------------------------------- items
  const variantLabel = (item) => [item.color, item.size].filter(Boolean).join(' · ');
  const atStockLimit = (item) => Number(item.stock) > 0 && item.quantity >= Number(item.stock);
  const subtotal = computed(() => Number(cart.finalTotal ?? cart.subtotal ?? 0));   // includes coupon discount

  const setQty = async (item, qty) => {
    if (qty < 1 || busy[item.id]) return;
    if (qty > item.quantity && atStockLimit(item)) return;
    busy[item.id] = true;
    delete errors[item.id];
    // The store only replaces items on success, so a failure leaves the old number showing.
    const ok = await cart.updateQuantity(item.id, qty);
    delete busy[item.id];
    if (!ok) {
      errors[item.id] = 'আপডেট হয়নি। আবার চেষ্টা করুন।';
      announcement.value = errors[item.id];
    }
  };

  const remove = async (item) => {
    if (busy[item.id]) return;
    busy[item.id] = true;
    delete errors[item.id];
    const name = item.name;
    const ok = await cart.removeItem(item.id);
    delete busy[item.id];
    if (ok) {
      announcement.value = `${name} ব্যাগ থেকে সরানো হয়েছে`;
      nextTick(() => { if (!panelRef?.value?.contains(document.activeElement)) closeBtnRef?.value?.focus(); });
    } else {
      errors[item.id] = 'সরানো যায়নি। আবার চেষ্টা করুন।';
      announcement.value = errors[item.id];
    }
  };

  // ---------------------------------------------------------------- focus
  const focusFirst = () => closeBtnRef?.value?.focus({ preventScroll: true });
  const focusables = () => [...(panelRef?.value?.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])') || [])]
    .filter((el) => el.offsetParent !== null);

  /** Esc closes (via the given close function); Tab / Shift+Tab stay inside. */
  const trapKeydown = (e, closeFn) => {
    if (e.key === 'Escape') { e.preventDefault(); closeFn(); return; }
    if (e.key !== 'Tab') return;
    const list = focusables();
    if (!list.length) return;
    const first = list[0];
    const last = list[list.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  };

  // ------------------------------------------------------------ lifecycle
  let returnFocusTo = null;
  const handleOpen = () => {
    returnFocusTo = document.activeElement;
    lockPageScroll();
    nextTick(focusFirst);
    onOpen?.();
  };
  const handleClose = () => {
    Object.keys(errors).forEach((k) => delete errors[k]);
    announcement.value = '';
    unlockPageScroll();
    const target = returnFocusTo;
    returnFocusTo = null;
    nextTick(() => { if (target && document.contains(target)) target.focus?.({ preventScroll: true }); });
    onClose?.();
  };

  watch(() => cart.isDrawerOpen, (open) => (open ? handleOpen() : handleClose()));
  // Switching layout (e.g. rotating a tablet) while open: the new surface takes over.
  onMounted(() => { if (cart.isDrawerOpen) handleOpen(); });
  onBeforeUnmount(() => { if (cart.isDrawerOpen) unlockPageScroll(); });

  return { cart, busy, errors, announcement, subtotal, variantLabel, atStockLimit, setQty, remove, focusFirst, trapKeydown };
}
