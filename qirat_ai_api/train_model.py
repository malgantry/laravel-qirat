import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.metrics import classification_report, accuracy_score, mean_squared_error
import joblib
import os

# Define paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_FILE = os.path.join(BASE_DIR, "financial_training_data.csv")
MODEL_FILE = os.path.join(BASE_DIR, "models", "financial_model.pkl")

def train():
    print("Loading data from:", DATA_FILE)
    if not os.path.exists(DATA_FILE):
        print(f"Error: {DATA_FILE} not found!")
        return

    df = pd.read_csv(DATA_FILE)
    
    # Features and Targets
    X = df.drop(columns=["target_class", "next_month_spending_ratio"])
    y_class = df["target_class"]

    # --- Train Classifier ---
    print("Training Classifier...")
    X_train, X_test, y_train, y_test = train_test_split(X, y_class, test_size=0.2, random_state=42)
    clf = RandomForestClassifier(n_estimators=100, random_state=42)
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)
    print("Classifier Accuracy:", accuracy_score(y_test, y_pred))

    # Save models
    os.makedirs(os.path.dirname(MODEL_FILE), exist_ok=True)
    joblib.dump(clf, MODEL_FILE)
    print(f"Classifier saved to {MODEL_FILE}")

if __name__ == "__main__":
    train()
