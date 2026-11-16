# 10. Docker and Local Development

## 1. Services

Local Docker Compose should provide: - `frontend`: Vite development
server; - `backend`: Laravel PHP application; - `queue`: Laravel queue
worker; - `pairing-engine`: FastAPI service on an internal network; -
`postgres`: persistent PostgreSQL database; - `redis`: queue/cache; -
optional `mailpit`: local email capture.

Use named volumes for PostgreSQL and dependency caches. Do not expose
PostgreSQL/Redis publicly in production.

## 2. Compose blueprint

This is a starter topology. Copilot must adapt build contexts,
Dockerfiles, health checks, and commands to the actual scaffold and
pinned dependency versions.

``` yaml
services:
  frontend:
    build:
      context: ./frontend
    ports:
      - "5173:5173"
    environment:
      VITE_API_BASE_URL: http://localhost:8000/api/v1
    depends_on:
      backend:
        condition: service_started

  backend:
    build:
      context: ./backend
    ports:
      - "8000:8000"
    env_file:
      - .env
    environment:
      DB_HOST: postgres
      REDIS_HOST: redis
      PAIRING_ENGINE_URL: http://pairing-engine:8001
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - app
      - internal

  queue:
    build:
      context: ./backend
    command: php artisan queue:work --tries=3 --timeout=120
    env_file:
      - .env
    environment:
      DB_HOST: postgres
      REDIS_HOST: redis
      PAIRING_ENGINE_URL: http://pairing-engine:8001
    depends_on:
      - backend
      - redis
    networks:
      - internal

  pairing-engine:
    build:
      context: ./pairing-engine
    expose:
      - "8001"
    environment:
      APP_ENV: local
      LOG_LEVEL: INFO
    networks:
      - internal

  postgres:
    image: postgres:17
    environment:
      POSTGRES_DB: openswiss
      POSTGRES_USER: openswiss
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U openswiss -d openswiss"]
      interval: 5s
      timeout: 5s
      retries: 10
    networks:
      - app
      - internal

  redis:
    image: redis:7-alpine
    command: ["redis-server", "--appendonly", "yes"]
    volumes:
      - redis_data:/data
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 5s
      timeout: 3s
      retries: 10
    networks:
      - internal

volumes:
  postgres_data:
  redis_data:

networks:
  app:
  internal:
    internal: true
```

Before using this compose file, verify image versions against the
project's support policy. Production deployment needs a reverse
proxy/TLS, secrets management, backups, monitoring, and non-root
containers.

## 3. Environment variables

`.env.example` should contain placeholders for: - `APP_ENV`, `APP_KEY`,
`APP_URL`; - database host/name/user/password; - Redis host/password; -
pairing engine internal URL and service token; - mail settings; - file
storage settings; - log level; - allowed frontend origin.

Never include real credentials. Generate a new Laravel application key
locally.

## 4. First-time setup sequence

1.  Copy `.env.example` to `.env`.
2.  Set unique local secrets and database password.
3.  Build images.
4.  Start PostgreSQL and Redis.
5.  Wait for health checks.
6.  Install backend dependencies and generate application key.
7.  Run migrations.
8.  Start queue worker.
9.  Start pairing engine.
10. Start frontend.
11. Run health checks and test suite.

Suggested commands (adapt to actual scripts):

``` bash
docker compose build
docker compose up -d postgres redis
docker compose up -d
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
docker compose exec backend php artisan test
docker compose exec pairing-engine pytest
```

## 5. Development rules

-   Keep local ports documented.
-   Use health checks and startup retries.
-   Do not rely on `depends_on` alone as proof a service is ready.
-   Use migrations, not manual schema edits.
-   Seed synthetic demo data only.
-   Never use real player personal data in committed fixtures.
-   Keep pairing engine private to the Docker network.
-   Provide a one-command reset for local development, with a clear
    warning that it deletes local data.
