# 14. VS Code Copilot Prompt Library

Use one prompt at a time. Keep `.github/copilot-instructions.md` enabled
as repository context.

## Prompt 0: Understand the project

> Read `.github/copilot-instructions.md` and all relevant documents
> under `/docs`. Do not write code yet. Summarize the architecture,
> domain boundaries, key invariants, and the first implementation
> milestone. Identify contradictions or missing decisions. Ask only
> essential clarification questions.

## Prompt 1: Scaffold the monorepo

> Implement Milestone 0 from `docs/13-DEVELOPMENT-ROADMAP.md`. Create
> the repository skeleton for React + TypeScript + Vite + Tailwind,
> Laravel API, Python FastAPI pairing engine, Docker Compose, and CI
> skeleton. Pin compatible dependency versions. Add health endpoints and
> basic README setup commands. Do not implement tournament features yet.
> Add smoke tests and report commands/results.

## Prompt 2: Database and Laravel foundation

> Read `docs/02-SYSTEM-ARCHITECTURE.md`, `docs/03-DATABASE-DESIGN.md`,
> and `docs/08-SECURITY.md`. Implement the Laravel database migrations
> and models for users, players, tournaments, tournament roles,
> tournament participants, rounds, pairing runs, pairings, result
> events, and audit logs. Add PostgreSQL constraints and tests for
> critical invariants. Do not implement pairings yet.

## Prompt 3: Tournament CRUD

> Implement tournament CRUD and lifecycle endpoints from
> `docs/04-API-CONTRACTS.md`. Use Form Requests, Policies, Resources,
> transactions, and API tests. Enforce tournament-scoped authorization.
> Keep all public IDs as UUIDs. Update OpenAPI documentation.

## Prompt 4: Player registration and TPN

> Implement canonical player management, tournament participant
> registration, CSV import preview, rating/title/federation snapshots,
> initial ordering, and TPN assignment according to the current FIDE
> C.04.2 version documented in `docs/06-FIDE-RULES-REGISTER.md`. Do not
> invent ordering rules. Add fixtures for late entries and
> participant-list corrections.

## Prompt 5: Pairing engine domain model

> Implement only the Python pairing engine's typed domain models,
> Pydantic API schema, ruleset registry, input normalization,
> historical-state derivation, and invariant validation. Read
> `docs/05-FIDE-DUTCH-PAIRING-SPECIFICATION.md`. Do not implement an
> approximate pairing algorithm. Add tests for malformed and
> contradictory history.

## Prompt 6: Dutch pairing algorithm research-to-code

> Before coding, map every applicable article and criterion from the
> current official FIDE C.04.3 Dutch System to a design note, domain
> function, and test fixture. Confirm exact rule wording from the
> official FIDE Handbook. Identify any ambiguity and stop for arbiter
> review. Then implement one algorithm phase at a time in the official
> prescribed order. Do not replace ordered criteria or alterations with
> weighted heuristics. Include candidate traces and deterministic replay
> tests.

## Prompt 7: Pairing service integration

> Implement Laravel queue orchestration for the Python pairing engine.
> Snapshot and hash normalized inputs, persist immutable pairing runs,
> call the internal service with authentication and timeouts, validate
> returned output, and store diagnostics. Add
> retry/idempotency/stale-state tests. Do not publish pairings
> automatically.

## Prompt 8: Pairing review and publication UI

> Implement the minimal arbiter pairing page from
> `docs/07-UI-UX-SPECIFICATION.md`. Show round, board list, bye row,
> engine/ruleset version, run status, diagnostics, and publication
> action. Require confirmation and server authorization. Handle stale
> data and all background job states. Add component and end-to-end
> tests.

## Prompt 9: Results and corrections

> Implement result entry, unplayed/forfeit categories, result history,
> and authorized correction workflow. Every correction requires a reason
> and creates an append-only event plus audit log. Use transactions and
> idempotency keys. Add tests for each result category and recalculation
> behavior.

## Prompt 10: Standings and tie-breaks

> Implement standings as a separate domain module. First read the
> current official FIDE Play-Off and Tie-Break Regulations and
> `docs/06-FIDE-RULES-REGISTER.md`. Implement only configured and fully
> specified tie-breaks. Pay particular attention to unplayed-round
> handling. Add independent fixtures and calculation-version metadata.
> Do not use float arithmetic.

## Prompt 11: Public portal

> Implement read-only public tournament pages for tournament details,
> published pairings, results, standings, and player cards. Ensure no
> draft pairings, private notes, or private player data can leak. Add
> authorization and response-shape tests.

## Prompt 12: Exports

> Implement CSV and PDF exports described in
> `docs/12-EXPORTS-AND-INTEROPERABILITY.md`. Protect against CSV formula
> injection, enforce export permissions, and ensure output is generated
> from a consistent snapshot. Do not implement TRF until the current
> official format has been reviewed and fixtures exist.

## Prompt 13: Security review

> Perform a security review against `docs/08-SECURITY.md`. Inspect
> authentication, authorization, CSRF/CORS, input validation, file
> import/export, service-to-service authentication, secret handling,
> logs, and public endpoints. Report findings by severity, then fix only
> confirmed issues and add regression tests.

## Prompt 14: Release readiness

> Evaluate the repository against every exit criterion in
> `docs/13-DEVELOPMENT-ROADMAP.md`. Run all tests, static checks,
> migrations, Docker builds, pairing reference fixtures, and security
> checks. Produce a release-readiness report listing passed checks,
> failures, known limitations, exact engine/ruleset versions, and any
> FIDE approval status. Do not claim approval unless documentary
> evidence exists.
