import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';
import { navGroups } from './admin-nav';

// Layouts
import PublicLayout from './layouts/PublicLayout.vue';
import AdminLayout from './layouts/AdminLayout.vue';

// Public Pages: the homepage ships in the main bundle for a fast first paint,
// every other page is code-split and loaded on demand.
import HomePage from './pages/public/HomePage.vue';
const CollectionPage = () => import('./pages/public/CollectionPage.vue');
const ProductPage = () => import('./pages/public/ProductPage.vue');
const CartPage = () => import('./pages/public/CartPage.vue');
const CheckoutPage = () => import('./pages/public/CheckoutPage.vue');
const WishlistPage = () => import('./pages/public/WishlistPage.vue');
const AccountPage = () => import('./pages/public/AccountPage.vue');
const OrderTrackingPage = () => import('./pages/public/OrderTrackingPage.vue');
const AboutPage = () => import('./pages/public/AboutPage.vue');
const ContactPage = () => import('./pages/public/ContactPage.vue');
const StaticPage = () => import('./pages/public/StaticPage.vue');
const BlogListPage = () => import('./pages/public/BlogListPage.vue');
const BlogPostPage = () => import('./pages/public/BlogPostPage.vue');
const NotFoundPage = () => import('./pages/public/NotFoundPage.vue');

// Admin Pages: storefront visitors never download the admin console.
const LoginPage = () => import('./pages/admin/LoginPage.vue');
const DashboardPage = () => import('./pages/admin/DashboardPage.vue');
const ProductManagementPage = () => import('./pages/admin/ProductManagementPage.vue');
const OrderManagementPage = () => import('./pages/admin/OrderManagementPage.vue');
const InventoryPage = () => import('./pages/admin/InventoryPage.vue');
const HomepageBuilderPage = () => import('./pages/admin/HomepageBuilderPage.vue');
const MediaLibraryPage = () => import('./pages/admin/MediaLibraryPage.vue');
const SettingsPage = () => import('./pages/admin/SettingsPage.vue');
const AppearancePage = () => import('./pages/admin/AppearancePage.vue');
const UsersPage = () => import('./pages/admin/UsersPage.vue');
const RolesPage = () => import('./pages/admin/RolesPage.vue');
const ResourceListPage = () => import('./pages/admin/ResourceListPage.vue');
const ResourceFormPage = () => import('./pages/admin/ResourceFormPage.vue');
const ReportsPage = () => import('./pages/admin/ReportsPage.vue');
const SalesAnalysisPage = () => import('./pages/admin/SalesAnalysisPage.vue');
const CustomersPage = () => import('./pages/admin/CustomersPage.vue');
const CouponsPage = () => import('./pages/admin/CouponsPage.vue');
const MessagesPage = () => import('./pages/admin/MessagesPage.vue');
const ReturnsPage = () => import('./pages/admin/ReturnsPage.vue');
const LogisticsPage = () => import('./pages/admin/LogisticsPage.vue');
const DeliveryChargesPage = () => import('./pages/admin/DeliveryChargesPage.vue');
const EmailTemplatesPage = () => import('./pages/admin/EmailTemplatesPage.vue');
const ReviewsPage = () => import('./pages/admin/ReviewsPage.vue');
const CategoriesPage = () => import('./pages/admin/CategoriesPage.vue');
const ContentCMSPage = () => import('./pages/admin/ContentCMSPage.vue');
const BlogPage = () => import('./pages/admin/BlogPage.vue');
const AuditLogsPage = () => import('./pages/admin/AuditLogsPage.vue');

const routes = [
  // Public Storefront Routes
  {
    path: '/',
    component: PublicLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: HomePage,
        meta: { title: 'হোম' }
      },
      {
        path: 'collections',
        name: 'collections.all',
        component: CollectionPage,
        meta: { title: 'সব পোশাক' }
      },
      {
        path: 'collection/:slug',
        name: 'collection.show',
        component: CollectionPage,
        meta: { title: 'কালেকশন' }
      },
      {
        path: 'category/:slug',
        name: 'category.show',
        component: CollectionPage,
        meta: { title: 'ক্যাটাগরি' }
      },
      {
        path: 'product/:slug',
        name: 'product.show',
        component: ProductPage,
        meta: { title: 'পণ্যের বিবরণ' }
      },
      {
        path: 'cart',
        name: 'cart',
        component: CartPage,
        meta: { title: 'শপিং ব্যাগ' }
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: CheckoutPage,
        meta: { title: 'চেকআউট' }
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: WishlistPage,
        meta: { title: 'পছন্দের তালিকা' }
      },
      {
        path: 'account',
        name: 'account',
        component: AccountPage,
        meta: { title: 'আমার অ্যাকাউন্ট' }
      },
      {
        path: 'track-order',
        name: 'order.tracking',
        component: OrderTrackingPage,
        meta: { title: 'অর্ডার ট্র্যাক' }
      },
      {
        path: 'about',
        name: 'about',
        component: AboutPage,
        meta: { title: 'আমাদের সম্পর্কে' }
      },
      {
        path: 'contact',
        name: 'contact',
        component: ContactPage,
        meta: { title: 'যোগাযোগ' }
      },
      {
        path: 'pages/:slug',
        name: 'static.page',
        component: StaticPage,
        meta: { title: 'পেজ' }
      },
      {
        path: 'blog',
        alias: 'journal',          // old links keep working
        name: 'journal',
        component: BlogListPage,
        meta: { title: 'ব্লগ' }
      },
      {
        path: 'blog/:slug',
        alias: 'journal/:slug',
        name: 'journal.post',
        component: BlogPostPage,
        meta: { title: 'ব্লগ' }
      },
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundPage,
        meta: { title: 'পেজ পাওয়া যায়নি' }
      }
    ]
  },

  // Admin Authentication Route (Unauthenticated)
  {
    path: '/admin/login',
    name: 'admin.login',
    component: LoginPage,
    meta: { title: 'Admin Sign In', guestOnly: true }
  },

  // Admin CMS Management Console Routes
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: DashboardPage,
        meta: { title: 'Executive Overview' }
      },
      {
        path: 'reports',
        name: 'admin.reports.index',
        component: ReportsPage,
        meta: { title: 'Sales & Financial Reports' }
      },
      {
        path: 'reports/sales',
        name: 'admin.reports.sales',
        component: SalesAnalysisPage,
        meta: { title: 'Visual Sales Analysis' }
      },
      {
        path: 'products',
        name: 'admin.products',
        component: ProductManagementPage,
        meta: { title: 'Product Catalog' }
      },
      {
        path: 'categories',
        name: 'admin.categories',
        component: CategoriesPage,
        meta: { title: 'Categories Hierarchy' }
      },
      {
        path: 'orders',
        name: 'admin.orders',
        component: OrderManagementPage,
        meta: { title: 'Orders & Fulfillment' }
      },
      {
        path: 'logistics',
        name: 'admin.logistics',
        component: LogisticsPage,
        meta: { title: 'Logistics & Steadfast Courier' }
      },
      {
        path: 'delivery-charges',
        name: 'admin.delivery-charges',
        component: DeliveryChargesPage,
        meta: { title: 'ডেলিভারি চার্জ' }
      },
      {
        path: 'email-templates',
        name: 'admin.email-templates',
        component: EmailTemplatesPage,
        meta: { title: 'ইমেইল টেমপ্লেট' }
      },
      {
        path: 'customers',
        name: 'admin.customers',
        component: CustomersPage,
        meta: { title: 'Customers Directory' }
      },
      {
        path: 'coupons',
        name: 'admin.coupons',
        component: CouponsPage,
        meta: { title: 'Coupons & Promotions' }
      },
      {
        path: 'messages',
        name: 'admin.messages',
        component: MessagesPage,
        meta: { title: 'Inquiries & B2B Messages' }
      },
      {
        path: 'returns',
        name: 'admin.returns',
        component: ReturnsPage,
        meta: { title: 'Returns & Exchanges' }
      },
      {
        path: 'inventory',
        name: 'admin.inventory',
        component: InventoryPage,
        meta: { title: 'Stock & Inventory Movements' }
      },
      {
        path: 'reviews',
        name: 'admin.reviews',
        component: ReviewsPage,
        meta: { title: 'Customer Reviews & Ratings' }
      },
      {
        path: 'homepage-builder',
        name: 'admin.homepage-builder',
        component: HomepageBuilderPage,
        meta: { title: 'Visual Homepage Builder' }
      },
      {
        path: 'content',
        name: 'admin.content',
        component: ContentCMSPage,
        meta: { title: 'Pages & Content CMS' }
      },
      {
        path: 'blog',
        name: 'admin.blog',
        component: BlogPage,
        meta: { title: 'Blog & Editorial Engine' }
      },
      {
        path: 'media',
        name: 'admin.media',
        component: MediaLibraryPage,
        meta: { title: 'Media Assets & CDN' }
      },
      {
        path: 'appearance',
        name: 'admin.appearance',
        component: AppearancePage,
        meta: { title: 'সাইট কাস্টমাইজ' }
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: SettingsPage,
        meta: { title: 'Central Settings Console' }
      },
      {
        path: 'users',
        name: 'admin.users',
        component: UsersPage,
        meta: { title: 'User Management' }
      },
      {
        path: 'roles',
        name: 'admin.roles',
        component: RolesPage,
        meta: { title: 'RBAC Roles & Permissions' }
      },
      {
        path: 'audit-logs',
        name: 'admin.audit-logs',
        component: AuditLogsPage,
        meta: { title: 'Security Audit Logs' }
      },
      // Generic Dynamic ResourceRegistry Management Routes
      {
        path: 'resources/:resource',
        name: 'admin.resources.index',
        component: ResourceListPage,
        meta: { title: 'Manage Content' }
      },
      {
        path: 'resources/:resource/create',
        name: 'admin.resources.create',
        component: ResourceFormPage,
        meta: { title: 'Create Entry' }
      },
      {
        path: 'resources/:resource/:id/edit',
        name: 'admin.resources.edit',
        component: ResourceFormPage,
        meta: { title: 'Edit Entry' }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Opening/closing the mobile cart sheet toggles a "#cart" history entry: never scroll for it.
    if (to.path === from.path && (to.hash === '#cart' || from.hash === '#cart')) return false;
    // Storefront pages animate out/in (~200ms); scroll once the new page is in place.
    const wait = to.path.startsWith('/admin') ? 0 : 220;
    return new Promise((resolve) => {
      setTimeout(() => {
        if (savedPosition) return resolve(savedPosition);           // Back/Forward keeps your place
        if (to.hash) return resolve({ el: to.hash, top: 90, behavior: 'smooth' }); // e.g. /#lookbook
        if (to.path === from.path) return resolve(false);           // only query changed (filters)
        resolve({ top: 0 });
      }, wait);
    });
  }
});

// The permission an /admin page needs, taken from the sidebar entry that owns the path
// (longest match wins, so /admin/resources/testimonials beats /admin).
const adminNavItems = navGroups.flatMap((g) => g.items).filter((i) => i.to !== '/admin');
const permissionForPath = (path) => {
  const owner = adminNavItems
    .filter((i) => path === i.to || path.startsWith(`${i.to}/`))
    .sort((a, b) => b.to.length - a.to.length)[0];
  return owner?.permission || null;
};

let adminPermissionsLoaded = false;

router.beforeEach(async (to, from, next) => {
  // Document title: "<page> | <site name>" (site name/SEO title come from Admin → সাইট কাস্টমাইজ)
  const site = window.__SITE__ || {};
  const siteName = site.branding?.site_name || 'DRAWER CLOTHING';
  if (to.name === 'home') {
    document.title = site.seo?.title || siteName;
  } else if (to.path.startsWith('/admin')) {
    document.title = `${to.meta?.title || 'Admin'} | ${siteName} Admin`;
  } else {
    document.title = to.meta?.title ? `${to.meta.title} | ${siteName}` : siteName;
  }

  const authStore = useAuthStore();

  if (to.meta.requiresAdmin) {
    if (!authStore.isAdminAuthenticated) {
      return next({ name: 'admin.login', query: { redirect: to.fullPath } });
    }

    // Pick up role changes made since login, once per page load, before any admin page renders.
    if (!adminPermissionsLoaded) {
      adminPermissionsLoaded = true;
      await authStore.refreshAdmin();
      if (!authStore.isAdminAuthenticated) {
        return next({ name: 'admin.login', query: { redirect: to.fullPath } });
      }
    }

    const needed = permissionForPath(to.path);
    if (needed && !authStore.hasPermission(needed)) {
      return next({ name: 'admin.dashboard' });
    }
  }

  if (to.meta.guestOnly && authStore.isAdminAuthenticated) {
    return next({ name: 'admin.dashboard' });
  }

  next();
});

export default router;
