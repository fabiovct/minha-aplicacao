# Testes de Carga

Este diretório contém scripts e configurações para testes de carga da API.

## Ferramentas Disponíveis

### 1. Artillery (Recomendado)
Ferramenta moderna e fácil de usar, escrita em Node.js.

**Instalação:**
```bash
npm install -g artillery
```

**Executar testes:**
```bash
# Teste básico
artillery run load-tests/artillery/basic-load.yml

# Teste completo com relatório
artillery run --output report.json load-tests/artillery/full-load.yml
artillery report report.json
```

### 2. k6
Ferramenta moderna e poderosa, escrita em Go.

**Instalação:**
```bash
# Windows (usando Chocolatey)
choco install k6

# Ou baixar de: https://k6.io/docs/getting-started/installation/
```

**Executar testes:**
```bash
k6 run load-tests/k6/basic-load.js
k6 run load-tests/k6/full-load.js
```

### 3. Apache Bench (ab)
Ferramenta simples e rápida, geralmente já instalada.

**Executar testes:**
```bash
# Windows PowerShell
bash load-tests/scripts/ab-test.sh

# Ou manualmente:
ab -n 1000 -c 10 -H "Authorization: Bearer TOKEN" http://localhost:8000/api/list
```

## Preparação

Antes de executar os testes, você precisa:

1. **Obter um token de autenticação:**
```bash
# Fazer login e obter token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"seu@email.com","password":"sua_senha"}'
```

2. **Atualizar os arquivos de teste** com o token obtido, ou usar o script helper:
```bash
bash load-tests/scripts/get-token.sh seu@email.com sua_senha
```

## Tipos de Testes

### Teste Básico
- 100 requisições
- 10 usuários simultâneos
- Testa apenas GET /api/list

### Teste Completo
- 1000 requisições
- 50 usuários simultâneos
- Testa todas as rotas (GET, POST, PUT, DELETE)
- Inclui criação e manipulação de dados

### Teste de Stress
- 5000 requisições
- 100 usuários simultâneos
- Testa limites do sistema

## Métricas Importantes

- **Requests per second (RPS)**: Quantas requisições por segundo o servidor consegue processar
- **Response time**: Tempo médio de resposta
- **Error rate**: Percentual de erros
- **95th percentile**: 95% das requisições respondem em menos de X ms

## Interpretando Resultados

### Bom desempenho:
- RPS > 100
- Response time < 200ms (média)
- Error rate < 1%

### Atenção necessária:
- RPS < 50
- Response time > 500ms
- Error rate > 5%
