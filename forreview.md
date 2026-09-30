# Review: `Employee/create_client2.php`

## Current Assessment

The page currently combines PACD intake, full client registration, OFW profiling, case filing, and action logging. Based on the agreed process, PACD should capture only what is needed to identify the client, record the concern, and route the client to the appropriate service window. The receiving program-in-charge can collect detailed case information later.

## Keep or Add for PACD Intake

- Search for an existing client to reduce duplicate registrations.
- Client name and a temporary queue or ticket number.
- Preferred contact method and contact number when follow-up is needed.
- Whether the visitor is the OFW or a representative. If a representative, capture the OFW's name and the relationship when relevant.
- Intake channel, requested service or concern category, and the client's concern in their own words (verbatim).
- An urgent or immediate-safety flag.
- Referred program or service window, with the PACD staff member and intake/referral time recorded automatically.
- A short privacy notice explaining why information is collected and which program or staff may receive it.

## Make Optional or Move to Program-in-Charge

- Date of birth, sex, email, and detailed address fields.
- Employment type and additional OFW profile information.
- Full case narrative and supporting documents.

Keep a short subject and category for routing, but label the main text area **Client's concern (verbatim)**. Keep any staff summary or assessment in a separate field. A referral can be recorded as the initial action instead of requiring a separate narrative.

## Issues to Fix

1. **Existing-client submission can be blocked by the province requirement.** Selecting a client fills the name and contact fields, but does not populate the province. The browser validation and server validation still require a province even when an existing client ID was selected. Skip new-client-only required fields for existing clients, or load the existing profile values before validation.
2. **OFW information is inserted on every case submission.** The handler attempts to create an `ofw_information` row for each submission, including cases for existing clients and clients who are not the OFW. Save or update OFW details only when applicable, and avoid creating duplicate profile rows for each new concern.


we need to know about the first touch and last touch for the day.

I want also to determine if the case if for follow up like for issuing of cheque or verification.

make it sure that also we can add private notes.


IS OFW AND IS BENEFECIARY

add additional details for this

add to connect to make transmittal.
add a button. submitted for evaluation check preparation.