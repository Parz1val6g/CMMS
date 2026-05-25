import { useState, useEffect, useCallback } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import AdminDashboard       from '@/Features/Dashboard/Components/AdminDashboard';
import ManagerDashboard     from '@/Features/Dashboard/Components/ManagerDashboard';
import AttendantDashboard   from '@/Features/Dashboard/Components/AttendantDashboard';
import TaskManagerDashboard from '@/Features/Dashboard/Components/TaskManagerDashboard';
import SectorManagerDashboard from '@/Features/Dashboard/Components/SectorManagerDashboard';
import TeamManagerDashboard from '@/Features/Dashboard/Components/TeamManagerDashboard';
import WorkerDashboard      from '@/Features/Dashboard/Components/WorkerDashboard';

const REFRESH_INTERVAL = 60;

const DASHBOARDS = {
  admin:          AdminDashboard,
  manager:        ManagerDashboard,
  attendant:      AttendantDashboard,
  task_manager:   TaskManagerDashboard,
  sector_manager: SectorManagerDashboard,
  team_manager:   TeamManagerDashboard,
  worker:         WorkerDashboard,
};

export default function Dashboard({ role, kpis, attention, mapOrders, period: initialPeriod, recentUsers, recentOrders, teamWorkers }) {
  const [period,    setPeriod]    = useState(initialPeriod ?? 'week');
  const [countdown, setCountdown] = useState(REFRESH_INTERVAL);

  const refresh = useCallback(() => {
    router.reload({ preserveScroll: true });
    setCountdown(REFRESH_INTERVAL);
  }, []);

  const handlePeriodChange = useCallback((p) => {
    setPeriod(p);
    router.reload({ data: { period: p }, preserveScroll: true });
  }, []);

  useEffect(() => {
    const tick = setInterval(() => {
      setCountdown((c) => {
        if (c <= 1) { refresh(); return REFRESH_INTERVAL; }
        return c - 1;
      });
    }, 1000);
    return () => clearInterval(tick);
  }, [refresh]);

  const RoleDashboard = DASHBOARDS[role] ?? ManagerDashboard;

  return (
    <AppLayout title="Dashboard">
      <div className="flex h-full flex-col overflow-hidden">
        <div className="flex-1 overflow-y-auto">
          <div className="mx-auto max-w-7xl space-y-5 px-6 py-6">
            <RoleDashboard
              kpis={kpis}
              attention={attention}
              mapOrders={mapOrders}
              period={period}
              onPeriodChange={handlePeriodChange}
              countdown={countdown}
              onRefresh={refresh}
              recentUsers={recentUsers}
              recentOrders={recentOrders}
              teamWorkers={teamWorkers}
            />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
