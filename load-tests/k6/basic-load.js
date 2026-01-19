import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

// Métricas customizadas
const errorRate = new Rate('errors');

// Configuração do teste
export const options = {
  stages: [
    { duration: '30s', target: 10 },  // Ramp up: 10 usuários em 30s
    { duration: '1m', target: 20 },   // Sustained: 20 usuários por 1min
    { duration: '30s', target: 0 },   // Ramp down: voltar a 0
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% das requisições devem ser < 500ms
    http_req_failed: ['rate<0.01'],   // Taxa de erro < 1%
    errors: ['rate<0.01'],
  },
};

// Configuração base
const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const TOKEN = __ENV.TOKEN || 'SEU_TOKEN_AQUI'; // Passe via: k6 run --env TOKEN=seu_token

const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${TOKEN}`,
};

export default function () {
  // Teste: Listar produtos
  const listResponse = http.get(`${BASE_URL}/api/list`, { headers });
  
  const listSuccess = check(listResponse, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
    'has products array': (r) => {
      try {
        const body = JSON.parse(r.body);
        return Array.isArray(body);
      } catch {
        return false;
      }
    },
  });

  errorRate.add(!listSuccess);
  sleep(1);
}

export function handleSummary(data) {
  return {
    'stdout': textSummary(data, { indent: ' ', enableColors: true }),
    'load-tests/results/k6-summary.json': JSON.stringify(data),
  };
}

function textSummary(data, options) {
  const indent = options.indent || '';
  const enableColors = options.enableColors || false;
  
  let summary = '\n';
  summary += `${indent}✓ Teste de Carga Concluído\n`;
  summary += `${indent}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
  summary += `${indent}Duração: ${data.state.testRunDurationMs / 1000}s\n`;
  summary += `${indent}Requisições: ${data.metrics.http_reqs.values.count}\n`;
  summary += `${indent}RPS: ${data.metrics.http_reqs.values.rate.toFixed(2)}\n`;
  summary += `${indent}Tempo médio: ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms\n`;
  summary += `${indent}P95: ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms\n`;
  summary += `${indent}Erros: ${((data.metrics.http_req_failed.values.rate || 0) * 100).toFixed(2)}%\n`;
  
  return summary;
}
