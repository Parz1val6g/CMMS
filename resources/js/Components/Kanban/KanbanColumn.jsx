import { useDroppable } from '@dnd-kit/core';
import {
  SortableContext,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import KanbanCard from './KanbanCard';

export default function KanbanColumn({ column, items, renderCardContent, onCardClick }) {
  const { setNodeRef } = useDroppable({
    id: column.id,
  });

  return (
    <div className="flex flex-col flex-1 min-w-[300px] rounded-lg bg-slate-900/50 border border-slate-700 overflow-hidden h-[calc(100vh-200px)]">
      {/* Column Header */}
      <div className="shrink-0 px-4 py-3 border-b border-slate-700 bg-slate-800/60">
        <div className="flex items-center justify-between">
          <h3 className="font-semibold text-slate-100">{column.label}</h3>
          <span className="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-600 text-xs font-medium text-slate-100">
            {items.length}
          </span>
        </div>
      </div>

      {/* Droppable Area */}
      <div
        ref={setNodeRef}
        className="flex-1 overflow-y-auto px-3 py-4 space-y-3 min-h-0"
      >
        <SortableContext
          items={items.map(item => item.id)}
          strategy={verticalListSortingStrategy}
        >
          {items.length > 0 ? (
            items.map((item) => (
              <KanbanCard
                key={item.id}
                item={item}
                renderCardContent={renderCardContent}
                onCardClick={() => onCardClick?.(item)}
              />
            ))
          ) : (
            <div className="flex items-center justify-center h-24 text-slate-500">
              <p className="text-sm">No items</p>
            </div>
          )}
        </SortableContext>
      </div>
    </div>
  );
}
