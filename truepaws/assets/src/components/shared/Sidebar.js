import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';

function Sidebar() {
  const location = useLocation();

  const menuItems = [
    { path: '/', label: __('Dashboard', 'truepaws'), icon: '📊' },
    { path: '/animals', label: __('Animals', 'truepaws'), icon: '🐕' },
    { path: '/litters', label: __('Litters', 'truepaws'), icon: '👨‍👩‍👧‍👦' },
    { path: '/contacts', label: __('Contacts', 'truepaws'), icon: '👥' },
    { path: '/settings', label: __('Settings', 'truepaws'), icon: '⚙️' },
  ];

  return (
    <aside className="truepaws-sidebar">
      <nav>
        <ul className="truepaws-menu">
          {menuItems.map((item) => (
            <li key={item.path}>
              <Link
                to={item.path}
                className={`truepaws-menu-item ${location.pathname === item.path ? 'active' : ''}`}
              >
                <span className="menu-icon">{item.icon}</span>
                <span className="menu-label">{item.label}</span>
              </Link>
            </li>
          ))}
        </ul>
      </nav>
    </aside>
  );
}

export default Sidebar;