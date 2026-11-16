# 04. API Contracts

Base path: `/api/v1`. JSON request/response bodies. UUID resource IDs.
All timestamps are ISO 8601 UTC unless explicitly documented as a
tournament-local date/time.

## 1. Conventions

-   Authentication: secure session or bearer-token strategy; choose one
    and document CSRF behavior.
-   Authorization: every protected endpoint checks tournament-scoped
    policy server-side.
-   Pagination: `data`, `meta`, `links`.
-   Errors use a stable envelope:

``` json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The request could not be processed.",
    "details": [{"field": "round_number", "message": "Must be within the tournament range."}],
    "request_id": "uuid"
  }
}
```

-   Use idempotency keys for operations that may be retried, especially
    imports, result submissions, and pairing generation.
-   Never expose stack traces or internal service URLs.

## 2. Tournament endpoints

  -------------------------------------------------------------------------------------------------
  Method                  Path                                              Purpose
  ----------------------- ------------------------------------------------- -----------------------
  POST                    `/tournaments`                                    Create draft tournament

  GET                     `/tournaments`                                    List tournaments
                                                                            visible to user

  GET                     `/tournaments/{tournamentId}`                     Tournament detail

  PATCH                   `/tournaments/{tournamentId}`                     Update permitted
                                                                            settings

  POST                    `/tournaments/{tournamentId}/open-registration`   Open registration

  POST                    `/tournaments/{tournamentId}/lock-participants`   Lock initial order

  POST                    `/tournaments/{tournamentId}/close`               Complete tournament
  -------------------------------------------------------------------------------------------------

Create example:

``` json
{
  "name": "OpenSwiss Test Open",
  "timezone": "Asia/Jakarta",
  "start_date": "2026-12-01",
  "end_date": "2026-12-02",
  "round_count": 7,
  "venue": "Semarang",
  "pairing_system": "fide_dutch",
  "ruleset_version": "fide-dutch-2026-02",
  "scoring_config": {"win": 2, "draw": 1, "loss": 0},
  "tie_break_config": ["buchholz", "direct_encounter"],
  "is_public": false
}
```

The example scoring uses half-point units. Do not assume a tournament's
point schedule without recording its regulations.

## 3. Player and participant endpoints

  -------------------------------------------------------------------------------------------------------------
  Method                  Path                                                          Purpose
  ----------------------- ------------------------------------------------------------- -----------------------
  POST                    `/players`                                                    Create canonical player

  GET                     `/players`                                                    Search canonical
                                                                                        players

  GET                     `/players/{playerId}`                                         Player detail

  POST                    `/tournaments/{id}/participants`                              Add participant

  POST                    `/tournaments/{id}/participants/import`                       Import CSV

  GET                     `/tournaments/{id}/participants`                              List participants

  PATCH                   `/tournaments/{id}/participants/{participantId}`              Update allowed
                                                                                        snapshot/registration
                                                                                        fields

  POST                    `/tournaments/{id}/participants/{participantId}/withdraw`     Withdraw participant

  POST                    `/tournaments/{id}/participants/{participantId}/late-entry`   Admit late entry
  -------------------------------------------------------------------------------------------------------------

TPN assignment is performed by Laravel according to the locked
initial-order policy. Clients must not choose arbitrary TPNs unless a
privileged correction workflow explicitly permits it.

## 4. Round and pairing endpoints

  ---------------------------------------------------------------------------------------------------------
  Method                  Path                                                      Purpose
  ----------------------- --------------------------------------------------------- -----------------------
  POST                    `/tournaments/{id}/rounds`                                Create round records
                                                                                    from tournament
                                                                                    schedule

  GET                     `/tournaments/{id}/rounds`                                List rounds

  GET                     `/rounds/{roundId}`                                       Round detail

  POST                    `/rounds/{roundId}/pairing-runs`                          Request pairing
                                                                                    calculation

  GET                     `/pairing-runs/{runId}`                                   Poll run status/output

  POST                    `/pairing-runs/{runId}/publish`                           Publish completed valid
                                                                                    run

  GET                     `/rounds/{roundId}/pairings`                              Get pairings

  POST                    `/rounds/{roundId}/pairings/{pairingId}/reassign-board`   Reassign board if
                                                                                    allowed

  POST                    `/rounds/{roundId}/complete`                              Close round
  ---------------------------------------------------------------------------------------------------------

Generate pairing request:

``` json
{
  "ruleset_version": "fide-dutch-2026-02",
  "dry_run": false,
  "expected_round_version": 4,
  "idempotency_key": "client-generated-unique-value"
}
```

Response:

``` json
{
  "data": {
    "pairing_run_id": "uuid",
    "status": "queued",
    "round_id": "uuid",
    "input_sha256": "64-character-hex"
  }
}
```

The client polls `GET /pairing-runs/{runId}`. Do not block an HTTP
request until a long calculation completes.

## 5. Pairing-engine internal contract

This is an internal service contract, not a public browser endpoint.

Request:

``` json
{
  "schema_version": "1.0",
  "tournament": {
    "id": "uuid",
    "round_number": 3,
    "total_rounds": 7,
    "ruleset_version": "fide-dutch-2026-02",
    "scoring": {"win": 2, "draw": 1, "loss": 0}
  },
  "participants": [
    {
      "participant_id": "uuid",
      "tpn": 1,
      "score": 4,
      "active": true,
      "rating_snapshot": 2100,
      "title_snapshot": "FM",
      "played_rounds": [
        {"round": 1, "opponent_tpn": 8, "color": "white", "result": "win", "played": true},
        {"round": 2, "opponent_tpn": 5, "color": "black", "result": "draw", "played": true}
      ],
      "pairing_allocated_bye_count": 0,
      "full_point_unplayed_win_count": 0,
      "requested_bye_rounds": [],
      "float_history": []
    }
  ],
  "previous_opponents": [[1, 8], [1, 5]],
  "constraints": {
    "locked_pairings": [],
    "arbiter_overrides": []
  }
}
```

Response:

``` json
{
  "schema_version": "1.0",
  "status": "completed",
  "engine": {"name": "openswiss-dutch", "version": "0.1.0"},
  "ruleset_version": "fide-dutch-2026-02",
  "pairings": [
    {"board": 1, "white_tpn": 1, "black_tpn": 4, "type": "game"},
    {"board": null, "white_tpn": null, "black_tpn": null, "bye_tpn": 12, "type": "pairing_allocated_bye"}
  ],
  "diagnostics": {
    "absolute_criteria_passed": true,
    "quality_criteria": [],
    "candidate_trace": [],
    "warnings": []
  }
}
```

This payload is illustrative, not a complete normative FIDE input model.
Before implementation, expand it to represent every rule-relevant
condition in the selected FIDE Handbook version. The engine must reject
incomplete or contradictory history rather than guess.

## 6. Results endpoints

  --------------------------------------------------------------------------------------------
  Method                  Path                                         Purpose
  ----------------------- -------------------------------------------- -----------------------
  POST                    `/pairings/{pairingId}/result`               Enter result

  POST                    `/pairings/{pairingId}/result-corrections`   Correct result with
                                                                       reason

  POST                    `/pairings/{pairingId}/approve-result`       Approve result where
                                                                       required

  GET                     `/pairings/{pairingId}/result-history`       Read append-only
                                                                       history
  --------------------------------------------------------------------------------------------

Result example:

``` json
{
  "result": "white_win",
  "result_type": "played",
  "notes": null,
  "idempotency_key": "unique-client-key"
}
```

Define result vocabulary explicitly. At minimum distinguish played
outcomes, double forfeit, forfeit win/loss, pairing-allocated bye,
requested bye, and adjourned/unplayed states. Do not collapse all
unplayed outcomes into `1-0` or `0-1` without retaining the reason and
tie-break semantics.

## 7. Standings and export endpoints

  Method   Path                                               Purpose
  -------- -------------------------------------------------- ---------------------------------
  GET      `/tournaments/{id}/standings`                      Current standings
  GET      `/tournaments/{id}/crosstable`                     Crosstable
  GET      `/tournaments/{id}/players/{participantId}/card`   Player card
  POST     `/tournaments/{id}/exports`                        Request export
  GET      `/exports/{exportId}`                              Export status/download metadata

Standings response should include rank, TPN, name, score, tie-break
values, played rounds, and status. Include the tie-break
algorithm/version used.

## 8. State and error codes

Useful stable error codes: - `TOURNAMENT_NOT_FOUND` -
`TOURNAMENT_STATE_CONFLICT` - `ROUND_NOT_READY` -
`ROUND_ALREADY_PUBLISHED` - `PAIRING_RUN_NOT_COMPLETED` -
`PAIRING_REQUIRES_ARBITER_DECISION` - `NO_VALID_PAIRING_FOUND` -
`PAIRING_INVARIANT_VIOLATION` - `DUPLICATE_OPPONENT` -
`PARTICIPANT_ALREADY_PAIRED` - `RESULT_CORRECTION_REASON_REQUIRED` -
`FORBIDDEN` - `IDEMPOTENCY_CONFLICT` - `STALE_TOURNAMENT_VERSION`

## 9. OpenAPI

Generate and maintain an OpenAPI 3.1 document from Laravel
route/request/resource definitions or a single source of truth. Add
contract tests that compare actual responses to the schema. Do not
maintain handwritten endpoint docs that drift from the implementation.
