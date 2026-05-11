import { useState, useRef, useCallback, useEffect } from 'react';
import { Download, Plus, SlidersHorizontal, Trash2 } from 'lucide-react';
import { t } from '@/utils/i18n';
import { useToast } from '@/Components/Toast/ToastContext';
import { exportCSV } from '@/utils/export';

const OPERATOR_KEYS = [
    { value: 'contains',         labelKey: 'filter_builder_op_contains' },
    { value: 'does_not_contain', labelKey: 'filter_builder_op_does_not_contain' },
    { value: 'is',               labelKey: 'filter_builder_op_is' },
    { value: 'is_not',           labelKey: 'filter_builder_op_is_not' },
    { value: 'starts_with',      labelKey: 'filter_builder_op_starts_with' },
    { value: 'ends_with',        labelKey: 'filter_builder_op_ends_with' },
    { value: 'is_empty',         labelKey: 'filter_builder_op_is_empty' },
    { value: 'is_not_empty',     labelKey: 'filter_builder_op_is_not_empty' },
];

const NO_VALUE_OPS = new Set(['is_empty', 'is_not_empty']);

function emptyRule(fields) {
    return { field: fields[0]?.value ?? '', operator: 'contains', value: '' };
}

export default function FilterBar({
    routes,
    filterSchema = [],
    advancedFilterFields = [],
    onNew = () => {},
    entityName = 'Record',
    onFilterChange = () => {},
    onAdvancedFiltersChange = () => {},
}) {
    const toast = useToast();
    const [search, setSearch] = useState('');
    const debounceRef = useRef(null);
    const [builderOpen, setBuilderOpen] = useState(
        () => typeof window !== 'undefined' && localStorage.getItem('filterBuilderOpen') === 'true'
    );
    const [rules, setRules] = useState(() => [emptyRule(advancedFilterFields)]);
    const [logic, setLogic] = useState('and');

    useEffect(() => {
        setRules([emptyRule(advancedFilterFields)]);
        onAdvancedFiltersChange([], logic);
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [advancedFilterFields]);

    const activeRuleCount = rules.filter(
        (r) => r.field && r.operator && (NO_VALUE_OPS.has(r.operator) || r.value)
    ).length;

    const emitSearch = useCallback((val) => {
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            onFilterChange(val ? { search: val } : {});
        }, 800);
    }, [onFilterChange]);

    const handleSearchChange = (e) => {
        setSearch(e.target.value);
        emitSearch(e.target.value);
    };

    const addRule = () => setRules((prev) => [...prev, emptyRule(advancedFilterFields)]);

    const removeRule = (idx) =>
        setRules((prev) => {
            const next = prev.filter((_, i) => i !== idx);
            return next.length === 0 ? [emptyRule(advancedFilterFields)] : next;
        });

    const updateRule = (idx, key, val) =>
        setRules((prev) => prev.map((r, i) => (i === idx ? { ...r, [key]: val } : r)));

    const handleApply = useCallback(() => {
        const active = rules.filter(
            (r) => r.field && r.operator && (NO_VALUE_OPS.has(r.operator) || r.value)
        );
        onAdvancedFiltersChange(active, logic);
    }, [rules, logic, onAdvancedFiltersChange]);

    const handleClearBuilder = useCallback(() => {
        const reset = [emptyRule(advancedFilterFields)];
        setRules(reset);
        onAdvancedFiltersChange([], logic);
    }, [advancedFilterFields, logic, onAdvancedFiltersChange]);

    const handleClearAll = useCallback(() => {
        setSearch('');
        setRules([emptyRule(advancedFilterFields)]);
        onFilterChange({});
        onAdvancedFiltersChange([], 'and');
    }, [advancedFilterFields, onFilterChange, onAdvancedFiltersChange]);

    const toggleBuilder = () => {
        const next = !builderOpen;
        setBuilderOpen(next);
        if (typeof window !== 'undefined') {
            if (next) localStorage.setItem('filterBuilderOpen', 'true');
            else localStorage.removeItem('filterBuilderOpen');
        }
    };

    const handleExport = useCallback(async () => {
        try {
            await exportCSV();
        } catch {
            toast.error(t('pages.datamanager.export_failed'));
        }
    }, [toast]);

    const hasBuilder = advancedFilterFields.length > 0;

    return (
        <div id="filter-bar">
            <div className="flex flex-wrap items-center gap-2">
                <div className="relative min-w-0 flex-1" style={{ maxWidth: 280 }}>
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg className="h-3.5 w-3.5 text-slate-500" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value={search}
                        onChange={handleSearchChange}
                        className="block w-full rounded-lg border border-slate-700 bg-slate-800/60 py-2 pl-9 pr-3 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:ring-0"
                        placeholder={t('pages.datamanager.search_placeholder')}
                        aria-label={t('pages.datamanager.search_aria')}
                    />
                </div>

                {hasBuilder && (
                    <button
                        type="button"
                        onClick={toggleBuilder}
                        className={`relative inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm transition-all ${
                            builderOpen || activeRuleCount > 0
                                ? 'border-indigo-600 bg-indigo-600/20 text-indigo-300'
                                : 'border-slate-700 bg-slate-800/60 text-slate-400 hover:bg-slate-700'
                        }`}
                        title={t('pages.datamanager.filter_builder_toggle_title')}
                    >
                        <SlidersHorizontal className="h-3.5 w-3.5" />
                        {t('pages.datamanager.filter_builder_label')}
                        {activeRuleCount > 0 && (
                            <span className="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-indigo-500 text-[9px] font-bold text-white ring-2 ring-slate-900">
                                {activeRuleCount}
                            </span>
                        )}
                    </button>
                )}

                <div className="ml-auto flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        onClick={handleExport}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800/60 px-3 py-2 text-sm text-slate-400 hover:bg-slate-700 transition-colors"
                        title={t('pages.datamanager.export_title')}
                    >
                        <Download className="h-3.5 w-3.5" />
                        {t('pages.datamanager.export_label')}
                    </button>

                    {routes.store && (
                        <button
                            type="button"
                            className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors"
                            onClick={onNew}
                            title={t('pages.datamanager.new_title')}
                        >
                            <Plus className="h-3.5 w-3.5" />
                            {t('pages.datamanager.new_entity', { name: entityName })}
                        </button>
                    )}
                </div>
            </div>

            {search && (
                <div className="mt-2 flex flex-wrap items-center gap-2">
                    <span className="inline-flex items-center gap-1 rounded bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-300">
                        {t('pages.datamanager.label_search')} <strong>{search}</strong>
                    </span>
                </div>
            )}

            {hasBuilder && builderOpen && (
                <div className="mt-3 rounded-lg border border-slate-700 bg-slate-800/80 p-4">
                    <div className="mb-3 flex items-center gap-3">
                        <span className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {t('pages.datamanager.filter_builder_match')}
                        </span>
                        <div className="flex overflow-hidden rounded-lg border border-slate-600 text-xs font-medium">
                            {['and', 'or'].map((l) => (
                                <button
                                    key={l}
                                    type="button"
                                    onClick={() => setLogic(l)}
                                    className={`px-3 py-1.5 transition-colors ${
                                        logic === l ? 'bg-indigo-600 text-white' : 'bg-transparent text-slate-400 hover:bg-slate-700'
                                    }`}
                                >
                                    {t(`pages.datamanager.filter_builder_${l}`)}
                                </button>
                            ))}
                        </div>
                        <span className="text-xs text-slate-500">
                            {logic === 'and'
                                ? t('pages.datamanager.filter_builder_match_all')
                                : t('pages.datamanager.filter_builder_match_any')}
                        </span>
                    </div>

                    <div className="flex flex-col gap-2">
                        {rules.map((rule, idx) => (
                            <div key={idx} className="flex items-center gap-2">
                                <span className="w-8 shrink-0 text-center text-xs font-bold text-slate-500">
                                    {idx === 0
                                        ? t('pages.datamanager.filter_builder_if')
                                        : t(`pages.datamanager.filter_builder_${logic}`)}
                                </span>
                                <select
                                    value={rule.field}
                                    onChange={(e) => {
                                        const newField = e.target.value;
                                        setRules(prev => prev.map((r, i) =>
                                            i === idx ? { ...r, field: newField, value: '' } : r
                                        ));
                                    }}
                                    className="min-w-[130px] rounded-lg border border-slate-600 bg-slate-700 px-2 py-1.5 text-sm text-slate-200 focus:border-indigo-500 focus:ring-0"
                                >
                                    {advancedFilterFields.map((f) => (
                                        <option key={f.value} value={f.value}>{f.label}</option>
                                    ))}
                                </select>
                                <select
                                    value={rule.operator}
                                    onChange={(e) => updateRule(idx, 'operator', e.target.value)}
                                    className="min-w-[160px] rounded-lg border border-slate-600 bg-slate-700 px-2 py-1.5 text-sm text-slate-200 focus:border-indigo-500 focus:ring-0"
                                >
                                    {OPERATOR_KEYS.map((op) => (
                                        <option key={op.value} value={op.value}>
                                            {t(`pages.datamanager.${op.labelKey}`)}
                                        </option>
                                    ))}
                                </select>
                                {(() => {
                                    const fieldDef = advancedFilterFields.find(f => f.value === rule.field);
                                    const isSelectField = fieldDef?.type === 'select' && fieldDef?.options?.length > 0;
                                    if (NO_VALUE_OPS.has(rule.operator)) return <div className="flex-1" />;
                                    if (isSelectField) return (
                                        <select
                                            value={rule.value}
                                            onChange={(e) => updateRule(idx, 'value', e.target.value)}
                                            className="flex-1 rounded-lg border border-slate-600 bg-slate-700 px-2 py-1.5 text-sm text-slate-200 focus:border-indigo-500 focus:ring-0"
                                        >
                                            <option value="">{t('pages.datamanager.filter_builder_value_placeholder')}</option>
                                            {fieldDef.options.map(opt => (
                                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                                            ))}
                                        </select>
                                    );
                                    return (
                                        <input
                                            type="text"
                                            value={rule.value}
                                            onChange={(e) => updateRule(idx, 'value', e.target.value)}
                                            onKeyDown={(e) => e.key === 'Enter' && handleApply()}
                                            placeholder={t('pages.datamanager.filter_builder_value_placeholder')}
                                            className="flex-1 rounded-lg border border-slate-600 bg-slate-700 px-2 py-1.5 text-sm text-slate-200 placeholder:text-slate-500 focus:border-indigo-500 focus:ring-0"
                                        />
                                    );
                                })()}
                                <button
                                    type="button"
                                    onClick={() => removeRule(idx)}
                                    className="shrink-0 rounded-lg border border-slate-700 p-1.5 text-slate-500 transition-colors hover:border-red-600 hover:bg-red-600/20 hover:text-red-400"
                                    title={t('pages.datamanager.filter_builder_remove_rule')}
                                >
                                    <Trash2 className="h-3.5 w-3.5" />
                                </button>
                            </div>
                        ))}
                    </div>

                    <div className="mt-3 flex items-center gap-2 border-t border-slate-700 pt-3">
                        <button
                            type="button"
                            onClick={addRule}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-600 bg-slate-700/60 px-3 py-1.5 text-xs font-medium text-slate-300 transition-colors hover:bg-slate-700"
                        >
                            <Plus className="h-3 w-3" />
                            {t('pages.datamanager.filter_builder_add_rule')}
                        </button>
                        <div className="ml-auto flex items-center gap-2">
                            <button
                                type="button"
                                onClick={handleClearBuilder}
                                className="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-400 transition-colors hover:bg-slate-700"
                            >
                                {t('pages.datamanager.filter_builder_clear')}
                            </button>
                            <button
                                type="button"
                                onClick={handleApply}
                                className="rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-medium text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                {t('pages.datamanager.filter_builder_apply')}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
