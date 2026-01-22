import unittest
from datetime import date, timedelta, datetime
from models import Goal
from analyzer import AIFinancialAnalyzer

class TestAIFinancialAnalyzer(unittest.TestCase):

    def test_deadline_warning(self):
        # Goal due in 3 days
        goal = Goal(
            id="1", 
            user_id="user1", 
            title="Short Term Goal", 
            target_amount=1000.0, 
            current_amount=100.0, 
            deadline=date.today() + timedelta(days=3)
        )
        
        feedbacks = AIFinancialAnalyzer.analyze_goal(goal)
        
        # Should have a deadline warning
        warnings = [f for f in feedbacks if f.type == 'deadline_warning']
        self.assertTrue(len(warnings) > 0, "Should generate a deadline warning for goals due in < 7 days")

    def test_goal_completion(self):
        # Goal matched
        goal = Goal(
            id="2", 
            user_id="user1", 
            title="Done Goal", 
            target_amount=1000.0, 
            current_amount=1000.0, 
            deadline=date.today() + timedelta(days=10)
        )
        
        feedbacks = AIFinancialAnalyzer.analyze_goal(goal)
        
        completed = [f for f in feedbacks if f.type == 'goal_completed']
        self.assertTrue(len(completed) > 0, "Should generate completion message")

    def test_early_bird_success(self):
        # 30% progress, 40 days remaining
        goal = Goal(
            id="3", 
            user_id="user1", 
            title="Early Goal", 
            target_amount=1000.0, 
            current_amount=300.0, 
            deadline=date.today() + timedelta(days=40),
            created_at=datetime.now() # Needed for early bird check
        )
        
        feedbacks = AIFinancialAnalyzer.analyze_goal(goal)
        
        early = [f for f in feedbacks if f.type == 'early_bird']
        self.assertTrue(len(early) > 0, "Should detect early bird success")

if __name__ == '__main__':
    unittest.main()
