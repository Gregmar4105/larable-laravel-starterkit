import { Component, ReactNode } from 'react';
import { AlertTriangle, RefreshCw } from 'lucide-react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

/**
 * ErrorBoundary
 *
 * Catches unhandled React rendering errors and displays
 * a user-friendly fallback UI instead of a white screen.
 */
export default class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('[ErrorBoundary] Caught error:', error, errorInfo);
  }

  handleReload = () => {
    window.location.reload();
  };

  handleReset = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-boundary">
          <div className="error-boundary-card">
            <div className="error-boundary-icon">
              <AlertTriangle size={48} />
            </div>
            <h1>Something went wrong</h1>
            <p>An unexpected error occurred. Please try reloading the page.</p>
            {this.state.error && (
              <code className="error-boundary-detail">
                {this.state.error.message}
              </code>
            )}
            <div className="error-boundary-actions">
              <button onClick={this.handleReload} className="btn btn-primary">
                <RefreshCw size={16} />
                Reload Page
              </button>
              <button onClick={this.handleReset} className="btn btn-secondary">
                Try Again
              </button>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
