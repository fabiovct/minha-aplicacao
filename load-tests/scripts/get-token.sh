#!/bin/bash

# Script para obter token de autenticação para testes de carga
# Uso: ./get-token.sh email@exemplo.com senha

if [ -z "$1" ] || [ -z "$2" ]; then
    echo "Uso: $0 <email> <senha>"
    echo "Exemplo: $0 usuario@exemplo.com senha123"
    exit 1
fi

EMAIL=$1
PASSWORD=$2
BASE_URL=${BASE_URL:-"http://localhost:8000"}

echo "Fazendo login como $EMAIL..."
echo ""

RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "Erro ao obter token!"
    echo "Resposta: $RESPONSE"
    exit 1
fi

echo "Token obtido com sucesso!"
echo ""
echo "Para usar nos testes:"
echo "  export TOKEN=$TOKEN"
echo ""
echo "Ou atualize os arquivos de configuração com:"
echo "  TOKEN=$TOKEN"
echo ""
echo "Token (copie e cole):"
echo "$TOKEN"
