# STORE — Ecommerce Application

A Laravel and Livewire ecommerce project with product browsing and filtering, a cart, wishlist, customer accounts, reviews, Stripe checkout, and an admin area for managing products, categories, and orders.

These instructions run a downloaded copy locally. Commands should be executed inside the `ecommerce` directory containing `artisan` and `composer.json`.

## Requirements

- PHP **8.4.1 or newer within PHP 8.4/8.5** for the committed dependency versions, including development tools. Although `composer.json` allows PHP 8.3, the lock file requires a newer version.
- Composer 2.
- Node.js **22.12+** (or Node 20.19+) and npm.
- PHP SQLite support (`pdo_sqlite` and `sqlite3`). Composer checks the other extensions required by the installed packages; image processing also requires an appropriate image driver such as GD.
- Internet access for dependency installation and the fonts downloaded during the frontend build.

SQLite is the simplest local setup. A separate MySQL/MariaDB server or Docker is not required for these instructions.

## 1. Install dependencies

Extract the project ZIP, open a terminal in the project directory, and run:

```sh
composer install
npm ci
```

Keep `composer.lock` and `package-lock.json`: these commands use them to install consistent dependency versions.

## 2. Create the local configuration

The following command creates `.env` from the example without overwriting an existing file:

```sh
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

For a fresh local installation, confirm these settings in `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
MAIL_MAILER=log
```

Leave `DB_DATABASE` unset to use Laravel's default `database/database.sqlite`. If the downloaded copy contains a `.env` from another computer, use `.env.example` as the starting point instead. Do not share real API keys or your personal `.env` file.

## 3. Create the database and storage link

```sh
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan config:clear
php artisan migrate
php artisan storage:link
```

The storage link makes uploaded product images available from `public/storage`. On Windows, creating the link may require Developer Mode or an administrator terminal.

The new database has no products or categories. You will add these through the admin area below.

## 4. Start the application

Keep two terminals running from the project directory.

**Terminal 1 — Laravel:**

```sh
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 — Vite:**

```sh
npm run dev
```

Open **[http://localhost:8000/products](http://localhost:8000/products)**.

Vite prints `http://localhost:5173`, but that is the frontend asset server, not the application. Both terminals must remain running during development. Press Ctrl+C to stop either server.

For a local demo without live CSS updates, stop Vite and run `npm run build`; then keep only the Laravel server running. If Vite was terminated abruptly, remove the stale `public/hot` file before using built assets.

## 5. Create an administrator and add products

1. Open [the registration page](http://localhost:8000/register) and create your own account.
2. Run `php artisan tinker` in another terminal.
3. Enter the following, replacing the email with the one you registered:

```php
$user = App\Models\User::where('email', 'your-email@example.com')->firstOrFail();
$user->is_admin = true;
$user->save();
```

Type `exit` to close Tinker. Open [the admin area](http://localhost:8000/admin), create a category, then add products with prices, stock, and images. Mark products active so they appear in the catalogue.

There is no preconfigured administrator. The optional `php artisan db:seed` command creates only a regular test user (`test@example.com`, password `password`); it does not populate products. It is not needed when registering your own account, and running it repeatedly can cause a duplicate-email error.

## Optional: Stripe test checkout

Browsing and administration work without Stripe credentials. Completing payment requires Stripe test credentials. Add these to `.env`:

```dotenv
STRIPE_KEY=pk_test_replace_me
STRIPE_SECRET=sk_test_replace_me
STRIPE_WEBHOOK_SECRET=whsec_replace_me
```

With the Stripe CLI installed and authenticated, forward local events:

```sh
stripe login
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

Use the signing secret printed by `stripe listen` for `STRIPE_WEBHOOK_SECRET`, then run `php artisan config:clear`. Keep the listener running while testing checkout. Use Stripe test mode rather than real payments for the demo.

## Email during local development

With `MAIL_MAILER=log`, emails are written to `storage/logs/laravel.log` instead of delivered to an inbox. Check that file for verification/reset messages. Configure a mail service in `.env` if actual delivery is needed.

## Tests and frontend build

```sh
php artisan test
npm run build
```

Tests use an in-memory SQLite database configured in `phpunit.xml`.

`composer test` additionally runs formatting and static-analysis checks; `php artisan test` runs the test suite alone.

## Project structure

- `app/`: models, controllers, services, middleware, and mail classes.
- `resources/views/pages/`: Livewire pages and Blade markup.
- `resources/views/layouts/`: shared page layouts.
- `resources/css/`: theme, component, layout, and page styles. See the [styling guide](resources/css/README.md).
- `routes/`: application routes.
- `database/`: migrations, factories, and seeders.
- `tests/`: feature and unit tests.

## Troubleshooting

| Problem | What to check |
| --- | --- |
| Browser cannot reach the website | Keep `php artisan serve` running and open port 8000, not 5173. |
| Terminal displays an old IP address | Set `APP_URL=http://localhost:8000`, run `php artisan config:clear`, and restart Vite. |
| Missing application key | Run `php artisan key:generate` for the fresh installation. |
| Missing database or tables | Create the SQLite file and run `php artisan migrate`. |
| PHP version or extension error | Check `php -v` and `composer check-platform-reqs`; install the required PHP version/extensions. |
| Missing CSS or Vite manifest | Run `npm ci`, then `npm run dev` or `npm run build`. |
| Product images do not load | Run `php artisan storage:link` and check the uploaded files exist in `storage/app/public`. |
| Empty product catalogue | Create active products through the admin area. |
| Checkout fails | Configure Stripe test credentials and the local webhook listener. |

These instructions are for local development. A public deployment requires separate production configuration.
