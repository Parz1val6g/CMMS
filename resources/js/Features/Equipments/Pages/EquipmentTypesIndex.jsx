import CrudPage from '@/Components/Common/CrudPage';

export default function EquipmentTypesIndex({ equipment_types, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.equipment_types"
      items={equipment_types}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/equipment-types"
    />
  );
}
