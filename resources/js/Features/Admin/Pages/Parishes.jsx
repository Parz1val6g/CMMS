import CrudPage from '@/Components/Common/CrudPage';

export default function ParishesIndex({ parishes, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.parishes"
      items={parishes}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/admin/parishes"
    />
  );
}
