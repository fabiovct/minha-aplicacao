# Script PowerShell para obter token de autenticação
# Uso: .\get-token.ps1 -Email "email@exemplo.com" -Password "senha"

param(
    [Parameter(Mandatory=$true)]
    [string]$Email,
    
    [Parameter(Mandatory=$true)]
    [string]$Password,
    
    [string]$BaseUrl = "http://localhost:8000"
)

Write-Host "Fazendo login como $Email..." -ForegroundColor Cyan
Write-Host ""

$body = @{
    email = $Email
    password = $Password
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/api/login" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    
    $token = $response.token
    
    if ($token) {
        Write-Host "Token obtido com sucesso!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Para usar nos testes:" -ForegroundColor Yellow
        Write-Host "  `$env:TOKEN = '$token'" -ForegroundColor White
        Write-Host ""
        Write-Host "Token (copie e cole):" -ForegroundColor Yellow
        Write-Host $token -ForegroundColor White
        Write-Host ""
        
        # Salvar em arquivo .env.local se solicitado
        $save = Read-Host "Deseja salvar em arquivo .env.local? (s/n)"
        if ($save -eq "s" -or $save -eq "S") {
            "TOKEN=$token" | Out-File -FilePath "load-tests\.env.local" -Encoding utf8
            Write-Host "Token salvo em load-tests\.env.local" -ForegroundColor Green
        }
    } else {
        Write-Host "Erro: Token não encontrado na resposta" -ForegroundColor Red
        Write-Host "Resposta: $($response | ConvertTo-Json)" -ForegroundColor Red
    }
} catch {
    Write-Host "Erro ao fazer login:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
