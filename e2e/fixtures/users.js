export const USERS = {
  attendant:       { email: 'ana.lima@cm-mangualde.pt',         password: 'password123' },
  manager:         { email: 'maria.pereira@cm-mangualde.pt',    password: 'password123', role: 'Gestor' },
  taskManager:     { email: 'sofia.marques@cm-mangualde.pt',    password: 'password123', role: 'Gestor de Tarefas' },
  miniTaskManager: { email: 'sofia.marques@cm-mangualde.pt',    password: 'password123', role: 'Gestor de Tarefas' },
  workLogManager:  { email: 'rita.silva@cm-mangualde.pt',       password: 'password123' },
  worker:          { email: 'antonio.ferreira@cm-mangualde.pt', password: 'password123' },
};

export async function login(page, user) {
  await page.goto('/login');
  await page.getByRole('textbox', { name: /email/i }).fill(user.email);
  await page.getByRole('textbox', { name: /palavra-passe/i }).fill(user.password);
  await page.getByRole('button', { name: /iniciar sessão/i }).click();
  if (user.role) {
    await page.waitForURL('**/select-role');
    await page.getByRole('button', { name: user.role, exact: true }).click();
  }
  await page.waitForURL('**/dashboard');
}

/** Fills the date range picker (start day and end day numbers in current month view) */
export async function fillDateRange(page, startDay, endDay) {
  await page.getByRole('button', { name: /selecione um intervalo/i }).click();
  await page.getByRole('button', { name: String(startDay), exact: true }).click();
  await page.getByRole('button', { name: String(endDay), exact: true }).click();
  // Confirm checkmark — enabled only when both dates are selected
  await page.locator('.flex.h-8.w-8.items-center.justify-center.rounded-full.transition-colors.text-brand-accent.bg-brand-accent\\/10').click();
}
