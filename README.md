# Nèg Mawon Cleaner–Customer Booking Portal

A private, two-sided web portal for **NEG MAWON CLEANING SERVICES LLC** (owner: James), a house cleaning
business in Philadelphia, PA. It replaces James's manual phone-booking process with a portal where:

- **Customers** submit cleaning job requests.
- **James (Admin)** reviews requests and manually assigns a cleaner to each job — no automated matching.
- **Cleaners** work jobs sourced through the platform and pay a recurring subscription fee to use it.

James is the sole point of contact for every booking. Cleaners and customers never interact directly, and
customers only ever see the assigned cleaner's **photo** — no name, phone, or email.

This is an MVP built under a tight 2-week timeline for a client on shared PHP hosting (no VPS/Node hosting).

> **Full spec lives in [`AGENTS.md`](./AGENTS.md)** — roles, business rules, data model, design system, and
> the phase-by-phase timeline. **[`CLAUDE.md`](./CLAUDE.md)** has working notes and non-obvious gotchas for
> AI coding agents picking up this repo. Read both before making product or scope decisions.

## Tech stack

- **Laravel** (PHP 8.3+) — single codebase for customer, cleaner, and admin surfaces via role-based route
  groups/middleware.
- **Livewire + Alpine.js** — all stateful UI (dashboards, forms, admin tables) is Livewire; Alpine only for
  small client-side bits (toggles, mobile nav).
- **Flux UI** — component library on top of Livewire.
- **Tailwind CSS v4** (via Vite) — custom theme (see `resources/css/app.css` `@theme` block) matching the
  client's approved brand: deep forest green primary, warm beige secondary, gold accent, Fraunces + Plus
  Jakarta Sans typography.
- **MySQL** in production, SQLite for local dev by default (see `.env.example`).
- **Fortify** for authentication (login, registration, password reset), extended with a `role` enum
  (`admin` / `cleaner` / `customer`).
- **Pest** for testing, **Pint** for formatting, **Larastan/PHPStan** for static analysis.

## Key rules (non-negotiable — see AGENTS.md §3 and CLAUDE.md for full detail)

- No automated job-to-cleaner matching — assignment is always a manual admin action.
- No cleaner PII ever reaches the customer side — only the assigned cleaner's photo, enforced at the
  API/query layer.
- No in-app e-signature — the exclusivity agreement is signed by hand; only a *photo* of it is handled
  in-app, and the cleaner (not James) uploads it, subject to James's approval.
- Self-service account deletion is a **soft delete** — job and financial history must never be destroyed.
- Scope is locked to `AGENTS.md` §4. No messaging, ratings, automated matching, native app, or in-app job
  payments unless explicitly requested.

## Project structure

```
app/Enums/          Role, JobStatus, AgreementStatus, PropertyType, ServiceType, JobFrequency, ...
app/Models/          User, CleaningJob, CleanerProfile, CustomerProfile, ...
resources/views/pages/
  ├─ admin/          James's dashboard, job detail, cleaners list, customers list
  ├─ cleaner/        Cleaner dashboard (assigned jobs, mark complete)
  ├─ customer/        Job request form, upcoming jobs, job history
  └─ settings/        Shared profile/account settings (photo, agreement upload, delete account)
design/              Client-approved landing page reference + extracted brand tokens (source of truth
                     for the whole app's design system, not just the homepage)
```

## Getting started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build   # or `npm run dev` for local asset watching
composer dev    # runs the app (serve + queue + vite) via `php artisan dev`
```

## Testing & quality checks

```bash
php artisan test     # Pest test suite
composer lint         # Pint (auto-fix)
composer lint:check   # Pint (check only, no changes)
composer types:check  # PHPStan / Larastan
composer test          # config:clear + lint:check + types:check + full test suite (CI equivalent)
```

## Current status

Job request flow, admin assignment, cleaner onboarding/self-registration, the exclusivity agreement
upload-and-approve workflow, and soft-delete account deletion are built. Stripe/Cashier subscription billing
(`AGENTS.md` §8, Phase 3) is intentionally **not** yet built — deferred until the client confirms — do not
add billing UI without being asked.
