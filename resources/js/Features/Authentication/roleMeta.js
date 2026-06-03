import {
  Shield, Briefcase, Wrench, Eye, HardHat, User, Building2,
  ClipboardCheck, ListChecks, Clock, Headphones, Ticket, Users,
} from 'lucide-react';

/**
 * Shared role metadata — icon, brand hue, and description (pt_PT).
 * Single source of truth used by RolePalette, SelectRole, and RoleSwitcher.
 */
export const ROLE_META = {
  admin:             { icon: Shield,          hue: '#2a9d8f', description: 'Administração do sistema, utilizadores e permissões' },
  manager:           { icon: Briefcase,        hue: '#2a9d8f', description: 'Gestão de ordens de serviço, ativação e conclusão' },
  equipment_manager:  { icon: Wrench,           hue: '#e07b39', description: 'Gestão de equipamentos e ciclo de empréstimos' },
  supervisor:        { icon: Eye,              hue: '#5b8def', description: 'Supervisão de operações e equipas no terreno' },
  worker:            { icon: HardHat,          hue: '#6b7280', description: 'Execução de mini-tarefas no terreno com registos de trabalho' },
  client:            { icon: User,             hue: '#9ca3af', description: 'Cidadão que reporta ocorrências' },
  entidade:          { icon: Building2,        hue: '#6366f1', description: 'Portal de entidade externa para pedidos de empréstimo' },
  task_manager:      { icon: ClipboardCheck,   hue: '#f59e0b', description: 'Divisão de tarefas em mini-tarefas e atribuição de recursos' },
  mini_task_manager:  { icon: ListChecks,         hue: '#8b5cf6', description: 'Gestão de mini-tarefas e atribuição a trabalhadores' },
  work_log_manager:  { icon: Clock,            hue: '#10b981', description: 'Revisão e gestão de registos de trabalho' },
  sector_manager:    { icon: Building2,        hue: '#0ea5e9', description: 'Gestão de equipas e trabalhadores do setor' },
  attendant:         { icon: Headphones,       hue: '#ef4444', description: 'Atendimento telefónico e criação de ordens de serviço' },
  ticket_manager:    { icon: Ticket,           hue: '#ec4899', description: 'Gestão de tickets e triagem de ocorrências' },
  team_manager:      { icon: Users,            hue: '#14b8a6', description: 'Gestão da composição das equipas' },
};

const RECENT_STORAGE_KEY = 'role-palette-recent';
const MAX_RECENT = 4;

/**
 * Get recently used role names from localStorage.
 * @returns {string[]}
 */
export function getRecentRoles() {
  try {
    const raw = localStorage.getItem(RECENT_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

/**
 * Record a role selection to the recent list (moves to front, max 4).
 * @param {string} roleName
 */
export function recordRecentRole(roleName) {
  const recent = getRecentRoles().filter((r) => r !== roleName);
  recent.unshift(roleName);
  try {
    localStorage.setItem(RECENT_STORAGE_KEY, JSON.stringify(recent.slice(0, MAX_RECENT)));
  } catch {
    // localStorage may be unavailable
  }
}
