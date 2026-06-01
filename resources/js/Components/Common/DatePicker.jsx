import { useState, useMemo, useRef, useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';
import {
  ChevronLeft, ChevronRight, Clock, AlertCircle, ChevronDown,
  Calendar, CheckCircle, MinusCircle, XCircle, Info, Check,
} from 'lucide-react';

/* ── Date utils ──────────────────────────────────────────────────────────── */

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

function formatTriggerValue(mode, startKey, endKey) {
  const fmtShort = (k) =>
    fromKey(k).toLocaleDateString('pt-PT', { day: 'numeric', month: 'short' });
  if (mode === 'single') {
    if (!startKey) return null;
    return fromKey(startKey).toLocaleDateString('pt-PT', {
      day: 'numeric', month: 'short', year: 'numeric',
    });
  }
  if (!startKey) return null;
  if (endKey) return `${fmtShort(startKey)} – ${fmtShort(endKey)}`;
  return `${fmtShort(startKey)} – ...`;
}

/* ── Calendar grid ───────────────────────────────────────────────────────── */

function buildCalendarGrid(year, month) {
  const first    = new Date(year, month, 1);
  const last     = new Date(year, month + 1, 0);
  const startDow = (first.getDay() + 6) % 7; // Mon=0 … Sun=6
  const cells    = [];
  const prevLast = new Date(year, month, 0);
  for (let i = startDow - 1; i >= 0; i--)
    cells.push({ date: new Date(year, month - 1, prevLast.getDate() - i), overflow: true });
  for (let d = 1; d <= last.getDate(); d++)
    cells.push({ date: new Date(year, month, d), overflow: false });
  let next = 1;
  while (cells.length % 7 !== 0)
    cells.push({ date: new Date(year, month + 1, next++), overflow: true });
  return cells;
}

const DOW_LABELS  = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
const MONTH_NAMES = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

const OCC_DOT = { free: 'bg-green-400', partial: 'bg-amber-400', full: 'bg-red-400' };

// Fixed year range — never shifts when the user navigates
const YEAR_MIN  = new Date().getFullYear() - 20;
const YEAR_MAX  = new Date().getFullYear() + 30;
const YEAR_LIST = Array.from({ length: YEAR_MAX - YEAR_MIN + 1 }, (_, i) => YEAR_MIN + i);

/* ── TimeSidePanel ───────────────────────────────────────────────────────── */

const TIME_SLOTS = (() => {
  const s = [];
  for (let h = 0; h < 24; h++)
    for (let m = 0; m < 60; m += 15)
      s.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`);
  return s;
})();

function fmt12(t) {
  if (!t) return '';
  const [h, m] = t.split(':').map(Number);
  return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h < 12 ? 'am' : 'pm'}`;
}

function TimeSidePanel({ selectedDateKey, timeValue, onTimeChange }) {
  const listRef   = useRef(null);
  const activeIdx = TIME_SLOTS.indexOf(timeValue ?? '');

  useEffect(() => {
    if (!listRef.current || activeIdx < 0) return;
    listRef.current.children[activeIdx]?.scrollIntoView({ block: 'center' });
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const dayLabel = (() => {
    if (!selectedDateKey) return 'Hora';
    const d = fromKey(selectedDateKey);
    if (!d) return 'Hora';
    const wd = d.toLocaleDateString('pt-PT', { weekday: 'long' });
    return `${d.getDate()} ${MONTH_NAMES[d.getMonth()].slice(0, 3)}, ${wd.charAt(0).toUpperCase() + wd.slice(1)}`;
  })();

  return (
    <div className="flex flex-col border-l border-gray-100 w-28 shrink-0">
      <div className="px-3 py-2.5 border-b border-gray-100">
        <p className="text-[11px] font-semibold text-gray-700 leading-snug truncate">{dayLabel}</p>
      </div>
      <div ref={listRef} className="overflow-y-auto overscroll-contain"
           style={{ scrollbarWidth: 'none', maxHeight: 256 }}>
        {TIME_SLOTS.map((slot) => (
          <button key={slot} type="button" onClick={() => onTimeChange?.(slot)}
            className={`w-full py-[7px] text-center text-xs transition-colors ${
              slot === timeValue
                ? 'bg-brand-accent/10 text-brand-accent font-semibold'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'
            }`}>
            {fmt12(slot)}
          </button>
        ))}
      </div>
    </div>
  );
}

/* ── AgendaPane ──────────────────────────────────────────────────────────── */

const LEVEL_CFG = {
  free:    { color: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200', Icon: CheckCircle, label: 'Disponível' },
  partial: { color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-200', Icon: MinusCircle, label: 'Parcialmente ocupado' },
  full:    { color: 'text-red-600',   bg: 'bg-red-50',   border: 'border-red-200',   Icon: XCircle,     label: 'Totalmente ocupado' },
};

function AgendaPane({ activeKey, occData }) {
  const data = activeKey ? occData?.[activeKey] : null;
  const date = activeKey ? fromKey(activeKey) : null;

  return (
    <div className="flex flex-col w-44 shrink-0 border-l border-gray-100 bg-white">
      <div className="px-3 py-2.5 border-b border-gray-100 bg-gray-50/60 min-h-[52px] flex flex-col justify-center">
        {date ? (
          <>
            <p className="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
              {date.toLocaleDateString('pt-PT', { weekday: 'long' })}
            </p>
            <p className="text-base font-bold text-gray-900 leading-tight mt-0.5">
              {date.getDate()}{' '}
              <span className="text-sm font-medium text-gray-500">
                {MONTH_NAMES[date.getMonth()].slice(0, 3)}
              </span>
            </p>
          </>
        ) : (
          <p className="text-[11px] text-gray-400 leading-snug">Passe o rato sobre um dia</p>
        )}
      </div>

      <div className="flex-1 overflow-y-auto p-3 space-y-2.5"
           style={{ maxHeight: 236, scrollbarWidth: 'none' }}>
        {!date ? (
          <div className="flex flex-col items-center gap-2 pt-5 text-center">
            <Calendar size={18} className="text-gray-300" />
            <p className="text-[11px] text-gray-400 leading-snug">
              Selecione uma data para ver disponibilidade
            </p>
          </div>
        ) : !data ? (
          <div className="flex flex-col items-center gap-2 pt-5 text-center">
            <Info size={16} className="text-gray-300" />
            <p className="text-[11px] text-gray-400 leading-snug">Sem dados de disponibilidade</p>
          </div>
        ) : (
          <>
            {(() => {
              const cfg = LEVEL_CFG[data.level] ?? LEVEL_CFG.free;
              const { Icon } = cfg;
              return (
                <div className={`flex items-center gap-1.5 rounded-lg px-2 py-1.5 ${cfg.bg} border ${cfg.border}`}>
                  <Icon size={11} className={cfg.color} />
                  <span className={`text-[11px] font-semibold ${cfg.color} leading-none`}>{cfg.label}</span>
                </div>
              );
            })()}
            {data.allocatedHours != null && data.totalHours != null && (
              <div className="space-y-1">
                <div className="flex justify-between text-[10px] text-gray-500">
                  <span>{data.allocatedHours}h alocadas</span>
                  <span>{data.totalHours}h total</span>
                </div>
                <div className="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                  <div className="h-full bg-brand-accent rounded-full transition-all"
                       style={{ width: `${Math.min(100, (data.allocatedHours / data.totalHours) * 100)}%` }} />
                </div>
              </div>
            )}
            {data.count != null && !data.items?.length && (
              <p className="text-[11px] text-gray-500">
                <span className="font-semibold text-gray-700">{data.count}</span>{' '}
                {data.count === 1 ? 'registo ativo' : 'registos ativos'}
              </p>
            )}
            {data.items?.map((item, i) => (
              <div key={i} className="flex items-start gap-1.5">
                <span className="mt-1 h-1.5 w-1.5 rounded-full bg-gray-300 shrink-0" />
                <span className="text-[11px] text-gray-600 leading-snug">{item.label}</span>
              </div>
            ))}
          </>
        )}
      </div>
    </div>
  );
}

/* ── DatePicker ──────────────────────────────────────────────────────────── */
/**
 * Controlled date picker — trigger input opens a split-pane popover.
 *
 * Modes:
 *   mode="single"       value: string | null  (YYYY-MM-DD)
 *   mode="range"        value: { start: string|null, end: string|null }
 *   mode="availability" value: { start, end, valid? }  + blockedDates: string[]
 *
 * Time:
 *   showTime={true}       → scrollable time list in right pane (no availabilityData)
 *   showTimeInline={true} → native time input row below calendar
 *   timeValue / onTimeChange
 *
 * Availability heatmap (right pane + per-day dots):
 *   availabilityData / occupation:
 *     { [YYYY-MM-DD]: { level: 'free'|'partial'|'full',
 *                       count?, allocatedHours?, totalHours?,
 *                       items?: { label: string }[] } }
 */
export default function DatePicker({
  mode             = 'single',
  value,
  onChange,
  blockedDates     = [],
  minDate,
  maxDate,
  label,
  name,
  startName,
  endName,
  error,
  required,
  showTime         = false,
  showTimeInline   = false,
  timeValue,
  onTimeChange,
  onCancel,
  showActions,
  placeholder,
  availabilityData,
  occupation,
}) {
  const todayKey   = toKey(new Date());
  const occData    = availabilityData ?? occupation ?? null;
  const showAgenda = !!occData;

  /* ── Derive start / end from value prop ──────────────────────────────── */
  const startKey = mode === 'single' ? (value ?? null) : (value?.start ?? null);
  const endKey   = mode === 'single' ? null             : (value?.end   ?? null);

  /* ── Popover open / position ─────────────────────────────────────────── */
  const [isOpen,      setIsOpen]      = useState(false);
  const [popoverPos,  setPopoverPos]  = useState({ top: 0, left: 0, minWidth: 268 });
  const triggerRef = useRef(null);
  const popoverRef = useRef(null);

  const hasRightPane = showAgenda || showTime;

  const openPopover = useCallback(() => {
    if (!triggerRef.current) return;
    // position:fixed portal — coordinates are viewport-relative, no scroll math needed
    const rect = triggerRef.current.getBoundingClientRect();
    const minW = hasRightPane ? 420 : 268;
    const left = Math.max(8, Math.min(rect.left, window.innerWidth - minW - 8));
    const spaceBelow = window.innerHeight - rect.bottom;
    const top = spaceBelow > 320
      ? rect.bottom + 6
      : Math.max(8, rect.top - 6 - 400);
    setPopoverPos({ top, left, minWidth: minW });
    setHeaderMode('nav');
    setIsOpen(true);
  }, [hasRightPane]);

  /* Click-outside + scroll-to-close ───────────────────────────────────── */
  useEffect(() => {
    if (!isOpen) return;
    const onMouseDown = (e) => {
      const inTrigger = triggerRef.current?.contains(e.target);
      const inPopover = popoverRef.current?.contains(e.target);
      if (!inTrigger && !inPopover) setIsOpen(false);
    };
    // Close on external scroll but not when scrolling inside the popover
    // (e.g. the time-slot panel or agenda pane)
    const onScroll = (e) => {
      if (popoverRef.current?.contains(e.target)) return;
      setIsOpen(false);
    };
    const onResize = () => setIsOpen(false);
    document.addEventListener('mousedown', onMouseDown);
    window.addEventListener('scroll', onScroll, true);
    window.addEventListener('resize', onResize);
    return () => {
      document.removeEventListener('mousedown', onMouseDown);
      window.removeEventListener('scroll', onScroll, true);
      window.removeEventListener('resize', onResize);
    };
  }, [isOpen]);

  /* ── View state ──────────────────────────────────────────────────────── */
  const initDate    = (startKey ? fromKey(startKey) : null) ?? new Date();
  const [viewYear,  setViewYear]  = useState(initDate.getFullYear());
  const [viewMonth, setViewMonth] = useState(initDate.getMonth());
  const [headerMode, setHeaderMode] = useState('nav'); // 'nav' | 'select'

  /* Sync view to externally set start date */
  const syncedKey = useRef(startKey);
  useEffect(() => {
    if (!startKey || startKey === syncedKey.current) return;
    syncedKey.current = startKey;
    const d = fromKey(startKey);
    if (d) { setViewYear(d.getFullYear()); setViewMonth(d.getMonth()); }
  }, [startKey]);

  /* ── Hover — drives agenda pane + range preview ──────────────────────── */
  const [hoverKey, setHoverKey] = useState(null);
  const activeKey = hoverKey ?? startKey;

  /* ── Blocked dates set ───────────────────────────────────────────────── */
  const blockedSet = useMemo(() => new Set(blockedDates), [blockedDates]);

  /* ── Range preview (while awaiting end date) ─────────────────────────── */
  const awaitingEnd   = (mode !== 'single') && !!startKey && !endKey;
  const previewAnchor = awaitingEnd ? startKey : null;
  const previewCursor = awaitingEnd ? hoverKey : null;
  const previewStart  = previewAnchor && previewCursor
    ? (previewCursor < previewAnchor ? previewCursor : previewAnchor) : null;
  const previewEnd    = previewAnchor && previewCursor
    ? (previewCursor < previewAnchor ? previewAnchor : previewCursor) : null;

  const dispStart = endKey ? startKey : previewStart;
  const dispEnd   = endKey ? endKey   : previewEnd;

  /* ── Range validity (availability mode) ──────────────────────────────── */
  const rangeInvalid = mode === 'availability' && !!endKey
    ? rangeOverlapsBlocked(dispStart, dispEnd, blockedSet)
    : false;

  /* ── Calendar cells ──────────────────────────────────────────────────── */
  const cells = useMemo(
    () => buildCalendarGrid(viewYear, viewMonth),
    [viewYear, viewMonth],
  );

  /* ── Navigation ──────────────────────────────────────────────────────── */
  const goToPrev = () => {
    if (viewMonth === 0) { setViewYear(y => y - 1); setViewMonth(11); }
    else setViewMonth(m => m - 1);
  };
  const goToNext = () => {
    if (viewMonth === 11) { setViewYear(y => y + 1); setViewMonth(0); }
    else setViewMonth(m => m + 1);
  };

  /* ── Day click ───────────────────────────────────────────────────────── */
  const handleDayClick = (key) => {
    if (blockedSet.has(key)) return;
    if (minDate && key < minDate) return;
    if (maxDate && key > maxDate) return;

    if (mode === 'single') {
      const next = key === startKey ? null : key;
      onChange?.(next);
      if (next) setIsOpen(false);
      return;
    }
    /* range / availability: first click sets start, second sets end */
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

  /* ── Cancelar ────────────────────────────────────────────────────────── */
  const handleCancel = () => {
    if (mode === 'single') onChange?.(null);
    else onChange?.(mode === 'availability'
      ? { start: null, end: null, valid: false }
      : { start: null, end: null });
    onCancel?.();
    setIsOpen(false);
  };

  /* ── Per-cell CSS state ──────────────────────────────────────────────── */
  const getCellState = (key) => {
    const isToday          = key === todayKey;
    const isBlocked        = blockedSet.has(key);
    const isDisabled       = isBlocked || (!!minDate && key < minDate) || (!!maxDate && key > maxDate);
    const isSelectedSingle = mode === 'single' && key === startKey;
    const isStart          = mode !== 'single' && !!dispStart && key === dispStart;
    const isEnd            = mode !== 'single' && !!dispEnd   && key === dispEnd;
    const isSingleDayRange = isStart && isEnd;
    const isInBand         = !isSingleDayRange && !!dispStart && !!dispEnd
                             && key > dispStart && key < dispEnd;
    return { isToday, isBlocked, isDisabled, isSelectedSingle, isStart, isEnd, isSingleDayRange, isInBand };
  };

  // yearItems uses the module-level YEAR_LIST constant — never shifts on navigation

  /* ── Derived display values ──────────────────────────────────────────── */
  const showActionsBar     = showActions ?? (mode !== 'single');
  const triggerDisplay     = formatTriggerValue(mode, startKey, endKey);
  const defaultPlaceholder = mode === 'single' ? 'Selecione uma data' : 'Selecione um intervalo';
  const hasError           = error || rangeInvalid;

  /* ── Popover content ─────────────────────────────────────────────────── */
  const popoverContent = (
    <div
      ref={popoverRef}
      style={{ position: 'fixed', zIndex: 9999, ...popoverPos }}
      className="rounded-2xl border border-gray-200 bg-white shadow-xl overflow-hidden select-none"
    >
      {/* Header: prev / month+year / next  or  quick-select dropdowns */}
      <div className="flex items-center gap-2 px-3 py-2.5 border-b border-gray-100">
        {headerMode === 'nav' ? (
          <>
            <button type="button" onClick={goToPrev}
              className="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
              <ChevronLeft size={14} />
            </button>
            <button type="button" onClick={() => setHeaderMode('select')}
              className="flex flex-1 items-center justify-center gap-1.5 rounded-lg py-1 hover:bg-gray-50 transition-colors">
              <span className="text-sm font-bold text-gray-900">{MONTH_NAMES[viewMonth]}</span>
              <span className="text-sm font-normal text-gray-400">{viewYear}</span>
              <ChevronDown size={12} className="text-gray-400" />
            </button>
            <button type="button" onClick={goToNext}
              className="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
              <ChevronRight size={14} />
            </button>
          </>
        ) : (
          <>
            <select value={viewMonth}
              onChange={e => { setViewMonth(Number(e.target.value)); setHeaderMode('nav'); }}
              className="flex-1 rounded-lg border border-gray-200 bg-white py-1.5 pl-2.5 pr-7 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:border-brand-accent appearance-none cursor-pointer"
              style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 8px center' }}>
              {MONTH_NAMES.map((n, i) => <option key={i} value={i}>{n}</option>)}
            </select>
            <select value={viewYear}
              onChange={e => { setViewYear(Number(e.target.value)); setHeaderMode('nav'); }}
              className="w-24 rounded-lg border border-gray-200 bg-white py-1.5 pl-2.5 pr-7 text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:border-brand-accent appearance-none cursor-pointer"
              style={{ backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")`, backgroundRepeat: 'no-repeat', backgroundPosition: 'right 8px center' }}>
              {YEAR_LIST.map(y => <option key={y} value={y}>{y}</option>)}
            </select>
          </>
        )}
      </div>

      {/* Split-pane body */}
      <div className="flex flex-col sm:flex-row">

        {/* Left — calendar grid */}
        <div className="flex-1 min-w-0">
          {/* Day-of-week header */}
          <div className="grid grid-cols-7 px-2 pt-3 pb-1">
            {DOW_LABELS.map(d => (
              <div key={d} className="text-center text-[11px] font-semibold text-gray-400 py-0.5 tracking-wide">
                {d}
              </div>
            ))}
          </div>

          {/* Day grid */}
          <div className="grid grid-cols-7 px-2 pb-3">
            {cells.map((cell, idx) => {
              const { date, overflow } = cell;
              const key = toKey(date);
              const st  = overflow
                ? { isToday: false, isBlocked: false, isDisabled: true, isSelectedSingle: false, isStart: false, isEnd: false, isSingleDayRange: false, isInBand: false }
                : getCellState(key);
              const { isToday, isBlocked, isDisabled, isSelectedSingle, isStart, isEnd, isSingleDayRange, isInBand } = st;

              const bandColor = rangeInvalid ? 'bg-red-100' : 'bg-brand-accent/12';
              let bandClass = '';
              if (!overflow && !isSingleDayRange) {
                if (isStart && dispEnd)      bandClass = `absolute inset-y-0 left-1/2 right-0 ${bandColor}`;
                else if (isEnd && dispStart) bandClass = `absolute inset-y-0 left-0 right-1/2 ${bandColor}`;
                else if (isInBand)           bandClass = `absolute inset-y-0 left-0 right-0 ${bandColor}`;
              }

              let btnClass = 'text-gray-800 hover:bg-gray-100';
              if (overflow) {
                btnClass = 'text-gray-300 cursor-default pointer-events-none';
              } else if (isSelectedSingle || isStart || isEnd) {
                btnClass = rangeInvalid ? 'bg-red-500 text-white shadow-sm' : 'bg-brand-accent text-white shadow-sm';
              } else if (isInBand) {
                btnClass = rangeInvalid ? 'text-red-600' : 'text-brand-accent font-medium';
              } else if (isBlocked) {
                btnClass = 'bg-red-50 text-red-300 cursor-not-allowed';
              }

              const isHighlighted = isSelectedSingle || isStart || isEnd;
              const occLevel = !overflow ? occData?.[key]?.level : null;

              return (
                <div key={`${key}-${idx}`} className="relative h-9 flex items-center justify-center">
                  {bandClass && <div className={bandClass} />}
                  {isBlocked && !overflow && (
                    <div className="absolute inset-0 flex items-center justify-center pointer-events-none z-20">
                      <div className="absolute h-px w-5 bg-red-300/70 rotate-45 rounded-full" />
                    </div>
                  )}
                  <button
                    type="button"
                    onClick={() => !isDisabled && !overflow && handleDayClick(key)}
                    onMouseEnter={() => !overflow && setHoverKey(key)}
                    onMouseLeave={() => setHoverKey(null)}
                    disabled={isDisabled}
                    className={`
                      relative z-10 flex h-8 w-8 flex-col items-center justify-center rounded-full
                      text-xs font-medium transition-colors disabled:cursor-not-allowed ${btnClass}
                    `}
                  >
                    {date.getDate()}
                    {/* Heatmap dot > today dot — only when not highlighted */}
                    {!overflow && !isHighlighted && (
                      occLevel
                        ? <span className={`absolute bottom-0.5 h-1 w-1 rounded-full ${OCC_DOT[occLevel] ?? 'bg-gray-400'}`} />
                        : isToday
                          ? <span className="absolute bottom-0.5 h-1 w-1 rounded-full bg-brand-accent" />
                          : null
                    )}
                  </button>
                </div>
              );
            })}
          </div>
        </div>

        {/* Right pane — Agenda or Time picker */}
        {showAgenda ? (
          <AgendaPane activeKey={activeKey} occData={occData} />
        ) : showTime ? (
          <TimeSidePanel
            selectedDateKey={startKey}
            timeValue={timeValue}
            onTimeChange={onTimeChange}
          />
        ) : null}
      </div>

      {/* Inline time row */}
      {showTimeInline && (
        <div className="flex items-center gap-3 px-4 py-3 border-t border-gray-100">
          <div className="flex items-center gap-1.5 text-sm font-medium text-gray-700">
            <Clock size={14} className="text-gray-400" />
            <span>Hora</span>
          </div>
          <input type="time" value={timeValue ?? ''}
            onChange={e => onTimeChange?.(e.target.value || null)}
            className="ml-auto rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:border-brand-accent"
          />
        </div>
      )}

      {/* Availability conflict warning */}
      {rangeInvalid && (
        <div className="flex items-start gap-2 mx-3 rounded-xl bg-red-50 border border-red-200 px-3 py-2">
          <AlertCircle size={13} className="text-red-500 shrink-0 mt-0.5" />
          <p className="text-xs text-red-600 leading-relaxed">
            Este intervalo não está disponível — o equipamento está ocupado em parte deste período.
          </p>
        </div>
      )}

      {/* Availability hint */}
      {mode === 'availability' && !startKey && !!blockedDates.length && (
        <div className="flex items-center gap-2 px-4 pb-3 text-xs text-gray-400">
          <span className="inline-block h-2.5 w-2.5 rounded-full bg-red-100 border border-red-300/60" />
          Datas ocupadas não podem ser incluídas no intervalo
        </div>
      )}

      {/* Heatmap legend */}
      {showAgenda && (
        <div className="flex items-center justify-center gap-4 px-4 py-2 border-t border-gray-100 bg-gray-50/50">
          {[['bg-green-400', 'Livre'], ['bg-amber-400', 'Parcial'], ['bg-red-400', 'Ocupado']].map(([cls, lbl]) => (
            <div key={lbl} className="flex items-center gap-1">
              <span className={`h-1.5 w-1.5 rounded-full ${cls}`} />
              <span className="text-[10px] text-gray-400">{lbl}</span>
            </div>
          ))}
        </div>
      )}

      {/* Footer: confirm checkmark */}
      {showActionsBar && (
        <div className="flex items-center justify-end px-4 py-3 border-t border-gray-100">
          {(() => {
            const ready = mode === 'single' || (!!startKey && !!endKey);
            return (
              <button type="button"
                disabled={!ready}
                onClick={() => setIsOpen(false)}
                className={`flex h-8 w-8 items-center justify-center rounded-full transition-colors
                  ${ready
                    ? 'text-brand-accent bg-brand-accent/10 ring-1 ring-brand-accent/25 hover:bg-brand-accent/20'
                    : 'text-gray-300 cursor-not-allowed'
                  }`}>
                <Check size={15} strokeWidth={ready ? 3 : 2} />
              </button>
            );
          })()}
        </div>
      )}
    </div>
  );

  /* ── Render ──────────────────────────────────────────────────────────── */
  return (
    <div className="mb-4">
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-1.5">
          {label}
          {required && <span className="ml-1 text-red-500">*</span>}
        </label>
      )}

      {/* Trigger */}
      <button
        ref={triggerRef}
        type="button"
        onClick={() => isOpen ? setIsOpen(false) : openPopover()}
        className={`
          w-full flex items-center gap-2 rounded-xl border bg-white px-3.5 py-2.5
          text-left text-sm transition-colors
          hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-accent/30
          ${hasError ? 'border-red-400' : isOpen ? 'border-brand-accent' : 'border-gray-200'}
        `}
      >
        <Calendar size={15} className={triggerDisplay ? 'text-brand-accent' : 'text-gray-400'} />
        {triggerDisplay ? (
          <span className="font-medium text-gray-900">{triggerDisplay}</span>
        ) : (
          <span className="text-gray-400">{placeholder ?? defaultPlaceholder}</span>
        )}
        <ChevronDown
          size={13}
          className={`ml-auto text-gray-400 transition-transform duration-150 ${isOpen ? 'rotate-180' : ''}`}
        />
      </button>

      {/* Popover via portal — escapes any overflow:hidden ancestor */}
      {isOpen && createPortal(popoverContent, document.body)}

      {/* Hidden inputs for form submission */}
      {mode === 'single' && (
        <input type="hidden" name={name} value={startKey ?? ''} readOnly />
      )}
      {mode !== 'single' && (
        <>
          <input type="hidden" name={startName || (name ? `${name}_start` : undefined)} value={startKey ?? ''} readOnly />
          <input type="hidden" name={endName   || (name ? `${name}_end`   : undefined)} value={endKey   ?? ''} readOnly />
        </>
      )}

      {error && (
        <p className="text-xs text-red-500 mt-1.5">{error}</p>
      )}
    </div>
  );
}
