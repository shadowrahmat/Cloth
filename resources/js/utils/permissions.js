// Which permission module a registry resource belongs to; mirrors ResourceController::PERMISSION_MODULES
// on the server. A resource whose permission name is not listed is administrators-only.
const RESOURCE_MODULES = {
  manage_categories: 'categories',
  manage_brands: 'products',
  manage_collections: 'products',
  manage_coupons: 'orders',
  manage_orders: 'orders',
  manage_shipping: 'settings',
  manage_settings: 'settings',
  manage_testimonials: 'cms',
  manage_cms: 'cms',
  manage_pages: 'cms',
  manage_inquiries: 'customers',
  manage_newsletter: 'customers',
};

/** Permission slug for an action (view | create | update | delete) on a registry resource, or '@admin'. */
export const resourcePermission = (config, action) => {
  const module = RESOURCE_MODULES[config?.permission];
  return module ? `${action}_${module}` : '@admin';
};
