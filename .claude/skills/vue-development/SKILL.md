---
name: vue-development
description: "Use for Vue work: Vue 3 Composition API, Vue 2 Options API, Pinia/Vuex, Vue Router, Nuxt, props/emits, and reactivity bugs. Detects the Vue version and existing style first."
---

# Vue development

Detect first: Vue 2 or 3, Nuxt version, Options API vs Composition API vs `<script setup>`, Pinia/Vuex, Vue Router, TS, UI library, test runner. Follow neighboring files; do not mix styles in one component or migrate Vue 2 to 3 unprompted.

## Rules
- Vue 3: prefer `<script setup>` with typed `defineProps`/`defineEmits` unless the project uses Options API.
- Destructuring a `reactive` object or store loses reactivity; use `toRefs`/`storeToRefs`. Use `.value` in script, not in templates.
- Never mutate props. Emit events or use `v-model`/`defineModel` (3.4+).
- `computed` for derived values (no side effects); `watch`/`watchEffect` for side effects, with cleanup. Do not use a watcher just to set derived state.
- `v-for` needs a stable `:key`; never put `v-if` and `v-for` on the same element.
- Put state in Pinia (or the existing store) only when shared; otherwise local `ref`. Centralize fetching in composables or the store as the project does.
- Route guards are UX only; enforce authorization on the server. Lazy-load route components.
- Nuxt: use auto-imports, `useFetch`/`useAsyncData` for SSR data, `server/` routes, and `runtimeConfig` for env values (public vs private). Avoid browser-only values during SSR render (hydration mismatch).
- Vue 2 projects: Options API, Vue 2 reactivity caveats (new properties need `Vue.set`); no Vue 3-only features.

## Verify
Typecheck (`vue-tsc`), lint, unit tests, build.
