import CrudPage from '@/Components/Common/CrudPage';

export default function WorkersIndex({ workers, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.workers"
      items={workers}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/workers"
      modalSize="lg"
    />
  );
}
