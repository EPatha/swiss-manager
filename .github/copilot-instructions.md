# OpenSwiss - GitHub Copilot Instructions

## Project Identity

OpenSwiss is an open-source web application for managing chess tournaments
using FIDE Swiss System rules. The application aims to provide a complete
alternative to traditional chess tournament management software.

## Technology Stack

### Frontend

- React
- TypeScript
- Vite
- Tailwind CSS

### Backend

- Laravel
- PHP
- REST API

### Pairing Engine

- Python
- FastAPI
- Pydantic

### Infrastructure

- PostgreSQL
- Docker
- Docker Compose
- Redis

## Architecture Rules

1. The frontend communicates with the Laravel REST API.
2. Laravel owns tournament business logic.
3. Python owns Swiss pairing calculations.
4. PostgreSQL is the authoritative data store.
5. Redis handles background jobs.
6. All services must have clear responsibilities.
7. Never duplicate business logic between services.

## Chess Rules

1. Implement FIDE Dutch System pairing.
2. Follow the applicable FIDE Handbook.
3. Never allow repeat opponents.
4. Track player colour history.
5. Validate bye eligibility.
6. Support score groups and floaters.
7. Implement configurable tie-break methods.
8. Preserve pairing history.
9. Never silently accept invalid pairings.
10. Keep pairing algorithm versions traceable.

Do not implement a simplified pairing algorithm and describe it as
FIDE-compliant.

## Development Rules

1. Write clean, modular, maintainable code.
2. Follow SOLID principles where appropriate.
3. Use database migrations.
4. Validate all incoming API requests.
5. Use transactions for critical tournament operations.
6. Add tests for every important business rule.
7. Use TypeScript types for frontend data.
8. Document complex algorithms.
9. Never hardcode tournament data.
10. Never modify existing functionality without considering its impact.

## Workflow

Before implementing a feature:

1. Read the relevant documentation in [`docs/`](../docs/).
2. Understand the existing architecture.
3. Identify affected modules.
4. Propose an implementation plan.
5. Implement the feature.
6. Write automated tests.
7. Run relevant tests.
8. Report completed work and remaining issues.

## Important

- Do not generate the entire application in one response.
- Build incrementally, following the development roadmap.
- Do not invent FIDE rules or claim compliance without tests.
