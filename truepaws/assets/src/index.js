import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './styles/main.css';

// Get the container element
const container = document.getElementById('truepaws-app');

// Create root and render app
const root = createRoot(container);
root.render(<App />);