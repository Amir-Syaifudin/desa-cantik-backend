# ============================================
# Authentication API Testing Script
# Project: Desa Cantik API
# ============================================

$BASE_URL = "http://localhost:8000/api/v1"
$ErrorActionPreference = "Continue"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  AUTHENTICATION API TESTING SUITE" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# ===== TEST 1: LOGIN WITH EMAIL =====
Write-Host "[1/9] Testing Login with Email..." -ForegroundColor Yellow
try {
    $loginResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/login" -Method POST -Body (@{
        login = "admin@bps.go.id"
        password = "password123"
    } | ConvertTo-Json) -ContentType "application/json"
    
    $token = $loginResponse.data.token
    Write-Host "  ✓ Login Success!" -ForegroundColor Green
    Write-Host "    User: $($loginResponse.data.user.full_name)" -ForegroundColor Gray
    Write-Host "    Email: $($loginResponse.data.user.email)" -ForegroundColor Gray
    if ($loginResponse.data.user.role) {
        Write-Host "    Role: $($loginResponse.data.user.role.display_name)" -ForegroundColor Gray
    }
    Write-Host "    Token: $($token.Substring(0, 30))..." -ForegroundColor Gray
} catch {
    $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "  ✗ Login Failed!" -ForegroundColor Red
    Write-Host "    Error: $($errorDetails.message)" -ForegroundColor Red
    exit 1
}

# ===== TEST 2: LOGIN WITH USERNAME =====
Write-Host "`n[2/9] Testing Login with Username..." -ForegroundColor Yellow
try {
    $loginUsernameResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/login" -Method POST -Body (@{
        login = "bps_admin"
        password = "password123"
    } | ConvertTo-Json) -ContentType "application/json"
    
    Write-Host "  ✓ Login with Username Success!" -ForegroundColor Green
    Write-Host "    User: $($loginUsernameResponse.data.user.username)" -ForegroundColor Gray
} catch {
    Write-Host "  ✗ Login with Username Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 3: GET CURRENT USER =====
Write-Host "`n[3/9] Testing Get Current User..." -ForegroundColor Yellow
$headers = @{ 
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}
try {
    $userResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/user" -Method GET -Headers $headers
    Write-Host "  ✓ Get User Success!" -ForegroundColor Green
    Write-Host "    ID: $($userResponse.data.id)" -ForegroundColor Gray
    Write-Host "    Username: $($userResponse.data.username)" -ForegroundColor Gray
    Write-Host "    Email: $($userResponse.data.email)" -ForegroundColor Gray
    Write-Host "    Full Name: $($userResponse.data.full_name)" -ForegroundColor Gray
    Write-Host "    Active: $($userResponse.data.is_active)" -ForegroundColor Gray
} catch {
    Write-Host "  ✗ Get User Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 4: UPDATE PROFILE =====
Write-Host "`n[4/9] Testing Update Profile..." -ForegroundColor Yellow
try {
    $updateResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/profile" -Method PUT -Headers $headers -Body (@{
        full_name = "Administrator Sistem Updated"
        phone_number = "081999999999"
    } | ConvertTo-Json) -ContentType "application/json"
    
    Write-Host "  ✓ Update Profile Success!" -ForegroundColor Green
    Write-Host "    New Full Name: $($updateResponse.data.full_name)" -ForegroundColor Gray
    Write-Host "    New Phone: $($updateResponse.data.phone_number)" -ForegroundColor Gray
} catch {
    Write-Host "  ✗ Update Profile Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 5: UPDATE PASSWORD (SKIP) =====
Write-Host "`n[5/9] Testing Update Password..." -ForegroundColor Yellow
Write-Host "  ⊘ Skipped (would invalidate current token)" -ForegroundColor Yellow

# ===== TEST 6: TOKEN REFRESH =====
Write-Host "`n[6/9] Testing Token Refresh..." -ForegroundColor Yellow
try {
    $refreshResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/token/refresh" -Method POST -Headers $headers
    $newToken = $refreshResponse.data.token
    Write-Host "  ✓ Token Refresh Success!" -ForegroundColor Green
    Write-Host "    New Token: $($newToken.Substring(0, 30))..." -ForegroundColor Gray
    
    # Update headers with new token
    $headers["Authorization"] = "Bearer $newToken"
} catch {
    Write-Host "  ✗ Token Refresh Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 7: FORGOT PASSWORD =====
Write-Host "`n[7/9] Testing Forgot Password..." -ForegroundColor Yellow
try {
    $forgotResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/password/forgot" -Method POST -Body (@{
        email = "admin@bps.go.id"
    } | ConvertTo-Json) -ContentType "application/json"
    
    Write-Host "  ✓ Forgot Password Request Success!" -ForegroundColor Green
    Write-Host "    Message: $($forgotResponse.message)" -ForegroundColor Gray
} catch {
    Write-Host "  ✗ Forgot Password Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 8: INVALID LOGIN =====
Write-Host "`n[8/9] Testing Invalid Login (should fail)..." -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "$BASE_URL/auth/login" -Method POST -Body (@{
        login = "invalid@example.com"
        password = "wrongpassword"
    } | ConvertTo-Json) -ContentType "application/json"
    
    Write-Host "  ✗ ERROR: Invalid login succeeded (should have failed)!" -ForegroundColor Red
} catch {
    Write-Host "  ✓ Correctly Rejected Invalid Login!" -ForegroundColor Green
}

# ===== TEST 9: LOGOUT =====
Write-Host "`n[9/9] Testing Logout..." -ForegroundColor Yellow
try {
    $logoutResponse = Invoke-RestMethod -Uri "$BASE_URL/auth/logout" -Method POST -Headers $headers
    Write-Host "  ✓ Logout Success!" -ForegroundColor Green
    Write-Host "    Message: $($logoutResponse.message)" -ForegroundColor Gray
} catch {
    Write-Host "  ✗ Logout Failed!" -ForegroundColor Red
    Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Red
}

# ===== TEST 10: ACCESS AFTER LOGOUT =====
Write-Host "`n[10/10] Testing Access After Logout (should fail)..." -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "$BASE_URL/auth/user" -Method GET -Headers $headers
    Write-Host "  ✗ ERROR: Still can access after logout!" -ForegroundColor Red
} catch {
    Write-Host "  ✓ Correctly Blocked! (Unauthorized)" -ForegroundColor Green
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  ALL TESTS COMPLETED" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan
