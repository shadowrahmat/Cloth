import { watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

/**
 * Lets admin pages be opened in a specific state from the sidebar, the
 * command palette (Ctrl+K) or dashboard links:
 *   ?search=ORD-123  pre-fills the page's search box
 *   ?new=1           opens the page's "create" form
 *
 * Call it at the END of <script setup> (after the refs and functions exist).
 * It applies the query synchronously, so the page's own onMounted fetch
 * already uses the search term. The query is then removed from the URL so a
 * refresh doesn't reopen the form.
 */
export function useAdminDeepLink({ search = null, onNew = null, reload = null } = {}) {
  const route = useRoute();
  const router = useRouter();

  const apply = (query, { initial }) => {
    let consumed = false;

    if (search && typeof query.search === 'string') {
      search.value = query.search;
      consumed = true;
      if (!initial && reload) reload();
    }
    if (onNew && query.new) {
      onNew();
      consumed = true;
    }

    if (consumed) {
      const { search: _s, new: _n, ...rest } = query;
      router.replace({ query: rest });
    }
  };

  apply(route.query, { initial: true });

  // Same page, new link (e.g. palette used while already on Orders).
  watch(
    () => route.query,
    (q) => {
      if (q.search !== undefined || q.new !== undefined) apply(q, { initial: false });
    },
  );
}
