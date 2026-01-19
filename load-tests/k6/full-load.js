import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { randomIntBetween, randomString } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// Métricas customizadas
const errorRate = new Rate('errors');
const createDuration = new Trend('create_duration');
const updateDuration = new Trend('update_duration');
const deleteDuration = new Trend('delete_duration');

// Configuração do teste
export const options = {
  stages: [
    { duration: '30s', target: 10 },   // Warm up
    { duration: '2m', target: 30 },    // Sustained load
    { duration: '1m', target: 50 },    // Spike test
    { duration: '30s', target: 0 },     // Cool down
  ],
  thresholds: {
    http_req_duration: ['p(95)<1000'],
    http_req_failed: ['rate<0.05'],
    errors: ['rate<0.05'],
    create_duration: ['p(95)<800'],
    update_duration: ['p(95)<600'],
    delete_duration: ['p(95)<500'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const TOKEN = __ENV.TOKEN || 'SEU_TOKEN_AQUI';

const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${TOKEN}`,
};

export default function () {
  // 70% das requisições: Fluxo completo CRUD
  if (Math.random() < 0.7) {
    crudFlow();
  } else {
    // 30% das requisições: Apenas leitura
    listOnly();
  }
  
  sleep(randomIntBetween(1, 3));
}

function crudFlow() {
  // 1. Listar produtos
  const listResponse = http.get(`${BASE_URL}/api/list`, { headers });
  check(listResponse, {
    'list: status 200': (r) => r.status === 200,
  });

  // 2. Criar produto
  const productData = {
    name: `Produto ${randomString(8)}`,
    description: `Descrição do produto de teste ${randomString(10)}`,
    qtd: randomIntBetween(1, 100),
  };

  const createResponse = http.post(
    `${BASE_URL}/api/list`,
    JSON.stringify(productData),
    { headers }
  );

  const createSuccess = check(createResponse, {
    'create: status 201': (r) => r.status === 201,
    'create: has data': (r) => {
      try {
        const body = JSON.parse(r.body);
        return body.data && body.data.id;
      } catch {
        return false;
      }
    },
  });

  createDuration.add(createResponse.timings.duration);
  errorRate.add(!createSuccess);

  if (!createSuccess) return;

  const productId = JSON.parse(createResponse.body).data.id;
  sleep(1);

  // 3. Atualizar produto
  const updateData = {
    name: `Produto Atualizado ${randomString(8)}`,
    qtd: randomIntBetween(1, 100),
  };

  const updateResponse = http.put(
    `${BASE_URL}/api/list/${productId}`,
    JSON.stringify(updateData),
    { headers }
  );

  const updateSuccess = check(updateResponse, {
    'update: status 200': (r) => r.status === 200,
  });

  updateDuration.add(updateResponse.timings.duration);
  errorRate.add(!updateSuccess);
  sleep(1);

  // 4. Deletar produto
  const deleteResponse = http.del(
    `${BASE_URL}/api/list/${productId}`,
    null,
    { headers }
  );

  const deleteSuccess = check(deleteResponse, {
    'delete: status 200': (r) => r.status === 200,
  });

  deleteDuration.add(deleteResponse.timings.duration);
  errorRate.add(!deleteSuccess);
}

function listOnly() {
  const listResponse = http.get(`${BASE_URL}/api/list`, { headers });
  check(listResponse, {
    'list: status 200': (r) => r.status === 200,
    'list: response time < 300ms': (r) => r.timings.duration < 300,
  });
}

export function handleSummary(data) {
  return {
    'stdout': textSummary(data),
    'load-tests/results/k6-full-summary.json': JSON.stringify(data),
  };
}

function textSummary(data) {
  let summary = '\n';
  summary += '═══════════════════════════════════════════════════\n';
  summary += '  TESTE DE CARGA COMPLETO - RESULTADOS\n';
  summary += '═══════════════════════════════════════════════════\n\n';
  
  summary += `Duração Total: ${(data.state.testRunDurationMs / 1000).toFixed(2)}s\n`;
  summary += `Requisições Totais: ${data.metrics.http_reqs.values.count}\n`;
  summary += `RPS Médio: ${data.metrics.http_reqs.values.rate.toFixed(2)}\n\n`;
  
  summary += 'Tempos de Resposta:\n';
  summary += `  Média: ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms\n`;
  summary += `  Mediana: ${data.metrics.http_req_duration.values.med.toFixed(2)}ms\n`;
  summary += `  P95: ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms\n`;
  summary += `  P99: ${data.metrics.http_req_duration.values['p(99)'].toFixed(2)}ms\n\n`;
  
  if (data.metrics.create_duration) {
    summary += 'Operações CRUD:\n';
    summary += `  Criar (P95): ${data.metrics.create_duration.values['p(95)'].toFixed(2)}ms\n`;
    summary += `  Atualizar (P95): ${data.metrics.update_duration.values['p(95)'].toFixed(2)}ms\n`;
    summary += `  Deletar (P95): ${data.metrics.delete_duration.values['p(95)'].toFixed(2)}ms\n\n`;
  }
  
  const errorPercent = (data.metrics.http_req_failed.values.rate || 0) * 100;
  summary += `Taxa de Erro: ${errorPercent.toFixed(2)}%\n`;
  summary += `Status: ${errorPercent < 5 ? '✓ PASSOU' : '✗ FALHOU'}\n`;
  summary += '\n═══════════════════════════════════════════════════\n';
  
  return summary;
}
