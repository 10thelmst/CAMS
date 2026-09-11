# CAMS Final Version

## Final Requirement

The system should be built around a reusable client model and a reusable OFW model.

- A client record should be reusable across multiple cases and transactions.
- An OFW record should also be reusable and not locked to just one client.
- The relationship between client and OFW should be many-to-many.
- For each client, we only need to collect the following details:
  - Subject
  - Verbatim
  - Action Taken

## Core Idea

We are not creating one-off client entries every time.
Instead, the system should allow the same client and the same OFW to be reused in multiple records without duplication.

A client can be linked to many OFWs, and an OFW can be linked to many clients.

## Data Design

### 1. Clients
A reusable master record for each client.

Fields:
- id
- client_name
- contact_number
- email
- address
- created_at
- updated_at

### 2. OFWs
A reusable master record for each OFW.

Fields:
- id
- full_name
- country
- mobile_number
- email
- status
- created_at
- updated_at

### 3. Client_OFW Relationship
Many-to-many relationship table.

Fields:
- id
- client_id
- ofw_id
- relationship_type
- notes
- created_at

This allows:
- one client to have multiple OFWs
- one OFW to be connected to multiple clients

### 4. Client Case Record
Each client should have a collection of case records.

Fields:
- id
- client_id
- subject
- verbatim
- action_taken
- created_at
- updated_at

This is the main collection table for every client.

## Business Rule

For each client, the system will only collect:

- Subject
- Verbatim
- Action Taken

No extra unnecessary fields should be forced for every entry.
The system should stay simple and reusable.

## Example

Client: Maria Santos
OFW: Juan dela Cruz

Relationship:
- Maria Santos is linked to Juan dela Cruz
- Juan dela Cruz may also be linked to other clients in the future

Case record for Maria Santos:
- Subject: Follow-up on complaint
- Verbatim: "I was not informed about the status of my case."
- Action Taken: Coordinated with the office and provided update to the client.

## Final System Goal

The final system should behave like this:

1. Client is reusable
2. OFW is reusable
3. Client and OFW have a many-to-many relationship
4. For each client, the system only stores:
   - Subject
   - Verbatim
   - Action Taken

## Summary

This is the final version of the requirement:

- Reusable Client
- Reusable OFW
- Many-to-many relationship between Client and OFW
- Per client record collection of:
  - Subject
  - Verbatim
  - Action Taken

Everything else should be kept minimal and focused on this structure.