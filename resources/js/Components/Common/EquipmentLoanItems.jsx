import FormInput from '@/Components/Common/FormInput';

const DATE_TYPE = { type: 'date', key: 'start_date', step: null, min: null, max: null, rules: [] };

export default function EquipmentLoanItems({ equipmentOptions, selectedIds, values, onChange }) {
  if (!selectedIds || selectedIds.length === 0) return null;

  const items = selectedIds.map(id => {
    const opt = equipmentOptions.find(o => o.value === id);
    return {
      equipment_id: id,
      label: opt?.label ?? id,
      ...(values?.[id] ?? {}),
    };
  });

  return (
    <div className="space-y-3 rounded-lg border border-brand-mid/20 bg-brand-light/50 p-3">
      <span className="text-xs font-semibold uppercase tracking-wider text-brand-mid">
        Detalhes por Equipamento
      </span>
      {items.map((item, i) => (
        <div key={item.equipment_id} className="rounded border border-brand-mid/10 bg-brand-white p-2 space-y-2">
          <span className="text-sm font-medium text-brand-accent">{item.label}</span>
          <div className="grid grid-cols-3 gap-2">
            <FormInput
              field={{ ...DATE_TYPE, name: `eq_${item.equipment_id}_start_date`, label: 'Data Inicial' }}
              value={item.start_date ?? ''}
              onChange={(e) => onChange(item.equipment_id, 'start_date', e.target.value)}
            />
            <FormInput
              field={{ ...DATE_TYPE, name: `eq_${item.equipment_id}_end_date`, label: 'Data Final' }}
              value={item.end_date ?? ''}
              onChange={(e) => onChange(item.equipment_id, 'end_date', e.target.value)}
            />
            <label className="flex items-center gap-2 pt-4">
              <input
                type="checkbox"
                checked={item.needs_operator ?? false}
                onChange={(e) => onChange(item.equipment_id, 'needs_operator', e.target.checked)}
                className="h-4 w-4 rounded border-brand-mid/30 text-brand-accent focus:ring-brand-accent"
              />
              <span className="text-sm text-brand-mid">Requer Operador</span>
            </label>
          </div>
        </div>
      ))}
    </div>
  );
}
