import React from 'react';
import { __ } from '@wordpress/i18n';

function LoadingSpinner({ size = 'medium', message }) {
  const displayMessage = message !== undefined ? message : __('Loading...', 'truepaws');
  const sizeClass = `truepaws-spinner--${size}`;

  return (
    <div className="truepaws-spinner-container">
      <div className={`truepaws-spinner ${sizeClass}`}></div>
      {displayMessage && <p className="truepaws-spinner-message">{displayMessage}</p>}
    </div>
  );
}

export default LoadingSpinner;