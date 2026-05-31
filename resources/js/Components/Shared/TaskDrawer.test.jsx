import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { MiniTasksTab } from '@/Components/Shared/TaskDrawer';

// Mock the FormField component
vi.mock('@/Components/Common/FormField', () => ({
  default: ({ field, value, error, onChange }) => (
    <div data-testid={`form-field-${field?.name ?? field?.key}`}>
      <label>{field?.label}</label>
      <input
        type="text"
        value={value ?? ''}
        onChange={(e) => onChange(e.target.value)}
        data-testid={`input-${field?.name ?? field?.key}`}
      />
      {error && <span data-testid={`error-${field?.name ?? field?.key}`}>{error}</span>}
    </div>
  ),
}));

vi.mock('@/Components/Toast/ToastContext', () => ({
  useToast: () => ({ error: vi.fn() }),
}));

vi.mock('@/utils/i18n', () => ({
  t: (key) => {
    const map = {
      'pages.tasks.drawer.task_period_label': 'Task Period',
      'pages.tasks.drawer.cancel': 'Cancel',
      'pages.tasks.drawer.new_mini_task': 'New Mini-Task',
      'pages.tasks.drawer.save_mini_task': 'Create Mini-Task',
      'pages.tasks.drawer.no_period_tooltip': 'Define period first',
      'pages.tasks.drawer.mini_task_create_error': 'Failed',
      'pages.tasks.drawer.tab_mini_tasks': 'Mini Tasks',
      'pages.tasks.drawer.th_reference': 'Ref',
      'pages.tasks.drawer.th_status': 'Status',
      'pages.tasks.drawer.no_mini_tasks': 'No mini-tasks',
    };
    return map[key] ?? key;
  },
}));

vi.mock('@/utils/csrf', () => ({
  csrfHeader: () => ({}),
}));

describe('MiniTasksTab', () => {
  const defaultProps = {
    miniTasks: [],
    taskId: 'task-1',
    schema: [
      { name: 'description', key: 'description', label: 'Description', type: 'textarea', rules: 'required|string|max:250' },
      { name: 'date_range', key: 'date_range', label: 'Date Range', type: 'daterange' },
      { name: 'worker_ids', key: 'worker_ids', label: 'Workers', type: 'select', multiple: true, options: [] },
    ],
    onCreated: vi.fn(),
    hasPeriod: true,
  };

  it('shows task period reference when creating mini-task with dates', async () => {
    render(
      <MiniTasksTab
        {...defaultProps}
        taskStartDate="01/06/2026"
        taskEndDate="30/06/2026"
      />
    );

    const btn = screen.getByRole('button', { name: /New Mini-Task/ });
    fireEvent.click(btn);

    await waitFor(() => {
      expect(screen.getByText((content) => content.includes('Task Period'))).toBeTruthy();
    });
    expect(screen.getByText((content) => content.includes('01/06/2026') && content.includes('30/06/2026'))).toBeTruthy();
  });

  it('does not show period reference when task dates are not provided', async () => {
    render(
      <MiniTasksTab
        {...defaultProps}
        taskStartDate={null}
        taskEndDate={null}
      />
    );

    const btn = screen.getByRole('button', { name: /New Mini-Task/ });
    fireEvent.click(btn);

    await waitFor(() => {
      expect(screen.getByText('Create Mini-Task')).toBeTruthy();
    });

    expect(screen.queryByText((content) => content.includes('Task Period'))).toBeNull();
  });
});
