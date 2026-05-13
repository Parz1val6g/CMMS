import CrudPage from '@/Components/Common/CrudPage';

export default function CountingTypesIndex({ counting_types, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.counting_types"
      items={counting_types}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/counting-types"
    />
  );
}
