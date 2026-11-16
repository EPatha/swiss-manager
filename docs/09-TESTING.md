# Testing

Tests should cover domain behavior first, especially pairing, scoring, colors,
byes, tie-breaks, and edge cases.

## Test layers

- Unit tests for pairing-engine rules and pure domain logic.
- Integration tests for API, persistence, and service boundaries.
- End-to-end tests for critical organizer workflows.

Tests must be deterministic and should include regression cases for every
pairing defect.
