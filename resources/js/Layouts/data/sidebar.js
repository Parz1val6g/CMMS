import {
  LayoutDashboard,
  ClipboardList,
  CheckSquare,
  ListChecks,
  FileClock,
  Wrench,
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
} from 'lucide-react';
import { t } from '@/utils/i18n';

/**
 * Sidebar navigation data — section groups with items.
 * `dev: true` items get a visual "Dev Preview" indicator in the sidebar.
 */
export const sections = [
  {
    label: null,
    items: [
      { label: t('pages.sidebar.dashboard'), icon: LayoutDashboard, href: '/dashboard', dev: false },
    ],
  },
  {
    label: t('pages.sidebar.section_operational'),
    items: [
      { label: t('pages.sidebar.service_orders'), icon: ClipboardList, href: '/service-orders', dev: false },
      { label: t('pages.sidebar.tasks'), icon: CheckSquare, href: '/tasks', dev: false },
      { label: t('pages.sidebar.mini_tasks'), icon: ListChecks, href: '/mini-tasks', dev: false },
      { label: t('pages.sidebar.work_logs'), icon: FileClock, href: '/work-logs', dev: false },
      { label: t('pages.sidebar.equipments'), icon: Wrench, href: '/equipments', dev: false },
    ],
  },
  {
    label: t('pages.sidebar.section_entities'),
    items: [
      { label: t('pages.sidebar.clients'), icon: Users, href: '/clients', dev: false },
      { label: t('pages.sidebar.locations'), icon: MapPin, href: '/locations', dev: false },
    ],
  },
  {
    label: t('pages.sidebar.section_hr'),
    items: [
      { label: t('pages.sidebar.sectors'), icon: Building2, href: '/sectors', dev: false },
      { label: t('pages.sidebar.teams'), icon: Group, href: '/teams', dev: false },
      { label: t('pages.sidebar.workers'), icon: UserCog, href: '/workers', dev: false },
    ],
  },
  {
    label: t('pages.sidebar.section_settings'),
    items: [
      { label: t('pages.sidebar.service_types'), icon: Package, href: '/service-types', dev: false },
      { label: t('pages.sidebar.materials'), icon: Package, href: '/materials', dev: false },
      { label: t('pages.sidebar.exports'), icon: Download, href: '/exports', dev: true },
      { label: t('pages.sidebar.notifications'), icon: Bell, href: '/notifications', dev: true },
      { label: t('pages.sidebar.analytics'), icon: BarChart3, href: '/analytics', dev: true },
    ],
  },
];

export const bottomItems = [
  { label: t('pages.sidebar.settings'), icon: Settings, href: '/settings', dev: false },
  { label: t('pages.sidebar.admin'), icon: Shield, href: '/admin', dev: false },
];
