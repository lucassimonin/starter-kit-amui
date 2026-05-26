# Amui Studio — Symfony starter kit

## Purpose

This repository is a **project starter** for shipping small to medium **marketing / portfolio sites** quickly. It bundles:

- **Public site** — page-based content with reusable **section blocks** (`hero`, `gallery`, `about`, `contact`, plus generic layouts) stored in Doctrine. The default demo follows a **one-page, Tailwind CDN** layout inspired by the `amui.html` reference (minimal black / white / accent look, anchor navigation, contact form).
- **Back-office** — [EasyAdmin](https://symfony.com/bundles/EasyAdminBundle) to manage pages, section payloads (JSON), inbound **contact messages**, and users.
- **Contact capture** — POST endpoint persists messages for review in the admin (no opinionated mailer setup required to get started).

Use it as a **scaffold**: replace branding, add migrations for new block types, plug your own theme or asset pipeline, or wire forms to Messenger / Mailer when you are ready for production.

---

## Requirements

- PHP 8.4+
- Composer
- A relational database supported by Doctrine (MySQL, MariaDB, PostgreSQL, etc.)

---

## Installation

1. **Install PHP dependencies**

   ```bash
   make install
   # or: composer install
   ```

2. **Configure the database** (`.env` / `.env.local`) so `DATABASE_URL` points to your server.

3. **Run migrations**

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

4. **Load demo fixtures** (wipes all data in managed tables, then seeds content + admin users)

   ```bash
   php bin/console doctrine:fixtures:load --no-interaction
   ```

---

## After fixtures

| What | Where |
|------|--------|
| Public demo home | `/` (expects one published page flagged as homepage) |
| Admin UI | `/admin` |
| Demo contact form | Anchored section on the homepage; submissions go to **Contact messages** in EasyAdmin |

### Demo administrator accounts

**For local and staging only.** Change passwords or delete these users before production.

| Email | Password |
|-------|----------|
| `admin@starter.kit` | `admin-admin` |
| `studio@amui.demo` | `studio-demo` |

Both users have **`ROLE_ADMIN`**.

---

## Project layout (high level)

- **`src/Entity/Page`** — slugs, SEO fields, optional `footerPayload` (header/footer chrome as structured JSON).
- **`src/Entity/SectionBlock`** — ordered blocks per page: `layout` (`SectionLayout` enum), `payload` (JSON), optional `anchorId` for in-page links.
- **`templates/site/`** — global layout and page shell.
- **`templates/sections/`** — one Twig file per section layout.
- **`src/DataFixtures/StarterWebsiteFixtures.php`** — demo homepage + the two admin users above.

---

## Security reminder

Default credentials and Tailwind **CDN** are convenient for demos; they are **not** production defaults. Harden authentication, secrets, HTTPS, and asset delivery before going live.
