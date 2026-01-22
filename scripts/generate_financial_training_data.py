import csv
import random
from pathlib import Path

OUTPUT_FILE = Path("financial_training_data.csv")

features = [
    "monthly_spending_ratio",
    "savings_ratio",
    "income_level",
    "fixed_commitments_ratio",
    "goal_progress_ratio",
    "goal_delay_ratio",
    "expense_frequency",
    "spending_variance",
    "emergency_fund_ratio",
    "income_growth_rate",
    "income_stability",
    "risk_level",
]


def r(a: float, b: float) -> float:
    return round(random.uniform(a, b), 4)


labels = {
    0: lambda: [r(0.75, 0.95), r(0.01, 0.06), r(0.45, 0.60), r(0.65, 0.80), r(0.20, 0.35), r(0.50, 0.80), r(0.70, 0.90), r(0.70, 0.85), r(0.05, 0.15), r(-0.05, 0.00), r(0.30, 0.50), r(0.70, 0.90)],
    1: lambda: [r(0.90, 1.00), r(0.01, 0.05), r(0.40, 0.55), r(0.75, 0.90), r(0.15, 0.30), r(0.30, 0.50), r(0.80, 0.95), r(0.80, 0.95), r(0.05, 0.10), r(-0.03, 0.01), r(0.25, 0.40), r(0.80, 0.95)],
    2: lambda: [r(0.80, 0.90), r(0.05, 0.10), r(0.60, 0.75), r(0.50, 0.65), r(0.40, 0.55), r(0.10, 0.25), r(0.65, 0.80), r(0.60, 0.75), r(0.15, 0.25), r(0.00, 0.03), r(0.50, 0.65), r(0.55, 0.70)],
    3: lambda: [r(0.35, 0.50), r(0.30, 0.45), r(0.65, 0.80), r(0.20, 0.35), r(0.75, 0.90), r(0.00, 0.05), r(0.30, 0.45), r(0.20, 0.35), r(0.45, 0.60), r(0.05, 0.10), r(0.70, 0.85), r(0.20, 0.35)],
    4: lambda: [r(0.65, 0.80), r(0.08, 0.15), r(0.55, 0.70), r(0.45, 0.60), r(0.40, 0.55), r(0.65, 0.90), r(0.55, 0.70), r(0.50, 0.65), r(0.15, 0.25), r(0.00, 0.02), r(0.45, 0.60), r(0.60, 0.75)],
    5: lambda: [r(0.75, 0.90), r(0.05, 0.10), r(0.60, 0.75), r(0.45, 0.60), r(0.50, 0.65), r(0.05, 0.15), r(0.85, 1.00), r(0.85, 1.00), r(0.15, 0.25), r(0.00, 0.03), r(0.50, 0.65), r(0.65, 0.80)],
    6: lambda: [r(0.65, 0.80), r(0.01, 0.07), r(0.55, 0.65), r(0.45, 0.55), r(0.30, 0.40), r(0.05, 0.10), r(0.45, 0.55), r(0.35, 0.45), r(0.05, 0.15), r(0.00, 0.02), r(0.40, 0.55), r(0.50, 0.65)],
    7: lambda: [r(0.45, 0.60), r(0.20, 0.35), r(0.60, 0.75), r(0.30, 0.45), r(0.65, 0.85), r(0.00, 0.03), r(0.40, 0.55), r(0.30, 0.45), r(0.30, 0.45), r(0.03, 0.08), r(0.65, 0.80), r(0.30, 0.45)],
    8: lambda: [r(0.60, 0.75), r(0.15, 0.25), r(0.55, 0.65), r(0.35, 0.45), r(0.55, 0.65), r(0.05, 0.15), r(0.75, 0.90), r(0.65, 0.80), r(0.20, 0.30), r(0.01, 0.04), r(0.55, 0.65), r(0.45, 0.60)],
    9: lambda: [r(0.55, 0.65), r(0.18, 0.28), r(0.70, 0.85), r(0.25, 0.35), r(0.60, 0.70), r(0.00, 0.05), r(0.35, 0.45), r(0.30, 0.40), r(0.40, 0.55), r(0.08, 0.15), r(0.75, 0.90), r(0.30, 0.45)],
}


with open(OUTPUT_FILE, "w", newline="", encoding="utf-8") as f:
    writer = csv.writer(f)
    writer.writerow(features + ["target_class", "next_month_spending_ratio"])

    for label, generator in labels.items():
        for _ in range(1000):
            row = generator()
            # Logic for next month spending:
            # Base it on current spending + variance + randomness
            current_spending = row[0] # monthly_spending_ratio
            variance = row[7] # spending_variance
            stability = row[10] # income_stability
            
            change_factor = r(0.9, 1.1)
            if variance > 0.5:
                 change_factor = r(0.7, 1.4) # High variance = wilder swings
            
            next_month = current_spending * change_factor
            if stability < 0.5:
                next_month *= r(0.9, 1.2) # Unstable income might lead to erratic spending
            
            writer.writerow(row + [label, round(next_month, 4)])

print("CSV generated:", OUTPUT_FILE)
