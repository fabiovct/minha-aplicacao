# Script PowerShell para executar testes de carga
# Uso: .\run-tests.ps1 -Email "email@exemplo.com" -Password "senha" -TestType "basic"

param(
    [Parameter(Mandatory=$false)]
    [string]$Email = "test@example.com",
    
    [Parameter(Mandatory=$false)]
    [string]$Password = "password",
    
    [Parameter(Mandatory=$false)]
    [ValidateSet("basic", "full", "artillery-basic", "artillery-full")]
    [string]$TestType = "basic",
    
    [string]$BaseUrl = "http://localhost:8000"
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  TESTE DE CARGA - API DE PRODUTOS" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Verificar se k6 está instalado (para testes k6)
if ($TestType -match "^basic|^full") {
    $k6Installed = Get-Command k6 -ErrorAction SilentlyContinue
    if (-not $k6Installed) {
        Write-Host "ERRO: k6 não está instalado!" -ForegroundColor Red
        Write-Host "Instale com: choco install k6" -ForegroundColor Yellow
        Write-Host "Ou baixe de: https://k6.io/docs/getting-started/installation/" -ForegroundColor Yellow
        exit 1
    }
}

# Verificar se Artillery está instalado (para testes Artillery)
if ($TestType -match "^artillery") {
    $artilleryInstalled = Get-Command artillery -ErrorAction SilentlyContinue
    if (-not $artilleryInstalled) {
        Write-Host "ERRO: Artillery não está instalado!" -ForegroundColor Red
        Write-Host "Instale com: npm install -g artillery" -ForegroundColor Yellow
        exit 1
    }
}

# Obter token
Write-Host "Obtendo token de autenticação..." -ForegroundColor Yellow
$tokenScript = Join-Path $PSScriptRoot "scripts\get-token.ps1"

try {
    $tokenResponse = & $tokenScript -Email $Email -Password $Password -BaseUrl $BaseUrl 2>&1 | Out-String
    
    # Extrair token da saída
    if ($tokenResponse -match "Bearer\s+([^\s]+)") {
        $token = $matches[1]
    } elseif ($tokenResponse -match "Token \(copie e cole\):\s*([^\r\n]+)") {
        $token = $matches[1].Trim()
    } else {
        # Tentar obter diretamente via API
        $body = @{
            email = $Email
            password = $Password
        } | ConvertTo-Json
        
        $response = Invoke-RestMethod -Uri "$BaseUrl/api/login" `
            -Method Post `
            -ContentType "application/json" `
            -Body $body
        
        $token = $response.token
    }
    
    if (-not $token) {
        throw "Token não encontrado"
    }
    
    Write-Host "✓ Token obtido: $($token.Substring(0, [Math]::Min(30, $token.Length)))..." -ForegroundColor Green
} catch {
    Write-Host "ERRO ao obter token: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Executar teste baseado no tipo
switch ($TestType) {
    "basic" {
        Write-Host "Executando teste básico com k6..." -ForegroundColor Cyan
        Write-Host ""
        $env:TOKEN = $token
        $env:BASE_URL = $BaseUrl
        k6 run (Join-Path $PSScriptRoot "k6\basic-load.js")
    }
    
    "full" {
        Write-Host "Executando teste completo com k6..." -ForegroundColor Cyan
        Write-Host ""
        $env:TOKEN = $token
        $env:BASE_URL = $BaseUrl
        k6 run (Join-Path $PSScriptRoot "k6\full-load.js")
    }
    
    "artillery-basic" {
        Write-Host "Executando teste básico com Artillery..." -ForegroundColor Cyan
        Write-Host ""
        
        # Criar arquivo temporário com token substituído
        $configFile = Join-Path $PSScriptRoot "artillery\basic-load.yml"
        $tempFile = Join-Path $env:TEMP "artillery-basic-temp.yml"
        (Get-Content $configFile) -replace "SEU_TOKEN_AQUI", $token | Set-Content $tempFile
        
        artillery run $tempFile
        
        Remove-Item $tempFile -ErrorAction SilentlyContinue
    }
    
    "artillery-full" {
        Write-Host "Executando teste completo com Artillery..." -ForegroundColor Cyan
        Write-Host ""
        
        # Criar arquivo temporário com token substituído
        $configFile = Join-Path $PSScriptRoot "artillery\full-load.yml"
        $tempFile = Join-Path $env:TEMP "artillery-full-temp.yml"
        (Get-Content $configFile) -replace "SEU_TOKEN_AQUI", $token | Set-Content $tempFile
        
        $reportFile = Join-Path $PSScriptRoot "results\artillery-report-$(Get-Date -Format 'yyyyMMdd-HHmmss').json"
        artillery run --output $reportFile $tempFile
        
        Write-Host ""
        Write-Host "Gerando relatório HTML..." -ForegroundColor Yellow
        $htmlReport = $reportFile -replace '\.json$', '.html'
        artillery report --output $htmlReport $reportFile
        
        Write-Host "Relatório salvo em: $htmlReport" -ForegroundColor Green
        
        Remove-Item $tempFile -ErrorAction SilentlyContinue
    }
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "  Teste concluído!" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""
