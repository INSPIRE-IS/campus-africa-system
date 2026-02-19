# TODOS

## Pending
- Add cash-student sponsor document injection to registration flow (RegisterController) to match ApplicationController step_4 logic around other_sponsor_id and other_sponsor_payslip (year >= 2026).
- Decide the exact registration step(s) where the cash sponsor docs should be created and ensure they appear in the same way as the online application flow.
- Add/extend tests to cover the cash-student sponsor docs requirement once the registration flow is updated.

## Testing
- Consider isolating tests to a dedicated test database (.env.testing or sqlite) to avoid touching real data when running PHPUnit.
