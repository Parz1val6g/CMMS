import { test, expect } from '@playwright/test';
import { readFileSync, writeFileSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { USERS, login } from '../fixtures/users.js';

const STATE_PATH = join(dirname(fileURLToPath(import.meta.url)), '../.state/workflow.json');

function readState() {
  if (!existsSync(STATE_PATH)) return {};
  return JSON.parse(readFileSync(STATE_PATH, 'utf-8'));
}
function saveState(updates) {
  writeFileSync(STATE_PATH, JSON.stringify({ ...readState(), ...updates }, null, 2));
}

test('Worker cria Work Log para a sua Mini-Tarefa', async ({ page }) => {
  const { miniTaskIds, antonioWorkerId } = readState();
  expect(miniTaskIds?.length, 'Nenhuma mini-tarefa no estado — spec 04 falhou?').toBeGreaterThan(0);

  await login(page, USERS.worker);

  // Cria work log via API (o formulário requer mini_task_id select + datetime pickers)
  const result = await page.evaluate(async ({ miniTaskId, workerId }) => {
    const xsrfCookie = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';
    const xsrf = decodeURIComponent(xsrfCookie);

    const payload = {
      mini_task_id: miniTaskId,
      description:  'Trabalho de campo realizado — E2E test',
      started_at:   '2026-06-15T08:00',
      completed_at: '2026-06-15T12:00',
      worker_ids:   workerId ? [workerId] : [],
    };

    const resp = await fetch('/api/work-logs', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrf,
      },
      body: JSON.stringify(payload),
    });
    const body = await resp.json().catch(() => null);
    return { status: resp.status, ok: resp.ok, id: body?.data?.id ?? null, body };
  }, { miniTaskId: miniTaskIds[0], workerId: antonioWorkerId });

  expect(result.ok, `Work log POST falhou: ${result.status} — ${JSON.stringify(result.body)}`).toBeTruthy();

  // Navega à página de Work Logs e verifica que aparece "Aprovação Pendente" (status submitted)
  await page.getByRole('link', { name: /work.log/i }).click();
  await page.waitForLoadState('networkidle');
  // Status pode aparecer como "submitted" ou "Aprovação Pendente" dependendo da tradução
  await expect(
    page.getByText(/submitted|aprovação pendente/i).first()
  ).toBeVisible({ timeout: 8000 });

  saveState({ workLogId: result.id });
});
