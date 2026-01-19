# Teste de carga simples usando PowerShell
# Não requer instalação de ferramentas externas

param(
    [string]$Token,
    [string]$BaseUrl = "http://localhost:8000",
    [int]$TotalRequests = 100,
    [int]$ConcurrentRequests = 10,
    [int]$DelayMs = 100
)

if (-not $Token) {
    Write-Host "ERRO: Token não fornecido!" -ForegroundColor Red
    Write-Host "Uso: .\simple-load-test.ps1 -Token 'seu_token' [-TotalRequests 100] [-ConcurrentRequests 10]" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  TESTE DE CARGA SIMPLES - API DE PRODUTOS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "URL: $BaseUrl/api/list" -ForegroundColor White
Write-Host "Total de requisições: $TotalRequests" -ForegroundColor White
Write-Host "Requisições simultâneas: $ConcurrentRequests" -ForegroundColor White
Write-Host ""

$headers = @{
    "Authorization" = "Bearer $Token"
    "Content-Type" = "application/json"
}

$results = @()
$errors = 0
$startTime = Get-Date

# Criar jobs para requisições simultâneas
$jobs = @()
$requestsPerJob = [Math]::Ceiling($TotalRequests / $ConcurrentRequests)

for ($i = 0; $i -lt $ConcurrentRequests; $i++) {
    $scriptBlock = {
        param($Url, $Headers, $RequestCount, $Delay)
        
        $jobResults = @()
        $jobErrors = 0
        
        for ($j = 0; $j -lt $RequestCount; $j++) {
            $requestStart = Get-Date
            try {
                $response = Invoke-RestMethod -Uri $Url -Method Get -Headers $Headers -ErrorAction Stop
                $requestEnd = Get-Date
                $duration = ($requestEnd - $requestStart).TotalMilliseconds
                
                $jobResults += @{
                    Success = $true
                    Duration = $duration
                    StatusCode = 200
                }
            } catch {
                $requestEnd = Get-Date
                $duration = ($requestEnd - $requestStart).TotalMilliseconds
                $jobErrors++
                
                $jobResults += @{
                    Success = $false
                    Duration = $duration
                    StatusCode = $_.Exception.Response.StatusCode.value__
                    Error = $_.Exception.Message
                }
            }
            
            if ($Delay -gt 0) {
                Start-Sleep -Milliseconds $Delay
            }
        }
        
        return @{
            Results = $jobResults
            Errors = $jobErrors
        }
    }
    
    $job = Start-Job -ScriptBlock $scriptBlock -ArgumentList "$BaseUrl/api/list", $headers, $requestsPerJob, $DelayMs
    $jobs += $job
}

Write-Host "Executando $TotalRequests requisições..." -ForegroundColor Yellow
Write-Host ""

# Aguardar conclusão dos jobs
$completed = 0
while ($jobs | Where-Object { $_.State -eq "Running" }) {
    $completed = ($jobs | Where-Object { $_.State -eq "Completed" }).Count
    $progress = [Math]::Round(($completed / $ConcurrentRequests) * 100)
    Write-Progress -Activity "Teste de Carga" -Status "Progresso: $progress%" -PercentComplete $progress
    Start-Sleep -Milliseconds 100
}

Write-Progress -Activity "Teste de Carga" -Completed

# Coletar resultados
foreach ($job in $jobs) {
    $jobResult = Receive-Job -Job $job
    $results += $jobResult.Results
    $errors += $jobResult.Errors
    Remove-Job -Job $job
}

$endTime = Get-Date
$totalDuration = ($endTime - $startTime).TotalSeconds

# Calcular estatísticas
$successfulRequests = $results | Where-Object { $_.Success -eq $true }
$failedRequests = $results | Where-Object { $_.Success -eq $false }
$durations = $successfulRequests | ForEach-Object { $_.Duration }

if ($durations.Count -gt 0) {
    $avgDuration = ($durations | Measure-Object -Average).Average
    $minDuration = ($durations | Measure-Object -Minimum).Minimum
    $maxDuration = ($durations | Measure-Object -Maximum).Maximum
    
    # Calcular percentis
    $sortedDurations = $durations | Sort-Object
    $p50Index = [Math]::Floor($sortedDurations.Count * 0.50)
    $p95Index = [Math]::Floor($sortedDurations.Count * 0.95)
    $p99Index = [Math]::Floor($sortedDurations.Count * 0.99)
    
    $p50 = $sortedDurations[$p50Index]
    $p95 = $sortedDurations[$p95Index]
    $p99 = $sortedDurations[$p99Index]
} else {
    $avgDuration = 0
    $minDuration = 0
    $maxDuration = 0
    $p50 = 0
    $p95 = 0
    $p99 = 0
}

$rps = [Math]::Round($successfulRequests.Count / $totalDuration, 2)
$errorRate = [Math]::Round(($errors / $TotalRequests) * 100, 2)

# Exibir resultados
Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "  RESULTADOS DO TESTE" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""
Write-Host "Duração Total: $([Math]::Round($totalDuration, 2))s" -ForegroundColor White
Write-Host "Requisições Totais: $TotalRequests" -ForegroundColor White
Write-Host "Requisições Bem-sucedidas: $($successfulRequests.Count)" -ForegroundColor Green
Write-Host "Requisições com Erro: $errors" -ForegroundColor $(if ($errors -gt 0) { "Red" } else { "Green" })
Write-Host ""
Write-Host "Performance:" -ForegroundColor Cyan
Write-Host "  RPS (Requests/Second): $rps" -ForegroundColor White
Write-Host "  Taxa de Erro: $errorRate%" -ForegroundColor $(if ($errorRate -lt 1) { "Green" } elseif ($errorRate -lt 5) { "Yellow" } else { "Red" })
Write-Host ""
Write-Host "Tempos de Resposta:" -ForegroundColor Cyan
Write-Host "  Mínimo: $([Math]::Round($minDuration, 2))ms" -ForegroundColor White
Write-Host "  Média: $([Math]::Round($avgDuration, 2))ms" -ForegroundColor White
Write-Host "  Máximo: $([Math]::Round($maxDuration, 2))ms" -ForegroundColor White
Write-Host "  Mediana (P50): $([Math]::Round($p50, 2))ms" -ForegroundColor White
Write-Host "  P95: $([Math]::Round($p95, 2))ms" -ForegroundColor White
Write-Host "  P99: $([Math]::Round($p99, 2))ms" -ForegroundColor White
Write-Host ""

# Status geral
if ($errorRate -lt 1 -and $avgDuration -lt 500) {
    Write-Host "Status: [OK] EXCELENTE" -ForegroundColor Green
} elseif ($errorRate -lt 5 -and $avgDuration -lt 1000) {
    Write-Host "Status: [AVISO] ACEITAVEL" -ForegroundColor Yellow
} else {
    Write-Host "Status: [ERRO] NECESSITA ATENCAO" -ForegroundColor Red
}

Write-Host ""
Write-Host "===================================================" -ForegroundColor Green
Write-Host ""
