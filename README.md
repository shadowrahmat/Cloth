# ATELIER ÉTOILE — Haute Prêt-à-Porter & Modern Tailoring
### Luxury Fashion E-Commerce SPA & Centralized Admin CMS
Built with **Laravel 13**, **Vue 3 (Composition API)**, **Vite**, **Tailwind CSS**, and **Pinia**. Inspired by the high-editorial luxury experience of the Shopify Universe Theme.

---

## 1. Executive Summary

**ATELIER ÉTOILE** is a production-grade, full-stack fashion and apparel e-commerce platform and administrative content management system. It eliminates static frontend hardcoding by centralizing all storefront content, collections, products, inventory, media, customer accounts, and 13 dynamic homepage sections into an admin management system.

- **Storefront:** Editorial luxury shopping experience styled with high-fashion typography (*Playfair Display*, *Cormorant Garamond*, and clean *Inter* geometric sans), interactive product discovery, real-time cart drawer, size/color variant matrices, atomic checkout, and live order tracking.
- **Admin Management Console:** Unified control suite with executive analytics, dedicated business controllers for complex domains (Products, Orders, Inventory, Visual Homepage Builder, Media Library), and a generic **`ResourceRegistry`** architecture that powers dynamic CRUD management for standard catalog and content modules without code duplication.

---

## 2. Default Access Credentials

The database is pre-seeded with rich catalog data, product variants, high-resolution photography, reviews, coupons, shipping methods, and user accounts.

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Super Administrator** | `admin@cloth.test` | `password` | Full access to Executive Console, Settings, Users, RBAC, Catalog, Orders & CMS |
| **Demo Customer** | `customer@cloth.test` | `password` | Customer account with order history, address book, and wishlist |

---

## 3. Technology Stack

### Backend
- **Framework:** Laravel 13 (RESTful API architecture)
- **PHP Version:** PHP 8.3+ / 8.5
- **Authentication:** Laravel Sanctum (Bearer Token authentication for admin & customers)
- **Database:** SQLite / MySQL compatible migrations & Eloquent ORM
- **Transactions & Concurrency:** Atomic database transactions for orders, stock deductions, and coupon calculations
- **Activity Logging:** Comprehensive audit logging (`ActivityLog`) tracking admin operations

### Frontend
- **Framework:** Vue 3 (Composition API with `<script setup>`)
- **State Management:** Pinia (Modular stores: `auth`, `cart`, `wishlist`, `customer`)
- **Routing:** Vue Router 4 (HTML5 history mode with navigation guards and scroll restoration)
- **Styling:** Tailwind CSS (Luxury editorial tokens and typography scale)
- **Icons:** Lucide Vue Next
- **Bundler:** Vite with `@vitejs/plugin-vue` and `@tailwindcss/vite`
- **HTTP Client:** Axios instance with automated Bearer token injection and error interceptors

---

## 4. Brand Design Tokens & Palette

ATELIER ÉTOILE adheres strictly to a minimalist European luxury palette:

- **Primary Black (`#111111`):** Dominant dark token for primary text, solid buttons, footer, and borders.
- **Warm Ivory (`#F7F5F0`):** Editorial background canvas, soft product badges, and drawer surfaces.
- **Sand (`#D8CCBC`):** Subtle luxury borders, dividers, and card outlines.
- **Taupe (`#A89B8A`):** Secondary metadata, editorial subheadings, and muted icons.
- **Pure White (`#FFFFFF`):** High-contrast cards, input backgrounds, and modal dialogs.
- **Typography:**
  - Headings: *Cormorant Garamond* & *Playfair Display* serif.
  - Body & UI: *Inter* sans-serif with tracking and micro-uppercase font-mono labels.

---

## 5. Architecture & Project Structure

The project strictly preserves standard Laravel conventions. All frontend code is organized inside `resources/js/` and backend code inside `app/`.

```text
Cloth/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── V1/
│   │               ├── Admin/
│   │               │   ├── AuthController.php
│   │               │   ├── DashboardController.php
│   │               │   ├── ProductController.php
│   │               │   ├── OrderController.php
│   │               │   ├── InventoryController.php
│   │               │   ├── HomepageBuilderController.php
│   │               │   ├── MediaController.php
│   │               │   ├── SettingsController.php
│   │               │   ├── UserController.php
│   │               │   ├── RoleController.php
│   │               │   └── ResourceController.php  <-- Generic CRUD Engine
│   │               ├── Customer/
│   │               │   ├── CustomerAuthController.php
│   │               │   └── CustomerOrderController.php
│   │               └── Public/
│   │                   ├── PublicController.php
│   │                   ├── CartController.php
│   │                   ├── CheckoutController.php
│   │                   └── WishlistController.php
│   ├── Models/
│   │   ├── User.php, Role.php, Permission.php, MediaFile.php
│   │   ├── Category.php, Brand.php, Collection.php, Product.php, ProductVariant.php, ProductReview.php
│   │   ├── Cart.php, CartItem.php, Wishlist.php, WishlistItem.php, Coupon.php
│   │   ├── Order.php, OrderItem.php, Payment.php, ShippingMethod.php, CustomerAddress.php
│   │   ├── InventoryMovement.php, HomepageSection.php, Testimonial.php
│   │   ├── NewsletterSubscriber.php, SocialGalleryItem.php, Page.php, SiteSetting.php, ActivityLog.php
│   ├── Services/
│   │   ├── CartService.php
│   │   └── CheckoutService.php
│   └── Support/
│       └── ResourceRegistry.php                     <-- Centralized Admin Registry
├── database/
│   ├── migrations/                                  <-- 7 Structured Migration Suites
│   └── seeders/
│       └── DatabaseSeeder.php                       <-- Complete Initial Catalog & CMS Data
├── resources/
│   ├── css/
│   │   └── app.css                                  <-- Tailwind Tokens & Font Imports
│   ├── js/
│   │   ├── api.js                                   <-- Axios Client Wrapper
│   │   ├── admin-resources.js                       <-- Frontend Resource Metadata Mirror
│   │   ├── router.js                                <-- Vue Router & Navigation Guards
│   │   ├── App.vue & app.js                         <-- SPA Entry Point
│   │   ├── stores/                                  <-- Pinia State Management
│   │   ├── layouts/
│   │   │   ├── PublicLayout.vue
│   │   │   └── AdminLayout.vue
│   │   ├── pages/
│   │   │   ├── public/                              <-- Storefront Pages
│   │   │   └── admin/                               <-- Admin Console Pages
│   │   └── components/
│   │       ├── common/                              <-- Shared UI Elements
│   │       ├── public/                              <-- Storefront Components & 13 Sections
│   │       └── admin/                               <-- Sidebar, Header, DataTable, DynamicForm, MediaPicker
│   └── views/
│       └── app.blade.php                            <-- SPA Blade Host
├── routes/
│   ├── api.php                                      <-- 77 Versioned Endpoints (/api/v1/...)
│   └── web.php                                      <-- SPA Fallback & Dynamic Media Streamer
├── tests/
│   └── Feature/
│       └── EcommerceApiTest.php                     <-- 100% Passing Automated Tests
└── vite.config.js
```

---

## 6. The `ResourceRegistry` Architecture

To prevent repetitive, boilerplate CRUD code for standard content and catalog modules, the application implements a centralized **`ResourceRegistry`** system on both backend and frontend:

- **Backend (`app/Support/ResourceRegistry.php`):** Declares models, labels, table columns, searchable fields, validation rules, input types, and relationship joins for:
  - `categories` (hierarchical categories with parent-child structure)
  - `brands` (brand information and logos)
  - `collections` (curated seasonal drops)
  - `coupons` (discount rules, usage limits, expirations)
  - `shipping-methods` (carrier rates, free delivery thresholds)
  - `testimonials` (client reviews and star ratings)
  - `social-gallery-items` (shoppable Instagram cards)
  - `pages` (custom policy, terms, and editorial pages)
  - `contact-messages` (incoming customer inquiries)
  - `newsletter-subscribers` (subscriber list)
- **Generic Controller (`ResourceController.php`):** Handles index listing, pagination, sorting, search, store, show, update, delete, and bulk actions dynamically using registry metadata.
- **Frontend Engine (`DataTable.vue`, `DynamicForm.vue`, `ResourceListPage.vue`, `ResourceFormPage.vue`):** Generates responsive tables, filter toolbars, search boxes, and schema-driven input forms automatically without manual Vue template writing.

---

## 7. The 13 Dynamic Homepage Sections

All 13 homepage sections are managed via the visual **Homepage Builder** (`/admin/homepage-builder`) and render dynamically on the storefront:

1. **Announcement Bar (`announcement`):** Top bar banner with promo ticker, link, and background customization.
2. **Main Navigation (`header`):** Logo, mega-menu trigger, dynamic links, and cart count.
3. **Hero Banner (`hero`):** Editorial campaign image, luxury typography, dual call-to-actions, and overlay control.
4. **Curated Categories (`category_grid`):** Visual department portals showcasing **Men**, **Women**, **Kids**, and **Winter**.
5. **New Arrivals (`new_arrivals`):** Dynamic 4-column product grid with quick add-to-bag, color swatches, and hover states.
6. **Editorial Story (`editorial`):** Split-screen lifestyle editorial with rich storytelling and direct look links.
7. **Best Sellers (`best_sellers`):** Curated carousel of most coveted items.
8. **Shop The Look (`shop_the_look`):** High-fashion lifestyle image with interactive hot-spot pins revealing tagged products.
9. **Brand Story (`brand_story`):** The Maison's heritage statement with 3 key sustainability and craft metrics.
10. **Customer Testimonials (`testimonials`):** Verified client reflections with star ratings and city markers.
11. **Instagram & Social Feed (`instagram_gallery`):** 6-column editorial grid showcasing customer styling.
12. **Newsletter Subscription (`newsletter`):** High-conversion VIP invite banner with instant subscription handling.
13. **Maison Footer (`footer`):** Multi-column footer with brand story, customer care links, legal disclosures, and currency badges.

---

## 8. Catalog & Hierarchy Requirements

The platform organizes garments into the four core primary departments, each populated with specialized subcategories:

- **Men:** Tailored Suits, Blazers, Shirts, Knitwear, Trousers, Outerwear
- **Women:** Evening Dresses, Tailored Coats, Silk Blouses, Knitwear, Trousers, Skirts
- **Kids:** Heritage Knitwear, Junior Blazers, Everyday Essentials, Outerwear
- **Winter:** Cashmere Overcoats, Shearling Jackets, Heavy Knitwear, Wool Scarves, Boots

Each product supports multi-variant dimensions (**Size:** XS, S, M, L, XL, XXL | **Color:** Noir, Ivory, Camel, Espresso, Slate) with independent SKU tracking and inventory counts.

---

## 9. Storefront User Experience

- **Interactive Mega Menu:** Multi-column drop-down featuring category hierarchies and curated seasonal lookbooks.
- **Instant Search Drawer:** Live client-side and server-side search querying products, categories, and brands.
- **Curated Wishlist:** Client & database synced wishlist toggling with count indicators.
- **Mini-Cart Drawer:** Slide-over cart displaying subtotal, threshold progress towards free global delivery, coupon inputs, and direct checkout trigger.
- **Frictionless Checkout:** Multi-step guest or customer checkout with shipping rate selector, address validation, coupon verification, order summary, and atomic inventory allocation.
- **Live Order Tracking:** Public order lookup endpoint (`/track-order`) displaying order timeline, fulfillment status, and items.
- **Customer Account Portal:** Overview of past orders, receipt summaries, shipping addresses, and personal profile management.

---

## 10. Admin Management Console

- **Executive Overview Dashboard (`/admin`):** Real-time gross revenue, order volume, active customer count, stock health, 7-day revenue chart, top-performing garments, and recent system audit trails.
- **Product Management (`/admin/products`):** Full catalog management with variant matrix generator (size/color/price/stock), rich description editor, gallery uploader, and status toggles.
- **Order Processing (`/admin/orders`):** Order fulfillment workflow with status transitions (*Pending* → *Processing* → *Shipped* → *Delivered* → *Cancelled*), customer contact cards, and tracking number assignment.
- **Inventory Movement Tracker (`/admin/inventory`):** Real-time stock audit tracking every adjustment reason (*Stock Arrival*, *Manual Correction*, *Order Deduction*, *Return*), with low-stock warnings.
- **Visual Homepage Builder (`/admin/homepage-builder`):** Live layout controller allowing section reordering, visibility toggling, headline edits, and media updates.
- **Media CDN & Asset Library (`/admin/media`):** Drag-and-drop file uploader with copyable URLs, asset dimension inspections, and filterable asset grids.
- **Store Configuration (`/admin/settings`):** Global store metadata, currency settings, tax rules, delivery thresholds, and social links.
- **User Management (`/admin/users`):** Administrator, staff manager, and customer access controls.
- **Role-Based Access Control (`/admin/roles`):** Granular permission matrix by system domain (Products, Orders, CMS, Inventory, Settings, Users).

---

## 11. Installation & Local Development

### Prerequisites
- PHP 8.3 or higher (with `pdo_sqlite`, `mbstring`, `fileinfo`, `curl` extensions enabled)
- Composer 2+
- Node.js 18+ & npm

### Setup Steps

1. **Clone & Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration:**
   Verify your `.env` file contains:
   ```env
   APP_NAME="ATELIER ÉTOILE"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

3. **Database Migration & Seeding:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Build Frontend Assets:**
   ```bash
   npm run build
   ```
   *(For active hot module replacement during development: `npm run dev`)*

5. **Start Laravel Server:**
   ```bash
   php artisan serve
   ```
   Open `http://localhost:8000` in your browser.

---

## 12. Automated Testing

The application includes an extensive feature test suite in `tests/Feature/EcommerceApiTest.php` verifying public catalog routes, category trees, cart workflows, atomic stock deduction during checkout, order tracking, Sanctum authentication, dashboard analytics, generic ResourceRegistry CRUD operations, and the 13 dynamic homepage builder sections.

To run the entire test suite:

```bash
php artisan test
```

**Results:**
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\ExampleTest
   PASS  Tests\Feature\EcommerceApiTest
  ✓ public homepage endpoint returns sections                       0.22s
  ✓ public categories endpoint returns hierarchy                    0.09s
  ✓ public products endpoint returns paginated list                 0.10s
  ✓ public product detail endpoint                                  0.09s
  ✓ cart workflow add update view                                   0.14s
  ✓ checkout places order and deducts inventory                     0.19s
  ✓ admin authentication and dashboard                              0.16s
  ✓ admin generic resource registry crud operations                 0.23s
  ✓ admin homepage builder sections endpoint                        0.10s

  Tests:    11 passed (102 assertions)
  Duration: 1.82s
```

---

## 13. License & Attribution

Designed and developed as a complete Full-Stack Fashion & Clothing E-Commerce Platform using modern Laravel and Vue 3 architectural best practices.
