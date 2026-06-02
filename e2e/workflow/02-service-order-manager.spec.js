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

let state;
test.beforeAll(() => { state = readState(); });

test('Gestor revê OS criada pelo atendente', async ({ page }) => {
  await login(page, USERS.manager);
  await page.getByRole('link', { name: 'Ordens Serviço' }).click();

  await page.getByRole('cell', { name: state.soDescription }).click();
  await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

  // Verifica que a OS está Pendente
  await expect(page.getByRole('dialog').getByText(/pendente/i)).toBeVisible();

  await page.keyboard.press('Escape');
});

test('Gestor activa OS [Optimistic UI — drawer fecha < 500ms]', async ({ page }) => {
  await login(page, USERS.manager);
  await page.getByRole('link', { name: 'Ordens Serviço' }).click();

  await page.getByRole('cell', { name: state.soDescription }).click();
  await expect(page.getByRole('dialog')).toBeVisible({ timeout: 5000 });

  // Atrasa a API 2s para provar que o UI não espera por ela
  await page.route('**/api/service-orders/*/activate', async route => {
    await new Promise(r => setTimeout(r, 2000));
    await route.continue();
  });

  await page.getByRole('button', { name: /ativar/i }).click();

  // Se houver diálogo de confirmação
  const confirmBtn = page.getByRole('button', { name: /confirmar/i });
  if (await confirmBtn.isVisible({ timeout: 1000 }).catch(() => false)) {
    await confirmBtn.click();
  }

  // Optimistic UI: drawer fecha ANTES da API responder (< 500ms)
  await expect(page.getByRole('dialog')).toBeHidden({ timeout: 500 });

  // Aguarda a API completar (a rota tem 2s de delay artificial)
  await page.waitForLoadState('networkidle', { timeout: 10000 });

  // Após API responder, OS aparece como "Em Progresso" na listagem
  await expect(page.getByText(/em progresso/i).first()).toBeVisible({ timeout: 3000 });
});
