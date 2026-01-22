from pydantic import BaseModel
from typing import List, Optional, Dict
from datetime import date, datetime

class FeedbackItem(BaseModel):
    id: str # Unique ID for recommendation tracking
    type: str  # 'warning', 'info', 'success', 'goal_near_completion', etc.
    message: str
    action_type: Optional[str] = None
    priority: int
    score: float = 1.0 # Recommendation weight (learns over time)

class Goal(BaseModel):
    id: str
    user_id: str
    title: str
    target_amount: float
    current_amount: float
    deadline: Optional[date] = None
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None
    features: Optional[List[float]] = None

class Transaction(BaseModel):
    id: str
    user_id: str
    amount: float
    category: str
    type: str = "expense"
    description: str
    date: date
    budget_limit: Optional[float] = None
    features: Optional[List[float]] = None

class BatchTransactions(BaseModel):
    transactions: List[Transaction]

class BatchGoals(BaseModel):
    goals: List[Goal]

class BatchAnalysisResult(BaseModel):
    results: Dict[str, List[FeedbackItem]] # Keyed by ID

class UserFeedback(BaseModel):
    user_id: str
    feedback_id: str
    action: str # 'accepted', 'dismissed', 'ignored'
    feedback_type: Optional[str] = None

class AnalysisResult(BaseModel):
    feedbacks: List[FeedbackItem]

class FinancialHealthInput(BaseModel):
    monthly_spending_ratio: float
    savings_ratio: float
    income_level: float
    fixed_commitments_ratio: float
    goal_progress_ratio: float
    goal_delay_ratio: float
    expense_frequency: float
    spending_variance: float
    emergency_fund_ratio: float
    income_growth_rate: float
    income_stability: float
    risk_level: float
