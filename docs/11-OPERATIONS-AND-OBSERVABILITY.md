# 11. Operations and Observability

## 1. Health endpoints

Provide: - Laravel `/health` for process readiness; - Laravel `/ready`
for dependency readiness; - Pairing engine `/health` and `/ready`; -
queue worker heartbeat/monitoring.

Do not expose internal dependency details publicly.

## 2. Structured logging

Include: - timestamp; - severity; - service; - environment; -
request/correlation ID; - tournament ID and round ID when relevant; -
pairing run ID; - actor ID where appropriate; - stable error code.

Do not log passwords, tokens, session cookies, full payment data, or
unnecessary player personal data.

## 3. Metrics

Monitor: - API latency and error rate; - queue depth and job duration; -
pairing run duration and failure rate; - pairing run status by
engine/ruleset version; - database connections and slow queries; -
export generation time; - authentication failures; - result correction
and pairing regeneration counts.

## 4. Backups and recovery

-   Automated PostgreSQL backups.
-   Point-in-time recovery where available.
-   Encrypted offsite backup storage.
-   Periodic restore drills.
-   Retention policy documented.
-   Pairing snapshots and audit history included in backups.
-   Test restoring a complete tournament and replaying its pairing runs.

## 5. Operational controls

-   Pause registration.
-   Lock participant list.
-   Disable public visibility.
-   Pause pairing generation during an incident.
-   Revoke compromised sessions.
-   Re-run a failed queue job only after checking idempotency and run
    state.
-   Preserve failed pairing-run diagnostics for investigation.

## 6. Incident categories

-   Pairing discrepancy.
-   Incorrect result.
-   Unauthorized correction.
-   Data exposure.
-   Database corruption.
-   Queue backlog/outage.
-   Pairing engine outage.
-   Export leak.

For pairing discrepancies, preserve the original input/output snapshots,
hashes, engine build, ruleset version, and arbiter notes.
