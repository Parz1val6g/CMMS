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

test('Mini-Task Manager cria Mini-Tarefas', async ({ page }) => {
  const { soReference, taskCount } = readState();
  await login(page, USERS.miniTaskManager);
  await page.getByRole('link', { name: 'Tarefas', exact: true }).click();

  const taskRows = page.getByRole('row').filter({ hasText: soReference });
  await expect(taskRows.first()).toBeVisible({ timeout: 10000 });
  const count = taskCount ?? await taskRows.count();

  const miniTaskIds = [];

  for (let i = 0; i < count; i++) {
    // Click the task row and capture the full task data (includes task UUID)
    const responsePromise = page.waitForResponse(
      resp => /\/api\/tasks\/[^?/]+$/.test(resp.url()) && resp.status() === 200,
      { timeout: 8000 }
    );

    const rows = page.getByRole('row').filter({ hasText: soReference });
    await rows.nth(i).click();
    await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

    const taskResp = await responsePromise;
    const taskData = await taskResp.json();
    const taskId = taskData.data?.id;
    expect(taskId, `Task ${i + 1}: UUID não encontrado`).toBeTruthy();

    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);

    // Cria mini-tarefa via API (o DatePicker de datas no formulário não é testável via Playwright)
    const result = await page.evaluate(async ({ tId, idx }) => {
      const xsrfCookie = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';
      const xsrf = decodeURIComponent(xsrfCookie);

      // Procura o worker_id de António Ferreira (search por nome, não email)
      const wResp = await fetch('/api/workers?search=Ant%C3%B3nio+Ferreira', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrf },
      });
      const wData = wResp.ok ? await wResp.json() : null;
      const workers = wData?.data ?? wData ?? [];
      const antonio = workers[0] ?? null;

      const payload = {
        task_id:     tId,
        description: `Mini-Tarefa E2E ${idx + 1}`,
        start_date:  '2026-06-15',
        end_date:    '2026-06-28',
        worker_ids:  antonio ? [antonio.id] : [],
      };

      const resp = await fetch('/api/mini-tasks', {
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
    }, { tId: taskId, idx: i });

    expect(result.ok, `Mini-task ${i + 1}: POST falhou com ${result.status} — ${JSON.stringify(result.body)}`).toBeTruthy();
    if (result.id) miniTaskIds.push(result.id);
  }

  // Verifica que as mini-tarefas criadas aparecem na drawer
  const rows = page.getByRole('row').filter({ hasText: soReference });
  await rows.first().click();
  await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });
  const miniTab = page.getByRole('button', { name: 'Mini-Tarefas', exact: true });
  if (await miniTab.isVisible({ timeout: 2000 }).catch(() => false)) {
    await miniTab.click();
    await expect(page.getByText(/MT\d{8,}/i).first()).toBeVisible({ timeout: 5000 });
  }
  await page.keyboard.press('Escape');

  // Save worker ID for spec 05 (re-read from DB to be sure we have it)
  const antonioWorkerIdForState = await page.evaluate(async () => {
    const xsrfCookie = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';
    const xsrf = decodeURIComponent(xsrfCookie);
    const resp = await fetch('/api/workers?search=Ant%C3%B3nio+Ferreira', {
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrf },
    });
    const data = resp.ok ? await resp.json() : null;
    return (data?.data ?? data ?? [])[0]?.id ?? null;
  });

  saveState({ miniTaskIds, antonioWorkerId: antonioWorkerIdForState });
  expect(miniTaskIds.length, 'Nenhuma mini-tarefa criada').toBeGreaterThan(0);
});
