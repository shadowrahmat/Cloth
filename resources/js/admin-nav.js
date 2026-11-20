// Single source of truth for the admin console navigation.
// The sidebar, header breadcrumb and command palette (Ctrl+K) all read from here,
// so adding a page means adding one entry below.
import {
  LayoutDashboard, ChartColumn, TrendingUp, FolderTree, Shirt, Package, Star, Image,
  ShoppingBag, Truck, Users, RotateCcw, TicketPercent, LayoutTemplate, MessageSquare,
  FileText, Newspaper, Settings, UserCog, ShieldCheck, ScrollText, Palette, Banknote, Mail, Quote, Instagram, Send,
} from 'lucide-vue-next';

/**
 * item fields
 *   label      Bengali label shown in the menu
 *   hint       short English name (shown small, also searchable)
 *   to         route path
 *   icon       lucide icon component
 *   badge      key from /admin/dashboard/badges to show as a counter
 *   badgeTone  colour of that counter: 'warn' | 'danger' | 'info'
 *   permission permission slug required (admins always pass)
 *   keywords   extra search words for the command palette
 *   exact      highlight only on an exact path match
 */
export const navGroups = [
  {
    key: 'overview',
    label: 'ওভারভিউ',
    items: [
      { label: 'ড্যাশবোর্ড', hint: 'Dashboard', to: '/admin', icon: LayoutDashboard, exact: true, keywords: 'home overview summary' },
      { label: 'বিক্রয় রিপোর্ট', hint: 'Reports', to: '/admin/reports', icon: ChartColumn, exact: true, permission: 'view_orders', keywords: 'report finance revenue' },
      { label: 'সেলস অ্যানালাইসিস', hint: 'Sales Analysis', to: '/admin/reports/sales', icon: TrendingUp, permission: 'view_orders', keywords: 'chart trend' },
    ],
  },
  {
    key: 'orders',
    label: 'অর্ডার ও গ্রাহক',
    items: [
      { label: 'অর্ডার', hint: 'Orders', to: '/admin/orders', icon: ShoppingBag, badge: 'pending_orders', badgeTone: 'warn', permission: 'view_orders', keywords: 'order checkout fulfilment invoice' },
      { label: 'রিটার্ন ও এক্সচেঞ্জ', hint: 'Returns', to: '/admin/returns', icon: RotateCcw, badge: 'pending_returns', badgeTone: 'danger', permission: 'view_orders', keywords: 'refund exchange rma' },
      { label: 'কুরিয়ার ও ডেলিভারি', hint: 'Logistics', to: '/admin/logistics', icon: Truck, permission: 'view_orders', keywords: 'steadfast courier shipment delivery' },
      { label: 'ডেলিভারি চার্জ', hint: 'Delivery charges', to: '/admin/delivery-charges', icon: Banknote, permission: 'manage_shipping', keywords: 'shipping rate fee dhaka free delivery' },
      { label: 'গ্রাহক', hint: 'Customers', to: '/admin/customers', icon: Users, permission: 'view_customers', keywords: 'client buyer' },
      { label: 'নিউজলেটার সাবস্ক্রাইবার', hint: 'Subscribers', to: '/admin/resources/newsletter-subscribers', icon: Send, permission: 'view_customers', keywords: 'newsletter subscriber email list mailchimp নিউজলেটার' },
      { label: 'মেসেজ', hint: 'Messages', to: '/admin/messages', icon: MessageSquare, badge: 'new_inquiries', badgeTone: 'info', permission: 'view_customers', keywords: 'contact inquiry inbox' },
    ],
  },
  {
    key: 'catalog',
    label: 'পণ্য ও স্টক',
    items: [
      { label: 'পণ্য', hint: 'Products', to: '/admin/products', icon: Shirt, badge: 'total_products', permission: 'view_products', keywords: 'product item garment catalog' },
      { label: 'ক্যাটাগরি', hint: 'Categories', to: '/admin/categories', icon: FolderTree, badge: 'total_categories', permission: 'view_categories', keywords: 'category taxonomy' },
      { label: 'স্টক ও ইনভেন্টরি', hint: 'Inventory', to: '/admin/inventory', icon: Package, badge: 'low_stock', badgeTone: 'warn', permission: 'view_inventory', keywords: 'stock quantity low' },
      { label: 'রিভিউ', hint: 'Reviews', to: '/admin/reviews', icon: Star, badge: 'unapproved_reviews', badgeTone: 'info', permission: 'view_products', keywords: 'rating feedback' },
      { label: 'কুপন ও ডিসকাউন্ট', hint: 'Coupons', to: '/admin/coupons', icon: TicketPercent, permission: 'view_orders', keywords: 'promo discount code' },
    ],
  },
  {
    key: 'website',
    label: 'ওয়েবসাইট',
    items: [
      { label: 'সাইট কাস্টমাইজ', hint: 'Logo, header, footer', to: '/admin/appearance', icon: Palette, permission: 'view_cms', keywords: 'logo header footer menu announcement contact social seo brand theme size chart সাইজ চার্ট' },
      { label: 'হোমপেজ বিল্ডার', hint: 'Homepage', to: '/admin/homepage-builder', icon: LayoutTemplate, permission: 'view_cms', keywords: 'banner hero slider slide section runway newsletter হিরো ব্যানার স্লাইড' },
      { label: 'গ্রাহকের মতামত', hint: 'Testimonials', to: '/admin/resources/testimonials', icon: Quote, permission: 'view_cms', keywords: 'testimonial review customer feedback homepage মতামত' },
      { label: 'ইনস্টাগ্রাম ছবি', hint: 'Instagram gallery', to: '/admin/resources/social-gallery-items', icon: Instagram, permission: 'view_cms', keywords: 'instagram social gallery photo homepage' },
      { label: 'মিডিয়া লাইব্রেরি', hint: 'Media', to: '/admin/media', icon: Image, permission: 'view_media', keywords: 'image photo upload' },
      { label: 'পেজ ও পলিসি', hint: 'Pages', to: '/admin/content', icon: FileText, permission: 'view_cms', keywords: 'about policy terms cms' },
      { label: 'ব্লগ / জার্নাল', hint: 'Blog', to: '/admin/blog', icon: Newspaper, permission: 'view_cms', keywords: 'post article journal' },
    ],
  },
  {
    key: 'system',
    label: 'সেটিংস',
    items: [
      { label: 'স্টোর সেটিংস', hint: 'Settings', to: '/admin/settings', icon: Settings, permission: 'view_settings', keywords: 'config store invoice stock threshold' },
      { label: 'ইমেইল টেমপ্লেট', hint: 'Email templates', to: '/admin/email-templates', icon: Mail, permission: 'view_settings', keywords: 'email mail smtp template notification order confirmation gmail ইমেইল' },
      { label: 'স্টাফ ও অ্যাডমিন', hint: 'Users', to: '/admin/users', icon: UserCog, permission: 'view_users', keywords: 'staff team account password' },
      { label: 'রোল ও পারমিশন', hint: 'Roles', to: '/admin/roles', icon: ShieldCheck, permission: 'view_users', keywords: 'rbac access permission' },
      { label: 'অডিট লগ', hint: 'Audit Log', to: '/admin/audit-logs', icon: ScrollText, permission: 'view_settings', keywords: 'activity security history' },
    ],
  },
];

/** Quick "create new" shortcuts. Each page opens its create form when it sees ?new=1. */
export const quickCreate = [
  { label: 'নতুন পণ্য', hint: 'New product', to: '/admin/products?new=1', permission: 'create_products' },
  { label: 'নতুন ক্যাটাগরি', hint: 'New category', to: '/admin/categories?new=1', permission: 'create_categories' },
  { label: 'নতুন কুপন', hint: 'New coupon', to: '/admin/coupons?new=1', permission: 'create_orders' },
  { label: 'নতুন ব্লগ পোস্ট', hint: 'New blog post', to: '/admin/blog?new=1', permission: 'create_cms' },
  { label: 'নতুন পেজ', hint: 'New page', to: '/admin/content?new=1', permission: 'create_cms' },
];

/** Flat list of every nav item with its group label attached. */
export const navItems = navGroups.flatMap((g) => g.items.map((item) => ({ ...item, group: g.label, groupKey: g.key })));

/** Best-matching nav item for a route path (longest prefix wins). */
export function findNavItem(path) {
  const clean = path.replace(/\/+$/, '') || '/admin';
  let best = null;
  for (const item of navItems) {
    const hit = item.exact ? clean === item.to : clean === item.to || clean.startsWith(item.to + '/');
    if (hit && (!best || item.to.length > best.to.length)) best = item;
  }
  return best;
}

/** Numbers shown in Bengali digits, matching the rest of the Bengali UI. */
const BN_DIGITS = '০১২৩৪৫৬৭৮৯';
export const bnNumber = (n) => String(n).replace(/\d/g, (d) => BN_DIGITS[d]);

/** Currency for the admin console. The shop sells in Bangladeshi Taka. */
export const money = (v, { decimals = 0 } = {}) =>
  '৳ ' + bnNumber(Number(v || 0).toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }));
