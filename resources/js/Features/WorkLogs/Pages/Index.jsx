import CrudPage from '@/Components/Common/CrudPage';

export default function WorkLogsIndex({ work_logs, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
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
    />
  );
}
