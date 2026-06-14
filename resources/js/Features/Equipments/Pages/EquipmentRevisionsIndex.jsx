import CrudPage from '@/Components/Common/CrudPage';

export default function EquipmentRevisionsIndex({ equipment_revisions, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.equipment_revisions"
      items={equipment_revisions}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/equipment-revisions"
    />
  );
}
