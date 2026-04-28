# WBS-Generator Build Notes

This file tracks what has been built from `UNIFIED_WBS_PROJECT_SPEC.md`.

## Completed

- Step 1: Bootstrapped a fresh Laravel 13 application in `WBS-Generator`
- Step 1: Added Breeze authentication scaffolding with login and reset password flow
- Step 1: Added Laravel Excel dependency
- Step 1: Renamed the application to `WBS-Generator`
- Step 1: Prepared MySQL-focused example environment defaults for Laragon
- Step 4 foundation: Disabled open self-registration so user creation stays owner-driven
- Step 5 foundation: Added protected route structure for all major v1 modules
- Step 2: Implemented the core v1 schema plus recommended support tables
- Step 3: Seeded owner, PM, demo project, working hours, holiday, and task master sample data
- Step 4: Added active-user login protection, role middleware, and project/task/export policies
- Step 5: Built the shared application shell, dashboard, and first CRUD screens
- Local run process: Added reusable PowerShell scripts for setup, app run, and dev run
- Step 9: Built the WBS hierarchy builder with create, edit, delete, and automatic renumbering
- Step 10: Built the Assigned List screen with assignment create/edit, FS dependency selection, PM/leader sync, and schedule generation
- Step 11: Implemented the scheduling engine with project dates, PIC availability, dependency sequencing, holidays, working hours, and leave handling
- Step 12: Built the Excel-style schedule grid with monthly summary, daily summary, today panel, editable actual hours, and progress recalculation
- Step 13: Added export history, xlsx export generation, and download flow
- Step 13 refinement: Rebuilt the xlsx export to follow the provided sample workbook format with a structured `WBS` sheet, formulas, platform/task detail columns, dynamic per-day allocation, color-coded today and holiday columns, and a matching `Holidays` sheet
- Step 13 refinement: Export headers now follow the Japanese template labels, use a three-line date header (`month`, `date`, `day`), leave `Plan Rest Hours` blank for manual editing, and color planned-day cells yellow / actual-day cells green when values exist
- Step 14: Added notification history, manual generation actions, and scheduled notification commands
- Step 15+: Added settings preferences, richer seeded demo data, and audit logging across core master-data and delivery workflows
- UI refinement: Moved WBS item creation and assignment creation into dedicated form pages, kept list screens focused on tables/actions, and added a collapsible side menu shell
- Verification: Added a feature smoke test that exercises the new step-15+ screens end to end

## In Progress

- Step 16+: Deeper behavior refinement for advanced scheduling rules, richer export formatting, and notification delivery channels

## Next Up

- Advanced schedule interactions and guardrails
- Export template polish and formula mapping
- Broader feature coverage tests for assignment, schedule, export, and notification actions
- Laragon virtual-host and MySQL-first local run option

## Notes

- `.env.example` targets Laragon MySQL for deployment-style setup.
- `.env` currently uses SQLite so the app can run immediately while local PDO authentication with this MySQL instance is still unresolved.
- Seeded login:
  - `owner@wbs-generator.test` / `password`
  - `pm@wbs-generator.test` / `password`
- Verified locally:
  - `php artisan migrate:fresh --seed`
  - `npm run build`
  - `php artisan test`
- Additional verification:
  - `php artisan test --filter=StepFifteenScreensTest`
- Recent fix pass tightened schedule date generation, manager-only progress controls, WBS subtree protection, and clearer surfaced form errors.
- Export format now follows the provided template workbook more closely instead of the earlier plain table-based workbook.
- The generated workbook is editable for progress entry, but round-tripping edited xlsx data back into Laravel still requires a dedicated import/save flow.
