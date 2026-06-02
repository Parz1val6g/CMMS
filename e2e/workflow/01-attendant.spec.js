import { test, expect } from '@playwright/test';
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { USERS, login, fillDateRange } from '../fixtures/users.js';

const STATE_PATH = join(dirname(fileURLToPath(import.meta.url)), '../.state/workflow.json');

function readState() {
  if (!existsSync(STATE_PATH)) return {};
  return JSON.parse(readFileSync(STATE_PATH, 'utf-8'));
}
function saveState(updates) {
  mkdirSync(dirname(STATE_PATH), { recursive: true });
  writeFileSync(STATE_PATH, JSON.stringify({ ...readState(), ...updates }, null, 2));
}

test('Atendente cria Ordem de Serviço', async ({ page }) => {
  await login(page, USERS.attendant);
  await page.getByRole('link', { name: 'Ordens Serviço' }).click();
  await page.getByRole('button', { name: 'Novo Ordem de Serviço' }).click();

  // Descrição (identificador único para o teste)
  const soDescription = `OS E2E ${Date.now()}`;
  await page.locator('textarea[name="description"]').fill(soDescription);

  // Data de Início (Junho 2026 já está visível)
  await fillDateRange(page, 15, 28);

  // Setores (multiselect custom combobox)
  await page.getByRole('combobox').first().click();
  await page.getByText('Departamento de Água e').click();
  // Fecha o dropdown clicando fora (no heading do modal)
  await page.getByRole('heading', { name: 'Detalhes Principais' }).click();

  // Gestor da Ordem de Serviço (native select)
  await page.locator('select[name="manager_id"]').selectOption({ label: 'Maria Pereira' });
 
  // Localização (Braga > Amares > Bouro Santa Maria)
  // A cascata está dentro de um único div.grid.grid-cols-3.gap-3
  const cascade = page.locator('.grid.grid-cols-3.gap-3');

  // Distrito (1.ª coluna)
  await cascade.locator('[role=combobox], select').nth(0).click();
  await page.getByText('Braga', { exact: true }).click();

  // Município (2.ª coluna — activa após seleccionar Distrito)
  await cascade.locator('[role=combobox], select').nth(1).click();
  await page.getByText('Amares').click();

  // Freguesia (3.ª coluna — activa após seleccionar Município)
  await cascade.locator('[role=combobox], select').nth(2).click();
  await page.getByText('Bouro (Santa Maria)').click();

  // Rua (obrigatória)
  await page.getByRole('textbox', { name: 'Rua*' }).fill('Rua E2E Playwright');

  // Submeter
  await page.getByRole('button', { name: 'Guardar Registo' }).click();

  // Modal deve fechar após criação
  await expect(page.getByRole('dialog')).toBeHidden({ timeout: 10000 });

  // Filtra a tabela pela descrição para encontrar a nova OS (independente da ordenação)
  await page.waitForLoadState('networkidle');
  await page.getByPlaceholder(/pesquisar/i).fill(soDescription.substring(0, 20));
  await page.waitForLoadState('networkidle');
  const row = page.getByRole('row').filter({ hasText: soDescription });
  await expect(row).toBeVisible({ timeout: 8000 });

  // Extrai a referência da 2.ª célula (1.ª é o checkbox de selecção)
  const soReference = (await row.locator('td').nth(1).textContent()).trim();

  saveState({ soReference, soDescription });
  expect(soReference).toMatch(/OS\d+/);
});
