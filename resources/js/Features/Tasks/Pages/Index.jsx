import CrudPage from '@/Components/Common/CrudPage';

export default function TasksIndex({ tasks, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields}) {
  return (
    <CrudPage
      title="pages.sidebar.tasks"
      items={tasks}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/tasks"
      modalSize="lg"
    />
  );
}
