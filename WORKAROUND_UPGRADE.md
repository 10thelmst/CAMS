# CAMS Workaround Upgrade Blueprint
**Date:** September 8, 2026  
**Purpose:** Convert the current app into a simple CRM-style case management system using the same core pattern as Oracle CRM On Demand: clients, cases, and case logs.

---

## 1) Decision
We do not need a full Oracle CRM system. We only need the core CRM structure:

- Client master record
- Case / service request record
- Case activity log / case history

This fits CAMS exactly because the system is already built around:

- client intake
- complaint or concern filing
- case tracking
- status updates
- action history
- assignment and handling workflow

---

## 2) Core Data Model to Use

### A. Clients table
This is the master profile of the person being assisted.

Required fields:
- id
- client_number (optional unique code)
- first_name
- middle_name
- last_name
- suffix
- contact_no
- email
- sex
- dob
- address1
- region_code
- province_code
- city_code
- barangay_code
- is_ofw
- created_at
- updated_at

Notes:
- Use normalized name fields, not a single `full_name` field.
- Keep a display name at runtime if needed, but do not store it as the source of truth.
- `suffix` should be a dropdown with values like `Jr.`, `Jra.`, `Sr.`, `II`, `III`, `IV`.

### B. Cases table
This is the actual service request / ticket / concern.

Required fields:
- id
- ticket_number
- client_id
- source_channel
- subject
- category
- description
- status
- priority (optional)
- current_program
- assigned_to
- created_by
- created_at
- updated_at
- closed_at

Notes:
- The current `concerns` table can remain as the case table, but the naming should be standardized to `cases` in the long run.
- `source_channel` replaces the old transaction type wording and is better for CRM style tracking.
- Keep `current_program` or map to department/unit.

### C. Case logs table
This is the CRM-style activity timeline.

Required fields:
- id
- case_id
- actor_id
- action_type
- old_status
- new_status
- remarks
- created_at

Suggested values for `action_type`:
- case_created
- status_changed
- assigned
- note_added
- action_taken
- follow_up
- resolved
- closed

Notes:
- This should replace or supplement the current `status_history` and `action_history` logic.
- This gives us a real event trail similar to a CRM case log.

### D. OFW information table
This should remain separate and related to the client or case.

Recommended fields:
- id
- client_id
- ofw_first_name
- ofw_middle_name
- ofw_last_name
- ofw_suffix
- relationship_to_client
- country
- employment_type
- created_at

Notes:
- This keeps the OFW profile separate and normalized.
- Do not combine OFW name into one string field.

---

## 3) What to Change in the Current Project

### Change 1: Treat the app as CRM-style case management
The app should behave like this:

- Step 1 = Search existing client
- Step 2 = Client personal profile
- Step 3 = OFW profile (if applicable)
- Step 4 = Case details and intake

This matches the CRM pattern of:

- master record = client
- activity record = case
- history record = case logs

### Change 2: Standardize naming
The current project mixes terms such as:
- clients
- concerns
- cases
- status_history
- action_history
- transaction type

We should settle on a consistent naming pattern:

- `clients` = people
- `cases` = service requests
- `case_logs` = all updates and history

If we keep backward compatibility, we can still leave old tables temporarily, but the UI and logic should work with the new naming internally.

### Change 3: Normalize the name structure
Use this pattern everywhere:
- last_name
- first_name
- middle_name
- suffix

Not:
- full_name
- single name column

This should apply to:
- clients
- OFW profile
- search logic
- display logic
- export/report logic

### Change 4: Use CRM-style case lifecycle
Add lifecycle states like:
- Open
- Assigned
- In Progress
- Pending
- For Action
- Resolved
- Closed

Each case should generate a `case_log` entry whenever:
- case is created
- status changes
- assigned person changes
- note/action is added
- case is resolved or closed

### Change 5: Remove the old transaction-type idea from the main flow
The earlier request was correct: do not push transaction type too early.

Recommended order:
- Step 2 = client information
- Step 3 = OFW information
- Step 4 = case details including source channel, subject, category, program-in-charge, description

This keeps the real case data at the end instead of mixing it into personal profile details.

### Change 6: Keep search and duplicate prevention at the top
The client search card should still be Step 1, because in CRM terms:

- we first check whether the client already exists
- then we either attach the case to an existing client or create a new client record

This is a critical business process and should stay in the flow.

### Change 7: Keep case records independent from person records
One client can have many cases.

This is the key CRM rule:
- one client -> many cases
- each case has its own status, notes, assignments, and history

This is the correct architecture for CAMS.

### Change 8: Simplify the database naming for the current phase
For the shortest path, keep the current tables but align them to CRM logic:

- `clients` stays as-is
- `concerns` can be treated as `cases`
- `status_history` and `action_history` should be folded into `case_logs`
- `ofw_information` stays as a related table

This keeps the app working while making the structure easier to understand and extend.

---

## 4) UI / Form Design Changes to Apply

### Step 2: Client Name Block
Use the exact same layout pattern across all name blocks:
- Last Name
- First Name
- Middle Name
- Suffix

Then keep the second line as:
- Contact Number
- Gender
- Email Address
- Date of Birth

### Step 3: OFW Name Block
Use the same input sizing and pattern as Step 2.

This gives the app a consistent “CRM master record” feel instead of a mixed, uneven form layout.

### Step 4: Case Details Block
This section should hold:
- source channel
- concern subject
- category
- program-in-charge
- detailed description
- initial action taken

This is the actual case intake segment.

---

## 5) Recommended Minimum Upgrade Scope

### Must-have changes
1. Keep `clients` as master profile record
2. Keep one case per intake record
3. Add `case_logs` for all event history
4. Standardize the 3NF name structure
5. Keep OFW details separate and normalized
6. Move source channel to the case section
7. Update search and duplicate-check logic
8. Add proper case status lifecycle

### Nice-to-have changes
1. Add priority field
2. Add notes timeline UI
3. Add assignment workflow
4. Add closed-date tracking
5. Add case filters by status, program, and date
6. Add export/report generation

---

## 6) Final Recommendation
The project should not try to become a full Oracle CRM product. It should adopt the Oracle CRM idea at the data and process level:

- Clients = master records
- Cases = service intake records
- Case logs = timeline/history

This is the right architecture for CAMS and matches the project’s actual business need.

If we implement this cleanly, the app becomes easier to extend, easier to debug, and much closer to a real CRM-style case management system without adding unnecessary complexity.
