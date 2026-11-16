# 09. Testing Strategy

## 1. Testing pyramid

1.  Unit tests: domain rules, validators, tie-break calculations,
    pairing criteria.
2.  Property-based tests: pairing invariants across generated
    tournaments.
3.  Contract tests: Laravel \<-\> Python and frontend \<-\> API schemas.
4.  Integration tests: database transactions, queues, imports, exports.
5.  End-to-end tests: organizer workflows.
6.  Reference tests: compare Dutch pairings with trusted independently
    generated fixtures.

## 2. Pairing-engine test requirements

Every pairing fixture includes: - ruleset version and FIDE article
references; - complete input history; - expected pairing result; -
expected colour allocation; - expected bye assignment, if any; -
expected candidate/criterion diagnostics where practical; - provenance
and reviewer.

Do not create expected pairings by running the implementation under
test. Fixtures must come from an independent trusted source or a
manually verified arbiter case.

### Invariants to property-test

-   no participant appears more than once in a round;
-   no player is paired with themselves;
-   no repeated opponent;
-   bye has no opponent and no colour;
-   at most one PAB per round;
-   no ineligible participant receives a PAB;
-   every eligible participant is accounted for;
-   same normalized input/version yields identical output;
-   output order is deterministic;
-   score and colour histories reconcile with round records.

## 3. Edge-case matrix

  -----------------------------------------------------------------------
  Area                                Cases
  ----------------------------------- -----------------------------------
  Field size                          1, 2, odd, even, large field

  Scoregroups                         one group, many groups, split
                                      groups

  Opponents                           all new, repeat constraints,
                                      constrained bracket

  Colours                             no history, absolute, strong, mild,
                                      consecutive same colour

  Bye                                 first PAB, prior PAB, full-point
                                      forfeit, requested bye

  Attendance                          withdrawal, late entry, inactive,
                                      suspended

  Results                             played, draw, forfeit, double
                                      forfeit, correction

  Timing                              adjourned, delayed result, round
                                      lock

  Final round                         topscorer rules and applicable
                                      exceptions

  Concurrency                         result changes during generation,
                                      duplicate requests

  Recovery                            worker crash, timeout, retry, stale
                                      pairing run
  -----------------------------------------------------------------------

## 4. Laravel tests

-   Policies for every role/action.
-   Request validation.
-   Transaction rollback on failure.
-   TPN uniqueness and ordering.
-   Pairing publication invariants.
-   Result correction reason and audit event.
-   Idempotency behavior.
-   Public endpoint data filtering.
-   Queue retry and failure handling.
-   Export authorization and content correctness.

Use a real PostgreSQL test service for constraint tests. SQLite-only
tests do not prove PostgreSQL behavior.

## 5. Frontend tests

-   Component tests for forms, tables, dialogs, and status states.
-   API error rendering.
-   Permission-aware action visibility (server remains authoritative).
-   Keyboard navigation in result entry.
-   Pairing diagnostics and publication confirmation.
-   Responsive public standings.
-   Accessibility checks.

## 6. CI quality gates

A pull request should run: - PHP formatting and static analysis; -
Laravel unit/integration tests; - Python formatting, lint, type checks,
and pytest; - TypeScript type check, lint, and frontend tests; -
OpenAPI/schema validation; - dependency/security scan; - Docker image
build; - migration smoke test.

Do not merge if pairing invariants or reference pairing tests fail.

## 7. Release evidence

Each release should publish: - software version and commit; - engine
version; - supported FIDE ruleset version; - rule-to-code traceability
status; - reference fixture test report; - known limitations; -
independent reviewer sign-off status.

Never equate code coverage percentage with pairing correctness.
