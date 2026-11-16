# Swiss Pairing Engine

The pairing engine produces pairings from tournament state, player
availability, scores, colors, and prior opponents.

## Requirements

- Pairing must be deterministic for the same input and configuration.
- Players must not be paired against a previous opponent when an alternative
  valid pairing exists.
- Color allocation and bye assignment must be explicit and explainable.
- Every pairing decision should be testable independently of the API.
