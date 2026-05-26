# Amui Studio — Symfony starter kit

## Purpose

This repository is a **project starter** for shipping small to medium **marketing / portfolio sites** quickly. It bundles:

- **Public site** — page-based content with **five generic block kinds** (`hero`, `text_image`, `cards_grid`, `image_gallery`, `contact`) stored as JSON payloads in Doctrine. The default demo follows a **one-page, Tailwind CDN** layout inspired by the `amui.html` reference (minimal black / white / accent look, anchor navigation).
- **Back-office** — [EasyAdmin](https://symfony.com/bundles/EasyAdminBundle) lets you edit each page with an embedded **collection of blocks** (`SectionBlockFormType`), inbound **contact messages**, and users.
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
| Contact capture | Le bloc générique **`contact`** embarque le formulaire (POST **`/contact`**) ; les messages sont listés sous **Contact messages** |

### Demo administrator accounts

**For local and staging only.** Change passwords or delete these users before production.

| Email | Password |
|-------|----------|
| `admin@starter.kit` | `admin-admin` |
| `studio@amui.demo` | `studio-demo` |

Both users have **`ROLE_ADMIN`**.

---

## Project layout (high level)

- **`src/Entity/Page`** — SEO, `sections` Collection, **`footerPayload`**: onglet admin « Bandeau & pied » (marque du `_nav`, **colonne pied en WYSIWYG** + réseaux + © dans `_footer`).
- **`src/Entity/SectionBlock`** — ordered blocks per page via `kind` (`SectionBlockKind` enum → `hero`, `text_image`, `cards_grid`, `image_gallery`, `contact`), `payload` (JSON shaped by the FormTypes below), optional `anchorId` / `navLabel` payload key for the header menu.
- **`src/Form/PageBuilder/`** — formulaires génériques de blocs + `SectionBlockFormType`.
- **`src/Controller/Admin/PageCrudController.php`** — formulaire Page en **onglets** EasyAdmin (infos, SEO, bandeau/pied, blocs).
- **`src/Form/Admin/`** — `FooterSiteChromeFormType` : pied (WYSIWYG + titre & liens sociaux) ; fusion avec le JSON pour le bandeau et le ©. `SocialLinkItemFormType` = une ligne dans `socialLinks`.
- **`templates/site/`** — global layout and page shell.
- **`templates/sections/block_*.html.twig`** — one template per `SectionBlockKind` value.
- **`public/uploads/page-builder/`** — default target for image uploads coming from bloc forms (persisted as `/uploads/page-builder/...` inside JSON).
- **`src/DataFixtures/StarterWebsiteFixtures.php`** — demo homepage + the two admin users above.

---

## Security reminder

Default credentials and Tailwind **CDN** are convenient for demos; they are **not** production defaults. Harden authentication, secrets, HTTPS, and asset delivery before going live.
