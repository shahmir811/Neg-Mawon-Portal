# CLAUDE.md

This file gives Claude (or any AI coding agent working in this repo via Antigravity or another IDE)
quick orientation. The full project spec – roles, features, business rules, data model, and timeline –
lives in **[AGENTS.md](./AGENTS.md)**. Read that file first; this file only adds working notes on top of it.

## Project in one line

A 2-week MVP web portal for NEG MAWON CLEANING SERVICES LLC where customers request cleaning jobs, James
(the sole admin) manually assigns a cleaner to each job, and customers only ever see the assigned cleaner's
photo – nothing else about them.

## Non-negotiable rules (see AGENTS.md Sections 3 & 5 for full detail)

- **No automated job-to-cleaner matching.** Assignment is a manual admin action.
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

## When something is ambiguous

Default to the simplest implementation that satisfies the MVP scope in AGENTS.md Section 4. This project
was deliberately scoped down (manual assignment, photo-only cleaner identity, no e-signature) to hit a
2-week timeline – when in doubt, don't add automation or extra surface area; flag it instead and ask.

## Commercial context (not implementation detail)

MVP build fee is $800 ($250 deposit / $550 on delivery), plus $150/month for ongoing maintenance and
hosting support. Domain and hosting costs are billed to the client separately. This isn't something to
build into the app – it's just context for why the scope in AGENTS.md is kept tight.
