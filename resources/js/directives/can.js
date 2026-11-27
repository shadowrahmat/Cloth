/**
 * v-can — hide an admin control the signed-in staff member has no permission for.
 *
 *   <button v-can="'delete_products'">      one permission
 *   <button v-can="['update_orders']">      all of the listed permissions
 *   <button v-can="'@admin'">               administrators only
 *
 * This is only about not showing buttons that would fail; the server enforces the
 * same permissions on every request, so hiding is never the security boundary.
 */
import { useAuthStore } from '../stores/auth';

const allowed = (value) => {
  const auth = useAuthStore();
  return (Array.isArray(value) ? value : [value]).every((p) => (
    p === '@admin' ? auth.adminUser?.role === 'admin' : auth.hasPermission(p)
  ));
};

const apply = (el, { value }) => {
  if (allowed(value)) el.style.removeProperty('display');
  else el.style.setProperty('display', 'none', 'important');
};

export default { mounted: apply, updated: apply };
