import requests
import json
import os

# Default to AI service port 8001
port = os.getenv("AI_SERVICE_PORT", "8001")
host = os.getenv("AI_SERVICE_HOST", "localhost")
url = f"http://{host}:{port}/analyze/transaction"

data = {
    "id": "test-tx-1",
    "user_id": "user-1",
    "amount": 150.0,
    "category": "تسوق",
    "description": "Weekly grocery",
    "date": "2024-01-15",
    "budget_limit": 100.0,
    "features": []  # Empty list for features, not dict
}

try:
    print(f"Testing URL: {url}")
    response = requests.post(url, json=data)
    print(f"Status Code: {response.status_code}")
    if response.status_code == 200:
        print(f"Response: {json.dumps(response.json(), indent=2, ensure_ascii=False)}")
    else:
        print(f"Error Response: {response.text}")
except Exception as e:
    print(f"Error: {e}")
