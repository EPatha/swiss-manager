# 03. Database Design

PostgreSQL is authoritative. Use UUID primary keys for externally
addressable records, `timestamptz` for instants, `date` for local event
dates, JSONB only for versioned snapshots/configuration, and integer
half-points for chess scores.

## 1. Entity relationship overview

``` mermaid
erDiagram
    USERS ||--o{ TOURNAMENT_USER : assigned
    TOURNAMENTS ||--o{ TOURNAMENT_USER : has
    TOURNAMENTS ||--o{ TOURNAMENT_PLAYERS : registers
    PLAYERS ||--o{ TOURNAMENT_PLAYERS : participates
    TOURNAMENTS ||--o{ ROUNDS : contains
    ROUNDS ||--o{ PAIRING_RUNS : calculates
    PAIRING_RUNS ||--o{ PAIRINGS : produces
    ROUNDS ||--o{ PAIRINGS : contains
    TOURNAMENT_PLAYERS ||--o{ PAIRINGS : white_or_black
    PAIRINGS ||--o{ RESULT_EVENTS : records
    TOURNAMENTS ||--o{ AUDIT_LOGS : audits
```

## 2. Tables

### `users`

-   `id uuid primary key`
-   `name varchar(160) not null`
-   `email varchar(255) unique not null`
-   `password varchar(...) not null`
-   `email_verified_at timestamptz null`
-   `is_platform_admin boolean not null default false`
-   `created_at`, `updated_at`

Use Laravel's secure password hashing. Do not store plaintext
credentials.

### `players`

Canonical person record, not tournament-specific rating data. -
`id uuid primary key` - `full_name varchar(200) not null` -
`fide_id varchar(20) null` - `birth_date date null` -
`gender_code varchar(20) null` (optional; collect only if needed) -
`federation_code char(3) null` - `club varchar(160) null` -
`created_at`, `updated_at`

A FIDE ID is not required for every player. Normalize and validate
format, but do not assume all federations or IDs are present.

### `tournaments`

-   `id uuid primary key`
-   `slug varchar(180) unique not null`
-   `name varchar(200) not null`
-   `description text null`
-   `venue varchar(255) null`
-   `city varchar(120) null`
-   `country_code char(2) null`
-   `timezone varchar(64) not null`
-   `start_date date not null`
-   `end_date date not null`
-   `round_count smallint not null check (round_count > 0)`
-   `status varchar(24) not null`
-   `pairing_system varchar(40) not null` (initially `fide_dutch`)
-   `ruleset_version varchar(80) not null`
-   `scoring_config jsonb not null`
-   `tie_break_config jsonb not null`
-   `initial_order_config jsonb not null`
-   `regulations_snapshot jsonb not null`
-   `current_round smallint null`
-   `is_public boolean not null default false`
-   `version bigint not null default 1`
-   `created_by uuid references users(id)`
-   timestamps

Statuses: `draft`, `registration`, `in_progress`, `completed`,
`cancelled`, `archived`.

### `tournament_user`

Tournament-scoped permissions. -
`tournament_id uuid references tournaments(id)` -
`user_id uuid references users(id)` - `role varchar(32) not null` -
`created_at`, `updated_at` - primary key `(tournament_id, user_id)` -
roles: `chief_arbiter`, `arbiter`, `organizer`, `assistant`

### `tournament_players`

Participant snapshot and tournament identity. - `id uuid primary key` -
`tournament_id uuid references tournaments(id)` -
`player_id uuid references players(id)` - `tpn integer not null` -
`initial_rank integer not null` - `rating_snapshot integer null` -
`rating_type varchar(20) null` (`standard`, `rapid`, `blitz`, etc.) -
`rating_list_date date null` - `title_snapshot varchar(12) null` -
`federation_snapshot char(3) null` - `club_snapshot varchar(160) null` -
`registration_status varchar(24) not null` -
`pairing_status varchar(24) not null` -
`late_entry_round smallint null` - `withdrawn_at timestamptz null` -
`created_at`, `updated_at` - unique `(tournament_id, player_id)` -
unique `(tournament_id, tpn)` - unique `(tournament_id, initial_rank)`

`registration_status`: `registered`, `confirmed`, `rejected`,
`withdrawn`. `pairing_status`: `active`, `withdrawn`, `suspended`.

### `rounds`

-   `id uuid primary key`
-   `tournament_id uuid references tournaments(id)`
-   `round_number smallint not null`
-   `status varchar(24) not null`
-   `scheduled_at timestamptz null`
-   `pairing_published_at timestamptz null`
-   `results_locked_at timestamptz null`
-   `version bigint not null default 1`
-   timestamps
-   unique `(tournament_id, round_number)`

Statuses: `pending`, `pairing`, `published`, `in_progress`, `completed`,
`locked`.

### `pairing_runs`

Immutable calculation record. - `id uuid primary key` -
`tournament_id uuid references tournaments(id)` -
`round_id uuid references rounds(id)` - `run_number integer not null` -
`status varchar(32) not null` -
`requested_by uuid references users(id)` -
`engine_name varchar(80) not null` -
`engine_version varchar(80) not null` -
`ruleset_version varchar(80) not null` -
`input_snapshot jsonb not null` - `input_sha256 char(64) not null` -
`output_snapshot jsonb null` - `output_sha256 char(64) null` -
`diagnostics jsonb not null default '{}'` -
`failure_code varchar(80) null` - `started_at`, `completed_at` -
`supersedes_run_id uuid null references pairing_runs(id)` - timestamps -
unique `(round_id, run_number)`

### `pairings`

A pairing is one board assignment or one bye. - `id uuid primary key` -
`tournament_id uuid references tournaments(id)` -
`round_id uuid references rounds(id)` -
`pairing_run_id uuid references pairing_runs(id)` -
`board_number integer null` - `pairing_type varchar(24) not null`
(`game`, `pairing_allocated_bye`) -
`white_participant_id uuid null references tournament_players(id)` -
`black_participant_id uuid null references tournament_players(id)` -
`bye_participant_id uuid null references tournament_players(id)` -
`white_tpn_snapshot integer null` - `black_tpn_snapshot integer null` -
`result_status varchar(24) not null default 'pending'` -
`is_published boolean not null default false` -
`published_at timestamptz null` - timestamps

Checks: - For `game`, white and black are non-null, different, bye
participant is null. - For `pairing_allocated_bye`, bye participant is
non-null, white/black are null, board number is null. - Unique
`(round_id, board_number)` for non-null board numbers. - Unique
participant appearance per round across white, black, and bye fields.
Enforce with service validation and, where practical, partial/expression
indexes or a normalized pairing-participants child table. - Prevent
duplicate opponent pairings across a tournament with a canonical
`(tournament_id, low_participant_id, high_participant_id)` uniqueness
strategy in a separate `pairing_opponents` table or a validated
transactional invariant. Do not rely on unordered pair comparison in
application code alone.

### `pairing_opponents`

Recommended normalized uniqueness table for played/published game
opponents. - `tournament_id uuid not null` -
`pairing_id uuid unique not null references pairings(id)` -
`participant_low_id uuid not null references tournament_players(id)` -
`participant_high_id uuid not null references tournament_players(id)` -
check `participant_low_id < participant_high_id` - unique
`(tournament_id, participant_low_id, participant_high_id)`

### `pairing_participants`

Recommended normalized appearance table. -
`pairing_id uuid references pairings(id)` -
`participant_id uuid references tournament_players(id)` -
`assigned_color varchar(5) null` (`white`, `black`, null for bye) -
primary key `(pairing_id, participant_id)` - unique
`(pairing_id, participant_id)`

Use a database strategy that prevents a participant appearing twice in
the same round, e.g. include `round_id` in this table and unique
`(round_id, participant_id)`.

### `result_events`

Append-only result history. - `id uuid primary key` -
`pairing_id uuid references pairings(id)` -
`event_type varchar(24) not null` (`result_entered`, `result_corrected`,
`result_approved`) - `previous_result varchar(24) null` -
`new_result varchar(24) not null` - `reason text null` -
`recorded_by uuid references users(id)` -
`approved_by uuid null references users(id)` -
`created_at timestamptz not null`

### `standings_snapshots` (optional cache)

-   `id uuid primary key`
-   `tournament_id uuid references tournaments(id)`
-   `after_round smallint not null`
-   `calculation_version varchar(80) not null`
-   `payload jsonb not null`
-   `created_at`
-   unique `(tournament_id, after_round, calculation_version)`

This is a rebuildable projection, not the source of truth.

### `audit_logs`

-   `id uuid primary key`
-   `tournament_id uuid null references tournaments(id)`
-   `actor_user_id uuid null references users(id)`
-   `action varchar(100) not null`
-   `entity_type varchar(100) not null`
-   `entity_id uuid null`
-   `before_state jsonb null`
-   `after_state jsonb null`
-   `reason text null`
-   `ip_address inet null`
-   `user_agent text null`
-   `created_at timestamptz not null`

Minimize personal data in audit payloads. Define retention policy.

## 3. Score representation

Store score in **half-point units**: - loss = `0` - draw = `1` - win =
`2`

If tournament regulations allow other point values, use integer
milli-points or a documented fixed-precision representation. Do not use
float/double for scoring or comparisons.

## 4. Important constraints and invariants

-   A participant belongs to a tournament once.
-   TPN is unique within a tournament.
-   A participant appears at most once in a round.
-   A game has exactly two different participants.
-   A bye has exactly one participant and no opponent/colour/board.
-   A participant cannot receive a pairing-allocated bye more than once.
-   A participant with a qualifying full-point unplayed win is not
    eligible for a PAB where the applicable FIDE rule excludes them.
-   A pair of opponents cannot recur.
-   Published pairing records are immutable except through a documented
    arbiter correction workflow allowed by applicable rules.
-   Every result correction has a reason and actor.
-   Historical rating/title/federation values are snapshots.
-   Every pairing output references the exact input and engine/ruleset
    versions.

## 5. Migration strategy

Create migrations in dependency order: 1. users and players; 2.
tournaments and tournament_user; 3. tournament_players; 4. rounds; 5.
pairing_runs; 6. pairings and normalized pairing participant/opponent
tables; 7. result_events; 8. audit_logs; 9. optional standings
snapshots.

Use foreign keys, check constraints, indexes, and transaction-safe
migrations. Add tests for every critical invariant.
