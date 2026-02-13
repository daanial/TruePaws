import React from 'react';

function LoadingSpinner({ size = 'medium', message = 'Loading...' }) {
  const sizeClass = `truepaws-spinner--${size}`;

  return (
    <div className="truepaws-spinner-container">
      <div className={`truepaws-spinner ${sizeClass}`}></div>
      {message && <p className="truepaws-spinner-message">{message}</p>}
    </div>
  );
}

export default LoadingSpinner;