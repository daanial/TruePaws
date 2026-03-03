import React from 'react';
import { __ } from '@wordpress/i18n';

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    this.setState({
      error,
      errorInfo
    });
    
    // Log error to console in development
    if (process.env.NODE_ENV === 'development') {
      console.error('Error caught by boundary:', error, errorInfo);
    }
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="truepaws-error-boundary">
          <div className="error-boundary-content">
            <svg 
              width="64" 
              height="64" 
              viewBox="0 0 24 24" 
              fill="none" 
              stroke="currentColor" 
              strokeWidth="1.5"
              className="error-icon"
            >
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            
            <h2>{__('Something went wrong', 'truepaws')}</h2>
            <p>{__("We're sorry, but something unexpected happened. Please try refreshing the page.", 'truepaws')}</p>
            
            <div className="error-actions">
              <button 
                className="truepaws-button" 
                onClick={() => window.location.reload()}
              >
                {__('Refresh Page', 'truepaws')}
              </button>
              <button 
                className="truepaws-button secondary" 
                onClick={() => this.setState({ hasError: false, error: null, errorInfo: null })}
              >
                {__('Try Again', 'truepaws')}
              </button>
            </div>

            {process.env.NODE_ENV === 'development' && this.state.error && (
              <details className="error-details">
                <summary>{__('Error Details (Development Only)', 'truepaws')}</summary>
                <pre>{this.state.error.toString()}</pre>
                <pre>{this.state.errorInfo?.componentStack}</pre>
              </details>
            )}
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
