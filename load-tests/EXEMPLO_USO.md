# Exemplo Prático de Uso

## Cenário: Testar API de Produtos

### 1. Preparação

```bash
# 1. Certifique-se que a aplicação está rodando
docker-compose up -d

# 2. Obter token (PowerShell)
cd load-tests/scripts
.\get-token.ps1 -Email "test@example.com" -Password "password"

# 3. Copiar o token exibido
```

### 2. Executar Teste Básico com k6

```powershell
# Definir token
$env:TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGc..."

# Executar teste básico
k6 run load-tests/k6/basic-load.js
```

**Resultado esperado:**
```
✓ Teste de Carga Concluído
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Duração: 180s
Requisições: 5400
RPS: 30.00
Tempo médio: 150.25ms
P95: 350.00ms
Erros: 0.15%
```

### 3. Executar Teste Completo

```powershell
# Teste completo com todas as operações CRUD
k6 run load-tests/k6/full-load.js
```

Este teste vai:
- Listar produtos (70% das requisições)
- Criar produtos
- Atualizar produtos
- Deletar produtos
- Medir performance de cada operação

### 4. Executar com Artillery

```bash
# 1. Editar arquivo e substituir SEU_TOKEN_AQUI
# load-tests/artillery/full-load.yml

# 2. Executar
artillery run load-tests/artillery/full-load.yml

# 3. Gerar relatório
artillery run --output report.json load-tests/artillery/full-load.yml
artillery report report.json
```

### 5. Comparar Resultados

Execute o mesmo teste várias vezes e compare:

```powershell
# Teste 1: Baseline
k6 run --out json=baseline.json load-tests/k6/basic-load.js

# Fazer otimizações na aplicação...

# Teste 2: Após otimizações
k6 run --out json=optimized.json load-tests/k6/basic-load.js

# Comparar resultados
```

## Exemplo de Script PowerShell Completo

```powershell
# load-tests/run-tests.ps1

param(
    [string]$Email = "test@example.com",
    [string]$Password = "password",
    [string]$TestType = "basic"  # basic ou full
)

Write-Host "=== Preparando Teste de Carga ===" -ForegroundColor Cyan

# Obter token
Write-Host "Obtendo token..." -ForegroundColor Yellow
$tokenScript = Join-Path $PSScriptRoot "scripts\get-token.ps1"
$tokenOutput = & $tokenScript -Email $Email -Password $Password 2>&1
$token = ($tokenOutput | Select-String -Pattern "Bearer\s+(\S+)" | ForEach-Object { $_.Matches.Groups[1].Value })

if (-not $token) {
    Write-Host "Erro ao obter token!" -ForegroundColor Red
    exit 1
}

$env:TOKEN = $token
Write-Host "Token obtido: $($token.Substring(0,20))..." -ForegroundColor Green

# Executar teste
Write-Host "`n=== Executando Teste ===" -ForegroundColor Cyan

if ($TestType -eq "full") {
    k6 run (Join-Path $PSScriptRoot "k6\full-load.js")
} else {
    k6 run (Join-Path $PSScriptRoot "k6\basic-load.js")
}

Write-Host "`n=== Teste Concluído ===" -ForegroundColor Green
```

**Uso:**
```powershell
cd load-tests
.\run-tests.ps1 -Email "seu@email.com" -Password "senha" -TestType "full"
```

## Monitoramento Durante Testes

Enquanto os testes rodam, monitore o servidor:

```powershell
# Em outro terminal, monitorar recursos do Docker
docker stats laravel_app laravel_mysql laravel_nginx

# Ou verificar logs
docker logs -f laravel_app
```

## Interpretação de Resultados

### Bom Desempenho:
- ✅ RPS > 100
- ✅ Tempo médio < 200ms
- ✅ P95 < 500ms
- ✅ Taxa de erro < 1%

### Atenção Necessária:
- ⚠️ RPS < 50
- ⚠️ Tempo médio > 500ms
- ⚠️ P95 > 1000ms
- ⚠️ Taxa de erro > 5%

### Ações Recomendadas:
1. **Se RPS baixo**: Verificar queries do banco, adicionar índices
2. **Se tempo alto**: Verificar cache, otimizar código
3. **Se muitos erros**: Verificar logs, aumentar recursos do servidor
