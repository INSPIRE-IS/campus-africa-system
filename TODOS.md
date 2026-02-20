# TODOS

## Pending
- Add cash-student sponsor document injection to registration flow (RegisterController) to match ApplicationController step_4 logic around other_sponsor_id and other_sponsor_payslip (year >= 2026).
- Decide the exact registration step(s) where the cash sponsor docs should be created and ensure they appear in the same way as the online application flow.
- Add/extend tests to cover the cash-student sponsor docs requirement once the registration flow is updated.
- Temp lease status mapping mismatch: createTempLeasePDF() and resendTempLease() set rob_51_temp_lease_approve to 0 (Rejected) even though 0=Rejected, 1=Approved, 2=Pending, 3=Not Set. This shows "Rejected" immediately after generation and may skip "Pending". Compare with full lease generation flow to confirm intended transition (likely set Pending after student signs so admin can review), then align temp-lease transitions accordingly.
- Temp lease role gating not enforced: `check_user_status()` returns `role_allow_generate_temp_lease = 1` regardless of the logged-in user, and `/generate-temp-lease` does not validate role or the temp-lease window server-side (may be no matching role in DB). Decide intended permission + window enforcement and implement.
- Investigate `email_check()` and `passport_check()` in HomeController: where they are called and what their return values drive (validation/flow/UI).

## Testing
- Consider isolating tests to a dedicated test database (.env.testing or sqlite) to avoid touching real data when running PHPUnit.

## Done
- SendStudentVerifyMailByAdminMail: updated to use the provided year when fetching StudentsInfo (verify mail now uses StudentsInfo for the job year).
- DocumentsController::view_document should guard when UserFiles::find($id) returns null; trace call sites and decide how to handle missing file (404 vs redirect/message).
