# System Architecture

OpenSwiss is organized into three primary components:

- **Frontend**: browser-based tournament management interface.
- **Backend**: API, authentication, persistence, and orchestration.
- **Pairing engine**: isolated domain service for Swiss pairing calculations.

The backend is the integration boundary between the frontend, database, and
pairing engine. Components should communicate through explicit, versioned
interfaces.
