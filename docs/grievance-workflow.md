# Grievance Workflow

This document follows a grievance from submission through routing, investigation, resolution, and closure.

## 0. Development login accounts and first owner

The fixed development accounts use the password `password` and can authenticate with **username, email, or phone**:

| Role | Username | Email | Phone | First workflow responsibility |
| --- | --- | --- | --- | --- |
| Director | `director` | `director@grms.com` | `+266 58000003` | Receives cases that cannot be automatically assigned to a division; can triage and allocate them. |
| Division Director | `division.director` | `division.director@grms.com` | `+266 58000004` | Receives cases automatically allocated to division `NR`; allocates them to a section. |
| Section Manager | `section.manager` | `section.manager@grms.com` | `+266 58000005` | Receives cases allocated to section `Berea Section`; assigns an investigating officer. |
| Helpdesk Officer | `helpdesk.officer` | `helpdesk@grms.com` | `+266 58000006` | Registers staff-mediated cases and investigates cases after assignment. |

After changing role permissions or seeded account scopes, run the User Management database seeder so Spatie role permissions and fixed account assignments are refreshed.

**First owner rule:** after submission, the case belongs to the responsible Division Director when category/district routing resolves a division. It belongs to the Director only when routing cannot resolve a division. It is not sent to all roles. The investigating officer receives a notification only after the Section Manager assigns that officer.

## 1. Submission channels

All intake paths are normalised into `GrievanceIntakeData`. The registration service is the single application entry point that creates a grievance row.

| Channel | Code | Intake behavior |
| --- | --- | --- |
| Public web form | `web` | `POST /grievances/add`; validates the CAPTCHA, accepts attachments, creates the reference, then completes classification/routing before returning so the responsible manager notification is available immediately. |
| Mobile API | `mobile_app` | Supported by `GrievanceIntakeData::fromMobileApi`; the authenticated API resource is available under `/api/v1/grievances`. |
| SMS | `sms` | Parses the message and optional category/district codes; free text can be classified by AI. |
| USSD | `ussd` | Captures category, district, description, and phone through the USSD flow. |
| WhatsApp | `whatsapp` | Uses the same free-text shape as SMS. |
| Staff-mediated intake | `helpdesk`, `clo`, `rd_office`, `box`, `grc`, `community_forum`, `chief`, `social_media`, or another configured channel code | A staff member enters the grievance through the authenticated form; the staff user is recorded as `registered_by`. |

The channel code is resolved against the active `grievance_channels` table. It is configuration-driven rather than a hard-coded PHP enum.

## 2. Immediate registration

The quick registration path performs one database transaction:

1. Allocates a reference number such as `GRM-2026-000001`.
2. Stores the description, complainant/contact data, location, source channel, metadata, and optional attachments.
3. Creates an initial status-history record from no status to `submitted`.
4. Preserves anonymous submissions without contact details.
5. Returns the reference number to the web client when the public form is used.

Public web submissions use `submitQuick()` and run `SubmitGrievanceJob` synchronously after the row is created. This guarantees that classification, routing, acknowledgement, and the responsible-manager notification exist when the response returns. SMS, USSD, and the staff registration path use the full `submit()` flow and process classification/routing in the request path.

## 3. Classification and automatic routing

The queued job calls `processAsync()` for public web submissions. It resolves the category in this order:

1. Use the category already supplied by the channel.
2. Otherwise ask the configured AI classifier to classify the description.
3. If AI is disabled, fails, has low confidence, or returns an invalid category, use the active `other` category or the first active category.

Routing then resolves the division in this order:

1. Use an explicitly supplied division.
2. Otherwise use the category’s configured `division_id`.
3. Otherwise map the district code to a division using the configured district mapping.

The section is taken from an explicitly supplied section or the category’s `default_section_id`. When a division is found, the grievance changes from `submitted` to `allocated_division`, receives SLA due dates, gets an assignment record, and sends a database notification to Division Directors in that division. If no division can be resolved, it remains in the Director triage queue.

An acknowledgement communication is queued to the complainant’s phone or email. Anonymous submissions are recorded as not applicable for delivery but remain trackable by reference number.

## 4. Role queues and actions

| Queue | Eligible role | Selection rule | Main action |
| --- | --- | --- | --- |
| Director triage | Director, Super Admin | `submitted` or `reallocation_required` with no division | Allocate to a division, reject, or close |
| Division queue | Division Director, Super Admin | `allocated_division` for the user’s division | Allocate to a section |
| Section queue | Section Manager, Super Admin | `allocated_section` for the user’s section | Assign an officer or reject the allocation |

All queue queries are FIFO and return the oldest eligible grievances first. Role middleware protects the routes, and the grievance policy checks role plus division/section ownership before an action is allowed.

The operational sequence is:

1. Director triage allocates the case to a division.
2. The Division Director selects a section in the edit workflow or uses the allocation action. This changes the case to `allocated_section`, appends an assignment record, and notifies Section Managers assigned to that section.
3. The Section Manager assigns an investigating officer.
4. The officer investigates and moves the case through active work.
5. The case is resolved, then closed, or rejected with a recorded reason.

If a Section Manager rejects an allocation, the division, section, and officer are cleared, the status becomes `reallocation_required`, and Directors receive a database notification so the case can be routed again.

## 5. Status lifecycle

The legal transitions are defined in `GrievanceStatus`:

```text
submitted -> acknowledged -> allocated_division -> allocated_section
           -> assigned_officer -> in_progress -> resolved -> closed
```

Other supported paths include escalation from `in_progress`, return from `escalated` to `in_progress`, rejection from eligible stages, and reopening from `resolved` or `closed`. Each transition is recorded in grievance status history. Allocation and assignment actions are also recorded in `grievance_assignments`.

## 6. Communications and notifications

The system queues:

- An acknowledgement after registration.
- A status update when a grievance is rejected, resolved, or closed, using SMS when a phone number exists and email otherwise.
- Database notifications when a grievance is automatically or manually allocated, assigned, or returned for reallocation.

The authenticated header shows a bell icon with the unread count for grievance notification classes and links to `/notifications`. The **Pending Grievances** count is in the navigation menu directly after User Management and links each role to its actionable queue. A Division Director is notified at automatic division allocation; an investigating officer is notified only after a Section Manager assigns that officer.

## 7. Main implementation locations

- `Modules/Grievance/routes/web.php`: public, authenticated, and role-protected routes.
- `Modules/Grievance/DataTransferObjects/GrievanceIntakeData.php`: channel-specific intake factories.
- `Modules/Grievance/app/Services/GrievanceRegistrationService.php`: registration, classification, automatic routing, SLA dates, and acknowledgement.
- `Modules/Grievance/app/Jobs/SubmitGrievanceJob.php`: asynchronous public-web processing.
- `Modules/Grievance/app/Services/GrievanceRoutingService.php`: role actions, status changes, assignment records, and notifications.
- `Modules/Grievance/app/Repositories/GrievanceRepository.php`: queue filters and FIFO ordering.
- `Modules/Grievance/app/Enums/GrievanceStatus.php`: allowed status transitions.
- `app/Http/Middleware/HandleInertiaRequests.php`: shared notification and pending-queue counts.
- `resources/js/components/app-top-menu.tsx`: Pending Grievances menu item.
- `resources/js/components/app-top-navigation.tsx`: notification bell.
