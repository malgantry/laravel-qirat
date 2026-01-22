from fastapi import FastAPI, Body, HTTPException, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from models import Goal, Transaction, AnalysisResult, UserFeedback, FinancialHealthInput, BatchTransactions, BatchGoals, BatchAnalysisResult
from analyzer import AIFinancialAnalyzer
from database import log_interaction, init_db
import logging
import uvicorn

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)
logger = logging.getLogger("qirat_ai_api")

app = FastAPI(
    title="Qirat AI Advanced Learning API",
    description="API for financial analysis, goal tracking, and transaction feedback using AI.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc"
)

# Origins should be configured via environment variables in production
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    logger.error(f"Unhandled exception: {exc}", exc_info=True)
    return JSONResponse(
        status_code=500,
        content={"message": "Internal Server Error. Please contact support."}
    )

@app.on_event("startup")
async def startup_event():
    logger.info("Starting up Qirat AI API...")
    try:
        init_db()
        logger.info("Database initialized successfully.")
    except Exception as e:
        logger.critical(f"Failed to initialize database: {e}")

@app.get("/", tags=["Health"])
async def root():
    return {"message": "Welcome to Qirat AI Smart Learning API"}

@app.post("/predict/health", tags=["Prediction"], summary="Predict Financial Health")
async def predict_health(data: FinancialHealthInput):
    """
    Predicts the financial health score and persona for a user based on provided metrics.
    """
    try:
        return AIFinancialAnalyzer.predict_health(data)
    except Exception as e:
        logger.error(f"Error in predict_health: {e}")
        raise HTTPException(status_code=500, detail="Prediction failed")

@app.post("/analyze/goal", response_model=AnalysisResult, tags=["Analysis"], summary="Analyze Single Goal")
async def analyze_goal(goal: Goal):
    """
    Analyzes a single financial goal and provides encouragement or warnings.
    """
    feedbacks = AIFinancialAnalyzer.analyze_goal(goal)
    return AnalysisResult(feedbacks=feedbacks)

@app.post("/analyze/goals/batch", response_model=BatchAnalysisResult, tags=["Analysis"], summary="Analyze Batch Goals")
async def analyze_goals_batch(batch: BatchGoals):
    """
    Analyzes a batch of goals for efficiency.
    """
    results = {}
    for goal in batch.goals:
        results[goal.id] = AIFinancialAnalyzer.analyze_goal(goal)
    return BatchAnalysisResult(results=results)

@app.post("/analyze/transaction", response_model=AnalysisResult, tags=["Analysis"], summary="Analyze Single Transaction")
async def analyze_transaction(transaction: Transaction):
    """
    Analyzes a single transaction and checks for budget overruns or unusual spending.
    """
    feedbacks = AIFinancialAnalyzer.analyze_transaction(transaction)
    return AnalysisResult(feedbacks=feedbacks)

@app.post("/analyze/transactions/batch", response_model=BatchAnalysisResult, tags=["Analysis"], summary="Analyze Batch Transactions")
async def analyze_transactions_batch(batch: BatchTransactions):
    """
    Analyzes a batch of transactions.
    """
    results = {}
    for tx in batch.transactions:
        # Pass context empty if not provided, or enhance Batch model if context needed per tx
        results[tx.id] = AIFinancialAnalyzer.analyze_transaction(tx)
    return BatchAnalysisResult(results=results)

@app.post("/feedback", tags=["Feedback"], summary="Submit User Feedback")
async def submit_feedback(feedback: UserFeedback):
    """
    Records user feedback on recommendations to improve the AI model (RLHF).
    """
    try:
        # Use provided feedback_type or fallback to feedback_id if necessary
        f_type = feedback.feedback_type if feedback.feedback_type else feedback.feedback_id
        
        log_interaction(feedback.user_id, f_type, feedback.action)
        return {"status": "success", "message": "Feedback recorded, model is learning..."}
    except Exception as e:
        logger.error(f"Error recording feedback: {e}")
        raise HTTPException(status_code=500, detail="Failed to record feedback")

if __name__ == "__main__":
    # Use port 8001 to avoid conflict with Laravel (8000)
    uvicorn.run(app, host="0.0.0.0", port=8001)
