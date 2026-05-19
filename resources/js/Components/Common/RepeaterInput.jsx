import FormField from '@/Components/Common/FormField';
import { Plus, Trash2 } from 'lucide-react';
import { t } from '@/utils/i18n';

export default function RepeaterInput({ field, value = [], onChange }) {
  const { label, subFields = [], maxItems = 10 } = field;
  const itemCols = field.metadata?.itemColumns ?? 2;
  const items = Array.isArray(value) ? value : [];
  const atMax = items.length >= maxItems;

  function getFilteredSubFields(rowIndex) {
    return subFields.map(sub => {
      if (sub.key !== 'equipment_id' || !sub.options) return sub;
      const otherIds = items
        .filter((_, i) => i !== rowIndex)
        .map(item => item.equipment_id)
        .filter(id => id);
      return {
        ...sub,
        options: sub.options.filter(opt => !otherIds.includes(opt.value)),
      };
    });
  }

  function addItem() {
    const empty = {};
    subFields.forEach(f => { empty[f.key] = f.value ?? ''; });
    onChange([...items, empty]);
  }

  function removeItem(index) {
    onChange(items.filter((_, i) => i !== index));
  }

  function updateItem(index, subKey, val) {
    const next = [...items];
    next[index] = { ...next[index], [subKey]: val };
    onChange(next);
  }

  return (
    <div className="mt-2">
      <div className="flex items-center justify-between mb-3">
        {label && (
          <span className="text-xs font-semibold uppercase tracking-wider text-brand-mid">{label}</span>
        )}
        {!atMax && (
          <button
            type="button"
            onClick={addItem}
            className="inline-flex items-center gap-1 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-1.5 text-xs font-medium text-brand-accent hover:bg-brand-light transition-colors"
          >
            <Plus className="h-3.5 w-3.5" />
            {subFields[0]?.placeholder || 'Adicionar'}
          </button>
        )}
      </div>

      {items.length > 0 && (
        <div className="space-y-3">
          {items.map((item, i) => (
            <div key={i} className="rounded-lg border border-brand-mid/10 bg-brand-light/50 p-3 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-medium text-brand-mid">#{i + 1}</span>
                <button
                  type="button"
                  onClick={() => removeItem(i)}
                  className="rounded p-0.5 text-brand-mid hover:text-red-500 hover:bg-red-50 transition-colors"
                  aria-label={t('pages.common.remove')}
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              </div>
              <div className="grid gap-3" style={{ gridTemplateColumns: `repeat(${itemCols}, minmax(0, 1fr))` }}>
                {getFilteredSubFields(i).map(sub => (
                  <FormField
                    key={sub.key}
                    field={sub}
                    value={item[sub.key] ?? ''}
                    onChange={(val) => updateItem(i, sub.key, val)}
                  />
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
