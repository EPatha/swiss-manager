# 06. FIDE Rules Register and Version Control

## 1. Baseline reviewed

This documentation baseline uses the FIDE Handbook provisions currently
published for the following effective dates:

  -----------------------------------------------------------------------
  Rule source                         Baseline
  ----------------------------------- -----------------------------------
  C.04.1 Basic Rules for Swiss        Effective 1 February 2026
  Systems                             

  C.04.2 General Handling Rules for   Effective 1 February 2026
  Swiss Tournaments                   

  C.04.3 FIDE (Dutch) System          Effective 1 February 2026

  Play-Off and Tie-Break Regulations  Effective 1 March 2026
  -----------------------------------------------------------------------

Official sources: - [FIDE Handbook](https://handbook.fide.com/) -
[C.04.2 General Handling Rules, effective 1 February
2026](https://handbook.fide.com/chapter/GeneralHandlingRulesForSwissTournaments202602) -
[C.04.3 Dutch System, effective 1 February
2026](https://handbook.fide.com/chapter/C0403202602) - [Play-Off and
Tie-Break Regulations, effective 1 March
2026](https://handbook.fide.com/chapter/TieBreakRegulations032026)

## 2. Release gate

Before every production release: 1. Visit the official FIDE Handbook. 2.
Confirm the effective version that applies on the tournament date. 3.
Download/archive the relevant official rule text with its
publication/effective metadata, subject to copyright and redistribution
terms. 4. Review changes against the current ruleset implementation. 5.
Update this register and the rule-to-code traceability matrix. 6. Add or
update tests for every changed rule. 7. Run the full reference pairing
suite. 8. Have a qualified arbiter independently review the
implementation and test report.

Do not assume the latest Handbook version automatically applies
retroactively to a tournament already underway. Select rules based on
applicable event regulations and FIDE provisions.

## 3. Traceability matrix template

  ------------------------------------------------------------------------------------------------------------------
  Rule ID        Official    Code module                 Test fixture                        Reviewer    Status
                 article                                                                                 
  -------------- ----------- --------------------------- ----------------------------------- ----------- -----------
  DUTCH-C1       C.04.3      `domain/dutch/criteria`     `test_no_repeat_opponent`           TBD         Planned
                 2.1.1                                                                                   

  DUTCH-C2       C.04.3      `domain/dutch/bye`          `test_pab_ineligible_history`       TBD         Planned
                 2.1.2                                                                                   

  DUTCH-C3       C.04.3      `domain/dutch/criteria`     `test_absolute_colour_preference`   TBD         Planned
                 2.1.3                                                                                   

  DUTCH-C4       C.04.3      `domain/dutch/completion`   `test_completion_criterion`         TBD         Planned
                 2.2.1                                                                                   

  DUTCH-C5       C.04.3      `domain/dutch/bye`          `test_lowest_score_pab`             TBD         Planned
                 2.3.1                                                                                   

  DUTCH-C6-C21   C.04.3 2.4  `domain/dutch/evaluation`   criterion-specific fixtures         TBD         Planned

  DUTCH-COLOUR   C.04.3      `domain/dutch/colour`       colour fixture suite                TBD         Planned
                 Article 5                                                                               

  HANDLING-TPN   C.04.2      `domain/participants/tpn`   TPN order fixtures                  TBD         Planned
                 Article 2                                                                               

  TB-UNPLAYED    Tie-Break   `domain/standings`          unplayed-round fixtures             TBD         Planned
                 Regs                                                                                    
                 Article 16                                                                              
  ------------------------------------------------------------------------------------------------------------------

This table is a starter index. Expand it to every sub-article
implemented. A feature cannot be marked complete while a relevant row
remains untested or unreviewed.

## 4. Important 2026 handling details to verify in code

-   Initial participant order and TPN assignment are governed by C.04.2,
    not simply by an arbitrary UI sort.
-   Late entries receive an appropriate TPN and do not receive points
    for earlier unplayed rounds unless the event rules state otherwise.
-   Withdrawn players are no longer paired.
-   Pairing publication and corrections have explicit handling rules.
-   FIDE pairing systems are expected to be objective and reproducible.
-   The 2026 tie-break regulations define ordered tie-break lists and
    specific handling of unplayed rounds. Do not assume a generic
    Buchholz implementation covers all categories.

## 5. Tie-break engine

Tie-breaks are not part of the Dutch pairing engine. Keep them in a
separate standings module with independently versioned algorithms.

The tournament must store the ordered tie-break list before the event
starts. Implement only selected tie-breaks initially, and do not present
an unsupported tie-break as official.

At minimum plan for: - Direct Encounter; - Buchholz; - Cut-1 Buchholz; -
Fore Buchholz; - Sonneborn-Berger where applicable; - Average Rating of
Opponents and other supported official methods as needed.

For each method, document exact calculation, treatment of unplayed
rounds, cut rules, rounding, and the effective regulation version. The
2026 Tie-Break Regulations include special unplayed-round handling in
Article 16. Implement this before claiming correct FIDE-rated tournament
standings.

## 6. Compliance wording

Allowed before validation: - "OpenSwiss is designed to implement the
FIDE Dutch System." - "This release targets FIDE Handbook C.04.3
effective 1 February 2026."

Not allowed without evidence/approval: - "FIDE-approved." -
"FIDE-certified." - "Guaranteed FIDE-compliant." - "Official FIDE
pairing software."

A passing internal test suite is necessary but not sufficient for
official approval.
