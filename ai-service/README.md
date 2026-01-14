# AI Service (FastAPI + TFLite)

This service hosts the TensorFlow Lite model and exposes an HTTP API for Laravel.

## Structure
- `app/main.py`: FastAPI app loading the TFLite model and exposing `/predict`, `/health`, `/metadata`.
- `models/financial_recommendation_model.tflite`: Place the exported model here (copy from Android assets).
- `.env`: Configure `MODEL_PATH`, `PORT`, `METADATA_PATH`, and optional `API_KEY`.

## Setup
1) Create and activate a Python 3.10+ virtual environment.
2) Install deps:
   ```bash
   pip install -r requirements.txt
   ```
3) Copy the model from Android assets:
   ```bash
   cp ../qiratae_updated/app/src/main/assets/models/financial_recommendation_model.tflite models/
   ```
4) Run dev server:
   ```bash
   uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload
   ```

## API
- `GET /health`
  - Returns `{ "status": "ok" }`
- `GET /metadata`
  - Returns the content of `METADATA_PATH` (JSON)
- `POST /predict`
  - Body (example):
    ```json
    {
      "features": [0.0, 1.0, 2.0]
    }
    ```
  - Response:
    ```json
    {
      "predictions": [0.1, 0.2, 0.7],
      "top_class": 2
    }
    ```

## Notes
- Adjust preprocessing/postprocessing in `main.py` to match your real model inputs/outputs.
- If `API_KEY` is set in `.env`, requests must include header `X-API-KEY: <API_KEY>`.
- Consider securing the endpoint (API key, private network) before production.
