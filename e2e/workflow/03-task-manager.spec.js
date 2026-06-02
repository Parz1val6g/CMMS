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

test('Task Manager define datas nas Tasks criadas pela activação', async ({ page }) => {
  const { soReference } = readState();
  await login(page, USERS.taskManager);
  await page.getByRole('link', { name: 'Tarefas', exact: true }).click();

  const taskRows = page.getByRole('row').filter({ hasText: soReference });
  await expect(taskRows.first()).toBeVisible({ timeout: 10000 });
  const taskCount = await taskRows.count();
  expect(taskCount).toBeGreaterThan(0);
  let taskIds;

  for (let i = 0; i < taskCount; i++) {
    // Intercept the task show API call BEFORE clicking the row
    const responsePromise = page.waitForResponse(
      resp => /\/api\/tasks\/[^?/]+$/.test(resp.url()) && resp.status() === 200,
      { timeout: 8000 }
    );

    const row = page.getByRole('row').filter({ hasText: soReference }).nth(i);
    await row.click();
    await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

    // Extract the task UUID from the API response
    const resp = await responsePromise;
    const taskData = await resp.json();
    const taskId = taskData.data?.id;
    expect(taskId, `Task ${i + 1}: UUID não encontrado na resposta da API`).toBeTruthy();

    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);

    // Update task dates via fetch in browser context (has session + XSRF cookie)
    const result = await page.evaluate(async (id) => {
      const xsrfCookie = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '';
      const xsrf = decodeURIComponent(xsrfCookie);
      const resp = await fetch(`/api/tasks/${id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': xsrf,
        },
        body: JSON.stringify({ start_date: '2026-06-15', end_date: '2026-06-28' }),
      });
      const body = await resp.json().catch(() => null);
      return { status: resp.status, ok: resp.ok, body };
    }, taskId);

    expect(
      result.ok,
      `Task ${i + 1} (${taskId}): PUT falhou com status ${result.status} — ${JSON.stringify(result.body)}`
    ).toBeTruthy();
    if (!taskIds) taskIds = [];
    taskIds.push(taskId);
  }

  saveState({ taskCount, taskIds: taskIds ?? [] });
});
