---
name: tailwindcss-development
description: "Use for Tailwind CSS work: utility classes, responsive layouts, dark mode, or styling in projects that already use Tailwind. Detects installed v3/v4 first; never upgrades versions unprompted."
license: MIT
metadata:
  author: laravel
---

# Tailwind CSS development

## Detect the installed version first

1. Inspect `package.json`, lockfile, CSS entrypoints, PostCSS/Vite configuration, and optional `tailwind.config.*`.
2. Determine whether the project uses Tailwind v3, v4, a different version, or no Tailwind at all. Use the installed major version and its documented features; do not upgrade just to follow this skill.
3. If Tailwind is not installed, do not add it without the user requesting Tailwind or approving the dependency. Use the project's existing styling system.
4. Consult installed package documentation or current version-matched official docs. `search-docs` is optional and must not be assumed present.


## Basic usage

- Use Tailwind CSS classes to style HTML. Before introducing new patterns, check and follow the project's existing Tailwind conventions.
- Offer to extract repeated patterns into components that match the project's conventions, such as Blade, JSX, or Vue components.
- Consider class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child elements carefully to reduce repetition, and group elements logically.

## Tailwind CSS v3 specifics

- Keep existing `tailwind.config.js`/`.cjs`/`.ts` configuration where used. Follow the established v3 directives (`@tailwind base;`, `@tailwind components;`, `@tailwind utilities;`) and v3-compatible plugins/utilities.
- Do not replace v3 directives with `@import "tailwindcss"` without a requested, tested major-version migration.

## Tailwind CSS v4 specifics (only when the project uses v4)

- Use v4 syntax and avoid utilities removed by v4. `corePlugins` is not supported in v4.
- The CSS-first patterns and utility replacement table below apply **only to v4**. An existing v4 project may use compatibility config; preserve it unless a migration is requested.

### CSS-first configuration

In Tailwind v4, you configure Tailwind in CSS with the `@theme` directive. You don't need a separate `tailwind.config.js` file:

<!-- CSS-First Config -->
```css
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
```

### Import syntax

In Tailwind v4, import Tailwind with a regular CSS `@import` statement instead of the `@tailwind` directives used in v3:

<!-- v4 Import Syntax -->
```diff
- @tailwind base;
- @tailwind components;
- @tailwind utilities;
+ @import "tailwindcss";
```

### Replaced utilities

Tailwind v4 removed deprecated utilities. Use the replacements shown below. For opacity, drop the `*-opacity-*` class and append the value as a modifier on the existing color: `bg-blue-500 bg-opacity-50` becomes `bg-blue-500/50`, `text-white text-opacity-75` becomes `text-white/75`.

| Deprecated | Replacement |
|------------|-------------|
| bg-opacity-* | bg-{color}/* |
| text-opacity-* | text-{color}/* |
| border-opacity-* | border-{color}/* |
| divide-opacity-* | divide-{color}/* |
| ring-opacity-* | ring-{color}/* |
| placeholder-opacity-* | placeholder-{color}/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

## Spacing

Use `gap` utilities instead of margins for spacing between siblings:

<!-- Gap Utilities -->
```html
<div class="flex gap-8">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

## Dark mode

If existing pages and components support dark mode, new pages and components must support it the same way, typically using the `dark:` variant:

<!-- Dark Mode -->
```html
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Content adapts to color scheme
</div>
```

## Common pitfalls

- Applying v4-only syntax to a v3 codebase, or v3-only conventions to a v4 codebase.
- Migrating config, plugins, or utility names without checking installed major version and build tooling.
- Failing to follow existing component, spacing, and dark-mode conventions.
- Introducing Tailwind where the codebase instead uses vanilla CSS, Bootstrap, or another system.

