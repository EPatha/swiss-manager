# Database Design

The database stores users, tournaments, players, rounds, games, results, and
pairing decisions. Schema changes should be versioned through migrations.

## Design principles

- Use stable identifiers for persisted entities.
- Preserve tournament and pairing history rather than overwriting audit data.
- Enforce relational integrity at the database boundary.
- Store timestamps in UTC.
