import CrudPage from '@/Components/Common/CrudPage';

export default function ServiceOrderCategoriesIndex({ service_order_categories, columns, formSchema, createFormSchema, routes, filterSchema, advancedFilterFields }) {
  return (
    <CrudPage
      title="pages.sidebar.service_order_categories"
      items={service_order_categories}
      columns={columns}
      formSchema={formSchema}
      createFormSchema={createFormSchema}
      routes={routes}
      filterSchema={filterSchema}
      advancedFilterFields={advancedFilterFields}
      baseRoute="/service-order-categories"
    />
  );
}
