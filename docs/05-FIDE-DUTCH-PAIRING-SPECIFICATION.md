# 05. FIDE Dutch Pairing Specification

**Status:** normative implementation target, not a claim of
certification.\
**Baseline ruleset:** FIDE Handbook C.04.3 Dutch System effective 1
February 2026, together with C.04.1 and C.04.2 effective 1 February
2026.\
**Implementation status:** not implemented until validated by tests and
independent pairing comparisons.

## 1. Core requirement

Do not implement "sort by score, split in half, pair top half against
bottom half" and call it Dutch. The FIDE Dutch System is a prescribed
bracket-based algorithm with absolute criteria, quality criteria,
candidate construction, ordered alterations, colour allocation, and
special cases.

The engine must implement the full applicable text of the versioned
rules. This document is an engineering contract and navigation aid, not
a replacement for the official Handbook.

## 2. Inputs required

The normalized input must represent, at minimum:

### Tournament

-   tournament ID and round number;
-   total scheduled rounds;
-   pairing system and exact ruleset version;
-   scoring values and tournament regulations;
-   initial-order/TPN policy;
-   any rule-authorized acceleration settings;
-   final-round indicator.

### Participant

-   stable participant ID and TPN;
-   current score in exact units;
-   active/withdrawn/suspended status;
-   initial strength/rating/title snapshot used for order;
-   rounds actually played and rounds not played;
-   opponent and colour for every relevant round;
-   results and points awarded;
-   pairing-allocated bye history;
-   full-point unplayed win history;
-   requested bye/absence history;
-   float history (upfloat/downfloat);
-   late-entry round and eligibility;
-   any approved, rule-authorized pairing constraint.

### Historical pairing data

-   all previous opponents, including relevant unplayed pairings;
-   actual colours played, not merely assigned colours;
-   whether a game was played, forfeited, adjourned, or otherwise
    unplayed;
-   round-by-round score state.

Reject missing or contradictory rule-relevant history. Do not infer that
a player had White because a colour was assigned if the game was not
played; apply the exact FIDE rule for the case.

## 3. Ordering and structures

For pairing order under Dutch rules, rank by: 1. score, descending; 2.
TPN, ascending.

A scoregroup contains all players with equal score. A bracket consists
of resident players from a scoregroup plus players moved down from the
previous bracket. A bracket may be homogeneous or heterogeneous. A
remainder is a sub-bracket of resident players in a heterogeneous
bracket.

Represent these concepts as typed domain objects. Avoid loosely
structured dictionaries inside the algorithm.

## 4. Absolute criteria and completion

Implement and validate the applicable criteria from C.04.3, including: -
**C1:** no repeat opponent. - **C2:** a player who already received a
pairing-allocated bye, or who already scored in one single round without
playing as many points as rewarded for a win, is not assigned another
pairing-allocated bye. - **C3:** the absolute-colour-preference
restriction for non-topscorers as defined in the applicable rules. -
**C4:** completion criterion: a valid completion must exist for all
players not yet paired, subject to the rule's scope. - **C5:** minimize
the score of the pairing-allocated-bye recipient.

Do not implement these as informal penalties. C1-C3 are hard
constraints. C4 and C5 must be handled in the exact priority and
procedure specified by the selected ruleset.

The full criteria C6-C21 must be implemented from the official rule text
in its published priority order. Do not replace them with a weighted sum
unless the official procedure explicitly defines such an evaluation.
Record each criterion's pass/fail/evaluation in the candidate trace.

## 5. Bracket processing

The official process starts at the highest scoregroup and proceeds
downwards. Implement the complete procedure from C.04.3 Articles 3 and
4, including: - creation of S1, Limbo, and S2 where prescribed; -
homogeneous bracket and remainder handling; - heterogeneous bracket and
moved-down-player pairing; - candidate construction and evaluation; -
prescribed alterations/transpositions/exchanges in their exact
sequence; - acceptance of a perfect candidate when the rule says to
accept it; - fallback procedure when no perfect candidate exists; -
final pairing-allocated-bye handling.

The engine must not skip an alteration because a different pairing
"looks better". Candidate generation order is part of deterministic
reproducibility.

## 6. Floats

A downfloater is a player left unpaired in a bracket and moved to the
next bracket. A player in a game against a lower-scoring opponent
receives a downfloat; the lower-ranked opponent receives an upfloat,
subject to the exact definitions. A PAB or qualifying unplayed scoring
event can also produce a downfloat under the rules.

Maintain round-by-round float history. Do not approximate float history
using only the previous round.

## 7. Colour allocation

Colour allocation is a separate, explicit phase after candidate pairings
are selected.

Track: - colour difference (White games minus Black games); - absolute
colour preference; - strong colour preference; - mild colour
preference; - last played colours and sequence; - whether a game was
actually played; - topscorer status as defined for the final round.

The 2026 Dutch rules define: - absolute preference when colour
difference exceeds +1 or is below -1, or the player's two latest played
games had the same colour; - strong preference when colour difference is
+1 or -1; - mild preference when colour difference is zero, with
preference alternating relative to the previous played game; - no
preference for a player with no played games.

Implement Article 5's full colour-allocation procedure and priority
order. Do not choose White by TPN parity or random coin flip. Include
the selected colour allocation and the reasoning trace in output.

## 8. Bye handling

A pairing-allocated bye: - has no opponent; - has no colour; - receives
the tournament's specified PAB points; - is assigned only under the
applicable eligibility rules; - must not be assigned to a player already
disqualified by prior PAB or qualifying full-point unplayed win
history; - is selected according to C5 and the subsequent official
procedure.

Represent a PAB distinctly from: - requested half-point bye; - requested
zero-point bye; - forfeit win; - forfeit loss; - withdrawal; - late
entry with no points for earlier rounds.

These categories can affect pairing eligibility and tie-break
calculations differently.

## 9. Determinism and auditability

For every run: - canonicalize input before hashing; - use stable sort
and deterministic candidate enumeration; - avoid random numbers and
nondeterministic collection iteration; - record engine build/version and
ruleset version; - record input/output hashes; - record candidate trace
and criterion evaluations; - produce the same output for the same
normalized input and versions.

If an arbiter decision is needed, return a structured unresolved state.
Do not hide it behind an arbitrary fallback.

## 10. Publication rules

Generation and publication are separate. Before publishing, Laravel must
validate: - all active eligible participants appear exactly once, except
permitted exclusions; - each game has two distinct participants; - no
participant appears twice in the round; - no repeat opponent exists; -
bye eligibility is satisfied; - colours are valid under the selected
ruleset; - round and tournament versions are current.

Once published, pairings are not casually regenerated. Follow C.04.2
publication/correction rules and preserve the original pairing plus the
correction event.

## 11. Failure behavior

Return structured outcomes: - `completed`; -
`requires_arbiter_decision`; - `no_valid_pairing_found`; -
`invalid_input`; - `unsupported_ruleset`.

Include: - blocking criterion/rule reference; - participants/bracket
involved; - attempted candidate count; - relevant prior opponents/colour
facts; - safe next action for the arbiter.

Never silently relax C1-C3 or manufacture a bye.

## 12. Algorithm implementation phases

1.  Build immutable domain models and input validator.
2.  Implement score/TPN ordering and scoregroup/bracket construction.
3.  Implement historical state derivation (colour, float, bye, unplayed
    results).
4.  Implement homogeneous bracket and remainder procedures.
5.  Implement heterogeneous bracket and moved-down-player procedure.
6.  Implement candidate evaluation with exact C1-C21 ordering.
7.  Implement ordered alterations and deterministic candidate
    enumeration.
8.  Implement colour allocation.
9.  Implement PAB selection and edge cases.
10. Implement full-round completion and output validation.
11. Compare against independent reference pairings and trusted test
    fixtures.
12. Freeze a versioned ruleset and publish a validation report.

## 13. Required test families

-   even and odd participant counts;
-   one and multiple scoregroups;
-   repeated opponent avoidance;
-   absolute/strong/mild/no colour preference;
-   consecutive colour history;
-   colour difference boundaries;
-   players with prior PAB;
-   full-point unplayed wins;
-   requested byes and withdrawals;
-   late entries;
-   adjourned games;
-   unplayed games and forfeits;
-   final-round topscorer handling;
-   no perfect candidate / fallback cases;
-   all bracket alteration paths;
-   deterministic replay;
-   invalid/inconsistent history.

Every fixture must cite the FIDE article and effective edition it
verifies.
