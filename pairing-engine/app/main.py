from fastapi import FastAPI

app = FastAPI(title="OpenSwiss Pairing Engine", version="0.1.0")


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok", "service": "pairing-engine"}


@app.get("/ready")
def ready() -> dict[str, str]:
    return {"status": "ready", "service": "pairing-engine"}
