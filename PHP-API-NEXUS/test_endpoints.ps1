# Test de endpoints usando PowerShell
Write-Host "=== TEST DE ENDPOINTS DE LA API ===" -ForegroundColor Green
Write-Host ""

$baseUrl = "http://localhost:8080/Skeletons/PHP-API-NEXUS/index.php"

# Test 1: GET /api/Constellations
Write-Host "1. Probando GET /api/Constellations" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl?request=api/Constellations" -Method GET -ContentType "application/json"
    Write-Host "   ✅ Respuesta válida - Constelaciones: $($response.Count)" -ForegroundColor Green
    if ($response.Count -gt 0) {
        Write-Host "   Primera constelación: $($response[0].english_name)" -ForegroundColor Cyan
    }
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   StatusCode: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}
Write-Host ""

# Test 2: GET /api/Constellations/6 (Ara)
Write-Host "2. Probando GET /api/Constellations/6" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl?request=api/Constellations/6" -Method GET -ContentType "application/json"
    Write-Host "   ✅ Constelación obtenida: $($response.english_name)" -ForegroundColor Green
    Write-Host "   Código: $($response.code) - Nombre latino: $($response.latin_name)" -ForegroundColor Cyan
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   StatusCode: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}
Write-Host ""

# Test 3: GET /api/Constellations/GetStars/6
Write-Host "3. Probando GET /api/Constellations/GetStars/6" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl?request=api/Constellations/GetStars/6" -Method GET -ContentType "application/json"
    Write-Host "   ✅ Estrellas obtenidas: $($response.Count)" -ForegroundColor Green
    if ($response.Count -gt 0) {
        Write-Host "   Primera estrella ID: $($response[0].id)" -ForegroundColor Cyan
    }
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   StatusCode: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}
Write-Host ""

# Test 4: GET /api/Stars (limitado para no sobrecargar)
Write-Host "4. Probando GET /api/Stars" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl?request=api/Stars" -Method GET -ContentType "application/json" -TimeoutSec 10
    Write-Host "   ✅ Respuesta válida - Estrellas: $($response.Count)" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   StatusCode: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}
Write-Host ""

# Test 5: GET /api/Account/GetComments/6
Write-Host "5. Probando GET /api/Account/GetComments/6" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl?request=api/Account/GetComments/6" -Method GET -ContentType "application/json"
    if ($response.success) {
        Write-Host "   ✅ Comentarios obtenidos: $($response.data.Count)" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  Sin comentarios: $($response.message)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   StatusCode: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
}
Write-Host ""

Write-Host "=== TESTS COMPLETADOS ===" -ForegroundColor Green
