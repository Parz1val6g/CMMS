import { useState, useCallback, useEffect, useRef } from 'react';
import CrudPage from '@/Components/Common/CrudPage';
import MiniTaskDrawer from '../Components/MiniTaskDrawer';

export default function MiniTasksIndex({ mini_tasks, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
    const [viewItem, setViewItem] = useState(null);
    const [viewLoading, setViewLoading] = useState(false);

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
                title="pages.sidebar.mini_tasks"
                items={mini_tasks}
                columns={columns}
                formSchema={formSchema}
                createFormSchema={createFormSchema}
                routes={routes}
                filterSchema={filterSchema}
                advancedFilterFields={advancedFilterFields}
                baseRoute="/mini-tasks"
                modalSize="lg"
                onRowClick={handleRowClick}
            />

            <MiniTaskDrawer
                isOpen={!!viewItem}
                onClose={() => setViewItem(null)}
                item={viewItem}
                loading={viewLoading}
            />
        </>
    );
}
