# 08. Security Specification

## 1. Threat model

Protect: - organizer and arbiter accounts; - private tournament data; -
player personal data; - result integrity; - pairing integrity and
reproducibility; - exports and uploaded imports; - internal service
credentials.

Assume public visitors may scrape public pages, submit malformed input,
and attempt authorization bypass. Assume a compromised organizer account
could alter results; therefore use least privilege and immutable audit
history.

## 2. Authentication

-   Use a maintained Laravel authentication mechanism.
-   Require strong password hashing (Laravel-supported Argon2id/bcrypt
    configuration).
-   Rate-limit login, password reset, and sensitive endpoints.
-   Use secure, HttpOnly, SameSite cookies if using session auth.
-   Enforce CSRF protection for cookie-authenticated mutations.
-   Add optional MFA for chief arbiters and platform administrators.
-   Revoke sessions/tokens on password reset and account compromise.

## 3. Authorization

-   Use Laravel Policies/Gates.
-   Check permissions on every request, not only in the UI.
-   Scope organizer/arbiter roles to a tournament.
-   Public endpoints expose only published public data.
-   Assistants cannot publish pairings, alter tournament rules, or
    approve their own privileged corrections unless explicitly allowed
    by policy.
-   Log denied sensitive actions without leaking data.

## 4. Data validation

-   Validate every request server-side.
-   Validate CSV imports for encoding, size, column mapping, duplicate
    identities, malformed IDs, and formula injection.
-   Reject unexpected fields where practical.
-   Use parameterized queries and ORM-safe patterns.
-   Validate pairing-engine output again in Laravel before
    persistence/publication.
-   Never trust the browser or Python service as an authorization
    authority.

## 5. Service-to-service security

-   Pairing engine is private-network only.
-   Authenticate Laravel-to-engine requests with service credentials or
    mutual TLS.
-   Use request IDs and timeouts.
-   Validate Pydantic schemas on both sides.
-   Do not expose the pairing engine publicly.
-   Do not allow the engine to access PostgreSQL directly.
-   Limit CPU, memory, request size, and execution time.

## 6. Integrity and audit

-   Published pairings and result events are append-only from the
    application perspective.
-   Corrections require reason, actor, timestamp, and before/after
    values.
-   Store pairing input/output snapshots and hashes.
-   Use transactions and concurrency checks.
-   Alert on unusual sequences: repeated corrections, repeated pairing
    regeneration, privilege changes, and bulk exports.
-   Restrict direct database write access in production.

## 7. Privacy

-   Collect only data needed for tournament administration.
-   Keep date of birth, contact details, and private notes out of public
    endpoints.
-   Define retention and deletion policies consistent with applicable
    law and tournament obligations.
-   Provide a privacy notice and lawful basis review for personal data
    processing.
-   Avoid exposing full audit payloads to public users.

## 8. Web security

-   HTTPS only in production.
-   Secure headers: CSP, HSTS, X-Content-Type-Options, Referrer-Policy,
    and appropriate frame restrictions.
-   Escape user-generated text.
-   Prevent stored/reflected XSS.
-   Apply CSRF protections as appropriate.
-   Restrict CORS to known frontend origins.
-   Protect exports from IDOR and path traversal.
-   Use signed, short-lived download URLs where object storage is used.

## 9. Secrets and supply chain

-   Never commit `.env`, credentials, private keys, or real player data.
-   Commit `.env.example` with placeholders only.
-   Pin dependencies and review automated vulnerability alerts.
-   Use lockfiles.
-   Scan containers and dependencies in CI.
-   Run application containers as non-root where feasible.
-   Separate development and production secrets.

## 10. Incident response

Document: - how to revoke sessions/service credentials; - how to disable
public access; - how to restore database backups; - how to investigate
result/pairing audit events; - how to notify affected users where
legally required.
