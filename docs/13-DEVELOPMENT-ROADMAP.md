# 13. Development Roadmap

Build in vertical slices. Do not ask Copilot to generate the entire
application at once.

## Milestone 0: Repository foundation

Deliver: - monorepo folders; - root README and Copilot instructions; -
formatter/linter configs; - Docker Compose; - CI skeleton; - environment
template.

Exit criteria: - services build; - health checks work; - no secrets
committed.

## Milestone 1: Identity and authorization

Deliver: - user authentication; - tournament-scoped roles; - policies
and tests; - audit-log foundation.

Exit criteria: - protected endpoints reject unauthorized access; -
public endpoints expose only public data.

## Milestone 2: Tournament and participant management

Deliver: - tournament CRUD and lifecycle; - player records; -
participant registration; - TPN assignment and rating snapshots; - CSV
import preview/validation; - participant lock.

Exit criteria: - database constraints and tests protect unique TPN and
participant identity.

## Milestone 3: Pairing engine foundation

Deliver: - typed Python domain models; - complete input schema; -
ruleset registry; - historical state derivation; - invariant
validator; - fixture harness; - deterministic engine shell.

Exit criteria: - no pairing algorithm is labelled Dutch until full
criteria and tests are implemented.

## Milestone 4: FIDE Dutch implementation

Deliver: - scoregroups/brackets; - candidate construction; - C1-C21
criteria; - ordered alterations; - colour allocation; - PAB handling; -
candidate trace; - reference pairing suite.

Exit criteria: - every applicable rule maps to code and test; -
independent arbiter review; - deterministic replay verified.

## Milestone 5: Round workflow and publication

Deliver: - queue orchestration; - pairing-run persistence; - diagnostics
UI; - publish workflow; - stale state protection; - immutable history.

Exit criteria: - cannot publish invalid/stale pairings; - published
pairing history is preserved.

## Milestone 6: Results and standings

Deliver: - result entry; - forfeit and unplayed-round semantics; -
correction workflow; - audit events; - standings and tie-break engine.

Exit criteria: - selected tie-breaks validated against current FIDE
regulations and independent fixtures.

## Milestone 7: Public portal and exports

Deliver: - public tournament page; - pairings, standings, player
cards; - CSV/PDF/XLSX; - TRF only after specification review.

Exit criteria: - public/private data boundary tested; - exports match
displayed data.

## Milestone 8: Release hardening

Deliver: - security review; - load/performance tests; - backup/restore
drill; - operations docs; - accessibility checks; - release notes; -
independent pairing review report.

Exit criteria: - known limitations disclosed; - no unsupported FIDE
approval/compliance claims.

## Working agreement

At the start of every milestone, Copilot must: 1. inspect current
repository state; 2. list tasks and dependencies; 3. identify tests; 4.
implement only the current milestone; 5. run checks; 6. summarize
changed files and unresolved risks.
