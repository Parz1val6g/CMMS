import { useState, useCallback } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { t } from '@/utils/i18n';
import { labelFor, badgeStyle } from '@/utils/enums';

function KpiCard({ label, value, colorClass }) {
  return (
    <div className={`card bg-brand-dark border-brand-mid shadow-sm flex-fill`}>
      <div className="card-body text-center py-3">
        <div className={`h3 fw-bold mb-1 ${colorClass}`}>{value}</div>
        <div className="text-brand-mid small">{label}</div>
      </div>
    </div>
  );
}

export default function EntityDashboard({ entity, loan_orders, stats, routes }) {
  const [createOpen, setCreateOpen] = useState(false);

  const breadcrumbs = [
    { label: t('pages.sidebar.dashboard'), href: '/dashboard' },
    { label: entity?.name ?? t('pages.entities.page_title') },
  ];

  const handleNewLoan = useCallback(() => {
    window.location.href = '/loan-orders?new=1';
  }, []);

  return (
    <AppLayout title={entity?.name ?? t('pages.entities.page_title')}>
      <div className="container-fluid px-4 py-3">

        {/* Header */}
        <div className="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 className="text-brand-white fw-bold mb-0">{entity?.name}</h4>
            <small className="text-brand-mid">{t('pages.entities.dashboard_subtitle')}</small>
          </div>
          <button className="btn btn-accent btn-sm" onClick={handleNewLoan}>
            {t('pages.entities.action_new_loan')}
          </button>
        </div>

        {/* KPI Cards */}
        <div className="d-flex gap-3 mb-4 flex-wrap">
          <KpiCard
            label={t('pages.entities.kpi_pending')}
            value={stats?.pending ?? 0}
            colorClass="text-warning"
          />
          <KpiCard
            label={t('pages.entities.kpi_active')}
            value={stats?.active ?? 0}
            colorClass="text-brand-accent"
          />
          <KpiCard
            label={t('pages.entities.kpi_completed')}
            value={stats?.completed ?? 0}
            colorClass="text-success"
          />
        </div>

        {/* Loan Orders Table */}
        <div className="card bg-brand-dark border-brand-mid shadow-sm">
          <div className="card-header border-brand-mid">
            <h6 className="text-brand-white fw-bold mb-0">{t('pages.entities.loan_orders_title')}</h6>
          </div>
          <div className="card-body p-0">
            {loan_orders?.data?.length === 0 ? (
              <div className="text-brand-mid p-4 text-center">{t('pages.entities.no_loans')}</div>
            ) : (
              <div className="table-responsive">
                <table className="table table-dark table-hover mb-0">
                  <thead>
                    <tr className="text-brand-mid small">
                      <th>{t('pages.loan_orders.col_reference')}</th>
                      <th>{t('pages.loan_orders.col_status')}</th>
                      <th>{t('pages.entities.col_equipment')}</th>
                      <th>{t('pages.loan_orders.col_created')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {(loan_orders?.data ?? []).map((lo) => (
                      <tr key={lo.id}>
                        <td className="text-brand-white fw-semibold small">{lo.reference}</td>
                        <td>
                          <span className={badgeStyle(lo.status)}>{labelFor(lo.status)}</span>
                        </td>
                        <td className="text-brand-mid small">
                          {lo.equipments?.map((eq) => eq.name).join(', ') || '-'}
                        </td>
                        <td className="text-brand-mid small">{lo.created_at}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>

      </div>
    </AppLayout>
  );
}
