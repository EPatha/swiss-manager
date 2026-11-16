# OpenSwiss

**OpenSwiss** is an open-source, web-based tournament management system for
individual Swiss chess tournaments. The initial pairing target is the
**FIDE Dutch System**.

This repository is a documentation-first blueprint for VS Code Copilot. It
defines the system boundaries, data model, API contracts, pairing-engine
requirements, UI, security, tests, deployment, and implementation sequence.

> **Important:** OpenSwiss is not FIDE-approved or certified. The pairing
> engine must not be advertised as FIDE-compliant until its implementation has
> been independently validated against the applicable FIDE rules and reference
> pairings, and any required approval has been obtained.

## Start here

1. Read [.github/copilot-instructions.md](./.github/copilot-instructions.md).
2. Read [docs/01-PROJECT-OVERVIEW.md](./docs/01-PROJECT-OVERVIEW.md).
3. Read [docs/02-SYSTEM-ARCHITECTURE.md](./docs/02-SYSTEM-ARCHITECTURE.md).
4. Read
   [docs/05-FIDE-DUTCH-PAIRING-SPECIFICATION.md](./docs/05-FIDE-DUTCH-PAIRING-SPECIFICATION.md)
   before implementing pairings.
5. Follow [docs/13-DEVELOPMENT-ROADMAP.md](./docs/13-DEVELOPMENT-ROADMAP.md)
   one milestone at a time.
6. Give Copilot one task from
   [docs/14-COPILOT-PROMPTS.md](./docs/14-COPILOT-PROMPTS.md) at a time.

The detailed specification documents referenced above are being added
incrementally. Do not infer missing requirements from this README.

## Technology baseline

| Layer | Technology | Responsibility |
| --- | --- | --- |
| Web client | React, TypeScript, Vite, Tailwind CSS | Minimal responsive UI |
| Application API | Laravel, PHP | Workflow, authorization, validation, persistence |
| Pairing service | Python, FastAPI, Pydantic | Deterministic pairing calculations |
| Database | PostgreSQL | Authoritative persistent data |
| Queue/cache | Redis | Background work and cache |
| Local environment | Docker Compose | Repeatable development stack |

Use stable, mutually compatible releases at implementation time. Pin versions
in lockfiles and container images. Do not blindly copy an old version number
from this document.

## Repository map

```text
openswiss/
├── .github/
│   └── copilot-instructions.md
├── docs/
│   ├── 01-PROJECT-OVERVIEW.md
│   ├── 02-SYSTEM-ARCHITECTURE.md
│   ├── 03-DATABASE-DESIGN.md
│   ├── 04-API-CONTRACTS.md
│   ├── 05-FIDE-DUTCH-PAIRING-SPECIFICATION.md
│   ├── 06-FIDE-RULES-REGISTER.md
│   ├── 07-UI-UX-SPECIFICATION.md
│   ├── 08-SECURITY.md
│   ├── 09-TESTING-STRATEGY.md
│   ├── 10-DOCKER-AND-LOCAL-DEVELOPMENT.md
│   ├── 11-OPERATIONS-AND-OBSERVABILITY.md
│   ├── 12-EXPORTS-AND-INTEROPERABILITY.md
│   ├── 13-DEVELOPMENT-ROADMAP.md
│   └── 14-COPILOT-PROMPTS.md
├── frontend/
├── backend/
├── pairing-engine/
├── infra/
├── docker-compose.yml
├── .env.example
├── LICENSE
└── README.md
```

## Getting started

Create local environment configuration and start the development stack:

```bash
cp .env.example .env
docker compose build
docker compose up -d
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

The frontend is available at <http://localhost:5173>, the Laravel API at
<http://localhost:8000>, and the pairing service is private to the Docker
network. The current identity foundation uses Laravel Sanctum bearer tokens.

Run checks locally:

```bash
cd backend && php artisan test
cd ../frontend && npm ci && npm run build && npm run lint
cd ../pairing-engine && python -m pytest
```

## Product principles

- Correctness before convenience.
- Reproducibility before clever heuristics.
- Explicit rules and audit trails before silent edits.
- The arbiter remains in control of tournament decisions.
- Never silently modify published pairings.
- Never erase historical pairing runs or result corrections.
- Never label a heuristic pairing as FIDE Dutch.
- Treat public standings as a read-only projection of authoritative tournament
  data.

## License

A network-copyleft license such as **AGPL-3.0-or-later** is a reasonable
default if the goal is to keep hosted modifications available to the
community. Confirm the license choice before the first public release.

The current repository license remains the license in [LICENSE](./LICENSE)
until that decision is made. Do not replace it with a substitute license text.

## Official rule references

- [FIDE Handbook: C.04.1 Basic Rules for Swiss Systems](https://handbook.fide.com/)
- [FIDE Handbook: C.04.2 General Handling Rules for Swiss Tournaments](https://handbook.fide.com/chapter/GeneralHandlingRulesForSwissTournaments202602)
- [FIDE Handbook: C.04.3 FIDE (Dutch) System, effective 1 February 2026](https://handbook.fide.com/chapter/C0403202602)
- [FIDE Handbook: Play-Off and Tie-Break Regulations, effective 1 March 2026](https://handbook.fide.com/chapter/TieBreakRegulations032026)

Always verify the live FIDE Handbook before implementing or releasing a
rule-sensitive version. See the FIDE rules register when it is added.
