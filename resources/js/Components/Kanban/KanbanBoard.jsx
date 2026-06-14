import { useMemo, useState, useCallback } from 'react';
import {
    DndContext,
    closestCorners,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    DragOverlay,
} from '@dnd-kit/core';
import { sortableKeyboardCoordinates } from '@dnd-kit/sortable';
import KanbanColumn from './KanbanColumn';
import KanbanCard from './KanbanCard';

/**
 * Generic Kanban Board Component
 * @param {Array} items - Array of items to display
 * @param {Array} columns - Array of column config {id, label, color}
 * @param {String} statusField - Field name to group by (default: 'status')
 * @param {Function} onDragEnd - Callback when drag ends
 * @param {Function} renderCardContent - Function to render card content (item) => JSX
 */
export default function KanbanBoard({
    items = [],
    columns = [],
    statusField = 'status',
    onDragEnd = null,
    renderCardContent = null,
    onCardClick = null,
}) {
    const [activeId, setActiveId] = useState(null);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            distance: 8,
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    // Group items by status field
    const groupedByStatus = useMemo(() => {
        const grouped = {};
        columns.forEach(col => {
            grouped[col.id] = [];
        });

        const itemsArray = Array.isArray(items) ? items : (items?.data ?? []);
        itemsArray.forEach(item => {
            const status = item[statusField];
            if (grouped[status]) {
                grouped[status].push(item);
            }
        });

        return grouped;
    }, [items, columns, statusField]);

    // Handle drag end
    const handleDragEnd = useCallback((event) => {
        const { active, over } = event;
        setActiveId(null);

        if (!over || !onDragEnd) return;

        onDragEnd({
            activeId: active.id,
            overId: over.id,
            items: groupedByStatus,
        });
    }, [onDragEnd, groupedByStatus]);

    const handleDragStart = useCallback((event) => {
        setActiveId(event.active.id);
    }, []);

    const activeItem = useMemo(() => {
        if (!activeId) return null;
        for (const column of Object.values(groupedByStatus)) {
            const item = column.find(card => String(card.id) === String(activeId));
            if (item) return item;
        }
        return null;
    }, [activeId, groupedByStatus]);

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCorners}
            onDragStart={handleDragStart}
            onDragEnd={handleDragEnd}
        >
            <div className="flex-1 flex overflow-x-auto gap-4 bg-brand-light p-6 rounded-2xl w-full">
                {/* Columns */}
                {columns.map((column) => (
                    <KanbanColumn
                        key={column.id}
                        column={column}
                        items={groupedByStatus[column.id] || []}
                        renderCardContent={renderCardContent}
                        onCardClick={onCardClick}
                    />
                ))}
            </div>

            {/* Drag Overlay */}
            <DragOverlay>
                {activeItem && (
                    <KanbanCard item={activeItem} isDragging={true} renderCardContent={renderCardContent} onCardClick={onCardClick} />
                )}
            </DragOverlay>
        </DndContext>
    );
}
