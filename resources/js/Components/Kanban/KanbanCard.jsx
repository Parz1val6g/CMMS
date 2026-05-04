import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';

export default function KanbanCard({ item, isDragging, renderCardContent, onCardClick }) {
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
            className={`group relative rounded-lg border border-slate-700/50 bg-slate-700/50 p-3 cursor-move transition-all shadow-md hover:border-indigo-500 hover:shadow-lg hover:shadow-indigo-500/20 ${isDragging || isSortableDragging ? 'opacity-75 ring-2 ring-indigo-500' : ''
                }`}
        >
            {/* Drag Handle */}
            <div
                {...attributes}
                {...listeners}
                className="absolute left-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity"
            >
                <GripVertical className="h-4 w-4 text-slate-400" />
            </div>

            {/* Custom Content */}
            <div className="pl-2">
                {renderCardContent ? renderCardContent(item) : <p className="text-sm text-slate-300">Item</p>}
            </div>
        </div>
    );
}
