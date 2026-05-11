import CrudPage from '@/Components/Common/CrudPage';

export default function MiniTasksIndex({ mini_tasks, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.mini_tasks"
      items={mini_tasks}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/mini-tasks"
      modalSize="lg"
    />
  );
}
