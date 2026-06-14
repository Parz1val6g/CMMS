import { useState, useCallback } from 'react';
import CrudPage from '@/Components/Common/CrudPage';
import WorkLogDrawer from '../Components/WorkLogDrawer';

export default function WorkLogsIndex({ work_logs, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
    const [viewItem, setViewItem] = useState(null);
    const [viewLoading, setViewLoading] = useState(false);

    const handleRowClick = useCallback(async (item) => {
        setViewLoading(true);
        setViewItem(item);
        try {
            const url = routes.show.replace('__ID__', item.id);
            const res = await fetch(url, { credentials: 'include', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            setViewItem(json.data);
        } finally {
            setViewLoading(false);
        }
    }, [routes.show]);

    return (
        <>
            <CrudPage
                title="pages.sidebar.work_logs"
                items={work_logs}
                columns={columns}
                formSchema={formSchema}
                createFormSchema={createFormSchema}
                routes={routes}
                filterSchema={filterSchema}
                advancedFilterFields={advancedFilterFields}
                baseRoute="/work-logs"
                modalSize="lg"
                onRowClick={handleRowClick}
            />

            <WorkLogDrawer
                isOpen={!!viewItem}
                onClose={() => setViewItem(null)}
                item={viewItem}
                loading={viewLoading}
            />
        </>
    );
}
