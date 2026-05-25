import { Users, Shield, UserPlus } from 'lucide-react';
import KpiCard from '@/Components/Common/KpiCard';
import RefreshIndicator from './RefreshIndicator';

export default function AdminDashboard({ kpis, recentUsers = [], countdown, onRefresh }) {
  const kpiCards = [
    { label: 'Total Utilizadores', value: kpis.total_users?.value,    color: 'blue',   icon: Users },
    { label: 'Roles Atribuídos',   value: kpis.active_roles?.value,   color: 'indigo', icon: Shield },
    { label: 'Novos Esta Semana',  value: kpis.new_users_week?.value, color: 'green',  icon: UserPlus },
  ];

  return (
    <>
      <div className="flex justify-end">
        <RefreshIndicator countdown={countdown} onRefresh={onRefresh} />
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {kpiCards.map((card) => (
          <KpiCard key={card.label} label={card.label} value={card.value} color={card.color} />
        ))}
      </div>

      <div className="rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
        <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
          <Users size={15} className="text-brand-mid" />
          <h2 className="text-sm font-semibold text-brand-darkest">Utilizadores Recentes</h2>
        </div>
        <div className="divide-y divide-brand-mid/10">
          {recentUsers.length === 0 ? (
            <p className="px-4 py-8 text-center text-sm text-brand-mid">Nenhum utilizador</p>
          ) : (
            recentUsers.map((u) => (
              <div key={u.id} className="flex items-center gap-3 px-4 py-3">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-light text-xs font-semibold text-brand-mid">
                  {u.name?.charAt(0)?.toUpperCase()}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-brand-darkest truncate">{u.name}</p>
                  <p className="text-xs text-brand-mid truncate">{u.email}</p>
                </div>
                <span className="text-xs text-brand-mid shrink-0">{u.roles}</span>
                <span className="text-xs text-brand-mid shrink-0 tabular-nums">{u.created_at}</span>
              </div>
            ))
          )}
        </div>
      </div>
    </>
  );
}
