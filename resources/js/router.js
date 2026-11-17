import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

// Layouts
import PublicLayout from './layouts/PublicLayout.vue';
import AdminLayout from './layouts/AdminLayout.vue';

// Public Pages
import HomePage from './pages/public/HomePage.vue';
import CollectionPage from './pages/public/CollectionPage.vue';
import ProductPage from './pages/public/ProductPage.vue';
import CartPage from './pages/public/CartPage.vue';
import CheckoutPage from './pages/public/CheckoutPage.vue';
import WishlistPage from './pages/public/WishlistPage.vue';
import AccountPage from './pages/public/AccountPage.vue';
import OrderTrackingPage from './pages/public/OrderTrackingPage.vue';
import AboutPage from './pages/public/AboutPage.vue';
import ContactPage from './pages/public/ContactPage.vue';
import StaticPage from './pages/public/StaticPage.vue';

// Admin Pages
import LoginPage from './pages/admin/LoginPage.vue';
import DashboardPage from './pages/admin/DashboardPage.vue';
import ProductManagementPage from './pages/admin/ProductManagementPage.vue';
import OrderManagementPage from './pages/admin/OrderManagementPage.vue';
import InventoryPage from './pages/admin/InventoryPage.vue';
import HomepageBuilderPage from './pages/admin/HomepageBuilderPage.vue';
import MediaLibraryPage from './pages/admin/MediaLibraryPage.vue';
import SettingsPage from './pages/admin/SettingsPage.vue';
import UsersPage from './pages/admin/UsersPage.vue';
import RolesPage from './pages/admin/RolesPage.vue';
import ResourceListPage from './pages/admin/ResourceListPage.vue';
import ResourceFormPage from './pages/admin/ResourceFormPage.vue';
import ReportsPage from './pages/admin/ReportsPage.vue';
import SalesAnalysisPage from './pages/admin/SalesAnalysisPage.vue';
import CustomersPage from './pages/admin/CustomersPage.vue';
import CouponsPage from './pages/admin/CouponsPage.vue';
import MessagesPage from './pages/admin/MessagesPage.vue';
import ReturnsPage from './pages/admin/ReturnsPage.vue';
import LogisticsPage from './pages/admin/LogisticsPage.vue';
import ReviewsPage from './pages/admin/ReviewsPage.vue';
import CategoriesPage from './pages/admin/CategoriesPage.vue';
import ContentCMSPage from './pages/admin/ContentCMSPage.vue';
import BlogPage from './pages/admin/BlogPage.vue';
import AuditLogsPage from './pages/admin/AuditLogsPage.vue';

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
        meta: { title: 'ATELIER ÉTOILE | Haute Prêt-à-Porter' }
      },
      {
        path: 'collections',
        name: 'collections.all',
        component: CollectionPage,
        meta: { title: 'Collections' }
      },
      {
        path: 'collection/:slug',
        name: 'collection.show',
        component: CollectionPage,
        meta: { title: 'Collection' }
      },
      {
        path: 'category/:slug',
        name: 'category.show',
        component: CollectionPage,
        meta: { title: 'Category' }
      },
      {
        path: 'product/:slug',
        name: 'product.show',
        component: ProductPage,
        meta: { title: 'Product Details' }
      },
      {
        path: 'cart',
        name: 'cart',
        component: CartPage,
        meta: { title: 'Shopping Bag' }
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: CheckoutPage,
        meta: { title: 'Checkout' }
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: WishlistPage,
        meta: { title: 'Curated Wishlist' }
      },
      {
        path: 'account',
        name: 'account',
        component: AccountPage,
        meta: { title: 'Customer Account' }
      },
      {
        path: 'track-order',
        name: 'order.tracking',
        component: OrderTrackingPage,
        meta: { title: 'Track Order' }
      },
      {
        path: 'about',
        name: 'about',
        component: AboutPage,
        meta: { title: 'The Maison' }
      },
      {
        path: 'contact',
        name: 'contact',
        component: ContactPage,
        meta: { title: 'Concierge & Contact' }
      },
      {
        path: 'pages/:slug',
        name: 'static.page',
        component: StaticPage,
        meta: { title: 'Page' }
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
  },

  // Fallback 404
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0, behavior: 'smooth' };
  }
});

router.beforeEach((to, from, next) => {
  // Update document title
  const pageTitle = to.meta?.title ? `${to.meta.title} | ATELIER ÉTOILE` : 'ATELIER ÉTOILE';
  document.title = pageTitle;

  const authStore = useAuthStore();

  if (to.meta.requiresAdmin) {
    if (!authStore.isAdminAuthenticated) {
      return next({ name: 'admin.login', query: { redirect: to.fullPath } });
    }
  }

  if (to.meta.guestOnly && authStore.isAdminAuthenticated) {
    return next({ name: 'admin.dashboard' });
  }

  next();
});

export default router;
