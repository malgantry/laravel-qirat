## Qirat Personal Finance (Laravel + FastAPI AI)

A Laravel 12 application for personal finance tracking with AI-powered insights from the bundled FastAPI service (`qirat_ai_api`).

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- Python 3.10+ (for the AI service)

### Environment
Copy `.env.example` to `.env` and set at least:

- `AI_SERVICE_URL` (default `http://localhost:8001`)
- `AI_SERVICE_TIMEOUT` (seconds)
- `AI_API_KEY` (optional shared secret between UI and AI API)
- `AI_MODEL_DIR` (path to ML artifacts; default `./models` or `qirat_ai_api/models`)
- `APP_URL`, database connection, mail settings as needed

### Install & Run (Laravel)
```bash
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev # or npm run build for production
php artisan serve
```

PDF export depends on `barryvdh/laravel-dompdf` (declared in composer.json). If you skipped `composer install`, run `composer require barryvdh/laravel-dompdf` before using PDF downloads.

### Install & Run (AI service)
```bash
cd qirat_ai_api
python -m venv .venv && .venv\Scripts\activate  # Windows PowerShell
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001
```

### Install & Run (Docker)
This project includes a `docker-compose.yml` for running the full stack (Laravel + MySQL + AI Service).

```bash
docker-compose up --build
```

### Project Structure
- `qirat_ai_api/`: The Python FastAPI service for AI insights.
- `scripts/`: Helper scripts (e.g., `generate_financial_training_data.py`).
- `docs/`: Documentation files (e.g., API PDF).


Optional environment for the AI service:
- `QIRAT_AI_DB` to choose the learning DB path (defaults to `qirat_learning.db` inside `qirat_ai_api`).
- `AI_MODEL_DIR` if you place models elsewhere (e.g., the repo-level `models/financial_recommendation_model.tflite`). Point `AI_SERVICE_URL` in Laravel to the running FastAPI instance.

### Data Model Notes
- Transactions, goals, and budgets are scoped to the authenticated user (`user_id`), including queries in reports/exports.
- Budgets link to user-owned categories only.
- The AI client now sends a fixed 12-feature vector for transactions/goals to match the training schema in `financial_training_data.csv`.

### Frontend
Vite is configured; use `npm run dev` during development. For production assets, run `npm run build` before deployment.
