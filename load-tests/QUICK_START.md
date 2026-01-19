# Guia Rápido - Testes de Carga

## Passo 1: Obter Token de Autenticação

### Windows (PowerShell):
```powershell
cd load-tests/scripts
.\get-token.ps1 -Email "seu@email.com" -Password "sua_senha"
```

### Linux/Mac:
```bash
cd load-tests/scripts
chmod +x get-token.sh
./get-token.sh seu@email.com sua_senha
```

## Passo 2: Escolher Ferramenta

### Opção 1: k6 (Recomendado)

**Instalar k6:**
- Windows: `choco install k6` ou baixar de https://k6.io/docs/getting-started/installation/
- Mac: `brew install k6`
- Linux: Seguir instruções em https://k6.io/docs/getting-started/installation/

**Executar teste básico:**
```bash
# Definir token
export TOKEN="seu_token_aqui"  # Linux/Mac
$env:TOKEN="seu_token_aqui"     # PowerShell

# Executar
k6 run load-tests/k6/basic-load.js
```

**Executar teste completo:**
```bash
k6 run load-tests/k6/full-load.js
```

### Opção 2: Artillery

**Instalar Artillery:**
```bash
npm install -g artillery
```

**Executar teste básico:**
```bash
# Editar load-tests/artillery/basic-load.yml e substituir SEU_TOKEN_AQUI
artillery run load-tests/artillery/basic-load.yml
```

**Executar teste completo com relatório:**
```bash
artillery run --output load-tests/results/report.json load-tests/artillery/full-load.yml
artillery report load-tests/results/report.json
```

### Opção 3: Apache Bench (ab)

**Windows:** Geralmente já vem instalado ou instalar via Chocolatey:
```powershell
choco install apache-httpd
```

**Executar:**
```bash
# Linux/Mac
export TOKEN="seu_token"
bash load-tests/scripts/ab-test.sh

# Ou manualmente:
ab -n 1000 -c 10 -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/list
```

## Passo 3: Interpretar Resultados

### Métricas Importantes:

1. **RPS (Requests Per Second)**: Quantas requisições por segundo
   - Bom: > 100 RPS
   - Ruim: < 50 RPS

2. **Response Time (Tempo de Resposta)**:
   - Média: Tempo médio de resposta
   - P95: 95% das requisições respondem em menos de X ms
   - Bom: < 200ms (média), < 500ms (P95)
   - Ruim: > 500ms (média), > 1000ms (P95)

3. **Error Rate (Taxa de Erro)**:
   - Bom: < 1%
   - Atenção: 1-5%
   - Ruim: > 5%

### Exemplo de Saída (k6):

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

## Exemplos de Comandos

### Teste rápido (30 segundos):
```bash
# k6
k6 run --duration 30s --vus 10 load-tests/k6/basic-load.js

# Artillery
artillery quick --count 100 --num 10 http://localhost:8000/api/list
```

### Teste de stress (muitos usuários):
```bash
# k6 - 100 usuários simultâneos por 2 minutos
k6 run --vus 100 --duration 2m load-tests/k6/full-load.js
```

### Teste com relatório HTML:
```bash
# k6
k6 run --out json=load-tests/results/result.json load-tests/k6/full-load.js
# Depois visualizar com k6-reporter ou k6-to-influxdb

# Artillery
artillery run --output load-tests/results/report.json load-tests/artillery/full-load.yml
artillery report --output load-tests/results/report.html load-tests/results/report.json
```

## Dicas

1. **Sempre comece com testes leves** antes de fazer testes pesados
2. **Monitore o servidor** durante os testes (CPU, memória, conexões)
3. **Execute testes em horários de baixo tráfego** se possível
4. **Compare resultados** antes e depois de otimizações
5. **Documente os resultados** para referência futura

## Troubleshooting

### Erro 401 (Unauthorized):
- Verifique se o token está correto
- Token pode ter expirado, gere um novo

### Erro de conexão:
- Verifique se a aplicação está rodando
- Verifique a URL (deve ser http://localhost:8000)

### Teste muito lento:
- Reduza o número de usuários simultâneos
- Verifique recursos do servidor (CPU, memória)
- Verifique se há queries lentas no banco de dados
