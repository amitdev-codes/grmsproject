# Grievance Workflow

The default grievance workflow is configurable in the workflow-step administration page. The seeded sequence is:

| Step | Responsibility | Action |
| --- | --- | --- |
| 1. Intake and informal resolution | RD Officer | Record the informal outcome; resolve when successful or return to formal submission when unresolved. |
| 2. Formal submission | Area-assigned Helpdesk Officer | Review and complete formal case registration. |
| 3. Registration, screening and allocation | Responsible Manager | Confirm severity and repeat-case information, select a division and section, and assign an Investigator. |
| 4. Acknowledgement | Assigned Investigator | Acknowledge receipt and start the investigation. |
| 5. Investigation and resolution proposal | Assigned Investigator | Record findings and evidence, submit a resolution draft, and propose corrective action. |
| 6. Decision and approval | GRC Chair | Approve the Investigator's resolution draft or escalate an unresolved case. |
| 7. Notification, closure and satisfaction | Contractor CLO | Communicate and verify the outcome, then record complainant acceptance and satisfaction. |
| 8. Escalation and external review | RD E&S Unit | Record an external referral for unresolved cases. |
| 9. Performance monitoring and reporting | Executive | Record monitoring notes and close the workflow for reporting. |

## Geographic Helpdesk routing

The grievance stores its district, routed division and section. When it enters the formal-submission step, the system selects one active Helpdesk Officer and sets that user's ID as the assigned officer. The matching order is:

1. Same district.
2. Same section.
3. Same division.
4. An officer with no geographic scope.
5. Any remaining active Helpdesk Officer as an operational fallback.

Among officers at the same matching level, the system prefers the person with the fewest active assigned grievances, then uses user ID for a stable tie-break. Only the selected officer receives the action-required notification and can perform the formal review. The same owner is used in the pending queue and the grievance's displayed workflow recipients. If there are no active Helpdesk Officers, the transition is rejected with an actionable error instead of leaving the case unassigned.

The seeded fixed Helpdesk account is scoped to Berea district, Northern Region division and Berea Section. Additional seeded staff accounts receive their configured geographic scope from `UserSeeder`; production accounts should likewise have appropriate district, division and section assignments.

## Investigator assignment and resolution

At screening/allocation, only active users with the Investigator role are eligible. The Responsible Manager selects an Investigator within the selected division/section or an Investigator without an assigned area. The assignment is persisted on the grievance. The acknowledgement and investigation steps are restricted to that assigned Investigator, not every user who holds the role.

The Investigator submits the resolution as a draft. This does not resolve or close the grievance. The GRC Chair's decision-approval action requires that draft, stamps its approver and approval time, and advances the case to Contractor CLO follow-up. The CLO records acceptance and satisfaction; an unaccepted outcome returns to investigation. The final monitoring/reporting step records notes and closes the workflow. AI does not classify, route, investigate, or decide individual grievances; its use is limited to requested summaries of aggregate dashboard/report data.

## Registration and runtime

All intake channels use `GrievanceIntakeData` and the grievance registration service. The selected category (or the human-review “Other” category when none is selected) and district determine the division/section routing. A newly registered grievance begins at the first active configured workflow step. The currently configured step, role or explicit approver, required permission, assignment and organization scope determine who can act. Workflow history, assignment records and database notifications preserve each handoff.

Rejection routing follows each step's configured rejection action and target. Returning to formal submission selects a new area-matched Helpdesk Officer. Returning to screening clears the Investigator assignment so the manager can allocate the case again.

After changing seeded roles, permissions, fixed account scopes or default workflow steps, rerun the appropriate User Management and Grievance database seeders.

## Main implementation locations

- `Modules/Grievance/app/Services/GrievanceRegistrationService.php`: intake, geographic routing and first-step setup.
- `Modules/Grievance/app/Services/HelpdeskOfficerAssignmentService.php`: active Helpdesk selection and workload-aware geographic matching.
- `Modules/Grievance/app/Services/GrievanceWorkflowService.php`: workflow transitions, authorization, Investigator resolution drafts and notifications.
- `Modules/Grievance/app/Services/PendingGrievanceQueueService.php`: actionable queue visibility and ownership filtering.
- `Modules/Grievance/database/seeders/GrievanceWorkflowSeeder.php`: default nine-step workflow.
- `Modules/UserManagement/database/seeders/UserSeeder.php`: seeded staff roles and geographic scopes.
