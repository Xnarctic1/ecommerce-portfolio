# Styles

`app.css` is the Vite entry point and imports the styles below.

- `themes/`: shared dark-mode variables. Page rules retain their original light colors as variable fallbacks.
- `layouts/`: storefront navigation, responsive layout, and legacy inline-style theme overrides.
- `components/`: shared navigation and product image styles.
- `pages/`: products, wishlist, and admin page styles.

Admin page rules are nested under the layout's `data-page` route name. This prevents similarly named classes on different admin pages from overriding each other. Keep that route selector updated if a route is renamed.

Prefer named classes for static styling. Keep values calculated by Blade, such as dashboard bar heights, inline. Put reusable presentation in `components/` and page-specific layout in `pages/`.

Products, wishlist, and admin pages have been migrated. Other storefront, authentication, and settings pages still contain their original styles. The storefront's legacy style-attribute selectors remain until those pages are migrated; new styles should use classes and theme variables.

Run `npm run dev` during development or `npm run build` for production after editing CSS.
