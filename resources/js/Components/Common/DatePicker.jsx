import { useState, useMemo, useRef, useEffect } from 'react';
import { ChevronLeft, ChevronRight, ChevronDown, AlertCircle, Check } from 'lucide-react';

/* ── Date utils ──────────────────────────────────────────────────────── */

function toKey(date) {
  if (!date) return null;
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function fromKey(key) {
  if (!key) return null;
  const [y, m, d] = key.split('-').map(Number);
  return new Date(y, m - 1, d);
}

function nextDay(date) {
  const d = new Date(date);
  d.setDate(d.getDate() + 1);
  return d;
}

function rangeOverlapsBlocked(startKey, endKey, blockedSet) {
  if (!startKey || !endKey || !blockedSet.size) return false;
  let cur = fromKey(startKey);
  const end = fromKey(endKey);
  while (cur <= end) {
    if (blockedSet.has(toKey(cur))) return true;
    cur = nextDay(cur);
  }
  return false;
}

/* ── Calendar grid ───────────────────────────────────────────────────── */

function buildCalendarGrid(year, month) {
  const first   = new Date(year, month, 1);
  const last    = new Date(year, month + 1, 0);
  const startDow = (first.getDay() + 6) % 7; // Mon=0 … Sun=6
  const cells   = [];
  for (let i = 0; i < startDow; i++) cells.push(null);
  for (let d = 1; d <= last.getDate(); d++) cells.push(new Date(year, month, d));
  while (cells.length % 7 !== 0) cells.push(null);
  return cells;
}

const DOW_LABELS  = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
const MONTH_NAMES = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];
const MONTH_FMT   = new Intl.DateTimeFormat('pt-PT', { month: 'long', year: 'numeric' });

/* ── Scroll picker ───────────────────────────────────────────────────── */

const ITEM_H  = 40;  // px — matches h-10
const REEL_H  = 200; // px — 5 visible items
const PAD_H   = REEL_H / 2 - ITEM_H / 2; // 80px — centres first/last item

function ScrollPicker({ items, selectedIndex, onSelect }) {
  const containerRef = useRef(null);
  const scrollTimer  = useRef(null);
  // Track whether the last scroll was triggered programmatically (click/mount)
  // so we don't fire onSelect in a loop when we scroll-to after onSelect.
  const programmatic = useRef(false);

  // Scroll to selected item on first render (instant, no animation)
  useEffect(() => {
    const el = containerRef.current;
    if (!el) return;
    programmatic.current = true;
    el.scrollTop = selectedIndex * ITEM_H;
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  // Cleanup debounce timer on unmount
  useEffect(() => () => clearTimeout(scrollTimer.current), []);

  // When the user scrolls (manually), debounce and select the centred item.
  const handleScroll = () => {
    if (programmatic.current) {
      programmatic.current = false;
      return;
    }
    clearTimeout(scrollTimer.current);
    scrollTimer.current = setTimeout(() => {
      const el = containerRef.current;
      if (!el) return;
      const index = Math.max(0, Math.min(Math.round(el.scrollTop / ITEM_H), items.length - 1));
      onSelect(index);
    }, 120);
  };

  const handleItemClick = (i) => {
    onSelect(i);
    const el = containerRef.current;
    if (el) {
      programmatic.current = true;
      el.scrollTo({ top: i * ITEM_H, behavior: 'smooth' });
    }
  };

  return (
    <div className="relative flex-1 overflow-hidden" style={{ height: REEL_H }}>

      {/* Gradient fades — top */}
      <div className="pointer-events-none absolute inset-x-0 top-0 z-10 h-16 bg-gradient-to-b from-white to-transparent" />

      {/* Gradient fades — bottom */}
      <div className="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-16 bg-gradient-to-t from-white to-transparent" />

      {/* Selection window highlight */}
      <div
        className="pointer-events-none absolute inset-x-3 z-10 rounded-lg bg-brand-accent/8 ring-1 ring-brand-accent/20"
        style={{ top: PAD_H, height: ITEM_H }}
      />

      {/* Scrollable list */}
      <div
        ref={containerRef}
        className="h-full overflow-y-auto overscroll-contain"
        style={{ scrollSnapType: 'y mandatory', scrollbarWidth: 'none' }}
        onScroll={handleScroll}
      >
        <div style={{ height: PAD_H }} />

        {items.map((item, i) => (
          <div
            key={item.value ?? i}
            onClick={() => handleItemClick(i)}
            style={{ height: ITEM_H, scrollSnapAlign: 'center' }}
            className={`flex cursor-pointer items-center justify-center px-4 transition-all duration-150 ${
              i === selectedIndex
                ? 'font-semibold text-brand-accent'
                : 'text-brand-mid hover:text-brand-darkest'
            }`}
          >
            <span className="text-sm">{item.label}</span>
          </div>
        ))}

        <div style={{ height: PAD_H }} />
      </div>
    </div>
  );
}

/* ── Month / year picker panel ───────────────────────────────────────── */

function buildYearItems(pivotYear) {
  const items = [];
  for (let y = pivotYear - 10; y <= pivotYear + 50; y++) {
    items.push({ value: y, label: String(y) });
  }
  return items;
}

function MonthYearPicker({ viewMonth, viewYear, onMonthChange, onYearChange }) {
  const monthItems = MONTH_NAMES.map((name, i) => ({ value: i, label: name }));
  const yearItems  = useMemo(() => buildYearItems(viewYear), []); // eslint-disable-line react-hooks/exhaustive-deps
  const yearIndex  = yearItems.findIndex(y => y.value === viewYear);

  return (
    <div className="flex border-b border-brand-mid/10">

      {/* Months */}
      <div className="flex-1 border-r border-brand-mid/10">
        <p className="py-2 text-center text-[10px] font-semibold uppercase tracking-widest text-brand-mid/60">
          Mês
        </p>
        <ScrollPicker
          items={monthItems}
          selectedIndex={viewMonth}
          onSelect={onMonthChange}
        />
      </div>

      {/* Years */}
      <div className="flex-1">
        <p className="py-2 text-center text-[10px] font-semibold uppercase tracking-widest text-brand-mid/60">
          Ano
        </p>
        <ScrollPicker
          items={yearItems}
          selectedIndex={yearIndex >= 0 ? yearIndex : 10}
          onSelect={(i) => onYearChange(yearItems[i].value)}
        />
      </div>
    </div>
  );
}

/* ── DatePicker ──────────────────────────────────────────────────────── */
/**
 * Controlled date picker with three modes:
 *
 *   mode="single"
 *     value: string | null  (YYYY-MM-DD)
 *     onChange: (string | null) => void
 *
 *   mode="range"
 *     value: { start: string|null, end: string|null }
 *     onChange: ({ start, end }) => void
 *
 *   mode="availability"
 *     value: { start: string|null, end: string|null, valid?: boolean }
 *     onChange: ({ start, end, valid }) => void
 *     blockedDates: string[]  — YYYY-MM-DD dates the resource is occupied.
 *     Any range overlapping a blocked date gets valid=false + red UI.
 */
export default function DatePicker({
  mode         = 'single',
  value,
  onChange,
  blockedDates = [],
  minDate,
  maxDate,
  label,
  name,
  error,
  required,
}) {
  const todayKey = toKey(new Date());

  /* ── Derive start / end from value ──────────────────────────────── */
  const startKey = mode === 'single' ? (value ?? null) : (value?.start ?? null);
  const endKey   = mode === 'single' ? null             : (value?.end   ?? null);

  /* ── View state ──────────────────────────────────────────────────── */
  const initDate   = (startKey ? fromKey(startKey) : null) ?? new Date();
  const [viewYear,  setViewYear]  = useState(initDate.getFullYear());
  const [viewMonth, setViewMonth] = useState(initDate.getMonth());

  // If value arrives after mount (async form fill / update pre-load),
  // navigate the calendar to the pre-filled date — but only once,
  // not on every keystroke.
  const syncedKey = useRef(startKey);
  useEffect(() => {
    if (!startKey || startKey === syncedKey.current) return;
    syncedKey.current = startKey;
    const d = fromKey(startKey);
    if (d) { setViewYear(d.getFullYear()); setViewMonth(d.getMonth()); }
  }, [startKey]);

  // 'days' = calendar grid  |  'month-year' = scroll pickers
  const [viewMode, setViewMode] = useState('days');

  /* ── Hover for range preview ─────────────────────────────────────── */
  const [hoverKey, setHoverKey] = useState(null);

  /* ── Blocked dates ───────────────────────────────────────────────── */
  const blockedSet = useMemo(() => new Set(blockedDates), [blockedDates]);

  /* ── Preview range (while awaiting end click) ────────────────────── */
  const awaitingEnd    = (mode !== 'single') && !!startKey && !endKey;
  const previewAnchor  = awaitingEnd ? startKey : null;
  const previewCursor  = awaitingEnd ? hoverKey : null;
  const previewStart   = previewAnchor && previewCursor
    ? (previewCursor < previewAnchor ? previewCursor : previewAnchor)
    : null;
  const previewEnd     = previewAnchor && previewCursor
    ? (previewCursor < previewAnchor ? previewAnchor : previewCursor)
    : null;

  /* ── Display range (confirmed or preview) ────────────────────────── */
  const dispStart = endKey ? startKey : previewStart;
  const dispEnd   = endKey ? endKey   : previewEnd;

  /* ── Validity ────────────────────────────────────────────────────── */
  const rangeInvalid = mode === 'availability' && !!endKey
    ? rangeOverlapsBlocked(dispStart, dispEnd, blockedSet)
    : false;

  /* ── Calendar cells ──────────────────────────────────────────────── */
  const cells = useMemo(
    () => buildCalendarGrid(viewYear, viewMonth),
    [viewYear, viewMonth],
  );

  /* ── Month navigation (days view) ────────────────────────────────── */
  const goToPrev = () => {
    if (viewMonth === 0) { setViewYear(y => y - 1); setViewMonth(11); }
    else setViewMonth(m => m - 1);
  };
  const goToNext = () => {
    if (viewMonth === 11) { setViewYear(y => y + 1); setViewMonth(0); }
    else setViewMonth(m => m + 1);
  };

  /* ── Day click ───────────────────────────────────────────────────── */
  const handleDayClick = (key) => {
    if (blockedSet.has(key)) return;
    if (minDate && key < minDate) return;
    if (maxDate && key > maxDate) return;

    if (mode === 'single') {
      onChange?.(key === startKey ? null : key);
      return;
    }

    // Two-click range selection
    if (!startKey || endKey) {
      onChange?.(mode === 'availability'
        ? { start: key, end: null, valid: false }
        : { start: key, end: null });
      return;
    }

    let s = startKey, e = key;
    if (e < s) [s, e] = [e, s];

    if (mode === 'availability') {
      onChange?.({ start: s, end: e, valid: !rangeOverlapsBlocked(s, e, blockedSet) });
    } else {
      onChange?.({ start: s, end: e });
    }
  };

  /* ── Clear ───────────────────────────────────────────────────────── */
  const handleClear = () => {
    if (mode === 'single') onChange?.(null);
    else onChange?.(mode === 'availability'
      ? { start: null, end: null, valid: false }
      : { start: null, end: null });
  };

  /* ── Per-cell visual state ───────────────────────────────────────── */
  const getCellState = (key) => {
    const isToday           = key === todayKey;
    const isBlocked         = blockedSet.has(key);
    const isDisabled        = isBlocked || (!!minDate && key < minDate) || (!!maxDate && key > maxDate);
    const isSelectedSingle  = mode === 'single' && key === startKey;
    const isStart           = mode !== 'single' && !!dispStart && key === dispStart;
    const isEnd             = mode !== 'single' && !!dispEnd   && key === dispEnd;
    const isSingleDayRange  = isStart && isEnd;
    const isInBand          = !isSingleDayRange && !!dispStart && !!dispEnd
                              && key > dispStart && key < dispEnd;
    return { isToday, isBlocked, isDisabled, isSelectedSingle, isStart, isEnd, isSingleDayRange, isInBand };
  };

  /* ── Go to today ─────────────────────────────────────────────────── */
  const goToToday = () => {
    const now = new Date();
    setViewYear(now.getFullYear());
    setViewMonth(now.getMonth());
    setViewMode('days');
    // In single mode, also select today (unless blocked / out of bounds)
    if (mode === 'single') {
      const disabled = blockedSet.has(todayKey)
        || (!!minDate && todayKey < minDate)
        || (!!maxDate && todayKey > maxDate);
      if (!disabled) onChange?.(todayKey);
    }
  };

  /* ── Render ──────────────────────────────────────────────────────── */
  const hasSelection = !!startKey;
  const outerBorder  = error || rangeInvalid ? 'border-red-400' : 'border-brand-mid/20';
  const titleText    = MONTH_FMT.format(new Date(viewYear, viewMonth));
  const alreadyOnToday = (() => {
    const now = new Date();
    return viewYear === now.getFullYear() && viewMonth === now.getMonth();
  })();

  return (
    <div className="mb-4">
      {label && (
        <label className="block text-sm font-medium text-brand-mid mb-1.5">
          {label}
          {required && <span className="ml-1 text-red-500">*</span>}
        </label>
      )}

      <div className={`rounded-xl border bg-white shadow-sm overflow-hidden select-none ${outerBorder}`}>

        {/* ── Header ───────────────────────────────────────────────── */}
        <div className="flex items-center justify-between px-4 py-3 border-b border-brand-mid/10">

          {viewMode === 'days' ? (
            <>
              <button
                type="button"
                onClick={goToPrev}
                className="p-1.5 rounded-lg text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
              >
                <ChevronLeft size={15} />
              </button>

              {/* Clickable title → enters month-year picker */}
              <button
                type="button"
                onClick={() => setViewMode('month-year')}
                className="flex items-center gap-1 rounded-lg px-2 py-1 text-sm font-semibold text-brand-darkest capitalize hover:bg-brand-light transition-colors"
              >
                {titleText}
                <ChevronDown size={13} className="text-brand-mid" />
              </button>

              <button
                type="button"
                onClick={goToNext}
                className="p-1.5 rounded-lg text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
              >
                <ChevronRight size={15} />
              </button>
            </>
          ) : (
            <>
              {/* Back arrow */}
              <button
                type="button"
                onClick={() => setViewMode('days')}
                className="p-1.5 rounded-lg text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
              >
                <ChevronLeft size={15} />
              </button>

              <span className="text-sm font-semibold text-brand-darkest capitalize">
                {titleText}
              </span>

              {/* Confirm — returns to calendar */}
              <button
                type="button"
                onClick={() => setViewMode('days')}
                className="p-1.5 rounded-lg text-brand-accent hover:bg-brand-accent/10 transition-colors"
                title="Confirmar"
              >
                <Check size={15} />
              </button>
            </>
          )}
        </div>

        {/* ── Month / year scroll pickers ──────────────────────────── */}
        {viewMode === 'month-year' && (
          <MonthYearPicker
            viewMonth={viewMonth}
            viewYear={viewYear}
            onMonthChange={(i) => setViewMonth(i)}
            onYearChange={(y) => setViewYear(y)}
          />
        )}

        {/* ── Day-of-week row (days mode only) ─────────────────────── */}
        {viewMode === 'days' && (
          <div className="grid grid-cols-7 px-3 pt-3 pb-1">
            {DOW_LABELS.map(d => (
              <div key={d} className="text-center text-xs font-medium text-brand-mid/70 py-0.5">
                {d}
              </div>
            ))}
          </div>
        )}

        {/* ── Calendar grid (days mode only) ───────────────────────── */}
        {viewMode === 'days' && (
          <div className="grid grid-cols-7 px-3 pb-3">
            {cells.map((date, idx) => {
              if (!date) return <div key={`pad-${idx}`} className="h-9" />;

              const key = toKey(date);
              const {
                isToday, isBlocked, isDisabled,
                isSelectedSingle, isStart, isEnd, isSingleDayRange, isInBand,
              } = getCellState(key);

              const bandColor = rangeInvalid ? 'bg-red-100' : 'bg-brand-accent/15';
              let bandClass = '';
              if (!isSingleDayRange) {
                if (isStart && dispEnd)      bandClass = `absolute inset-y-1 left-1/2 right-0 ${bandColor}`;
                else if (isEnd && dispStart) bandClass = `absolute inset-y-1 left-0 right-1/2 ${bandColor}`;
                else if (isInBand)           bandClass = `absolute inset-y-1 left-0 right-0 ${bandColor}`;
              }

              let circleClass = 'text-brand-darkest hover:bg-brand-light';
              if (isSelectedSingle || isStart || isEnd) {
                circleClass = rangeInvalid ? 'bg-red-500 text-white' : 'bg-brand-accent text-white';
              } else if (isBlocked) {
                circleClass = 'bg-red-50 text-red-300 cursor-not-allowed';
              }

              const todayRing = isToday && !isSelectedSingle && !isStart && !isEnd
                ? 'ring-1 ring-brand-accent/40'
                : '';

              return (
                <div key={key} className="relative h-9 flex items-center justify-center">
                  {bandClass && <div className={bandClass} />}

                  {isBlocked && (
                    <div className="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                      <div className="absolute h-px w-6 bg-red-300/80 rotate-45 rounded-full" />
                    </div>
                  )}

                  <button
                    type="button"
                    onClick={() => !isDisabled && handleDayClick(key)}
                    onMouseEnter={() => setHoverKey(key)}
                    onMouseLeave={() => setHoverKey(null)}
                    disabled={isDisabled}
                    className={`
                      relative z-10 flex h-8 w-8 items-center justify-center rounded-full
                      text-xs font-medium transition-colors
                      disabled:cursor-not-allowed disabled:pointer-events-none
                      ${circleClass} ${todayRing}
                    `}
                  >
                    {date.getDate()}
                  </button>
                </div>
              );
            })}
          </div>
        )}

        {/* ── Footer: always visible ───────────────────────────────── */}
        <div className="flex items-center gap-3 px-4 py-2.5 border-t border-brand-mid/10 bg-brand-light/40">

          {/* Today button */}
          <button
            type="button"
            onClick={goToToday}
            className={`shrink-0 text-xs font-semibold transition-colors ${
              alreadyOnToday && startKey === todayKey
                ? 'text-brand-accent/50 cursor-default'
                : 'text-brand-accent hover:text-brand-accent/70'
            }`}
          >
            Hoje
          </button>

          {/* Selection summary */}
          <span className="flex-1 text-center text-xs text-brand-mid truncate">
            {mode === 'single' && startKey && (
              <span className="font-medium text-brand-darkest">{startKey}</span>
            )}
            {mode !== 'single' && startKey && !endKey && (
              <>
                <span className="font-medium text-brand-darkest">{startKey}</span>
                <span className="mx-1 text-brand-accent">→ seleciona fim</span>
              </>
            )}
            {mode !== 'single' && startKey && endKey && (
              <>
                <span className="font-medium text-brand-darkest">{startKey}</span>
                <span className="mx-1.5 text-brand-mid">→</span>
                <span className="font-medium text-brand-darkest">{endKey}</span>
              </>
            )}
          </span>

          {/* Clear button */}
          {hasSelection ? (
            <button
              type="button"
              onClick={handleClear}
              className="shrink-0 text-xs text-red-400 hover:text-red-600 font-medium transition-colors"
            >
              Limpar
            </button>
          ) : (
            <span className="shrink-0 w-10" /> /* spacer to balance "Hoje" */
          )}
        </div>

        {/* ── Availability conflict ─────────────────────────────────── */}
        {rangeInvalid && (
          <div className="flex items-start gap-2 mx-3 mb-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2">
            <AlertCircle size={14} className="text-red-500 shrink-0 mt-0.5" />
            <p className="text-xs text-red-600 leading-relaxed">
              Este intervalo não está disponível — o equipamento está ocupado em parte deste período.
            </p>
          </div>
        )}

        {/* ── Availability hint (no selection yet) ─────────────────── */}
        {mode === 'availability' && !hasSelection && !!blockedDates.length && (
          <div className="flex items-center gap-2 px-4 pb-3 text-xs text-brand-mid">
            <span className="inline-block h-3 w-3 rounded-full bg-red-100 border border-red-300/60" />
            Datas ocupadas não podem ser incluídas no intervalo
          </div>
        )}
      </div>

      {/* ── Hidden inputs for form serialisation ─────────────────────── */}
      {mode === 'single' && (
        <input type="hidden" name={name} value={startKey ?? ''} readOnly />
      )}
      {mode !== 'single' && (
        <>
          <input type="hidden" name={name ? `${name}_start` : undefined} value={startKey ?? ''} readOnly />
          <input type="hidden" name={name ? `${name}_end`   : undefined} value={endKey   ?? ''} readOnly />
        </>
      )}

      {error && (
        <p className="text-xs text-red-500 mt-1.5">{error}</p>
      )}
    </div>
  );
}
