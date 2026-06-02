import { useState, useCallback, useEffect, useRef } from 'react';
import CrudPage from '@/Components/Common/CrudPage';
import TaskDrawer from '@/Components/Shared/TaskDrawer';
import ServiceOrderDrawer from '@/Components/Shared/ServiceOrderDrawer';
import { csrfHeader } from '@/utils/csrf';

export default function TasksIndex({ tasks, columns, formSchema, createFormSchema, miniTaskCreateSchema, routes, filterSchema, advancedFilterFields }) {
    const [viewItem, setViewItem] = useState(null);
    const [viewLoading, setViewLoading] = useState(false);
    const [tableRefreshKey, setTableRefreshKey] = useState(0);

    const [soDrawerOpen, setSoDrawerOpen] = useState(false);
    const [soOrder, setSoOrder] = useState(null);
    const [soLoading, setSoLoading] = useState(false);

    const handleRowClick = useCallback(async (item) => {
        setViewLoading(true);
        setViewItem(item);
        try {
            const url = routes.show.replace('__ID__', item.id);
            const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            setViewItem(json.data);
        } finally {
            setViewLoading(false);
        }
    }, [routes.show]);

    const handleOpenSODrawer = useCallback(async (soId) => {
        setSoDrawerOpen(true);
        setSoLoading(true);
        setSoOrder(null);
        try {
            const res = await fetch(`/api/service-orders/${soId}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
            });
            if (!res.ok) throw new Error();
            const body = await res.json();
            setSoOrder(body.data ?? body);
        } finally {
            setSoLoading(false);
        }
    }, []);

    const handleCloseSODrawer = useCallback(() => {
        setSoDrawerOpen(false);
    }, []);

    // Intercept clicks on SO links to open drawer in-place instead of navigating away
    useEffect(() => {
        const handler = (e) => {
            const link = e.target.closest('a[href*="/service-orders?view="]');
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            const match = link.getAttribute('href').match(/[?&]view=([^&]+)/);
            if (match) handleOpenSODrawer(match[1]);
        };
        document.addEventListener('click', handler, true);
        return () => document.removeEventListener('click', handler, true);
    }, [handleOpenSODrawer]);

    // Auto-open drawer when arriving via ?view=<id> link
    const viewParamHandled = useRef(false);
    useEffect(() => {
        if (viewParamHandled.current) return;
        viewParamHandled.current = true;
        const id = new URLSearchParams(window.location.search).get('view');
        if (!id) return;
        window.history.replaceState({}, '', window.location.pathname);
        handleRowClick({ id });
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            <CrudPage
                title="pages.sidebar.tasks"
                items={tasks}
                columns={columns}
                formSchema={formSchema}
                createFormSchema={createFormSchema}
                routes={routes}
                filterSchema={filterSchema}
                advancedFilterFields={advancedFilterFields}
                baseRoute="/tasks"
                modalSize="lg"
                onRowClick={handleRowClick}
                refreshKey={tableRefreshKey}
            />

            <TaskDrawer
                isOpen={!!viewItem}
                onClose={() => setViewItem(null)}
                item={viewItem}
                loading={viewLoading}
                miniTaskCreateSchema={miniTaskCreateSchema}
                onCompleted={() => {
                    if (viewItem) handleRowClick(viewItem);
                    setTableRefreshKey(k => k + 1);
                }}
            />

            <ServiceOrderDrawer
                order={soOrder}
                isOpen={soDrawerOpen}
                loading={soLoading}
                onClose={handleCloseSODrawer}
            />
        </>
    );
}
