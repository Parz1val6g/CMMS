import CrudPage from '@/Components/Common/CrudPage';

export default function ServiceTypesIndex({ service_types, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.service_types"
      items={service_types}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/service-types"
    />
  );
}
