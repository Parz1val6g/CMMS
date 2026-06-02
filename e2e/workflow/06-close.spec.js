import { test, expect } from '@playwright/test';
import { readFileSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { USERS, login } from '../fixtures/users.js';

const STATE_PATH = join(dirname(fileURLToPath(import.meta.url)), '../.state/workflow.json');

function readState() {
  if (!existsSync(STATE_PATH)) return {};
  return JSON.parse(readFileSync(STATE_PATH, 'utf-8'));
}

// Helper: POST via browser session (includes XSRF + session cookie)
async function apiPost(page, path) {
  return page.evaluate(async (url) => {
    const xsrfCookie = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';
    const xsrf = decodeURIComponent(xsrfCookie);
    const resp = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrf,
      },
    });
    const body = await resp.json().catch(() => null);
    return { status: resp.status, ok: resp.ok, body };
  }, path);
}

// Gestor de Work Logs aprova o work log submetido pelo trabalhador
test('Gestor de Work Logs aprova Work Log', async ({ page }) => {
  const { workLogId } = readState();
  expect(workLogId, 'workLogId não encontrado — spec 05 falhou?').toBeTruthy();

  await login(page, USERS.workLogManager);

  const result = await apiPost(page, `/api/work-logs/${workLogId}/approve`);
  expect(result.ok, `Aprovação de work log falhou: ${result.status} — ${JSON.stringify(result.body)}`).toBeTruthy();

  // Navega a Work Logs e verifica que aparece como Aprovado
  await page.getByRole('link', { name: /work.log/i }).click();
  await page.waitForLoadState('domcontentloaded');
  await expect(page.getByText(/aprovado/i).first()).toBeVisible({ timeout: 10000 });
});

// Task Manager conclui Mini-Tarefas via API (task_manager tem permission mini_tasks.complete)
test('Task Manager conclui Mini-Tarefas', async ({ page }) => {
  const { miniTaskIds } = readState();
  expect(miniTaskIds?.length, 'miniTaskIds não encontrados — spec 04 falhou?').toBeGreaterThan(0);

  await login(page, USERS.miniTaskManager);

  for (const id of miniTaskIds) {
    const result = await apiPost(page, `/api/mini-tasks/${id}/complete`);
    expect(result.ok, `Conclusão mini-tarefa ${id} falhou: ${result.status}`).toBeTruthy();
  }

  // Navega a Mini-Tarefas para verificar que estão Concluídas
  await page.getByRole('link', { name: /mini.taref/i, exact: false }).click();
  await page.waitForLoadState('networkidle');
  await expect(page.getByText(/concluíd/i).first()).toBeVisible({ timeout: 8000 });
});

// Task Manager aprova Tasks (status passa a "A Aguardar Aprovação" via cascade de mini-tasks)
test('Task Manager conclui Task', async ({ page }) => {
  await login(page, USERS.taskManager);
  await page.getByRole('link', { name: 'Tarefas', exact: true }).click();

  // A task deve estar "A Aguardar Aprovação" (cascade activado quando todas as mini-tasks concluídas)
  const row = page.getByRole('row').filter({ hasText: /a aguardar/i }).first();
  await expect(row).toBeVisible({ timeout: 10000 });
  await row.click();
  await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

  // Clica "Concluir Tarefa" (visível apenas quando status = awaiting_approval)
  await page.getByRole('button', { name: /concluir tarefa/i }).click();
  await page.waitForLoadState('networkidle', { timeout: 10000 });
  await page.keyboard.press('Escape');
  await page.waitForTimeout(300);
});

// Gestor conclui OS [Optimistic UI — drawer fecha < 500ms]
test('Gestor conclui OS [Optimistic UI — drawer fecha < 500ms]', async ({ page }) => {
  const { soDescription } = readState();
  await login(page, USERS.manager);
  await page.getByRole('link', { name: 'Ordens Serviço' }).click();

  await page.getByRole('cell', { name: soDescription }).click();
  await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

  // Atrasa API 2s para validar que o drawer fecha antes da resposta
  await page.route('**/api/service-orders/*/complete', async route => {
    await new Promise(r => setTimeout(r, 2000));
    await route.continue();
  });

  await page.getByRole('button', { name: /concluir/i }).click();

  const confirmBtn = page.getByRole('button', { name: /confirmar/i });
  if (await confirmBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
    await confirmBtn.click();
  }

  // Optimistic UI: drawer fecha ANTES da API responder
  await expect(page.getByRole('dialog')).toBeHidden({ timeout: 500 });

  // Aguarda API completar e verifica estado final
  await page.waitForLoadState('networkidle', { timeout: 10000 });
  // Status PT: "Concluído" (masculino) — regex cobre ambas as formas
  await expect(
    page.getByRole('row').filter({ hasText: soDescription }).getByText(/concluíd/i)
  ).toBeVisible({ timeout: 6000 });
});
