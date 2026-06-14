import CrudPage from '@/Components/Common/CrudPage';

export default function DistrictsIndex({ districts, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.districts"
      items={districts}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/admin/districts"
    />
  );
}
