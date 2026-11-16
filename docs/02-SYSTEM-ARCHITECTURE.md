# 02. System Architecture

## 1. High-level architecture

``` mermaid
flowchart LR
    Browser[Organizer / Arbiter / Public Browser]
    UI[React + TypeScript UI]
    API[Laravel REST API]
    DB[(PostgreSQL)]
    Queue[Redis Queue]
    Worker[Laravel Queue Worker]
    Pair[Python FastAPI Pairing Engine]
    Files[Private Object / Local Storage]
    Browser --> UI
    UI --> API
    API --> DB
    API --> Queue
    Queue --> Worker
    Worker --> Pair
    Worker --> DB
    API --> Files
```

## 2. Ownership boundaries

### Frontend

-   Renders organizer, arbiter, and public screens.
-   Performs client-side usability validation only.
-   Calls Laravel REST endpoints.
-   Never contains authoritative pairing or standings logic.
-   Never connects directly to PostgreSQL, Redis, or the pairing
    service.

### Laravel application

Laravel is the system orchestrator and source of truth. It owns: -
Authentication and authorization. - Tournament lifecycle and state
transitions. - Player registration and participant snapshots. - Result
entry, correction, and approval. - Pairing run orchestration and
persistence. - Standings orchestration and export requests. - Audit
events and public visibility rules. - API contracts and database
transactions.

### Python pairing engine

-   Accepts a fully normalized pairing snapshot.
-   Validates the request schema and internal invariants.
-   Computes candidate pairings using the specified ruleset.
-   Returns pairing output plus structured diagnostics.
-   Does not read/write the application database.
-   Does not decide permissions or publish pairings.
-   Does not mutate tournament state.

The pairing service should be a pure calculation boundary. A later
optimization may use local caches, but cache contents must never change
the result.

## 3. Pairing workflow

``` mermaid
sequenceDiagram
    participant A as Arbiter
    participant UI as React UI
    participant L as Laravel API
    participant DB as PostgreSQL
    participant Q as Redis Queue
    participant P as Python Engine
    A->>UI: Generate round pairings
    UI->>L: POST pairing-runs
    L->>DB: Validate state and snapshot inputs
    L->>DB: Create immutable pairing-run record
    L->>Q: Dispatch calculation job
    Q->>P: Normalized snapshot + ruleset
    P-->>Q: Candidate pairs + diagnostics
    Q-->>L: Calculation result
    L->>DB: Persist output and validation status
    L-->>UI: Pairing run status/result
    A->>UI: Review and publish
    UI->>L: POST publish
    L->>DB: Transactionally publish pairings
```

## 4. Pairing run lifecycle

`queued -> running -> completed | failed | requires_arbiter_decision`

A completed run is not automatically published. Publication is a
separate authorized operation.

A pairing run must store: - tournament and round identifiers; - request
actor; - created/started/completed timestamps; - engine
name/version/build identifier; - FIDE ruleset identifier and effective
date; - normalized input snapshot and cryptographic hash; - output
snapshot and cryptographic hash; - diagnostics and validation report; -
status and failure code; - parent/superseded run reference, if
applicable.

Never overwrite an earlier run. A regenerated run creates a new record.

## 5. Transaction boundaries

Use database transactions for: - creating tournament plus initial
settings; - registering a player into a tournament and assigning TPN; -
publishing a pairing run into round pairings; - recording a result and
creating its audit event; - correcting a result and recalculating
affected projections; - closing a round.

Do not keep a database transaction open while waiting for the Python
service. Snapshot inputs and commit the queued run first. Persist the
response in a separate transaction after validating that the tournament
state has not changed incompatibly.

## 6. Concurrency and stale data

Every tournament and round has a version/updated timestamp. Pairing
generation captures a state version. Before publishing, Laravel
verifies: - the round is still in the expected state; - relevant
results/participant states have not changed; - the run belongs to the
current tournament/ruleset; - all hard invariants still pass.

If stale, reject publication and require a new pairing run. Use row
locks or optimistic version checks where appropriate.

## 7. Suggested code organization

### Laravel

``` text
backend/app/
├── Domain/
│   ├── Tournament/
│   ├── Participant/
│   ├── Pairing/
│   ├── Result/
│   └── Standings/
├── Http/Controllers/Api/V1/
├── Http/Requests/
├── Http/Resources/
├── Policies/
├── Jobs/
├── Services/
└── Models/
```

### Python

``` text
pairing-engine/app/
├── api/
├── domain/
│   ├── models.py
│   ├── rules/
│   ├── dutch/
│   └── validation/
├── application/
├── infrastructure/
└── tests/
```

Keep the Dutch algorithm independent of FastAPI so it can be tested as a
deterministic library.
