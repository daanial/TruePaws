import React, { useState, useEffect } from 'react';
import { HashRouter as Router, Routes, Route, Link, useLocation } from 'react-router-dom';
import AnimalList from './components/Animals/AnimalList';
import AnimalForm from './components/Animals/AnimalForm';
import AnimalProfile from './components/Animals/AnimalProfile';
import LitterList from './components/Litters/LitterList';
import LitterForm from './components/Litters/LitterForm';
import WhelpingWizard from './components/Litters/WhelpingWizard';
import ContactList from './components/Contacts/ContactList';
import ContactForm from './components/Contacts/ContactForm';
import ContactProfile from './components/Contacts/ContactProfile';
import SettingsPage from './components/Settings/SettingsPage';
import Sidebar from './components/shared/Sidebar';
import LoadingSpinner from './components/shared/LoadingSpinner';
import LatestEvents from './components/shared/LatestEvents';
import { ToastProvider } from './components/shared/ToastContainer';
import ErrorBoundary from './components/shared/ErrorBoundary';
import { BreedDistributionChart, SalesChart } from './components/shared/DashboardCharts';
import ActivityHeatmap from './components/shared/ActivityHeatmap';
import { dashboardAPI } from './api/client';
import './styles/main.css';

function App() {
  const [isLoading, setIsLoading] = useState(false);

  return (
    <ToastProvider>
      <Router>
        <div className="truepaws-app">
          <Header />
          <div className="truepaws-main">
            <Sidebar />
            <main className="truepaws-content">
              {isLoading && <LoadingSpinner />}
              <ErrorBoundary>
                <Routes>
                  <Route path="/" element={<Dashboard />} />
                  <Route path="/animals" element={<AnimalList />} />
                  <Route path="/animals/new" element={<AnimalForm />} />
                  <Route path="/animals/:id/edit" element={<AnimalForm />} />
                  <Route path="/animals/:id" element={<AnimalProfile />} />
                  <Route path="/litters" element={<LitterList />} />
                  <Route path="/litters/new" element={<LitterForm />} />
                  <Route path="/litters/:id/whelp" element={<WhelpingWizard />} />
                  <Route path="/contacts" element={<ContactList />} />
                  <Route path="/contacts/new" element={<ContactForm />} />
                  <Route path="/contacts/:id" element={<ContactProfile />} />
                  <Route path="/settings" element={<SettingsPage />} />
                </Routes>
              </ErrorBoundary>
            </main>
          </div>
        </div>
      </Router>
    </ToastProvider>
  );
}

function Header() {
  const pluginUrl = (window.truepawsData && window.truepawsData.pluginUrl) || '';
  const logoUrl = `${pluginUrl}assets/src/images/truepaws-logo.png`;

  return (
    <header className="truepaws-header">
      <div className="truepaws-brand">
        <img
          src={logoUrl}
          alt="TruePaws"
          className="truepaws-logo"
          onError={(e) => { e.currentTarget.style.display = 'none'; }}
        />
        <h1 className="truepaws-title">TruePaws</h1>
      </div>
      <nav className="truepaws-navigation">
        <Link to="/animals" className="truepaws-nav-button">Animals</Link>
        <Link to="/litters" className="truepaws-nav-button">Litters</Link>
        <Link to="/contacts" className="truepaws-nav-button">Contacts</Link>
      </nav>
    </header>
  );
}

const FEATURES = [
  {
    icon: '🐕',
    title: 'Lineage Tracking',
    description: 'Comprehensive pedigree trees and ancestral history for every animal.'
  },
  {
    icon: '👨‍👩‍👧‍👦',
    title: 'Reproduction Management',
    description: 'Track matings, whelping dates, and litter health in one place.'
  },
  {
    icon: '👥',
    title: 'Client Database',
    description: 'Manage contacts, waitlists, and sales history effortlessly.'
  },
  {
    icon: '🩺',
    title: 'Health Records',
    description: 'Log vaccinations, vet visits, and medical history with reminders.'
  },
  {
    icon: '📄',
    title: 'Digital Handovers',
    description: 'Automated professional PDF handover packets for new owners.'
  },
  {
    icon: '☁️',
    title: 'Secure Cloud',
    description: 'Your data is safely stored and accessible anywhere, anytime.'
  }
];

function Dashboard() {
  const [stats, setStats] = useState({
    totalAnimals: 0,
    activeLitters: 0,
    totalContacts: 0,
    upcomingEvents: 0,
    breedsByCount: []
  });
  const [latestEvents, setLatestEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [eventsLoading, setEventsLoading] = useState(true);

  // Get plugin URL for the banner image
  const pluginUrl = window.truepawsData?.pluginUrl || '';
  const bannerUrl = `${pluginUrl}assets/src/images/banner.jpg`;

  useEffect(() => {
    loadStats();
    loadLatestEvents();
  }, []);

  const loadStats = async () => {
    try {
      const response = await dashboardAPI.getStats();
      console.log('Dashboard API Response:', response.data);
      if (response.data.success) {
        console.log('Setting stats to:', response.data.stats);
        setStats(response.data.stats);
      }
    } catch (error) {
      console.error('Error loading dashboard stats:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadLatestEvents = async () => {
    try {
      const response = await dashboardAPI.getLatestEvents();
      if (response.data.success) {
        setLatestEvents(response.data.events || []);
      }
    } catch (error) {
      console.error('Error loading latest events:', error);
    } finally {
      setEventsLoading(false);
    }
  };

  return (
    <div className="truepaws-dashboard">
      <div 
        className="dashboard-banner" 
        style={{ backgroundImage: `url(${bannerUrl})` }}
      >
        <div className="dashboard-welcome">
          <h2 className="dashboard-title">Welcome to TruePaws</h2>
          <p className="dashboard-subtitle">Your Complete Kennel Management System</p>
        </div>
      </div>

      <div className="dashboard-stats">
        <div className="stat-card">
          <div className="stat-icon" style={{background: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'}}>🐕</div>
          <div className="stat-info">
            <div className="stat-value">{stats.totalAnimals}</div>
            <div className="stat-label">Total Animals</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon" style={{background: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'}}>👨‍👩‍👧‍👦</div>
          <div className="stat-info">
            <div className="stat-value">{stats.activeLitters}</div>
            <div className="stat-label">Active Litters</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon" style={{background: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'}}>👥</div>
          <div className="stat-info">
            <div className="stat-value">{stats.totalContacts}</div>
            <div className="stat-label">Contacts</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon" style={{background: 'linear-gradient(135deg, #ff9a56 0%, #ffce54 100%)'}}>📅</div>
          <div className="stat-info">
            <div className="stat-value">{stats.upcomingEvents}</div>
            <div className="stat-label">Upcoming Events</div>
          </div>
        </div>
      </div>

      <div className="dashboard-grid">
        <div className="dashboard-card quick-actions">
          <h3 className="card-title">Quick Actions</h3>
          <div className="action-buttons">
            <Link to="/animals/new" className="truepaws-button">
              <span>➕</span> Add New Animal
            </Link>
            <Link to="/litters/new" className="truepaws-button secondary">
              <span>📝</span> Log Mating
            </Link>
            <Link to="/contacts/new" className="truepaws-button secondary">
              <span>👤</span> Add Contact
            </Link>
          </div>
        </div>

        <div className="dashboard-card getting-started">
          <h3 className="card-title">Getting Started</h3>
          <ul className="checklist">
            <li>Add your breeding animals (sire and dam)</li>
            <li>Log matings to create litters</li>
            <li>Record whelping dates and puppy counts</li>
            <li>Manage contacts and sales</li>
            <li>Generate PDF handover packets</li>
          </ul>
        </div>

        <div className="dashboard-card latest-events-card">
          <LatestEvents events={latestEvents} loading={eventsLoading} />
        </div>

        <div className="dashboard-card breed-analytics">
          <h3 className="card-title">Breed Distribution</h3>
          <BreedDistributionChart breedsByCount={stats.breedsByCount} />
        </div>

        <div className="dashboard-card sales-analytics">
          <h3 className="card-title">Sales Overview</h3>
          <SalesChart />
        </div>
      </div>

      <ActivityHeatmap />

      <div className="dashboard-features">
        <h3 className="features-section-title">Why TruePaws?</h3>
        <div className="features-grid">
          {FEATURES.map((feature, index) => (
            <div 
              key={index} 
              className="feature-box"
              style={{ animationDelay: `${index * 0.1}s` }}
            >
              <div className="feature-icon">{feature.icon}</div>
              <h4 className="feature-title">{feature.title}</h4>
              <p className="feature-description">{feature.description}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default App;