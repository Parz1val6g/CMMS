import { usePage, Link } from '@inertiajs/react';
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

// ── Section definitions ────────────────────────────────────────────────
// `dev: true` items get a visual "Dev Preview" indicator
const sections = [
  {
    label: null,
    items: [
      { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard', dev: false },
    ],
  },
  {
    label: 'Operacional',
    items: [
      { label: 'Ordens Serviço', icon: ClipboardList, href: '/service-orders', dev: false },
      { label: 'Tarefas', icon: CheckSquare, href: '/tasks', dev: false },
      { label: 'Mini-Tarefas', icon: ListChecks, href: '/mini-tasks', dev: false },
      { label: 'Work Logs', icon: FileClock, href: '/work-logs', dev: false },
      { label: 'Equipamentos', icon: Wrench, href: '/equipments', dev: true },
    ],
  },
  {
    label: 'Entidades',
    items: [
      { label: 'Clientes', icon: Users, href: '/clients', dev: false },
      { label: 'Localizações', icon: MapPin, href: '/locations', dev: false },
    ],
  },
  {
    label: 'Recursos Humanos',
    items: [
      { label: 'Sectores', icon: Building2, href: '/sectors', dev: false },
      { label: 'Equipas', icon: Group, href: '/teams', dev: false },
      { label: 'Trabalhadores', icon: UserCog, href: '/workers', dev: false },
    ],
  },
  {
    label: 'Configurações',
    items: [
      { label: 'Tipos Serviço', icon: Package, href: '/service-types', dev: false },
      { label: 'Materiais', icon: Package, href: '/materials', dev: false },
      { label: 'Exportações', icon: Download, href: '/exports', dev: true },
      { label: 'Notificações', icon: Bell, href: '/notifications', dev: true },
      { label: 'Analytics', icon: BarChart3, href: '/analytics', dev: true },
    ],
  },
];

const bottomItems = [
  { label: 'Configurações', icon: Settings, href: '/settings', dev: false },
  { label: 'Admin', icon: Shield, href: '/admin', dev: false },
];

// ── Sub-components ──────────────────────────────────────────────────────

function NavItem({ item }) {
  const { url } = usePage();
  const isActive = url === item.href || url.startsWith(item.href + '/');
  const Icon = item.icon;

  return (
    <Link
      href={item.href}
      className={`group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
        ${isActive
          ? 'bg-indigo-600 text-white shadow-sm'
          : 'text-slate-300 hover:bg-slate-800 hover:text-white'
        }
        ${item.dev ? 'opacity-60 hover:opacity-100' : ''}
      `}
    >
      <Icon className="h-5 w-5 shrink-0" />
      <span className="flex-1 truncate">{item.label}</span>
      {item.dev && (
        <span className="shrink-0 rounded-full bg-indigo-900/60 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-indigo-300 ring-1 ring-indigo-700/40">
          Dev
        </span>
      )}
    </Link>
  );
}

function NavSection({ section }) {
  return (
    <div>
      {section.label && (
        <p className="px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
          {section.label}
        </p>
      )}
      <nav className="space-y-0.5">
        {section.items.map((item) => (
          <NavItem key={item.href} item={item} />
        ))}
      </nav>
    </div>
  );
}

// ── Main Sidebar ────────────────────────────────────────────────────────

export default function Sidebar() {
  const { auth } = usePage().props;

  return (
    <aside className="w-64 flex flex-col h-full bg-slate-950 border-r border-slate-800 shrink-0">
      {/* Logo */}
      <div className="shrink-0 p-4">
        <span className="text-lg font-bold tracking-tight text-white">ERP Gestão</span>
      </div>

      {/* Navigation */}
      <div className="flex-1 overflow-y-auto px-3 space-y-4 py-2">
        {sections.map((section, i) => (
          <NavSection key={section.label ?? `top-${i}`} section={section} />
        ))}
      </div>

      {/* Bottom — Settings, Admin, User */}
      <div className="shrink-0 mt-auto border-t border-slate-800 p-4 space-y-1">
        {bottomItems.map((item) => (
          <NavItem key={item.href} item={item} />
        ))}
        {auth?.user && (
          <Link
            href="/profile"
            className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 hover:bg-slate-800 hover:text-white transition-colors"
          >
            <div className="flex h-5 w-5 shrink-0 items-center justify-center">
              <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <span className="truncate">{auth.user.first_name} {auth.user.last_name}</span>
          </Link>
        )}
      </div>
    </aside>
  );
}
