# 07. UI/UX Specification

## 1. Design direction

**Minimal arbiter interface.** The interface should feel like a clear
tournament control desk: compact, calm, readable, keyboard-friendly, and
optimized for fast data entry.

Avoid decorative charts, gradients, oversized cards, and unnecessary
animations. Use strong hierarchy, restrained colour, and consistent
table layouts.

## 2. Application shell

-   Left navigation: Dashboard, Tournaments, Players, Settings.
-   Top bar: current tournament, round selector, user menu.
-   Main area: page title, primary action, content.
-   Public tournament pages use a simplified header without organizer
    controls.
-   Desktop-first for arbiter workflows, responsive down to
    tablet/mobile for public viewing.

## 3. Pages

### A. Sign in

-   Email and password.
-   Clear validation errors.
-   Password reset.
-   No public registration unless platform policy enables it.

### B. Dashboard

-   List tournaments the user can access.
-   Status, dates, rounds, participant count.
-   Quick actions: Open tournament, Create tournament.
-   No vanity metrics.

### C. Create/Edit Tournament

Sections: 1. Identity: name, venue, city, country, timezone. 2.
Schedule: start/end date, number of rounds, round schedule. 3. Format:
individual Swiss, FIDE Dutch ruleset version. 4. Scoring: win/draw/loss
points. 5. Tie-break: ordered list, published in regulations. 6.
Registration: open/close, late-entry policy. 7. Visibility:
private/public.

Display a warning if ruleset version or tie-break configuration is not
reviewed.

### D. Participant Management

-   Search/add existing player or create new player.
-   Import CSV with preview and validation.
-   Columns: TPN, name, FIDE ID, title, rating snapshot, federation,
    club, status.
-   Bulk actions: confirm, withdraw, export.
-   Show the source/date of rating snapshot.
-   Lock initial order before round 1 pairing.
-   Clearly distinguish late entry from initial participant.

### E. Tournament Control / Overview

-   Current round and state.
-   Participant count and active count.
-   Unresolved results.
-   Pairing status and last pairing run.
-   Actions depend on tournament state and permission.

### F. Pairings

-   Round selector.
-   Pairing table: board, White, Black, result/status.
-   Bye shown in a separate clearly labeled row, not as a fake opponent.
-   Generate button starts a pairing run.
-   Show engine version, ruleset version, and run status.
-   Show diagnostics and warnings before publication.
-   Publish requires confirmation and permission.
-   Never silently replace published pairings.

### G. Result Entry

-   Fast keyboard navigation by board.
-   Result choices: White win, draw, Black win, forfeit outcomes, other
    configured statuses.
-   Show confirmation for destructive/corrective action.
-   Correction requires reason.
-   Result history accessible from each board.

### H. Standings

-   Rank, TPN, player, points, tie-break columns, status.
-   Sort order follows tournament configuration.
-   Show calculation version and "updated after round N".
-   Export CSV/PDF.
-   Explain tie-break abbreviations in a help panel.

### I. Player Card

-   Player identity and tournament snapshots.
-   Round-by-round opponents, colours, results, byes, floats.
-   Score and tie-break details.
-   Public view omits private organizer-only data.

### J. Audit Log

-   Timestamp, actor, action, entity, reason.
-   Filter by action and user.
-   Read-only.
-   No editing or deleting audit records from the normal UI.

### K. Public Tournament Portal

-   Tournament details.
-   Published pairings by round.
-   Results and standings.
-   Search player.
-   Public player card.
-   No draft data or private notes.

## 4. UI states

Every data page must handle: - loading; - empty; - validation error; -
permission denied; - stale data; - server error with retry; -
partial/background job state; - success confirmation.

Pairing generation has explicit states: queued, running, completed,
failed, requires arbiter decision.

## 5. Accessibility and interaction

-   Semantic HTML and table headers.
-   Keyboard-accessible controls.
-   Visible focus indicators.
-   Labels for every input.
-   Do not communicate status by colour alone.
-   Confirm irreversible actions.
-   Use inline errors near fields.
-   Support screen readers for result status and pairing changes.

## 6. Component inventory

-   `AppShell`
-   `TournamentSwitcher`
-   `StatusBadge`
-   `DataTable`
-   `PlayerSearch`
-   `ParticipantImportWizard`
-   `RoundTabs`
-   `PairingTable`
-   `ResultSelect`
-   `PairingRunPanel`
-   `PairingDiagnostics`
-   `StandingsTable`
-   `TieBreakHelp`
-   `AuditTimeline`
-   `ConfirmDialog`
-   `EmptyState`
-   `ErrorState`

Build shared components only when at least two pages need them. Avoid
premature abstraction.
