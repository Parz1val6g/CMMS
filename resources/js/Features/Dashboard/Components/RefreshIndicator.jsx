import { RefreshCw } from 'lucide-react';

export default function RefreshIndicator({ countdown, onRefresh }) {
  return (
    <button
      type="button"
      onClick={onRefresh}
      className="flex items-center gap-1.5 rounded-lg border border-brand-mid/20 bg-brand-white px-3 py-1.5 text-xs text-brand-mid shadow-sm hover:text-brand-darkest transition-colors"
    >
      <RefreshCw size={12} className={countdown <= 5 ? 'animate-spin' : ''} />
      ↻ {countdown}s
    </button>
  );
}
