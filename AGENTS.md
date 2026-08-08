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
   automatic/algorithmic matching in the MVP.
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
- View job status and history.
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
- **File storage:** Laravel's filesystem abstraction (`Storage` facade) for profile photos and the admin-
  uploaded agreement photos. Start on the `public` local disk (fine for shared hosting); the abstraction
  means swapping to an S3-compatible disk later requires no code changes, just config.
- **Payments:** Laravel Cashier (Stripe) for cleaner subscriptions – two Stripe Price objects (monthly $25,
  annual $225), Cashier webhook handling keeps subscription status in sync automatically.
- **Notifications:** Laravel's built-in Mail/Notification system for the "cleaner assigned" email to
  customers – use whatever SMTP/API mail provider the shared host supports (or a low-cost API provider like
  Mailgun/Resend if the host's SMTP is unreliable).
- **Icons:** Lucide (already used in the landing-page reference) – either the Blade Lucide package or the
  CDN script, matching the reference file.
- **Hosting:** Shared PHP hosting to start (confirm PHP version support, Composer/SSH access, and a cron
  entry for `php artisan schedule:run`, which Cashier/subscription renewal checks need). Domain and hosting
  costs are billed to the client separately from the build fee – not a dev concern, just noted for context.

### Suggested core data model (Eloquent-style)

```
users            { id, role[admin|cleaner|customer], name, email, password, created_at }
cleaner_profiles { id, user_id, phone, photo_path, agreement_photo_path, agreement_signed:boolean,
                   subscription_plan[monthly|annual], subscription_status, stripe_id, stripe_status,
                   next_renewal_at }
customer_profiles{ id, user_id, phone }
jobs             { id, customer_id, cleaner_id(nullable), address, requested_at, notes, property_size,
                   status[requested|assigned|completed], created_at }
```

This is a starting point, not a locked schema – refine as needed during Phase 1.

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
- **Target audience:** Northeast Philadelphia homeowners seeking a trusted, high-quality house cleaning
  service.
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
