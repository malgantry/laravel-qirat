
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8001/" -Method Get -ErrorAction Stop
    Write-Host "Service is running! Status: $($response.StatusCode)"
    Write-Host "Response: $($response.Content)"
} catch {
    Write-Host "Service is NOT responding on port 8001. Error: $_"
    Write-Host "Starting service now..."
    Start-Process -FilePath "python" -ArgumentList "qirat_ai_api/main.py" -WorkingDirectory "c:\Users\Alkafaa\Downloads\Telegram Desktop\qiratae\web-laravel" -WindowStyle Minimized
    Write-Host "Service start attempted in background."
}
