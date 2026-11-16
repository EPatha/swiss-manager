# OpenSwiss

OpenSwiss is an open-source chess tournament management platform with a
dedicated Swiss-system pairing engine.

## Repository layout

- [`frontend/`](./frontend/) — web application
- [`backend/`](./backend/) — API and application services
- [`pairing-engine/`](./pairing-engine/) — Swiss pairing domain logic
- [`docs/`](./docs/) — project, architecture, API, rules, and roadmap docs
- [`docker-compose.yml`](./docker-compose.yml) — local PostgreSQL service

## Getting started

The repository is currently scaffolded for implementation. Start the local
database with:

```bash
docker compose up -d database
```

See [`docs/10-DEVELOPMENT-ROADMAP.md`](./docs/10-DEVELOPMENT-ROADMAP.md) for
the planned implementation sequence.
