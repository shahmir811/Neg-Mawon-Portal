# CLAUDE.md

This file gives Claude (or any AI coding agent working in this repo via Antigravity or another IDE)
quick orientation. The full project spec – roles, features, business rules, data model, and timeline –
lives in **[AGENTS.md](./AGENTS.md)**. Read that file first; this file only adds working notes on top of it.

## Project in one line

A 2-week MVP web portal for NEG MAWON CLEANING SERVICES LLC where customers request cleaning jobs, James
(the sole admin) manually assigns a cleaner to each job, and customers only ever see the assigned cleaner's
photo – nothing else about them.

## Non-negotiable rules (see AGENTS.md Sections 3 & 5 for full detail)

- **No automated job-to-cleaner matching.** Assignment is a manual admin action. The cleaner `zip_code`
  field (Phase A, item 9 below) is a manual proximity hint shown to James only — never build distance
  sorting or auto-selection on top of it.
- **No cleaner PII ever reaches the customer side.** Only the assigned cleaner's photo. Enforce this in
  the API/query layer, not just hidden in the UI.
- **No in-app e-signature.** The exclusivity agreement is signed by hand outside the app; only a *photo* of
  it is ever handled in-app. The cleaner uploads that photo, but James's approval is what marks it signed.
- **Account deletion must never destroy job or financial history.** Self-service delete is a soft delete,
  not a hard delete — see item 7 below.
- **Keep scope to AGENTS.md Section 4.** This client has a hard 14-day deadline – do not add messaging,
  ratings, automated matching, a native app, or in-app job payments unless explicitly asked.

## Before starting work

1. Read `AGENTS.md` in full – it has the data model, phase-by-phase timeline, and the decided stack:
   **Laravel + Livewire + Alpine.js + MySQL**, chosen specifically to deploy on ordinary PHP shared hosting
   (no VPS/Node hosting needed) given the client's budget.
2. Read AGENTS.md **Section 8a (Design System)** before building any UI. Two files back it:
   `design/landing-page-reference.html` (approved landing page markup) and `design/neg-mawon-brand.json`
   (extracted brand spec – colors, type scale, spacing, component tokens, logo lockup, CTA copy, brand
   voice). Same colors (`primary` #0B3B2E, `secondary` #E8DFD3, `accent` #C89B3C, background #FAF7F2), same
   fonts (Fraunces for headings, Plus Jakarta Sans for body), same component style (pill buttons, 16px
   rounded cards, eyebrow-badge section headers, the Lucide-sparkles-in-a-badge + "Nèg Mawon" logo lockup).
   The landing page itself becomes the app's `/` (index) route – convert it to a Blade view early in
   Phase 1.
3. If no `.env.example` exists yet, create one as you introduce each integration (MySQL, Stripe/Cashier,
   mail provider, file storage disk) rather than hardcoding secrets.
4. Confirm which phase (1–4 in AGENTS.md Section 9) the current task belongs to before building – this
   keeps work aligned with the day-by-day plan the client was quoted.
5. Cleaners self-register in-app – the `register` page has a Customer/Cleaner role toggle
   (`app/Actions/Fortify/CreateNewUser.php`, `resources/views/pages/auth/register.blade.php`), and choosing
   Cleaner collects phone + profile photo at signup. There is no separate admin-invite flow – James approves
   a new cleaner by reviewing them in the admin Cleaners List and completing the agreement/subscription
   steps in AGENTS.md Section 6, not by creating the account himself. Cleaners can also replace their photo
   later from Settings → Profile (`resources/views/pages/settings/⚡profile.blade.php`), which updates the
   same `cleaner_profiles.photo_path` – it's not a registration-only field. The admin Cleaners List shows
   this photo next to each cleaner's name.
6. The exclusivity agreement workflow (AGENTS.md Section 6) is a two-step, cleaner-submits /
   admin-approves flow – **not** admin-upload-only anymore:
   - Cleaner uploads the signed-agreement photo from Settings → Profile
     (`resources/views/pages/settings/⚡profile.blade.php`, `uploadAgreement()`), which sets
     `agreement_photo_path` but always leaves `agreement_signed = false` (pending review) – including on a
     resubmission, even if it was previously approved.
   - Admin approves it from the Cleaners List (`resources/views/pages/admin/⚡cleaners.blade.php`,
     `approveAgreement()`), which just flips `agreement_signed = true` — no re-upload needed since the
     cleaner already provided the file.
   - Admin's original upload modal on that same page still exists as a fallback (e.g. cleaner sent the
     photo outside the app) and still auto-approves on save, since it's James's own action.
   - Status is derived, not stored separately: `CleanerProfile::agreementStatus()` returns the
     `App\Enums\AgreementStatus` enum (`NotSubmitted` / `Pending` / `Approved`) purely from
     `agreement_photo_path` + `agreement_signed` — no new migration was needed for this.
   - Job assignment is gated on this same `agreement_signed` flag (AGENTS.md Section 3, rule 5): the admin
     dashboard's assign-cleaner dropdown (`resources/views/pages/admin/⚡dashboard.blade.php`, `cleaners()`)
     only lists cleaners with an approved agreement, and `assign()` re-checks `agreement_signed` server-side
     before writing `cleaner_id` — so changes to the approval flow also change who's assignable.

   Stripe/Cashier subscription billing (AGENTS.md Section 8, Phase 3) is intentionally **not** built yet –
   deferred by client decision until the client shows intent to move forward. The `subscription_plan` /
   `subscription_status` / `stripe_id` columns on `cleaner_profiles` exist but are only ever set via the
   database seeder today; there's no checkout flow or admin UI to change them. Don't build billing without
   being asked again.
7. Self-service "delete account" (AGENTS.md Section 3, rule 6) is a **soft delete**, not the Fortify/Breeze
   default hard delete:
   - `App\Models\User` uses `SoftDeletes`; the `users` table has `deleted_at`
     (`add_soft_deletes_to_users_table` migration). Delete logic itself in
     `resources/views/pages/settings/⚡delete-user-modal.blade.php` is unchanged (`->delete()`) — Eloquent
     runs a soft delete automatically once the trait is present, no explicit code change was needed there.
   - This was deliberate: without it, deleting a customer cascade-deletes their entire `cleaning_jobs`
     history, and deleting a cleaner destroys their `cleaner_profiles` row and unlinks (`cleaner_id = null`)
     every job they ever did. Soft delete means no `DELETE` statement runs at all, so none of that cascading
     fires — job history is intact either way. Don't switch this back to a hard delete without re-checking
     that trade-off.
   - The `users.email` column changed from a plain unique index to a composite `(email, deleted_at)` unique
     index in the same migration, and `ProfileValidationRules::emailRules()` uses
     `Rule::unique(...)->withoutTrashed()`. Both changes were required together — without the index change,
     a soft-deleted account's email still throws a DB-level unique-constraint error on re-registration even
     though app-level validation says it's fine (this was caught by a test, not obvious from reading Fortify
     alone). If you ever touch email uniqueness logic again, keep both in sync.
   - A soft-deleted user can't log in — `SoftDeletingScope` is a global scope, so it's automatically excluded
     from every default `User::` query (auth lookups, admin Cleaners/Customers lists, password reset), no
     extra `whereNull('deleted_at')` needed anywhere.
8. The customer job-request form (create *and* edit) shares one set of fields, validation, and business
   logic via `App\Concerns\CleaningJobFormFields` (a trait) and
   `resources/views/partials/customer-job-form-fields.blade.php` (a Blade partial). Both
   `resources/views/pages/customer/⚡dashboard.blade.php` (create) and
   `resources/views/pages/customer/⚡job-edit.blade.php` (edit) `use` the trait and `@include` the partial —
   if you need to change a field, its validation, or the Deep/Soft eligibility rule, change it once in the
   trait/partial, not in both pages. They only differ in `mount()` (edit prefills from the existing job) and
   `submit()` (create vs. `update()`, different toast text).
   - **Deep vs. Soft cleaning eligibility** (`CleaningJobFormFields::eligibleForSoftCleaning()`): looks only
     at the customer's most recent job with `status = Completed`
     (`CleaningJob::lastCompletedFor()` in `app/Models/CleaningJob.php`) — a `Requested` or `Assigned` job
     never counts as "last service." First-time customers, or anyone whose last completed job's
     `requested_at` was more than 30 days ago, are only offered Deep Cleaning; the form only reveals Soft as
     an option once that check passes. This is re-validated server-side in `rules()` on every submit (create
     *and* edit), not just hidden in the UI.
   - **Job editing is locked once assigned**: `⚡job-edit.blade.php`'s `mount()` scopes the job lookup to
     `Auth::user()->jobsAsCustomer()->findOrFail($job)` (a mismatched/foreign job 404s, same pattern as
     `CleanerDashboard::markComplete()`), then redirects back to Upcoming Jobs with a toast if
     `status !== Requested`. `submit()` re-checks the same condition on a fresh fetch of the job before
     saving, closing the race where James assigns it in the moments the edit form is open. The Edit button
     on `resources/views/components/customer-job-card.blade.php` is gated on the same `Requested` status, so
     it naturally disappears the instant a job is assigned.
   - Photo editing is add-only in both create and edit — customers can attach more photos, but there's no
     UI to remove ones already uploaded. Don't assume photo removal exists without checking.
   - Once a job is `Assigned` or `Completed` (no longer editable), the customer-job-card's action slot swaps
     the Edit button for a **View** button (`route('customer.jobs.show', $job['id'])`,
     `resources/views/pages/customer/⚡job-show.blade.php`) — a read-only detail page, not a second edit
     surface. It mirrors the admin job-detail page's layout (full field breakdown, embedded map, photos) but
     scopes the lookup to `Auth::user()->jobsAsCustomer()->findOrFail($job)` and renders
     `CleaningJob::toCustomerArray()`, so it's subject to the same no-PII rule as everywhere else on the
     customer side — only the assigned cleaner's photo, never their name/phone/email. Shared by both
     Upcoming Jobs (Assigned) and Job History (Completed) since they render the same card component.
9. **Phase A additions (AGENTS.md Section 4a)** — pricing calculator, floor type, bedroom/bathroom counts,
   and cleaner zip code, all added from client feedback after the original MVP kickoff:
   - **Pricing.** `App\Services\JobPriceCalculator` (`estimate()` and `breakdown()`) computes a job's price
     off admin-editable rules in `App\Models\PricingSetting` (a singleton row via `PricingSetting::current()`
     — no pricing document was ever provided, so it ships with placeholder numbers James edits from
     Admin → Pricing, `resources/views/pages/admin/⚡pricing.blade.php` / `admin.pricing` route). The result
     is stored on every job as `estimated_price` (`CleaningJobFormFields::cleaningJobAttributes()`), and
     `resources/views/pages/admin/⚡job.blade.php` shows the full line-item breakdown plus a `final_price`
     field James can save to override it — `CleaningJob::displayPrice()` always prefers `final_price` over
     `estimated_price` wherever a job's price is shown. **This is admin-only.** The trait still has an
     `estimatedPrice` `#[Computed]` property, but the customer-facing partial
     (`resources/views/partials/customer-job-form-fields.blade.php`) deliberately doesn't render it — showing
     a number built from placeholder rates before James confirms real ones would set a price expectation the
     app can't honor. Don't re-wire it into the customer view without checking real rates are in
     `pricing_settings` first.
   - **Bedroom/bathroom counts.** `bedroom_count`/`bathroom_count` on `cleaning_jobs` are required in
     `CleaningJobFormFields::rules()` only when `property_type` is Residential (`required_if`); the
     calculator falls back to the `property_size`-band rate for every other property type.
   - **Floor type.** `App\Enums\FloorType` (carpet / hard_floor / mixed), optional, shown to the assigned
     cleaner as equipment-prep info on their dashboard. Not a pricing input.
   - **Cleaner zip code.** `cleaner_profiles.zip_code`, optional, set at registration or later from
     Settings → Profile (`updateZipCode()` in `resources/views/pages/settings/⚡profile.blade.php`). Shown as
     plain text next to each cleaner's name in the admin assign-cleaner dropdown
     (`resources/views/pages/admin/⚡dashboard.blade.php`, `cleaners()`) and the Cleaners List — a manual
     hint for James to judge proximity himself, not a distance calculation. See the non-negotiable rule
     above before building anything that sorts or auto-selects on this field.
   - Tests for all of the above live in `tests/Feature/PricingTest.php`.
10. **Contact form, email, S3 storage, and database backups** (AGENTS.md Section 12 has full detail) — none
    of this was part of the original Section 4 MVP scope; it came from client feedback and infra requests
    after Phase A:
    - The landing page contact form (`resources/views/welcome.blade.php`,
      `App\Http\Controllers\ContactController`) now has a required `phone` field and sends via Resend
      (`MAIL_MAILER=resend`) using a fully custom branded template (`App\Mail\ContactFormReceived`,
      `resources/views/emails/contact-form.blade.php`) — not Laravel's default Markdown mail styling.
      Current recipients: `ngmcleaning2026@gmail.com`, `jnguillaume4@gmail.com`, `jldajeune@gmail.com`.
    - Brand copy was updated per client feedback: Haitian-American heritage references removed site-wide
      (hero tag, "Our Story" section, image alt text, footer), service-area messaging broadened from
      Northeast Philadelphia to Pennsylvania/New Jersey/Delaware. A "professionalism as the primary brand"
      voice rewrite was explicitly requested but **not yet done** — don't assume it's finished; the "Our
      Story" section is a minimal placeholder pending that rewrite.
    - Photo storage (cleaner profile photos, job photos, agreement photos) moved to S3 — two buckets,
      `ngmcleaning-dev` and `ngmcleaning-prod`, both private (Block Public Access on, ACLs disabled, no
      public-read). The `public` disk (`config/filesystems.php`) now switches between `local`/`s3` via
      `FILESYSTEM_PUBLIC_DRIVER`; every existing `store('x', 'public')` call site is unchanged. All reads go
      through `App\Support\StorageUrl::for($path)` (30-minute signed temporary URL on s3, plain URL on
      local) — don't reintroduce raw `Storage::url()` calls for user-uploaded photos.
    - Database backups: `spatie/laravel-backup`, scheduled in `routes/console.php`
      (`backup:run --only-db` every 6 hours, `backup:clean` monthly on the first Sunday — both
      production-only via `->environments(['production'])`), uploading to the same per-environment S3
      buckets under an `ngm-cleaning/` key prefix, 30-day flat retention. Admin UI at
      `resources/views/pages/admin/⚡backups.blade.php` (`admin.backups` route, sidebar link) — list, manual
      "Back up now" trigger, delete with confirmation. If a manual backup fails with
      `mysqldump: command not found`, it's a PATH-resolution gap between the CLI and the web server
      process — set `DB_DUMP_BINARY_PATH` in `.env` (read by `config/database.php`), don't chase it as a
      PHP/Livewire bug.
    - Still outstanding: the Namecheap production `.env` doesn't have any of the above wired up yet (AWS
      credentials, `AWS_BUCKET=ngmcleaning-prod`, `DB_DUMP_BINARY_PATH`, Resend key), and there's still no
      server cron for `php artisan schedule:run` — both flagged to the client, neither resolved as of this
      writing.

## When something is ambiguous

Default to the simplest implementation that satisfies the MVP scope in AGENTS.md Section 4. This project
was deliberately scoped down (manual assignment, photo-only cleaner identity, no e-signature) to hit a
2-week timeline – when in doubt, don't add automation or extra surface area; flag it instead and ask.

## Commercial context (not implementation detail)

MVP build fee is $800 ($250 deposit / $550 on delivery), plus $150/month for ongoing maintenance and
hosting support. Domain and hosting costs are billed to the client separately. This isn't something to
build into the app – it's just context for why the scope in AGENTS.md is kept tight.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
