import sqlite3
import os
from contextlib import contextmanager

DB_PATH = os.environ.get(
    "QIRAT_AI_DB",
    os.path.join(os.path.dirname(__file__), "qirat_learning.db")
)

@contextmanager
def get_db_connection():
    """
    Context manager for database connection.
    Ensures connection is closed properly even if an error occurs.
    """
    conn = None
    try:
        conn = sqlite3.connect(DB_PATH)
        yield conn
    except sqlite3.Error as e:
        print(f"Database error: {e}")
        # Re-raise or handle depending on requirements
        raise
    finally:
        if conn:
            conn.close()

def init_db():
    with get_db_connection() as conn:
        cursor = conn.cursor()
        # Table to store user interactions with recommendations
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS user_interactions (
                user_id TEXT,
                feedback_type TEXT,
                action TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ''')
        # Table to store weights for recommendations per user (Learning)
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS user_weights (
                user_id TEXT,
                feedback_type TEXT,
                weight REAL DEFAULT 1.0,
                PRIMARY KEY (user_id, feedback_type)
            )
        ''')
        conn.commit()

def log_interaction(user_id, feedback_type, action):
    """
    Logs user interaction and updates weights.
    
    Args:
        user_id (str): The user ID.
        feedback_type (str): The type of feedback.
        action (str): The action taken (accepted, dismissed, ignored).
    """
    with get_db_connection() as conn:
        cursor = conn.cursor()
        cursor.execute('INSERT INTO user_interactions (user_id, feedback_type, action) VALUES (?, ?, ?)', 
                       (user_id, feedback_type, action))
        
        # Update weight based on action
        # Accepted: +0.1, Dismissed: -0.1, Ignored: -0.05
        adjustment_map = {
            'accepted': 0.1,
            'dismissed': -0.1
        }
        adjustment = adjustment_map.get(action, -0.05)
        
        cursor.execute('''
            INSERT INTO user_weights (user_id, feedback_type, weight)
            VALUES (?, ?, 1.0 + ?)
            ON CONFLICT(user_id, feedback_type) DO UPDATE SET weight = weight + ?
        ''', (user_id, feedback_type, adjustment, adjustment))
        
        conn.commit()

def get_user_weights(user_id):
    """
    Retrieves user weights for feedback types.
    
    Args:
        user_id (str): The user ID.
        
    Returns:
        dict: A dictionary of feedback types and their weights.
    """
    with get_db_connection() as conn:
        cursor = conn.cursor()
        cursor.execute('SELECT feedback_type, weight FROM user_weights WHERE user_id = ?', (user_id,))
        weights = dict(cursor.fetchall())
        return weights

if __name__ == "__main__":
    init_db()
