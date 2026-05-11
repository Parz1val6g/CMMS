import CrudPage from '@/Components/Common/CrudPage';

export default function LocationsIndex({ locations, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.locations"
      items={locations}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/locations"
    />
  );
}
