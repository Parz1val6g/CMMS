import CrudPage from '@/Components/Common/CrudPage';

export default function EquipmentsIndex({ equipments, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.equipments"
      items={equipments}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/equipments"
      modalSize="lg"
    />
  );
}
