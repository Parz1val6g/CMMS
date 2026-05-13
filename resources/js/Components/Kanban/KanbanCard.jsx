import { memo } from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';

function KanbanCard({ item, isDragging, renderCardContent, onCardClick }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging: isSortableDragging,
    } = useSortable({ id: item.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isSortableDragging ? 0.5 : 1,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            onClick={onCardClick}
            className={`group relative rounded-lg border border-brand-mid/20 bg-brand-white p-3 cursor-move transition-all shadow-md hover:border-brand-accent hover:shadow-lg hover:shadow-brand-accent/20 ${isDragging || isSortableDragging ? 'opacity-75 ring-2 ring-brand-accent' : ''
                }`}
        >
            {/* Drag Handle */}
            <div
                {...attributes}
                {...listeners}
                className="absolute left-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity"
            >
                <GripVertical className="h-4 w-4 text-brand-mid" />
            </div>

            {/* Custom Content */}
            <div className="pl-2">
                {renderCardContent ? renderCardContent(item) : <p className="text-sm text-brand-mid">Item</p>}
            </div>
        </div>
    );
}

export default memo(KanbanCard, (prev, next) =>
    prev.item.id === next.item.id &&
    prev.item.status === next.item.status &&
    prev.isDragging === next.isDragging
);
