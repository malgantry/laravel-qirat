import os
from typing import List, Optional

import numpy as np
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException, Request
from pydantic import BaseModel, Field
TF_AVAILABLE = True
try:
    import tensorflow as tf
except Exception:
    TF_AVAILABLE = False

load_dotenv()

MODEL_PATH = os.getenv("MODEL_PATH", "models/financial_recommendation_model.tflite")
METADATA_PATH = os.getenv("METADATA_PATH", "models/model_metadata.json")
API_KEY = os.getenv("API_KEY")

app = FastAPI(title="AI Service", version="0.1.0")


class PredictRequest(BaseModel):
    features: List[float] = Field(..., description="Flat feature vector matching the model input shape")


class PredictResponse(BaseModel):
    predictions: List[float]
    top_class: Optional[int]


def load_interpreter():
    if not TF_AVAILABLE:
        return None
    if not os.path.exists(MODEL_PATH):
        return None
    try:
        interpreter = tf.lite.Interpreter(model_path=MODEL_PATH)
        interpreter.allocate_tensors()
        return interpreter
    except Exception:
        return None


interpreter = load_interpreter()
input_details = interpreter.get_input_details() if interpreter is not None else [{"shape": [1, 12], "index": 0}]
output_details = interpreter.get_output_details() if interpreter is not None else [{"index": 0}]

"""
Optional: read model metadata for expected input size
Supports keys: input_length, inputShape, input_shape
"""
EXPECTED_INPUT_SIZE = None
_METADATA_CACHE = None
try:
    if METADATA_PATH and os.path.exists(METADATA_PATH):
        import json
        with open(METADATA_PATH, 'r', encoding='utf-8') as f:
            _METADATA_CACHE = json.load(f)
        if isinstance(_METADATA_CACHE, dict):
            if 'input_length' in _METADATA_CACHE and isinstance(_METADATA_CACHE['input_length'], (int, float)):
                EXPECTED_INPUT_SIZE = int(_METADATA_CACHE['input_length'])
            elif 'inputShape' in _METADATA_CACHE and isinstance(_METADATA_CACHE['inputShape'], (list, tuple)):
                import numpy as _np
                EXPECTED_INPUT_SIZE = int(_np.prod(_np.array(_METADATA_CACHE['inputShape'])))
            elif 'input_shape' in _METADATA_CACHE and isinstance(_METADATA_CACHE['input_shape'], (list, tuple)):
                import numpy as _np
                EXPECTED_INPUT_SIZE = int(_np.prod(_np.array(_METADATA_CACHE['input_shape'])))
except Exception:
    # Metadata is optional; ignore errors
    EXPECTED_INPUT_SIZE = None


def _check_auth(req: Request):
    if API_KEY:
        hdr = req.headers.get("X-API-KEY")
        if not hdr or hdr != API_KEY:
            raise HTTPException(status_code=401, detail="Unauthorized")


def _apply_normalization(x: np.ndarray) -> np.ndarray:
    """
    Apply feature normalization if present in metadata.
    Supported formats in metadata:
    {
      "normalization": {
         "type": "standard", "mean": [...], "std": [...]
      }
    }
    or
    {
      "normalization": {
         "type": "minmax", "min": [...], "max": [...]
      }
    }
    """
    try:
        if isinstance(_METADATA_CACHE, dict) and 'normalization' in _METADATA_CACHE:
            norm = _METADATA_CACHE['normalization']
            ntype = norm.get('type', '').lower()
            if ntype == 'standard' and 'mean' in norm and 'std' in norm:
                mean = np.array(norm['mean'], dtype=np.float32)
                std = np.array(norm['std'], dtype=np.float32)
                if mean.size == x.size and std.size == x.size:
                    # avoid division by zero
                    std_safe = np.where(std == 0, 1.0, std)
                    return (x - mean) / std_safe
            elif ntype == 'minmax' and 'min' in norm and 'max' in norm:
                vmin = np.array(norm['min'], dtype=np.float32)
                vmax = np.array(norm['max'], dtype=np.float32)
                if vmin.size == x.size and vmax.size == x.size:
                    denom = np.where((vmax - vmin) == 0, 1.0, (vmax - vmin))
                    return (x - vmin) / denom
    except Exception:
        pass
    return x


@app.get("/health")
async def health(req: Request):
    _check_auth(req)
    return {"status": "ok"}


@app.get("/metadata")
async def metadata(req: Request):
    _check_auth(req)
    try:
        import json
        if METADATA_PATH and os.path.exists(METADATA_PATH):
            with open(METADATA_PATH, 'r', encoding='utf-8') as f:
                data = json.load(f)
            return data
        raise HTTPException(status_code=404, detail="Metadata file not found")
    except HTTPException:
        raise
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc


@app.post("/predict", response_model=PredictResponse)
async def predict(payload: PredictRequest, req: Request):
    _check_auth(req)
    try:
        x = np.array(payload.features, dtype=np.float32)
        input_shape = input_details[0]["shape"]
        input_size = int(np.prod(input_shape))
        expected = EXPECTED_INPUT_SIZE or input_size
        if x.size != expected:
            raise HTTPException(
                status_code=400,
                detail=f"Expected feature length {expected} (model shape {input_shape}), got {x.size}",
            )

        # Apply normalization if provided by metadata
        x = _apply_normalization(x)

        if interpreter is None:
            # Dummy fallback: produce a deterministic softmax based on input mean
            out_size = None
            try:
                if isinstance(_METADATA_CACHE, dict) and 'output_shape' in _METADATA_CACHE:
                    shp = _METADATA_CACHE['output_shape']
                    if isinstance(shp, (list, tuple)) and len(shp) > 0 and isinstance(shp[0], (int, float)):
                        out_size = int(shp[0])
            except Exception:
                pass
            if out_size is None:
                out_size = 3
            mean_val = float(np.mean(x)) if x.size > 0 else 0.0
            logits = np.arange(out_size, dtype=np.float32) + 0.1 * mean_val
            exps = np.exp(logits - np.max(logits))
            preds_np = exps / np.sum(exps)
            preds = preds_np.tolist()
            top_class = int(np.argmax(preds)) if preds else None
            return PredictResponse(predictions=preds, top_class=top_class)

        x = x.reshape(input_shape)
        interpreter.set_tensor(input_details[0]["index"], x)
        interpreter.invoke()

        output_data = interpreter.get_tensor(output_details[0]["index"])
        preds = output_data.flatten().tolist()
        top_class = int(np.argmax(preds)) if preds else None

        return PredictResponse(predictions=preds, top_class=top_class)
    except HTTPException:
        raise
    except Exception as exc:  # minimal safe guard; extend for logging/metrics
        raise HTTPException(status_code=500, detail=str(exc)) from exc


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=int(os.getenv("PORT", "8001")), reload=True)
