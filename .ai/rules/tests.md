---
paths:
  - 'tests/**'
---

# Tests

## Don't swap app 'env' inside HTTP feature tests
Overriding the environment mid-test via `app()->instance('env', 'production')` (to exercise `App\Support\NotificationRecipients` or similar env-branching code) breaks `$this->post()`/`$this->get()` calls in the same test: Laravel's CSRF middleware (`PreventRequestForgery::runningUnitTests()`) only skips verification when `app()->environment() === 'testing'`, so swapping env to anything else causes a silent 419 and the request's side effects (mail, DB writes) never happen.

Test environment-dependent logic (e.g. `NotificationRecipients::all()`) as a plain unit test that calls the class directly after the env swap — never combine an env swap with an HTTP test helper call in the same test.
