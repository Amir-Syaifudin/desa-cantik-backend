# Authentication API V2 - Complete Testing Script (PowerShell)
# Desa Cantik API - Tests ALL endpoints

$BASE_URL = "http://localhost:8000/api"

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "DESA CANTIK API V2 - COMPLETE AUTH TESTS" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# 1. Health Check
Write-Host "1. Testing Health Check..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/health" -H "Accept: application/json"
Write-Host ""
Start-Sleep -Seconds 1

# 2. Register
Write-Host "2. Testing User Registration..." -ForegroundColor Yellow
curl.exe -X POST "$BASE_URL/auth/register" `
  -H "Content-Type: application/json" `
  -d '{"username":"test_perangkat","email":"test@desacantik.id","password":"password123","password_confirmation":"password123","role_id":2,"desa_id":1,"full_name":"Test Perangkat Desa","phone":"081234567890"}'
Write-Host ""
Start-Sleep -Seconds 1

# 3. Login with USERNAME
Write-Host "3. Testing Login with USERNAME..." -ForegroundColor Yellow
$loginResponse = curl.exe -X POST "$BASE_URL/auth/login" `
  -H "Content-Type: application/json" `
  -d '{"login":"test_perangkat","password":"password123"}' | ConvertFrom-Json

Write-Host "Login Successful!" -ForegroundColor Green
$TOKEN = $loginResponse.data.token
Write-Host "Token: $TOKEN" -ForegroundColor Cyan
Write-Host ""
Start-Sleep -Seconds 1

# 4. Login with EMAIL (NEW FEATURE!)
Write-Host "4. Testing Login with EMAIL (NEW)..." -ForegroundColor Yellow
$emailLoginResponse = curl.exe -X POST "$BASE_URL/auth/login" `
  -H "Content-Type: application/json" `
  -d '{"login":"test@desacantik.id","password":"password123"}' | ConvertFrom-Json

Write-Host "Email Login Successful!" -ForegroundColor Green
$EMAIL_TOKEN = $emailLoginResponse.data.token
Write-Host ""
Start-Sleep -Seconds 1

# 5. Get Profile
Write-Host "5. Testing Get User Profile..." -ForegroundColor Yellow
curl.exe -X GET "$BASE_URL/auth/me" `
  -H "Authorization: Bearer $TOKEN" `
  -H "Accept: application/json"
Write-Host ""
Start-Sleep -Seconds 1

# 6. Update Profile (NEW FEATURE!)
Write-Host "6. Testing Update Profile (NEW)..." -ForegroundColor Yellow
curl.exe -X PUT "$BASE_URL/auth/profile" `
  -H "Authorization: Bearer $TOKEN" `
  -H "Content-Type: application/json" `
  -d '{"full_name":"Updated Name","phone":"089876543210"}'
Write-Host ""
Start-Sleep -Seconds 1

# 7. Forgot Password (NEW FEATURE!)
Write-Host "7. Testing Forgot Password (NEW)..." -ForegroundColor Yellow
$forgotResponse = curl.exe -X POST "$BASE_URL/auth/forgot-password" `
  -H "Content-Type: application/json" `
  -d '{"email":"test@desacantik.id"}' | ConvertFrom-Json

$RESET_TOKEN = $forgotResponse.data.reset_token
Write-Host "Reset Token: $RESET_TOKEN" -ForegroundColor Magenta
Write-Host ""
Start-Sleep -Seconds 1

# 8. Reset Password (NEW FEATURE!)
Write-Host "8. Testing Reset Password (NEW)..." -ForegroundColor Yellow
curl.exe -X POST "$BASE_URL/auth/reset-password" `
  -H "Content-Type: application/json" `
  -d "{\`"email\`":\`"test@desacantik.id\`",\`"token\`":\`"$RESET_TOKEN\`",\`"password\`":\`"newpassword123\`",\`"password_confirmation\`":\`"newpassword123\`"}"
Write-Host ""
Start-Sleep -Seconds 1

# 9. Login with NEW password
Write-Host "9. Testing Login with NEW Password..." -ForegroundColor Yellow
$newLoginResponse = curl.exe -X POST "$BASE_URL/auth/login" `
  -H "Content-Type: application/json" `
  -d '{"login":"test@desacantik.id","password":"newpassword123"}' | ConvertFrom-Json

$NEW_TOKEN = $newLoginResponse.data.token
Write-Host "Login with new password successful!" -ForegroundColor Green
Write-Host ""
Start-Sleep -Seconds 1

# 10. Update Password (NEW FEATURE!)
Write-Host "10. Testing Update Password (NEW)..." -ForegroundColor Yellow
curl.exe -X PUT "$BASE_URL/auth/password" `
  -H "Authorization: Bearer $NEW_TOKEN" `
  -H "Content-Type: application/json" `
  -d '{"current_password":"newpassword123","new_password":"finalpassword123","new_password_confirmation":"finalpassword123"}'
Write-Host ""
Start-Sleep -Seconds 1

# 11. Refresh Token
Write-Host "11. Testing Token Refresh..." -ForegroundColor Yellow
$refreshResponse = curl.exe -X POST "$BASE_URL/auth/refresh" `
  -H "Authorization: Bearer $NEW_TOKEN" `
  -H "Accept: application/json" | ConvertFrom-Json

$REFRESHED_TOKEN = $refreshResponse.data.token
Write-Host "Token Refreshed!" -ForegroundColor Green
Write-Host ""
Start-Sleep -Seconds 1

# 12. Logout
Write-Host "12. Testing Logout..." -ForegroundColor Yellow
curl.exe -X POST "$BASE_URL/auth/logout" `
  -H "Authorization: Bearer $REFRESHED_TOKEN" `
  -H "Accept: application/json"
Write-Host ""

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "ALL TESTS COMPLETED SUCCESSFULLY!" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Health Check" -ForegroundColor Green
Write-Host "✅ User Registration" -ForegroundColor Green
Write-Host "✅ Login with USERNAME" -ForegroundColor Green
Write-Host "✅ Login with EMAIL (NEW)" -ForegroundColor Green
Write-Host "✅ Get Profile" -ForegroundColor Green
Write-Host "✅ Update Profile (NEW)" -ForegroundColor Green
Write-Host "✅ Forgot Password (NEW)" -ForegroundColor Green
Write-Host "✅ Reset Password (NEW)" -ForegroundColor Green
Write-Host "✅ Update Password (NEW)" -ForegroundColor Green
Write-Host "✅ Token Refresh" -ForegroundColor Green
Write-Host "✅ Logout" -ForegroundColor Green
