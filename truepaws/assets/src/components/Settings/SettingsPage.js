import React, { useState, useEffect } from 'react';
import { settingsAPI, dashboardAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';
import BreedsManager from './BreedsManager';

function SalesReportsTab() {
  const [report, setReport] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const load = async () => {
      try {
        const res = await dashboardAPI.getSalesReport();
        if (res.data?.success) {
          setReport(res.data.report);
        }
      } catch (err) {
        console.error('Error loading sales report:', err);
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  if (loading) return <LoadingSpinner message="Loading sales report..." />;
  if (!report) return <p>Unable to load sales report.</p>;

  return (
    <div className="settings-tab-content sales-reports">
      <h3>Sales Report</h3>
      <div className="sales-stats-grid">
        <div className="sales-stat-card">
          <div className="sales-stat-value">${report.totalRevenue.toLocaleString()}</div>
          <div className="sales-stat-label">Total Revenue</div>
        </div>
        <div className="sales-stat-card">
          <div className="sales-stat-value">{report.totalSales}</div>
          <div className="sales-stat-label">Total Sales</div>
        </div>
        <div className="sales-stat-card">
          <div className="sales-stat-value">${report.averagePrice.toLocaleString()}</div>
          <div className="sales-stat-label">Average Price</div>
        </div>
      </div>

      {report.salesByMonth?.length > 0 && (
        <div className="sales-section">
          <h4>Sales by Month</h4>
          <table className="truepaws-table sales-table">
            <thead>
              <tr>
                <th>Month</th>
                <th>Sales</th>
                <th>Revenue</th>
              </tr>
            </thead>
            <tbody>
              {report.salesByMonth.map((row) => (
                <tr key={row.month}>
                  <td>{row.month}</td>
                  <td>{row.count}</td>
                  <td>${row.revenue.toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {report.topBuyers?.length > 0 && (
        <div className="sales-section">
          <h4>Top Buyers</h4>
          <table className="truepaws-table sales-table">
            <thead>
              <tr>
                <th>Contact</th>
                <th>Purchases</th>
                <th>Total Revenue</th>
              </tr>
            </thead>
            <tbody>
              {report.topBuyers.map((buyer) => (
                <tr key={buyer.contact_id}>
                  <td>{buyer.name || buyer.email}</td>
                  <td>{buyer.purchases}</td>
                  <td>${buyer.revenue.toLocaleString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {report.totalSales === 0 && (
        <p className="sales-empty">No sales recorded yet. Mark animals as sold from their profiles to track sales.</p>
      )}
    </div>
  );
}

function SettingsPage() {
  const [activeTab, setActiveTab] = useState('general');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [settings, setSettings] = useState({
    breeder_prefix: 'TP',
    default_species: 'dog',
    pregnancy_days_dog: 63,
    pregnancy_days_cat: 65,
    feeding_instructions: '',
    breeder_name: '',
    business_name: '',
    license_number: '',
    breeder_phone: '',
    breeder_email: '',
    address_street: '',
    address_city: '',
    address_state: '',
    address_zip: '',
    address_country: '',
    contact_url: '#contact',
    breeds: [],
    gemini_api_key: '',
  });

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      const response = await settingsAPI.getAll();
      if (response.data.success) {
        setSettings(response.data.settings);
      }
    } catch (error) {
      console.error('Error loading settings:', error);
      setError('Failed to load settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field, value) => {
    setSettings(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleBreedsChange = (newBreeds) => {
    setSettings(prev => ({
      ...prev,
      breeds: newBreeds
    }));
  };

  const handleSave = async () => {
    setSaving(true);
    setError('');
    setSuccess('');

    try {
      const response = await settingsAPI.update(settings);
      if (response.data.success) {
        setSuccess('Settings saved successfully!');
        setTimeout(() => setSuccess(''), 3000);
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      setError(error.response?.data?.message || 'Failed to save settings');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <LoadingSpinner message="Loading settings..." />;
  }

  const tabs = [
    { id: 'general', label: 'General' },
    { id: 'breeder', label: 'Breeder Info' },
    { id: 'breeds', label: 'Breeds' },
    { id: 'communication', label: 'Communication' },
    { id: 'sales', label: 'Sales Reports' },
  ];

  return (
    <Layout title="Settings">
      <div className="settings-page">
        <div className="settings-tabs">
          {tabs.map(tab => (
            <button
              key={tab.id}
              className={`settings-tab ${activeTab === tab.id ? 'active' : ''}`}
              onClick={() => setActiveTab(tab.id)}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <div className="settings-content">
          {error && <div className="truepaws-error">{error}</div>}
          {success && <div className="truepaws-success">{success}</div>}

          {activeTab === 'general' && (
            <div className="settings-tab-content">
              <h3>General Settings</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>Default Species *</label>
                  <select
                    value={settings.default_species}
                    onChange={(e) => handleChange('default_species', e.target.value)}
                  >
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="horse">Horse</option>
                    <option value="rabbit">Rabbit</option>
                    <option value="guinea_pig">Guinea Pig</option>
                    <option value="ferret">Ferret</option>
                    <option value="bird">Bird</option>
                  </select>
                  <p className="description">The main type of animal you are breeding. This applies to all animals, litters, and AI care advice.</p>
                </div>

                <div className="truepaws-form-group">
                  <label>Breeder Prefix *</label>
                  <input
                    type="text"
                    value={settings.breeder_prefix}
                    onChange={(e) => handleChange('breeder_prefix', e.target.value)}
                    maxLength="5"
                    placeholder="TP"
                  />
                  <p className="description">Prefix used for litter and registration numbers</p>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>Pregnancy Duration - Dogs (Days) *</label>
                    <input
                      type="number"
                      value={settings.pregnancy_days_dog}
                      onChange={(e) => handleChange('pregnancy_days_dog', parseInt(e.target.value))}
                      min="50"
                      max="80"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>Pregnancy Duration - Cats (Days) *</label>
                    <input
                      type="number"
                      value={settings.pregnancy_days_cat}
                      onChange={(e) => handleChange('pregnancy_days_cat', parseInt(e.target.value))}
                      min="50"
                      max="80"
                    />
                  </div>
                </div>

                <div className="truepaws-form-group">
                  <label>Feeding Instructions</label>
                  <textarea
                    value={settings.feeding_instructions}
                    onChange={(e) => handleChange('feeding_instructions', e.target.value)}
                    rows="5"
                    placeholder="Default feeding instructions for handover packets..."
                  />
                  <p className="description">Default feeding instructions included in handover packets</p>
                </div>

                <div className="truepaws-form-group">
                  <label>Gemini API Key</label>
                  <input
                    type="password"
                    value={settings.gemini_api_key}
                    onChange={(e) => handleChange('gemini_api_key', e.target.value)}
                    placeholder={settings.gemini_api_key === '********' ? 'Leave blank to keep current key' : 'Enter your API key'}
                    autoComplete="new-password"
                  />
                  <p className="description">For AI care advice on animal profiles. Get one at <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer">Google AI Studio</a></p>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'breeder' && (
            <div className="settings-tab-content">
              <h3>Breeder Information</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>Breeder Name</label>
                  <input
                    type="text"
                    value={settings.breeder_name}
                    onChange={(e) => handleChange('breeder_name', e.target.value)}
                    placeholder="Your full name"
                  />
                </div>

                <div className="truepaws-form-group">
                  <label>Business Name</label>
                  <input
                    type="text"
                    value={settings.business_name}
                    onChange={(e) => handleChange('business_name', e.target.value)}
                    placeholder="Your kennel/business name"
                  />
                </div>

                <div className="truepaws-form-group">
                  <label>License Number</label>
                  <input
                    type="text"
                    value={settings.license_number}
                    onChange={(e) => handleChange('license_number', e.target.value)}
                    placeholder="Breeder license number"
                  />
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>Phone</label>
                    <input
                      type="tel"
                      value={settings.breeder_phone}
                      onChange={(e) => handleChange('breeder_phone', e.target.value)}
                      placeholder="Phone number"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>Email</label>
                    <input
                      type="email"
                      value={settings.breeder_email}
                      onChange={(e) => handleChange('breeder_email', e.target.value)}
                      placeholder="email@example.com"
                    />
                  </div>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'breeds' && (
            <div className="settings-tab-content">
              <h3>Breeds Management</h3>
              <p className="description">Manage the breeds you work with. These will be available when creating animals.</p>
              <BreedsManager 
                breeds={settings.breeds} 
                onBreedsChange={handleBreedsChange}
              />
            </div>
          )}

          {activeTab === 'sales' && (
            <SalesReportsTab />
          )}

          {activeTab === 'communication' && (
            <div className="settings-tab-content">
              <h3>Communication & Address</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>Street Address</label>
                  <input
                    type="text"
                    value={settings.address_street}
                    onChange={(e) => handleChange('address_street', e.target.value)}
                    placeholder="Street address"
                  />
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>City</label>
                    <input
                      type="text"
                      value={settings.address_city}
                      onChange={(e) => handleChange('address_city', e.target.value)}
                      placeholder="City"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>State/Province</label>
                    <input
                      type="text"
                      value={settings.address_state}
                      onChange={(e) => handleChange('address_state', e.target.value)}
                      placeholder="State or Province"
                    />
                  </div>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>ZIP/Postal Code</label>
                    <input
                      type="text"
                      value={settings.address_zip}
                      onChange={(e) => handleChange('address_zip', e.target.value)}
                      placeholder="ZIP or Postal Code"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>Country</label>
                    <input
                      type="text"
                      value={settings.address_country}
                      onChange={(e) => handleChange('address_country', e.target.value)}
                      placeholder="Country"
                    />
                  </div>
                </div>

                <div className="truepaws-form-group">
                  <label>Contact / Inquiry URL</label>
                  <input
                    type="text"
                    value={settings.contact_url}
                    onChange={(e) => handleChange('contact_url', e.target.value)}
                    placeholder="/contact or #contact"
                  />
                  <p className="description">Default link for "Inquire / Contact" button on animal shortcode pages</p>
                </div>
              </div>
            </div>
          )}

          <div className="settings-actions">
            <button
              type="button"
              className="truepaws-button"
              onClick={handleSave}
              disabled={saving}
            >
              {saving ? 'Saving...' : 'Save Settings'}
            </button>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default SettingsPage;
