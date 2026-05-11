import CrudPage from '@/Components/Common/CrudPage';

export default function SectorsIndex({ sectors, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.sectors"
      items={sectors}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/sectors"
      modalSize="lg"
    />
  );
}
