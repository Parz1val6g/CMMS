import { Component } from 'react';
import { t } from '@/utils/i18n';

const SENTRY_DSN = import.meta.env.VITE_SENTRY_DSN;

export default class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    componentDidCatch(error, info) {
        if (import.meta.env.DEV) {
            console.error('[ErrorBoundary]', error, info.componentStack);
        }
        // Send to Sentry in production if DSN is configured
        if (!import.meta.env.DEV && SENTRY_DSN) {
            try {
                // Dynamic import avoids bundling Sentry if DSN is absent
                import('@sentry/react').then((Sentry) => {
                    Sentry.captureException(error, { contexts: { react: { componentStack: info?.componentStack } } });
                });
            } catch {
                // Sentry unavailable — fail silently
            }
        }
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="flex h-screen w-screen items-center justify-center bg-brand-light">
                    <div className="text-center max-w-md px-6">
                        <h1 className="text-2xl font-bold text-brand-darkest mb-2">{t('common.error_boundary.heading')}</h1>
                        <p className="text-brand-mid mb-6 text-sm">
                            {t('common.error_boundary.description')}
                        </p>
                        <button
                            onClick={() => window.location.reload()}
                            className="rounded-xl bg-brand-accent px-4 py-2 text-sm font-medium text-brand-white hover:bg-brand-accent/90 transition-colors"
                        >
                            {t('common.error_boundary.reload_button')}
                        </button>
                    </div>
                </div>
            );
        }
        return this.props.children;
    }
}
