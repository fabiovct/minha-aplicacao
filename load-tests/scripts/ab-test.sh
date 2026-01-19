#!/bin/bash

# Script de teste usando Apache Bench (ab)
# Requer: ab instalado e token de autenticação

TOKEN=${TOKEN:-"SEU_TOKEN_AQUI"}
BASE_URL=${BASE_URL:-"http://localhost:8000"}
REQUESTS=${REQUESTS:-1000}
CONCURRENCY=${CONCURRENCY:-10}

if [ "$TOKEN" = "SEU_TOKEN_AQUI" ]; then
    echo "AVISO: Token não configurado!"
    echo "Execute: export TOKEN=seu_token"
    echo "Ou edite este script e defina TOKEN"
    exit 1
fi

echo "═══════════════════════════════════════════════════"
echo "  Teste de Carga com Apache Bench"
echo "═══════════════════════════════════════════════════"
echo ""
echo "URL: $BASE_URL/api/list"
echo "Requisições: $REQUESTS"
echo "Concorrência: $CONCURRENCY"
echo ""

ab -n $REQUESTS \
   -c $CONCURRENCY \
   -H "Authorization: Bearer $TOKEN" \
   -H "Content-Type: application/json" \
   -v 2 \
   "$BASE_URL/api/list"

echo ""
echo "═══════════════════════════════════════════════════"
echo "  Teste concluído!"
echo "═══════════════════════════════════════════════════"
