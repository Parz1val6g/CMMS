import CrudPage from '@/Components/Common/CrudPage';

export default function TeamsIndex({ teams, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.teams"
      items={teams}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/teams"
      modalSize="lg"
    />
  );
}
