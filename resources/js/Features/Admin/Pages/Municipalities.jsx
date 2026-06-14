import CrudPage from '@/Components/Common/CrudPage';

export default function MunicipalitiesIndex({ municipalities, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.municipalities"
      items={municipalities}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/admin/municipalities"
    />
  );
}
