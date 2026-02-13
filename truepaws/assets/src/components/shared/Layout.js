import React from 'react';

function Layout({ children, title, actions }) {
  return (
    <div className="truepaws-page">
      <div className="truepaws-page-header">
        {title && <h2 className="truepaws-page-title">{title}</h2>}
        {actions && <div className="truepaws-page-actions">{actions}</div>}
      </div>
      <div className="truepaws-page-content">
        {children}
      </div>
    </div>
  );
}

export default Layout;