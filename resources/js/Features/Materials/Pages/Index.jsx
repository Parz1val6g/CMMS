import CrudPage from '@/Components/Common/CrudPage';

export default function MaterialsIndex({ materials, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.materials"
      items={materials}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/materials"
      modalSize="lg"
    />
  );
}
