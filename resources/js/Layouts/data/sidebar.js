import {
  LayoutDashboard,
  ClipboardList,
  CheckSquare,
  ListChecks,
  FileClock,
  Wrench,
  Handshake,
  Users,
  MapPin,
  Building2,
  UserCog,
  Group,
  Package,
  Download,
  Bell,
  BarChart3,
  Settings,
  Shield,
  Ticket,
} from 'lucide-react';
import { t } from '@/utils/i18n';

/**
 * Sidebar navigation data — factory functions so translations resolve at
 * render time (not at module init), fixing language toggle without full reload.
 *
 * Taxonomy (enterprise ERP standard):
 *   Dashboard → Work Management → Assets → Parties → HR → Reports → Master Data
 *   Footer: Notifications · Settings · Admin
 */
export function getSections() {
  return [
    // ── 1. Home ──────────────────────────────────────────────────────────
    {
      label: null,
      items: [
        { label: t('pages.sidebar.dashboard'), icon: LayoutDashboard, href: '/dashboard', dev: false },
      ],
    },

    // ── 2. Work Management — daily operational workflow ───────────────────
    {
      label: t('pages.sidebar.section_operational'),
      items: [
        { label: t('pages.sidebar.tickets'),        icon: Ticket,       href: '/tickets',        dev: false, can: 'viewTickets' },
        { label: t('pages.sidebar.service_orders'), icon: ClipboardList, href: '/service-orders', dev: false, can: 'viewServiceOrders' },
        { label: t('pages.sidebar.tasks'),          icon: CheckSquare,   href: '/tasks',          dev: false, can: 'viewTasks' },
        { label: t('pages.sidebar.mini_tasks'),     icon: ListChecks,    href: '/mini-tasks',     dev: false, can: 'viewMiniTasks' },
        { label: t('pages.sidebar.work_logs'),      icon: FileClock,     href: '/work-logs',      dev: false, can: 'viewWorkLogs' },
      ],
    },

    // ── 3. Assets — physical equipment catalog and loan management ────────
    {
      label: t('pages.sidebar.section_assets'),
      items: [
        { label: t('pages.sidebar.equipments'),  icon: Wrench,    href: '/equipments',  dev: false, can: 'viewEquipments' },
        { label: t('pages.sidebar.loan_orders'), icon: Handshake, href: '/loan-orders', dev: false, can: 'viewLoanOrders' },
      ],
    },

    // ── 4. Parties — clients, external organisations, locations ───────────
    {
      label: t('pages.sidebar.section_entities'),
      items: [
        { label: t('pages.sidebar.clients'),   icon: Users,     href: '/clients',   dev: false, can: 'viewClients' },
        { label: t('pages.sidebar.entities'),  icon: Building2, href: '/entities',  dev: false, can: 'viewEntities' },
        { label: t('pages.sidebar.locations'), icon: MapPin,    href: '/locations', dev: false, can: 'viewLocations' },
      ],
    },

    // ── 5. Human Resources — org structure and workforce ─────────────────
    {
      label: t('pages.sidebar.section_hr'),
      items: [
        { label: t('pages.sidebar.sectors'), icon: Building2, href: '/sectors', dev: false, can: 'viewSectors' },
        { label: t('pages.sidebar.teams'),   icon: Group,     href: '/teams',   dev: false, can: 'viewTeams' },
        { label: t('pages.sidebar.workers'), icon: UserCog,   href: '/workers', dev: false, can: 'viewWorkers' },
      ],
    },

    // ── 6. Reports — analytics and data exports (elevated from Settings) ──
    {
      label: t('pages.sidebar.section_reports'),
      items: [
        { label: t('pages.sidebar.analytics'), icon: BarChart3, href: '/analytics', dev: true },
        { label: t('pages.sidebar.exports'),   icon: Download,  href: '/exports',   dev: true },
      ],
    },

    // ── 7. Master Data — reference taxonomies and resource catalogs ───────
    {
      label: t('pages.sidebar.section_settings'),
      items: [
        { label: t('pages.sidebar.service_types'),   icon: Package, href: '/service-types',   dev: false, can: 'viewServiceTypes' },
        { label: t('pages.sidebar.equipment_types'), icon: Wrench,  href: '/equipment-types', dev: false, can: 'viewEquipmentTypes' },
        { label: t('pages.sidebar.counting_types'),  icon: Wrench,  href: '/counting-types',  dev: false, can: 'viewCountingTypes' },
        { label: t('pages.sidebar.materials'),       icon: Package, href: '/materials',        dev: false, can: 'viewMaterials' },
      ],
    },

    // ── 2. Work Management — daily operational workflow ───────────────────
    // Tickets → Service Orders → Tasks → Mini-Tasks → Work Logs
    {
      label: t('pages.sidebar.section_operational'),
      items: [
        { label: t('pages.sidebar.tickets'),        icon: Ticket,       href: '/tickets',        dev: false },
        { label: t('pages.sidebar.service_orders'), icon: ClipboardList, href: '/service-orders', dev: false },
        { label: t('pages.sidebar.tasks'),          icon: CheckSquare,   href: '/tasks',          dev: false },
        { label: t('pages.sidebar.mini_tasks'),     icon: ListChecks,    href: '/mini-tasks',     dev: false },
        { label: t('pages.sidebar.work_logs'),      icon: FileClock,     href: '/work-logs',      dev: false },
      ],
    },

    // ── 3. Assets — physical equipment catalog and loan management ────────
    {
      label: t('pages.sidebar.section_assets'),
      items: [
        { label: t('pages.sidebar.equipments'),  icon: Wrench,    href: '/equipments',  dev: false },
        { label: t('pages.sidebar.loan_orders'), icon: Handshake, href: '/loan-orders', dev: false },
      ],
    },

    // ── 4. Parties — clients, external organisations, locations ───────────
    {
      label: t('pages.sidebar.section_entities'),
      items: [
        { label: t('pages.sidebar.clients'),   icon: Users,     href: '/clients',   dev: false },
        { label: t('pages.sidebar.entities'),  icon: Building2, href: '/entities',  dev: false },
        { label: t('pages.sidebar.locations'), icon: MapPin,    href: '/locations', dev: false },
      ],
    },

    // ── 5. Human Resources — org structure and workforce ─────────────────
    {
      label: t('pages.sidebar.section_hr'),
      items: [
        { label: t('pages.sidebar.sectors'), icon: Building2, href: '/sectors', dev: false },
        { label: t('pages.sidebar.teams'),   icon: Group,     href: '/teams',   dev: false },
        { label: t('pages.sidebar.workers'), icon: UserCog,   href: '/workers', dev: false },
      ],
    },

    // ── 6. Reports — analytics and data exports (elevated from Settings) ──
    {
      label: t('pages.sidebar.section_reports'),
      items: [
        { label: t('pages.sidebar.analytics'), icon: BarChart3, href: '/analytics', dev: true },
        { label: t('pages.sidebar.exports'),   icon: Download,  href: '/exports',   dev: true },
      ],
    },

    // ── 7. Master Data — reference taxonomies and resource catalogs ───────
    // (renamed from "Configurações" to resolve naming conflict with footer Settings)
    {
      label: t('pages.sidebar.section_settings'),
      items: [
        { label: t('pages.sidebar.service_types'),   icon: Package, href: '/service-types',   dev: false },
        { label: t('pages.sidebar.equipment_types'), icon: Wrench,  href: '/equipment-types', dev: false },
        { label: t('pages.sidebar.counting_types'),  icon: Wrench,  href: '/counting-types',  dev: false },
        { label: t('pages.sidebar.materials'),       icon: Package, href: '/materials',        dev: false },
      ],
    },
  ];
}

export function getBottomItems() {
  return [
    { label: t('pages.sidebar.notifications'), icon: Bell,     href: '/notifications', dev: true,  can: 'viewNotifications' },
    { label: t('pages.sidebar.settings'),      icon: Settings, href: '/settings',      dev: false, can: 'viewSettings' },
    { label: t('pages.sidebar.admin'),         icon: Shield,   href: '/admin',         dev: false, can: 'viewUsers' },
  ];
}
