# 01. Project Overview

## 1. Vision

OpenSwiss provides tournament organizers and arbiters with a
transparent, auditable way to operate individual Swiss chess
tournaments. The initial system supports a single-section tournament
using the FIDE Dutch System, with a public read-only portal for
participants and spectators.

## 2. Goals

-   Create, configure, publish, operate, and close tournaments.
-   Register players and preserve tournament-specific identity/rating
    snapshots.
-   Generate reproducible Dutch System pairings.
-   Publish round pairings and record results.
-   Calculate standings and ordered tie-breaks.
-   Handle withdrawals, late entries, requested byes, forfeits, and
    corrections.
-   Export tournament data for printing and interoperability.
-   Maintain an audit trail for all sensitive operations.

## 3. Non-goals for the first release

-   Team Swiss events.
-   Round-robin, knockout, Scheveningen, and hybrid events.
-   Online chess gameplay or clock control.
-   Automatic FIDE rating submission.
-   Automatic FIDE certification or approval.
-   Unsupervised automatic resolution of ambiguous arbiter decisions.
-   Multi-tenant SaaS billing.

Keep the architecture extensible, but do not implement these before the
individual Swiss MVP is correct.

## 4. Users and permissions

  -----------------------------------------------------------------------
  Role                                Main capabilities
  ----------------------------------- -----------------------------------
  Platform administrator              Manage platform settings and users

  Chief arbiter                       Full tournament operation, approve
                                      corrections, publish pairings

  Organizer                           Configure tournament, players,
                                      rounds, exports

  Arbiter                             Enter results, manage round
                                      operations within granted scope

  Assistant                           Limited player/result entry

  Public visitor                      View published tournament,
                                      pairings, results, standings
  -----------------------------------------------------------------------

Roles are scoped to a tournament wherever possible. A platform account
must not automatically gain access to every tournament.

## 5. MVP acceptance criteria

A tournament organizer can: 1. Create an individual Swiss tournament
with name, venue, timezone, dates, rounds, time control, scoring, and
pairing ruleset. 2. Register/import players and assign unique TPNs. 3.
Capture rating, title, federation, club, and initial-order snapshots. 4.
Lock the initial participant list. 5. Generate round pairings using the
selected, versioned Dutch ruleset. 6. Review pairing diagnostics and
approve/publish the result. 7. Record played results, forfeits, byes,
and requested absences. 8. View standings with configured tie-breaks. 9.
Correct an erroneous result with reason and audit history. 10. Export
pairings, crosstables, standings, and supported interchange formats.

The MVP must support odd and even participant counts, withdrawals, late
entries, no-show/forfeit states, and players with prior pairing history.

## 6. Product principles

-   Arbiter-first: the software assists, explains, and records. It does
    not hide decisions.
-   Explainability: pairing runs expose their ruleset, input, output,
    and diagnostics.
-   Reproducibility: a pairing run can be replayed with its recorded
    versions and snapshot.
-   Historical integrity: corrections create new audit events; they do
    not erase history.
-   Accessibility: keyboard-friendly controls, clear contrast, semantic
    tables.
-   Minimal UI: focus on operational clarity rather than decorative
    dashboards.

## 7. Key domain vocabulary

-   **TPN:** Tournament Pairing Number assigned to a participant for the
    event.
-   **Scoregroup:** Participants with the same current score.
-   **Bracket:** Current scoregroup plus eligible players moved down
    from a prior bracket.
-   **Downfloater:** Player left unpaired in a bracket and moved to the
    next bracket.
-   **Upfloater:** Player who meets an opponent with a lower score.
-   **PAB:** Pairing-allocated bye, no opponent and no colour.
-   **Requested bye:** A participant-requested absence under tournament
    regulations.
-   **Forfeit:** Result awarded because a participant/opponent did not
    play.
-   **Pairing run:** Immutable execution record of a pairing engine
    calculation.
