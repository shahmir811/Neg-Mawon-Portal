# NEG MAWON Cleaner–Customer Booking Portal

## 1. What this project is

A private, two-sided web portal for **NEG MAWON CLEANING SERVICES LLC** (owner: James), a house cleaning
business in Philadelphia, PA. Today James books jobs manually by phone with no system to onboard cleaners,
take job requests, or assign work. This project replaces that manual process with a web portal where:

- **Customers** (house owners) submit cleaning job requests.
- **James (Admin)** reviews requests and manually assigns a cleaner to each job.
- **Cleaners** work jobs sourced through the platform and pay James a recurring subscription fee to use it.

James is positioned as the sole point of contact for every booking. Cleaners and customers never interact
directly outside the platform, and customers are deliberately shown minimal information about the cleaner
assigned to them (see Section 5, Privacy Rules).

This is an MVP being built under a tight **2-week (14-day)** timeline. Keep scope disciplined – see
Section 7 (Out of Scope) before adding anything not listed in Section 4.

Client contact: James – jnguillaume4@gmail.com / +1 (267) 690-1707
Business: NEG MAWON CLEANING SERVICES LLC, 7135 Rising Sun Ave, Philadelphia, PA 19111

## 2. User roles

### Admin (James) – the only admin account

- Approves and onboards new cleaners.
- Reviews the signed exclusivity agreement photo each cleaner submits in-app and approves it (see Section 6
  – there is no in-app e-signature; the cleaner uploads a photo of a hand-signed document, James just
  approves it). Can also upload the photo himself as a fallback (e.g. a cleaner sends it via WhatsApp/email
  instead of using the app), which counts as instantly approved.
- Reviews all incoming job requests from customers.
- Manually assigns a specific cleaner to a specific job (no automated/algorithmic matching in the MVP).
- Manages each cleaner's subscription status (monthly/annual, paid/unpaid).
- Has full visibility into all jobs, cleaners, and customers.
- Basic reporting: number of jobs, active cleaners, revenue from subscriptions.

### Cleaner

- Signs up in-app (name, email, password, phone number, profile photo) – self-registration, not an
  admin-created account. James then reviews the new cleaner via the admin Cleaners List and handles the
  agreement + subscription steps below; there is no separate "approve" action beyond that.
- Signs the exclusivity agreement **outside the app** (by hand), then photographs it and uploads that photo
  from their own Settings page for James to review and approve (see Section 6). Sending it to James outside
  the app (WhatsApp/email) for him to upload instead remains a fallback, not the primary path.
- Pays the platform subscription fee: **$25/month**, or **$225/year** (a 25% discount vs. paying monthly
  for 12 months, which would be $300/year).
- Uploads a profile photo – this is the only thing customers ever see about them.
- Logs into a dashboard to view jobs assigned to them (address, date/time, job notes) and mark jobs
  complete.

**Implemented:** photo upload isn't limited to registration – cleaners can replace their profile photo
anytime from Settings → Profile (`resources/views/pages/settings/⚡profile.blade.php`), which updates the
same `cleaner_profiles.photo_path` used everywhere else. The admin Cleaners List
(`resources/views/pages/admin/⚡cleaners.blade.php`) also shows this photo next to each cleaner's name, so
James can verify one is on file.
- Views their own subscription status and next payment date.

### Customer (house owner)

- Creates an account and submits a job request: service address, preferred date/time, notes, property
  size/number of rooms.
- Views job status (requested → assigned → completed) and job history.
- Once James assigns a cleaner, sees **only that cleaner's photo** – no name, phone number, or any other
  identifying detail (see Section 5).
- Receives an email notification when a cleaner is assigned.

## 3. Core business rules (do not violate these when implementing)

1. **Manual assignment only.** Job ↔ cleaner matching is done by James in the admin panel. Do not build
   automatic/algorithmic matching in the MVP. (The cleaner `zip_code` field added in Phase A — see Section
   4a — is a manual proximity hint for James only; do not build distance sorting or auto-selection on top
   of it without revisiting this rule.)
2. **Cleaner privacy.** A customer must never see a cleaner's name, phone number, email, or any contact
   info – only their profile photo, and only after James has assigned them to that customer's job.
3. **No in-app e-signature.** The exclusivity agreement is signed by hand outside the app – only a *photo*
   of that signed document is ever handled in-app. The cleaner uploads that photo from their own profile;
   James reviews it and approves it. There is no typed/drawn signature capture anywhere.
4. **Subscription pricing is fixed logic**: $25/month flat; annual plan is $225/year (25% off the
   equivalent $300/year monthly cost). Billing should support both cadences plus proration/renewal.
5. **Cleaners are exclusive to the platform** once a client is sourced through NEG MAWON – this is a policy
   enforced by the signed agreement. The admin panel makes agreement status (not submitted / pending
   James's review / approved) visible per cleaner, and application logic enforces it at the assignment
   step: only cleaners with an approved agreement (`agreement_signed = true`) appear in the admin's
   assign-cleaner dropdown, and the assignment action itself is server-side blocked for any other cleaner –
   James cannot assign a job to an unapproved cleaner even by manipulating the request.
6. **Account deletion must never destroy job or financial history.** A customer's job history and a
   cleaner's completed-job record are business records James relies on (reporting, revenue, disputes) – they
   must survive even if that customer or cleaner deletes their own account. Self-service "delete account"
   is implemented as a soft delete on `users` for exactly this reason (see Section 8).

## 4. MVP feature scope

### Customer-facing

- Sign up / log in.
- Create a job request (address, date/time, notes, property size/rooms).
- Specify service details on the request: home/business type (including a generic Commercial option),
  service needed, frequency, type of cleaning (Deep or Soft), pets (yes/no, kind, count, with a free-text
  field when "Other" is picked), and a laundry add-on flag (billed by James off-platform, not an in-app
  charge).
- Deep vs. Soft cleaning is gated by a 30-day repeat-client rule: first-time customers and anyone whose last
  *completed* job was more than 30 days ago only get Deep Cleaning offered; a customer with a completed job
  within the last 30 days can also pick Soft. Enforced both in the form UI and server-side on submit (see
  `App\Concerns\CleaningJobFormFields`).
- Edit a submitted job request (any of the fields above, plus address/date/notes/photos) at any time before
  James assigns a cleaner. Once assigned, the request is locked — the edit link disappears and the edit page
  itself redirects away if visited directly, checked both on page load and on save to close the race where
  James assigns it while the form is open.
- View job status and history, sorted by most recently requested first.
- See the assigned cleaner's photo once assigned.
- Email notification when a cleaner is assigned.

### Cleaner-facing

- Sign up / log in – registration collects phone number and profile photo directly (a role toggle on the
  same registration screen used by customers, not a separate invite flow).
- Onboarding: profile info + profile photo.
- Upload a photo of the signed exclusivity agreement for James to review and approve (Section 6); can
  resubmit anytime, which resets its status back to pending review.
- View assigned jobs (address, date/time, notes).
- Mark a job complete.
- View own subscription status and next payment/renewal date.

### Admin panel (James)

- Dashboard of all incoming job requests.
- Review new cleaner sign-ups in the Cleaners List (self-registered, shown with agreement/subscription
  status) and approve the signed-agreement photo each cleaner submits (or upload it himself as a fallback).
- Assign a specific cleaner to a specific job.
- Manage all cleaners (status, subscription plan, payment status).
- Manage all customers and their job history.
- Basic reporting: job count, active cleaner count, subscription revenue.

## 4a. Post-MVP-kickoff additions (Phase A — client feedback, 2026-08-15)

James gave feedback beyond the original Section 4 scope after the MVP kickoff (pricing calculator, floor
type, room counts, cleaner distance/zip). These are tracked separately from Section 4 rather than merged
into it, since Section 4 is what the $800 MVP fee was quoted against (see Section 10) — everything below is
an addition on top of that, useful context if there's a Phase B billing conversation later.

- **Cost estimate calculator.** No pricing document was ever provided by James, so instead of blocking on
  one, every job stores a system-calculated `estimated_price`, computed from admin-editable rules (base
  fee, per-bedroom/bathroom rate, a size-band fallback rate for non-residential jobs, pet/laundry/deep-clean
  fees, and a recurring-frequency discount) — see `App\Services\JobPriceCalculator` and
  `App\Models\PricingSetting`. James edits the actual numbers from Admin → Pricing (`admin.pricing`) any
  time, no code change needed. The estimate and its full line-item breakdown are admin-only, shown on the
  job details page (`resources/views/pages/admin/⚡job.blade.php`) alongside a `final_price` field James can
  set to override it. **Deliberately not shown to the customer** on the job request form — showing a number
  built from placeholder rates before James confirms real ones would set a price expectation the app can't
  honor. Only re-enable it for customers once real rates are entered into `pricing_settings`.
- **Bedroom/bathroom counts.** Required on the job request form only when `property_type` is Residential;
  non-residential jobs (commercial, church, restaurant, office, retail, other) still price off the
  `property_size` band instead.
- **Floor type** (carpet / hardwood-tile / mixed). Equipment-prep info shown to the assigned cleaner; not
  currently a pricing input.
- **Cleaner zip code** (`cleaner_profiles.zip_code`, optional). James wants to assign the closest cleaner to
  a job. This is a **manual hint only** — shown as plain text next to each cleaner's name on the admin
  assign-cleaner dropdown and the Cleaners List so James can judge proximity himself. There is no distance
  calculation, sorting, or automatic selection built on it — that would cross into automated job-to-cleaner
  matching, which Section 3 rule 1 forbids. If the client explicitly asks for real distance sorting later,
  treat that as a deliberate scope decision to revisit, not something to add quietly.

## 5. Privacy rule (critical, re-stated)

When James assigns a cleaner to a job, the customer-facing job record must expose **only the cleaner's
photo**. Do not include the cleaner's name, phone, email, or user ID in any customer-facing API response,
page, or notification. Enforce this at the API/query layer, not just in the UI – the customer's client
should never even receive the excluded fields in the payload.

## 6. Exclusivity agreement workflow

1. James sends the agreement document to the cleaner (outside the app).
2. Cleaner signs by hand and photographs the signed document.
3. Cleaner uploads that photo from their own Settings page in-app. This does **not** immediately mark the
   agreement as signed — it puts it in a "pending review" state.
4. James reviews the submitted photo in the admin Cleaners List and approves it, which is what finally
   marks the agreement as signed.
5. Fallback: a cleaner can still send the photo to James outside the app (WhatsApp/email), and James uploads
   it himself from the admin panel – this path marks it approved immediately, since it's James's own action.

There is still no e-signature UI anywhere – both paths only ever handle a photo of a document that was
signed by hand.

**Implemented:** the whole flow above is built.
- **Cleaner side** (`resources/views/pages/settings/⚡profile.blade.php`): an "Exclusivity agreement"
  section lets the cleaner upload/replace the photo. Any upload (including a resubmission) sets
  `agreement_photo_path` and always resets `agreement_signed = false`, since a new photo needs fresh review.
- **Admin side** (`resources/views/pages/admin/⚡cleaners.blade.php`): the Agreement column shows a status
  badge – Not on file / Pending review / Approved – derived from those same two columns
  (`CleanerProfile::agreementStatus()`, backed by the `App\Enums\AgreementStatus` enum). When status is
  Pending, an "Approve" button sets `agreement_signed = true` without re-uploading anything. The original
  admin "Upload/Replace photo" modal still exists as the fallback path described in step 5 above, and still
  auto-approves on save.

## 7. Out of scope for MVP (do not build without explicit request)

- In-app messaging between customer and cleaner.
- Automated/algorithmic job-to-cleaner matching.
- Customer ratings/reviews of individual cleaners.
- Native mobile app (MVP is a mobile-friendly responsive web portal only).
- In-app payment from customer to James for the cleaning job itself (only the cleaner's platform
  subscription is billed in-app; job payment can remain off-platform for now).
- E-signature (typed/drawn signature capture) anywhere in the app. Cleaner-facing document upload is in
  scope and built (Section 6) – it's a photo of a hand-signed paper, not an e-signature.

## 8. Technical stack (decided)

Chosen specifically because the client's budget doesn't support a VPS or Node hosting – this stack deploys
cleanly on ordinary PHP shared hosting.

- **Framework:** Laravel (latest stable). Single codebase for customer, cleaner, and admin surfaces using
  route groups + role-based middleware to separate them.
- **Frontend interactivity:** Livewire + Alpine.js. Livewire ships Alpine under the hood, so this is one
  cohesive layer, not two – use Livewire components for anything stateful (job assignment, admin tables,
  dashboards) and raw Alpine only for small client-side UI (toggles, dropdowns, mobile nav).
- **Styling:** Tailwind CSS, compiled via Laravel's Vite integration (`npm run build`) for production – do
  **not** use the Tailwind CDN `<script>` tag in the shipped app (that's fine for the static landing-page
  reference file, but the real Blade views should use a proper compiled Tailwind config so the custom theme
  in Section 8a below is available as real utility classes, e.g. `bg-primary`, `font-heading`).
- **Auth:** Laravel Breeze (Blade + Livewire stack) scaffolds login/register fast; extend with a `role`
  enum column (`admin` / `cleaner` / `customer`) on the `users` table and route middleware per role.
- **Account deletion:** the self-service "delete account" feature (Fortify/Breeze default) soft-deletes
  (`SoftDeletes` on `App\Models\User`, `deleted_at` column) instead of hard-deleting, per the business rule
  in Section 3. This is deliberate: `cleaner_profiles`/`customer_profiles` cascade-delete on a real `DELETE`,
  and `cleaning_jobs.customer_id` does too (`cleaning_jobs.cleaner_id` is only `nullOnDelete`) – none of that
  fires on a soft delete, so job/financial history survives regardless of who deletes their account. The
  `users.email` unique index is a composite `(email, deleted_at)` index, not a plain unique column, so a
  soft-deleted account's email frees up for a new registration instead of permanently blocking it (see the
  `add_soft_deletes_to_users_table` migration). `ProfileValidationRules::emailRules()` also excludes trashed
  rows from the app-level uniqueness check via `Rule::unique(...)->withoutTrashed()`.
- **Database:** MySQL – universally available on shared hosting, no extra config needed with Laravel.
- **File storage: now on S3** (done 2026-10-02 — Section 12 has full detail; this bullet originally said
  "start on local disk," which has been superseded). Two buckets in one AWS account: `ngmcleaning-dev`
  (local/dev) and `ngmcleaning-prod` (production), both with Block Public Access on and ACLs disabled —
  nothing is served via a plain public URL. The `public` filesystem disk (`config/filesystems.php`) is now
  driver-switchable via `FILESYSTEM_PUBLIC_DRIVER` (`local` or `s3`); every existing upload call site
  (`store('x', 'public')`) was left untouched — only the disk definition changed. Reads go through
  `App\Support\StorageUrl::for($path)`, which generates a 30-minute signed temporary URL on S3
  (`providesTemporaryUrls()`) and falls back to a plain URL on the local disk — this replaced every
  `Storage::url(...)` call site (the `CleaningJob`, `User`, and `CleaningJobPhoto` models; the admin
  Cleaners List; Settings → Profile).
- **Payments:** Laravel Cashier (Stripe) for cleaner subscriptions – two Stripe Price objects (monthly $25,
  annual $225), Cashier webhook handling keeps subscription status in sync automatically.
- **Notifications:** Resend is the decided mail provider (`MAIL_MAILER=resend`, `RESEND_API_KEY` in `.env`,
  `resend/resend-php` installed). As of this writing it's only wired up for the public landing-page contact
  form (`App\Mail\ContactFormReceived`, a fully custom branded HTML template at
  `resources/views/emails/contact-form.blade.php` — not Laravel's default Markdown mail styling) — see
  Section 12. The Section 4 "cleaner assigned" customer-facing notification is still not built; reuse this
  same Resend setup for it.
- **Icons:** Lucide (already used in the landing-page reference) – either the Blade Lucide package or the
  CDN script, matching the reference file.
- **Hosting:** Shared PHP hosting to start (confirm PHP version support, Composer/SSH access, and a cron
  entry for `php artisan schedule:run`, which Cashier/subscription renewal checks need). Domain and hosting
  costs are billed to the client separately from the build fee – not a dev concern, just noted for context.

### Suggested core data model (Eloquent-style)

```
users            { id, role[admin|cleaner|customer], name, email, password, created_at }
cleaner_profiles { id, user_id, phone, zip_code(nullable), photo_path, agreement_photo_path,
                   agreement_signed:boolean, subscription_plan[monthly|annual], subscription_status,
                   stripe_id, stripe_status, next_renewal_at }
customer_profiles{ id, user_id, phone }
jobs             { id, customer_id, cleaner_id(nullable), address, requested_at, notes, property_size,
                   bedroom_count(nullable), bathroom_count(nullable),
                   property_type[residential|commercial|church|restaurant|office|retail|other],
                   service_type, frequency, cleaning_type[deep|soft], has_pets:boolean, pet_types:json,
                   pet_type_other(nullable), pet_count(nullable), laundry_addon:boolean,
                   floor_type[carpet|hard_floor|mixed](nullable), estimated_price(nullable),
                   final_price(nullable), status[requested|assigned|completed], created_at }
pricing_settings { id, base_flat_fee, per_bedroom_rate, per_bathroom_rate, base_rates_by_size:json,
                   pet_fee_per_pet, laundry_fee, deep_cleaning_surcharge, frequency_discounts:json }
```

This is a starting point, not a locked schema – refine as needed during Phase 1. (The columns above beyond
`property_size`/`notes` were added post-MVP-kickoff, after a client call requested richer service-detail
capture — see the `CleaningType`, `PropertyType`, `PetType` enums under `app/Enums/`. The
`bedroom_count`/`bathroom_count`/`floor_type`/`estimated_price`/`final_price` columns on `jobs`, `zip_code`
on `cleaner_profiles`, and the `pricing_settings` table were added later still, in Phase A — see Section
4a.)

## 8a. Design system (from client-approved landing page)

The client's landing page was already designed and approved – its look and feel is the design system for
the **entire application**, not just the homepage. Two reference files live in `design/` in this project
folder and are both the visual source of truth for every screen (customer, cleaner, and admin), not only
the public marketing page:

- `design/landing-page-reference.html` – the approved landing page markup/layout.
- `design/neg-mawon-brand.json` – the extracted brand spec (colors, type scale, spacing, component tokens,
  logo lockup, CTA copy, brand voice) backing the details in this section. If this section and the JSON
  ever disagree, the JSON is the source of truth – update this section to match it.

**The landing page itself is the app's index/homepage.** Convert `design/landing-page-reference.html` into
`resources/views/welcome.blade.php` (or equivalent) and wire it to the `/` route – that's what visitors see
first. Keep its content and layout intact; only adapt the contact form / nav links as needed once auth
routes exist (e.g. "Free Quote" / login links).

### Colors

| Token        | Utility class                     | Hex       | Usage                                                                                                                        |
| ------------ | --------------------------------- | --------- | ---------------------------------------------------------------------------------------------------------------------------- |
| `primary`    | `bg-primary` / `text-primary`     | `#0B3B2E` | Deep forest green – headers, primary buttons, headings, icons on dark, links                                                 |
| `secondary`  | `bg-secondary` / `text-secondary` | `#E8DFD3` | Warm beige – section backgrounds, borders, badge backgrounds                                                                 |
| `gold`       | `bg-gold` / `text-gold`           | `#C89B3C` | Gold/mustard – icon accents, highlights, hover states, star ratings (this is the brand's "accent" color, but see note below) |
| `background` | `bg-background`                   | `#FAF7F2` | Off-white/cream – default page background                                                                                    |
| `text`       | `text-text`                       | `#1A1A1A` | Near-black – body copy (often at 70–85% opacity for secondary text)                                                          |
| `surface`    | `bg-surface`                      | `#FFFFFF` | Card backgrounds                                                                                                             |

Color scheme is light-only for the MVP (no dark-mode toggle).

**Implementation note:** this project's Tailwind v4 setup has no `tailwind.config.js` – colors and fonts are
declared as CSS custom properties in the `@theme` block of `resources/css/app.css`, which Tailwind v4 turns
directly into utility classes:

```css
/* resources/css/app.css, inside the existing @theme { ... } block */
--font-heading: "Fraunces", Georgia, "Times New Roman", serif;
--font-body:
    "Plus Jakarta Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;

--color-primary: #0b3b2e;
--color-secondary: #e8dfd3;
--color-gold: #c89b3c;
--color-background: #faf7f2;
--color-text: #1a1a1a;
--color-surface: #ffffff;
```

The brand's "accent" color is named `gold` here, **not `accent`** – Flux (already installed) reserves
`--color-accent` in this same file for its own semantic UI role (focus rings, etc.), and overwriting it
would silently change focus-ring styling across every existing auth/dashboard screen. Don't rename it back
to `accent` without deliberately deciding to also restyle Flux's semantic usage.

The two brand fonts are self-hosted via Laravel's Vite font bundling (`bunny()` entries in
`vite.config.js`, rendered via the `@fonts` Blade directive in `<head>`) rather than a Google Fonts
`<link>` tag – same fonts, just fetched through Laravel's own asset pipeline instead of an external request
on every page load.

### Fonts & type scale

- **Headings (`font-heading`):** Fraunces (serif, weights 300–700) – Google Fonts. Used for all `h1`–`h6`
  and card/section titles, and doubles as the "display" role. Headings are typically `font-weight: 500`,
  tight tracking (`tracking-tight` / `letter-spacing: -0.02em`), and sometimes styled with an italic accent
  word in `gold` (e.g. "_Cleaned With Pride._"). Font stack fallback: `Fraunces, Georgia, "Times New
Roman", serif`.
- **Body (`font-body`):** Plus Jakarta Sans (sans-serif, weights 300–700) – Google Fonts. Used for
  everything else: nav, buttons, labels, paragraph text, form fields. Font stack fallback:
  `"Plus Jakarta Sans", "Helvetica Neue", Helvetica, Arial, sans-serif`.
- Load via: `https://fonts.googleapis.com/css2?family=Fraunces:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap`
- **Reference sizes:** `h1` 56px, `h2` 36px, body 18px. Treat these as the anchor sizes for the largest
  hero headline and default paragraph text respectively – scale other headings (`h3`–`h6`) down
  proportionally using normal Tailwind heading conventions.

### Spacing & radius

- **Base unit:** 4px – use Tailwind's default spacing scale (which is already 4px-based); don't introduce a
  custom spacing scale.
- **Default border radius:** 16px for cards/panels/inputs (`rounded-2xl` ≈ 16px in Tailwind's default
  scale). Buttons are the exception – always full pill radius (`rounded-full` / `9999px`), never 16px.

### Logo / wordmark

There is no standalone logo image asset. The brand mark is an icon + text lockup, not a file:

- A Lucide **`sparkles`** icon inside a circular `primary`-colored (`#0B3B2E`) badge.
- Paired with the **"Nèg Mawon"** wordmark set in `font-heading` (Fraunces).
- Links to `/` (home).

Reuse this exact lockup in the nav bar on every screen (customer, cleaner, admin) – don't substitute a
different icon or generate a logo image.

### Component tokens

- **Button primary:** background `primary` (`#0B3B2E`), text `background` (cream `#FAF7F2`), fully pill
  radius, shadow `0px 10px 25px rgba(11, 59, 46, 0.2)` (a soft `primary`-tinted drop shadow, never neutral
  gray). Example primary CTA copy from the landing page: **"Call (267) 690-1707"**.
- **Button secondary:** translucent white background (`rgba(255,255,255,0.7)`), text `primary`, border
  `rgba(11, 59, 46, 0.2)`, fully pill radius, no shadow. Example secondary CTA copy: **"Get a Free Quote"**.
  Pair a primary + secondary CTA together (a firm commitment action next to a lower-commitment one), as the
  hero section does.
- **Inputs (on dark/glass sections, e.g. the contact form):** background `rgba(255,255,255,0.08)`, text
  cream (`#FAF7F2`), border `rgba(255,255,255,0.2)`, `12px` radius, no shadow – this is the glassmorphism
  treatment described below, not the default light-surface input style.
- **Buttons (general):** fully pill-shaped (`rounded-full`); on dark sections, CTA buttons instead use a
  gold gradient (`linear-gradient(135deg, #C89B3C, #b8892e)`) with `primary`-colored text.

### Other component/UI conventions to reuse app-wide

- **Cards:** white surface, `secondary`-colored border, large radius (`rounded-2xl`/`rounded-3xl`, ~16px+),
  subtle hover lift (`-translate-y-2`) and shadow on hover.
- **Section pattern:** small uppercase pill "eyebrow" badge (`secondary` bg, `primary` text, tracking-wide,
  text-xs) → `font-heading` headline → supporting paragraph in `font-body` at ~75% text opacity.
  Alternate section backgrounds between `background` (#FAF7F2) and `secondary` (#E8DFD3) for rhythm, same
  as the landing page's Services/About/Process/Testimonials sections.
- **Dark sections (e.g. contact/footer):** deep `primary` gradient background, `background`-cream text,
  `gold` highlights, glassmorphism cards (`backdrop-blur`, low-opacity white overlays,
  semi-transparent borders, and the glass input style above).
- **Icons:** Lucide icon set throughout (`data-lucide="..."` + `lucide.createIcons()`), typically inside a
  rounded `primary`-colored badge with a `gold` icon.
- **Motion:** smooth-scroll on the page, and a simple fade/slide-up on scroll via an
  `IntersectionObserver` toggling an `.animate-on-scroll` → `.visible` class (opacity 0→1,
  translateY 20px→0). Reuse this pattern for any new marketing-style sections.
- **Layout:** centered content container `max-w-6xl mx-auto px-6 sm:px-8`; generous vertical section padding
  (`py-20 md:py-28 lg:py-32`).

### Brand voice & personality

- **Tone:** warm, family-owned, trustworthy.
- **Energy:** calm-confident – not hypey or high-pressure.
- **Target audience (updated 2026-10-02, client feedback — see Section 12):** homeowners, businesses, and
  organizations across Pennsylvania, New Jersey, and Delaware — broadened from the original
  Northeast-Philadelphia-only positioning. Updated in `resources/views/welcome.blade.php` and the auth-page
  side panel (`resources/views/layouts/auth/split.blade.php`). The physical office address (7135 Rising Sun
  Ave, Philadelphia, Section 1) is unchanged – it's still the real HQ, just no longer stated as a service-area
  limit.
- **Haitian-American ownership/heritage framing removed** (same client feedback round): the "Our Story"
  section's "Nèg Mawon" origin narrative, the Haitian-American hero/footer tags, and related image alt text
  were all pulled. The company name itself stays "NGM Cleaning." A **professionalism-first brand voice
  rewrite** was explicitly requested by the client but **deferred, not yet built** – the warm/family-run tone
  described below is still current until that's revisited; don't assume it's been replaced.
- Carry this tone into all in-app copy (empty states, confirmation messages, emails), not just marketing
  copy – e.g. the "cleaner assigned" email notification (Section 4) should read as warm and reassuring, not
  transactional/robotic.

Apply this same palette, type system, and component language to the customer portal, cleaner dashboard, and
admin panel – buttons, cards, badges, and section headers across the whole app should look like they belong
to the same brand as the landing page, not a generic admin-template aesthetic.

## 9. Timeline (2-week build)

| Phase   | Scope                                                                                     | Days  |
| ------- | ----------------------------------------------------------------------------------------- | ----- |
| Phase 1 | Landing page → Blade/Tailwind conversion, cleaner/customer/admin account structure & auth | 1–3   |
| Phase 2 | Job request flow, admin job assignment, photo display                                     | 4–7   |
| Phase 3 | Agreement photo upload to cleaner profile, subscription billing (Stripe monthly/annual)   | 8–11  |
| Phase 4 | Testing, revisions, launch                                                                | 12–14 |

## 10. Commercial terms (context only, not application logic)

- MVP build fee: $800 total ($250 deposit to start, $550 on delivery).
- Ongoing maintenance/hosting support: $150/month, billed to the client separately.
- Domain and hosting costs are NOT included in either fee above and are billed to the client at cost.

These are business terms between The Techmint and the client – not something to encode into the app itself,
but useful context for why the timeline and scope are kept tight.

## 11. Working conventions for AI coding agents

- Keep the MVP scope in Section 4 as the source of truth. If a request seems to expand scope, flag it
  rather than silently building it.
- Enforce the privacy rule (Section 5) at the data/API layer in every new endpoint that returns job or
  cleaner data to a customer-facing context.
- Prefer small, working increments per phase over a big-bang implementation – the client has a hard 14-day
  deadline.
- Write environment variables to `.env` (never commit secrets); keep `.env.example` up to date as each
  integration is introduced (DB credentials, `APP_KEY`, Stripe keys, mail provider credentials, filesystem
  disk config).
- Keep the visual design consistent with Section 8a everywhere – new screens (dashboards, forms, admin
  tables) should reuse the same color tokens, fonts, and component patterns as the landing page, not
  default Tailwind/Livewire styling.
- When in doubt about a product decision not covered here, default to the simplest option that satisfies
  the MVP scope in Section 4 – this project intentionally avoids automation, messaging, and ratings in v1.

## 12. Phase B additions (contact form, S3 storage, backups — client feedback round 2, 2026-10-01/02)

A second round of client feedback and infra requests came in after Phase A (Section 4a), mostly unrelated to
the core booking-portal feature set. Tracked here for the same reason as Section 4a – none of this was part
of the original $800 MVP scope quoted against Section 4.

### Landing page contact form

- Added a required `phone` field alongside name/email/message (`resources/views/welcome.blade.php`,
  `App\Http\Controllers\ContactController`, validated server-side on every submit).
- Submissions email a fully custom branded HTML template (`App\Mail\ContactFormReceived`,
  `resources/views/emails/contact-form.blade.php`) via Resend – not Laravel's default Markdown `MailMessage`
  styling. Reply-To is set to the submitter's own email, and the template includes a "Reply to [Name]"
  `mailto:` button.
- Current recipients: `ngmcleaning2026@gmail.com`, `jnguillaume4@gmail.com`, `jldajeune@gmail.com` (set in
  `ContactController::store()` – the client explicitly removed `bookings@ngmcleaning.com` from this list; it
  remains the `MAIL_FROM_ADDRESS` emails send *from*, just not a recipient).
- Fixed a UX bug where submitting the form caused a visible double-scroll (page loads at the top, then jumps
  to `#contact`): the cause was global `scroll-behavior: smooth` CSS animating the browser's native anchor
  landing after the POST-redirect. Fixed by removing that global rule and adding a click-only smooth-scroll
  JS handler for in-page anchor links instead – the post-submit redirect now lands instantly, no animation.

### Brand/geography copy changes

See the Section 8a "Brand voice & personality" update above for the full detail (heritage framing removed,
service area broadened to PA/NJ/DE, professionalism rewrite requested but deferred).

**Flagged, not resolved:** the client's feedback also mentioned "a private two-sided marketplace," which may
describe something structurally different from what's actually built (manual admin-mediated assignment –
Section 3 rule 1 forbids self-serve/algorithmic matching). This was surfaced back to the client as a
clarifying question, not yet answered as of this writing. Don't assume either interpretation (cosmetic
language vs. real scope change) if this resurfaces – confirm with the client first.

### File storage on S3

See the Section 8 "File storage" update above for the technical detail. Summary: two private buckets
(`ngmcleaning-dev`, `ngmcleaning-prod`, region `ap-southeast-2`), an IAM user (`ngm-cleaning-app`) scoped to
only those two buckets rather than the AWS root/console user, and access exclusively via signed temporary
URLs (`App\Support\StorageUrl`) – never plain public URLs, since the buckets block all public access by
design. Two non-obvious gotchas hit and fixed, worth knowing if this needs touching again:

- The `root` config key means "local folder" for the `local` driver but becomes a bucket key-prefix for
  `s3` – it must be an empty string on s3, not `storage_path(...)`, or uploads land under a garbage key built
  from the server's absolute filesystem path.
- `visibility: public` tries to set a `public-read` ACL on every upload, which S3 silently rejects (with
  `throw: false` swallowing the failure) since the buckets have ACLs disabled by design (bucket-owner-
  enforced). Only apply `visibility: public` for the `local` driver.

Local/dev currently has `FILESYSTEM_PUBLIC_DRIVER=s3` set, meaning local development uploads really do hit
the real `ngmcleaning-dev` bucket, not local disk.

### Database backups

- `spatie/laravel-backup` installed, configured to dump the database only (`--only-db`, no application
  files) and upload to the `s3` disk – same AWS account/credentials as the file storage above, same
  bucket-per-environment split, separate from the `public` disk config.
- Backup S3 key format: `ngm-cleaning/ngm-cleaning-db-backup-{Y-m-d-H-i-s}.zip` (`config/backup.php`
  `backup.name` + `destination.filename_prefix`).
- Scheduled in `routes/console.php`, **production environment only** (`->environments(['production'])`, so
  nothing fires on local/dev automatically): `backup:run --only-db` every 6 hours, `backup:clean` on the
  first Sunday of each month (`cron('0 0 1-7 * 0')`). Trigger a dev backup manually with
  `php artisan backup:run --only-db`, or from the admin UI below.
- Retention: a flat 30-day cutoff, no tiered daily/weekly/monthly thinning
  (`cleanup.default_strategy` in `config/backup.php` – `keep_all_backups_for_days: 30`, every other tier set
  to `0`). `backup:clean` only ever deletes files inside the `ngm-cleaning/` folder prefix – cleaner/job/
  agreement photos live in separate top-level folders in the same bucket and are never touched by it.
- Default Spatie email notifications (backup success/failure) are disabled in config – out of the box they
  pointed at a placeholder address. Not wired to a real inbox; ask before enabling.
- **Admin UI**: `/admin/backups` (`resources/views/pages/admin/⚡backups.blade.php`, admin-only, sidebar link
  added) – lists backups newest-first with size/date, a "Back up now" button that runs `backup:run --only-db`
  synchronously and toasts success/failure, and a per-backup Delete button behind a confirmation modal.
- **Known gotcha, already hit and fixed:** `spatie/laravel-backup` shells out to `mysqldump`, resolved via
  the OS `PATH`. The web server process (`php -S` locally, likely PHP-FPM on the Namecheap host) does **not**
  see the same `PATH` a login shell does, so a backup triggered from the CLI can succeed while the identical
  command triggered from the admin UI fails with `mysqldump: command not found`. Fixed by setting
  `DB_DUMP_BINARY_PATH` (`config/database.php` → `connections.mysql.dump.dump_binary_path`) to the directory
  containing `mysqldump` explicitly, bypassing `PATH` resolution entirely. Local `.env` has this set to
  `/opt/homebrew/bin`; **production will need its own value** once deployed – find it via `which mysqldump`
  over SSH on the Namecheap host and set `DB_DUMP_BINARY_PATH` accordingly if the same error shows up there.
- Tests: `tests/Feature/AdminBackupsTest.php` (access control, empty state, listing order, delete, manual
  trigger) – all run against `Storage::fake('s3')`, never real AWS.

### Still outstanding from this round

- The Namecheap production `.env` does not yet have any of the above wired up (AWS credentials,
  `AWS_BUCKET=ngmcleaning-prod`, `DB_DUMP_BINARY_PATH`, the Resend key) – flagged repeatedly during this
  work, not yet done.
- No cron entry exists yet on the Namecheap host for `php artisan schedule:run` – needed both for the
  6-hourly backup schedule above and for Cashier's subscription renewal checks (Section 8) whenever billing
  is eventually built. This was already a known gap before this round and is still unresolved.
- The "professionalism brand rewrite" and the "two-sided marketplace" scope question above are both open
  questions with the client, not yet answered.

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
