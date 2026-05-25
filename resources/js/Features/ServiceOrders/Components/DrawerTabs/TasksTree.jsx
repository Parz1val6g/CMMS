import { useState, useEffect, useCallback, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Loader2, AlertCircle } from 'lucide-react';
import TaskTreeNode from './TaskTreeNode';
import buildTaskTree from '../../Utils/buildTaskTree';
import { csrfHeader } from '@/utils/csrf';
import { t } from '@/utils/i18n';

/**
 * SOTasksTree — Hierarchical tasks tree for a Service Order.
 *
 * Fetches tasks + mini-tasks from the API, transforms the flat
 * lists into a nested tree, and renders it with expand/collapse.
 *
 * @param {Object}   props
 * @param {string}   props.serviceOrderId   - The SO UUID
 * @param {string}   [props.taskApiUrl]     - Override tasks API base
 * @param {string}   [props.miniTaskApiUrl] - Override mini-tasks API base
 */
export default function SOTasksTree({
  serviceOrderId,
  taskApiUrl = '/api/tasks',
  miniTaskApiUrl = '/api/mini-tasks',
}) {
  const [tasks, setTasks] = useState([]);
  const [miniTasksMap, setMiniTasksMap] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  /* ── Expanded set (node ids) ──────────────────────────────── */
  const [expandedIds, setExpandedIds] = useState(new Set());

  /* ── Fetch tasks ──────────────────────────────────────────── */
  useEffect(() => {
    if (!serviceOrderId) {
      setLoading(false);
      return;
    }

    let cancelled = false;
    setLoading(true);
    setError(null);

    fetch(`${taskApiUrl}?service_order_id=${serviceOrderId}&per_page=100`, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
    })
      .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
      .then((body) => {
        if (cancelled) return;
        const list = body.data ?? body ?? [];
        setTasks(list);

        // Auto-expand root tasks
        setExpandedIds(new Set(list.map((t) => t.id)));
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err.message);
      })
      .finally(() => { if (!cancelled) setLoading(false); });

    return () => { cancelled = true; };
  }, [serviceOrderId, taskApiUrl]);

  /* ── Fetch mini-tasks for each task (parallel) ────────────── */
  useEffect(() => {
    if (tasks.length === 0) return;

    let cancelled = false;

    Promise.all(
      tasks.map((task) =>
        fetch(`${miniTaskApiUrl}?task_id=${task.id}&per_page=100`, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeader() },
        })
          .then((r) => (r.ok ? r.json() : { data: [] }))
          .then((body) => ({ taskId: task.id, miniTasks: body.data ?? [] }))
          .catch(() => ({ taskId: task.id, miniTasks: [] }))
      )
    ).then((results) => {
      if (cancelled) return;
      const map = {};
      for (const { taskId, miniTasks } of results) {
        map[taskId] = miniTasks;
      }
      setMiniTasksMap(map);
    });

    return () => { cancelled = true; };
  }, [tasks, miniTaskApiUrl]);

  /* ── Build tree from flat data ─────────────────────────────── */
  const tree = useMemo(() => {
    if (tasks.length === 0) return [];

    // Normalize mini-tasks into the same flat array with parent_id
    const flat = [];

    for (const t of tasks) {
      flat.push({ ...t, _type: 'task' });
    }

    for (const mtList of Object.values(miniTasksMap)) {
      for (const mt of mtList) {
        flat.push({ ...mt, _type: 'mini_task', parent_id: mt.task_id });
      }
    }

    return buildTaskTree(flat, { parentKey: 'parent_id' });
  }, [tasks, miniTasksMap]);

  /* ── Toggle expand/collapse ────────────────────────────────── */
  const handleToggle = useCallback((id) => {
    setExpandedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }, []);

  /* ── Loading state ────────────────────────────────────────── */
  if (loading) {
    return (
      <div className="flex items-center justify-center h-40 text-brand-mid gap-2">
        <Loader2 className="h-5 w-5 animate-spin" />
        <span className="text-sm">{t('pages.service_orders.tasks_tree.loading')}</span>
      </div>
    );
  }

  /* ── Error state ──────────────────────────────────────────── */
  if (error) {
    return (
      <div className="flex items-center justify-center h-40 text-red-400 gap-2">
        <AlertCircle className="h-5 w-5" />
        <span className="text-sm">{t('pages.service_orders.tasks_tree.load_failed')}{error}</span>
      </div>
    );
  }

  /* ── Empty state ──────────────────────────────────────────── */
  if (tree.length === 0) {
    return (
      <div className="flex items-center justify-center h-40 text-brand-mid">
        <p className="text-sm">{t('pages.service_orders.tasks_tree.no_tasks')}</p>
      </div>
    );
  }

  /* ── Summary bar ──────────────────────────────────────────── */
  const totalTasks = tasks.length;
  const completedTasks = tasks.filter((t) => t.status === 'completed').length;
  const subTaskCount = tree.reduce((sum, n) => sum + countDescendants(n), 0);

  return (
    <div>
      {/* Summary */}
      <div className="flex items-center justify-between mb-4 px-4 py-2 rounded-lg bg-brand-white border border-brand-mid/20">
        <span className="text-xs text-brand-mid">
          <span className="font-semibold text-brand-darkest">{totalTasks}</span> {t('pages.service_orders.tasks_tree.tasks_label')}
          {completedTasks > 0 && (
            <>
              {' · '}
              <span className="font-semibold text-green-400">{completedTasks}</span> {t('pages.service_orders.tasks_tree.completed_label')}
            </>
          )}
        </span>
        <span className="text-xs text-brand-mid">
          {totalTasks} {totalTasks !== 1 ? t('pages.service_orders.tasks_tree.task_plural') : t('pages.service_orders.tasks_tree.task_singular')}
          {subTaskCount > 0 && (
            <> · {subTaskCount} {subTaskCount !== 1 ? t('pages.service_orders.tasks_tree.sub_task_plural') : t('pages.service_orders.tasks_tree.sub_task_singular')}</>
          )}
        </span>
      </div>

      {/* Tree */}
      <div className="rounded-lg border border-brand-mid/20 overflow-hidden divide-y divide-brand-mid/10">
        {tree.map((node) => (
          <TaskTreeNode
            key={node.item.id}
            node={node}
            depth={0}
            expandedIds={expandedIds}
            onToggle={handleToggle}
          />
        ))}
      </div>
    </div>
  );
}

/* ── Count all descendant nodes ─────────────────────────────── */
function countDescendants(node) {
  if (!node.children?.length) return 0;
  return node.children.reduce((sum, c) => sum + 1 + countDescendants(c), 0);
}
