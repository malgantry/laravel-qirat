from datetime import date, datetime
from models import Goal, Transaction, FeedbackItem, FinancialHealthInput
from typing import List, Dict, Any
from database import get_user_weights
import uuid
import joblib
import os
import random

from text_generator import MessageGenerator
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load Model
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "models", "financial_model.pkl")

try:
    ml_model = joblib.load(MODEL_PATH)
except FileNotFoundError:
    ml_model = None
    logger.warning("ML model not found at %s. Run train_model.py first.", MODEL_PATH)
except Exception as e:
    ml_model = None
    logger.error("Error loading ML model: %s", e)

class AIFinancialAnalyzer:
    """
    Main class for financial analysis logic.
    Separates concern of text generation to MessageGenerator.
    """

    @staticmethod
    def predict_health(data: FinancialHealthInput) -> dict:
        """
        Predicts financial health score and persona based on input data.
        
        Args:
            data (FinancialHealthInput): Financial data input.
            
        Returns:
            dict: Prediction results including score, persona, and class_id.
        """
        if not ml_model:
            return {"score": -1, "status": "Model not loaded", "error": "ML Model is not available"}
        
        try:
            vector = [
                data.monthly_spending_ratio, data.savings_ratio, data.income_level,
                data.fixed_commitments_ratio, data.goal_progress_ratio, data.goal_delay_ratio,
                data.expense_frequency, data.spending_variance, data.emergency_fund_ratio,
                data.income_growth_rate, data.income_stability, data.risk_level
            ]
            
            prediction = ml_model.predict([vector])[0]
            
            personas = {
                0: "Balanced Saver", 1: "High Earner / High Saver", 2: "Standard Planner",
                3: "Risky Spender", 4: "Aggressive Saver", 5: "Investor",
                6: "Conservative", 7: "Living Paycheck to Paycheck", 8: "Growth Focused", 9: "Stable"
            }
            
            return {
                "class_id": int(prediction.item() if hasattr(prediction, 'item') else prediction),
                "persona": personas.get(int(prediction), "Unknown"),
                "health_score": int(float(min(100, (prediction + 1) * 10)))
            }
        except Exception as e:
            logger.error("Error in predict_health: %s", e)
            return {"score": -1, "status": "Error", "error": str(e)}

    @staticmethod
    def analyze_goal(goal: Goal) -> List[FeedbackItem]:
        """
        Analyzes a financial goal and generates feedback.
        
        Args:
            goal (Goal): The financial goal to analyze.
            
        Returns:
            List[FeedbackItem]: A list of feedback items sorted by priority/score.
        """
        feedbacks = []
        try:
            today = date.today()
            user_weights = get_user_weights(goal.user_id)
            tone_level = AIFinancialAnalyzer._tone_from_persona(None)
            
            progress_percentage = (goal.current_amount / goal.target_amount) * 100 if goal.target_amount > 0 else 0

            # --- 1. Encouragement ---
            if 90 <= progress_percentage < 100:
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="goal_near_completion",
                    message=MessageGenerator.generate_goal_message("goal_near", tone="success", tone_level=tone_level, title=goal.title, percent=f"{100-progress_percentage:.1f}"),
                    priority=1,
                    score=user_weights.get("goal_near_completion", 1.5)
                ))
            elif progress_percentage >= 100:
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="goal_completed",
                    message=MessageGenerator.generate_goal_message("goal_done", tone="success", tone_level=tone_level, title=goal.title),
                    priority=1,
                    score=user_weights.get("goal_completed", 2.0)
                ))

            # --- 2. Deadline Warnings ---
            if goal.deadline:
                days_left = (goal.deadline - today).days
                if 0 < days_left <= 7:
                    feedbacks.append(FeedbackItem(
                        id=str(uuid.uuid4()),
                        type="deadline_warning",
                        message=MessageGenerator.generate_goal_message("deadline_warn", tone="warning", tone_level=tone_level, title=goal.title, days=days_left),
                        priority=2,
                        score=user_weights.get("deadline_warning", 1.8)
                    ))
                    
                    amount_left = goal.target_amount - goal.current_amount
                    daily_needed = amount_left / days_left
                    feedbacks.append(FeedbackItem(
                        id=str(uuid.uuid4()),
                        type="daily_savings_target",
                        message=MessageGenerator.generate_goal_message("daily_amount", tone="info", tone_level=tone_level, title=goal.title, amount=f"{daily_needed:.2f}"),
                        priority=1,
                        score=user_weights.get("daily_savings_target", 1.2)
                    ))

            # --- 3. Early Achievement Recognition ---
            if progress_percentage > 20 and goal.created_at and (datetime.now().date() - goal.created_at.date()).days < 14:
                 feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="early_bird",
                    message=MessageGenerator.generate_goal_message("early_bird", tone="success", tone_level=tone_level, title=goal.title),
                    priority=1,
                    score=user_weights.get("early_bird", 1.3)
                ))

            # --- 4. Stagnation Warning ---
            if progress_percentage < 100 and (not goal.updated_at or (datetime.now() - goal.updated_at.replace(tzinfo=None)).days > 15):
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="stagnation_warning",
                    message=MessageGenerator.generate_goal_message("stagnant", tone="warning", tone_level=tone_level, title=goal.title),
                    priority=2,
                    score=user_weights.get("stagnation_warning", 1.6)
                ))

            # --- 5. Default Encouragement ---
            if not feedbacks and progress_percentage > 0:
                 feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="generic_encouragement",
                    message=MessageGenerator.generate_goal_message("generic", tone="info", tone_level=tone_level, title=goal.title),
                    priority=0,
                    score=user_weights.get("generic_encouragement", 1.0)
                ))

            return sorted(feedbacks, key=lambda x: x.score, reverse=True)
            
        except Exception as e:
            logger.error("Error in analyze_goal: %s", e)
            return []

    @staticmethod
    def analyze_transaction(transaction: Transaction, context: Dict[str, Any] = {}) -> List[FeedbackItem]:
        """
        Analyzes a transaction and generates feedback based on context.
        
        Args:
            transaction (Transaction): The transaction to analyze.
            context (Dict[str, Any], optional): Additional context like budget status. Defaults to {}.
            
        Returns:
            List[FeedbackItem]: A list of feedback items.
        """
        feedbacks = []
        try:
            user_weights = context.get("user_weights", {})
            tone_level = AIFinancialAnalyzer._tone_from_persona(context.get("persona"))

            # --- 0. Pre-Calculation from Features (if context missing) ---
            if not context.get('budget_status') and transaction.features and len(transaction.features) >= 3:
                amount = transaction.features[0]
                limit = transaction.features[1]
                ratio = transaction.features[2]
                
                if limit > 0:
                    if ratio > 1.0:
                        context['budget_status'] = 'exceeded'
                        context['over_amount'] = amount - limit
                    elif ratio > 0.85:
                        context['budget_status'] = 'near_limit'
                
                # Simple Large Expense Detection
                if transaction.type == 'expense' and amount > 500:
                     context['burn_rate'] = 2.0 # Force warning

            # 1. Budget Overrun
            if context.get("budget_status") == "exceeded":
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="budget_overrun",
                    message=MessageGenerator.generate_transaction_message("budget_exceeded", tone="warning", tone_level=tone_level, category=transaction.category, amount=f"{context.get('over_amount', 0):.2f}"),
                    priority=2,
                    score=user_weights.get("budget_overrun", 1.9)
                ))
            elif context.get("budget_status") == "near_limit":
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="near_budget_limit",
                    message=MessageGenerator.generate_transaction_message("near_budget_limit", tone="warning", tone_level=tone_level, category=transaction.category),
                    priority=1,
                    score=user_weights.get("near_budget_limit", 1.5)
                ))

            # 2. Saving Efficiency
            if transaction.type == "expense" and context.get("is_lower_than_avg", False):
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="saving_achievement",
                    message=MessageGenerator.generate_transaction_message("saving_hero", tone="success", tone_level=tone_level, category=transaction.category),
                    priority=1,
                    score=user_weights.get("saving_achievement", 1.4)
                ))

            # 3. High Burn Rate
            if context.get("burn_rate", 0) > 1.5:
                 feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="high_burn_rate",
                    message=MessageGenerator.generate_transaction_message("burn_rate_warning", tone="warning", tone_level=tone_level),
                    priority=2,
                    score=user_weights.get("high_burn_rate", 1.7)
                ))

            # 4. Discipline
            if context.get("is_disciplined", False):
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="discipline_affirmation",
                    message=MessageGenerator.generate_transaction_message("financial_discipline", tone="success", tone_level=tone_level),
                    priority=1,
                    score=user_weights.get("discipline_affirmation", 1.2)
                ))

            # 5. Exceptional Income
            if transaction.type == "income" and transaction.amount > context.get("avg_income", 0) * 2:
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="exceptional_income",
                    message=MessageGenerator.generate_transaction_message("large_income", tone="success", tone_level=tone_level, amount=f"{transaction.amount:.2f}"),
                    priority=1,
                    score=user_weights.get("exceptional_income", 1.6)
                ))

            # 6. Fallback
            if not feedbacks:
                feedbacks.append(FeedbackItem(
                    id=str(uuid.uuid4()),
                    type="general_observation",
                    message=MessageGenerator.generate_transaction_message("general_observation", tone="info", tone_level=tone_level, category=transaction.category),
                    priority=5,
                    score=0.5
                ))

            return sorted(feedbacks, key=lambda x: x.score, reverse=True)
            
        except Exception as e:
            logger.error("Error in analyze_transaction: %s", e)
            return []

    @staticmethod
    def _tone_from_persona(persona: Any) -> str:
        """
        Maps persona label to tone level. Defaults to balanced.
        """
        persona_map = {
            "Risky Spender": "firm",
            "Living Paycheck to Paycheck": "firm",
            "Balanced Saver": "motivating",
            "High Earner / High Saver": "motivating",
            "Aggressive Saver": "motivating",
            "Investor": "motivating",
            "Growth Focused": "motivating",
            "Stable": "balanced",
            "Standard Planner": "balanced",
            "Conservative": "balanced",
        }
        if not persona:
            return MessageGenerator.DEFAULT_TONE
        return persona_map.get(str(persona), MessageGenerator.DEFAULT_TONE)
