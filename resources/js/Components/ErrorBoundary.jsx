import { Component } from 'react';

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
                <div className="flex h-screen w-screen items-center justify-center bg-slate-900">
                    <div className="text-center max-w-md px-6">
                        <h1 className="text-2xl font-bold text-white mb-2">Algo correu mal</h1>
                        <p className="text-slate-400 mb-6 text-sm">
                            Ocorreu um erro inesperado. Por favor, recarregue a página.
                        </p>
                        <button
                            onClick={() => window.location.reload()}
                            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                        >
                            Recarregar Página
                        </button>
                    </div>
                </div>
            );
        }
        return this.props.children;
    }
}
