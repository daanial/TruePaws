import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
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

  if (loading) return <LoadingSpinner message={__('Loading sales report...', 'truepaws')} />;
  if (!report) return <p>{__('Unable to load sales report.', 'truepaws')}</p>;

  return (
    <div className="settings-tab-content sales-reports">
      <h3>{__('Sales Report', 'truepaws')}</h3>
      <div className="sales-stats-grid">
        <div className="sales-stat-card">
          <div className="sales-stat-value">${report.totalRevenue.toLocaleString()}</div>
          <div className="sales-stat-label">{__('Total Revenue', 'truepaws')}</div>
        </div>
        <div className="sales-stat-card">
          <div className="sales-stat-value">{report.totalSales}</div>
          <div className="sales-stat-label">{__('Total Sales', 'truepaws')}</div>
        </div>
        <div className="sales-stat-card">
          <div className="sales-stat-value">${report.averagePrice.toLocaleString()}</div>
          <div className="sales-stat-label">{__('Average Price', 'truepaws')}</div>
        </div>
      </div>

      {report.salesByMonth?.length > 0 && (
        <div className="sales-section">
          <h4>{__('Sales by Month', 'truepaws')}</h4>
          <table className="truepaws-table sales-table">
            <thead>
              <tr>
                <th>{__('Month', 'truepaws')}</th>
                <th>{__('Sales', 'truepaws')}</th>
                <th>{__('Revenue', 'truepaws')}</th>
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
          <h4>{__('Top Buyers', 'truepaws')}</h4>
          <table className="truepaws-table sales-table">
            <thead>
              <tr>
                <th>{__('Contact', 'truepaws')}</th>
                <th>{__('Purchases', 'truepaws')}</th>
                <th>{__('Total Revenue', 'truepaws')}</th>
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
        <p className="sales-empty">{__('No sales recorded yet. Mark animals as sold from their profiles to track sales.', 'truepaws')}</p>
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
    webhook_url: '',
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
      setError(__('Failed to load settings', 'truepaws'));
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
        setSuccess(__('Settings saved successfully!', 'truepaws'));
        setTimeout(() => setSuccess(''), 3000);
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      setError(error.response?.data?.message || __('Failed to save settings', 'truepaws'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <LoadingSpinner message={__('Loading settings...', 'truepaws')} />;
  }

  const tabs = [
    { id: 'general', label: __('General', 'truepaws') },
    { id: 'breeder', label: __('Breeder Info', 'truepaws') },
    { id: 'breeds', label: __('Breeds', 'truepaws') },
    { id: 'communication', label: __('Communication', 'truepaws') },
    { id: 'sales', label: __('Sales Reports', 'truepaws') },
  ];

  return (
    <Layout title={__('Settings', 'truepaws')}>
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
              <h3>{__('General Settings', 'truepaws')}</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>{__('Default Species', 'truepaws')} *</label>
                  <select
                    value={settings.default_species}
                    onChange={(e) => handleChange('default_species', e.target.value)}
                  >
                    <option value="dog">{__('Dog', 'truepaws')}</option>
                    <option value="cat">{__('Cat', 'truepaws')}</option>
                    <option value="horse">{__('Horse', 'truepaws')}</option>
                    <option value="rabbit">{__('Rabbit', 'truepaws')}</option>
                    <option value="guinea_pig">{__('Guinea Pig', 'truepaws')}</option>
                    <option value="ferret">{__('Ferret', 'truepaws')}</option>
                    <option value="bird">{__('Bird', 'truepaws')}</option>
                  </select>
                  <p className="description">{__('The main type of animal you are breeding. This applies to all animals, litters, and AI care advice.', 'truepaws')}</p>
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Breeder Prefix', 'truepaws')} *</label>
                  <input
                    type="text"
                    value={settings.breeder_prefix}
                    onChange={(e) => handleChange('breeder_prefix', e.target.value)}
                    maxLength="5"
                    placeholder="TP"
                  />
                  <p className="description">{__('Prefix used for litter and registration numbers', 'truepaws')}</p>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Pregnancy Duration - Dogs (Days)', 'truepaws')} *</label>
                    <input
                      type="number"
                      value={settings.pregnancy_days_dog}
                      onChange={(e) => handleChange('pregnancy_days_dog', parseInt(e.target.value))}
                      min="50"
                      max="80"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Pregnancy Duration - Cats (Days)', 'truepaws')} *</label>
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
                  <label>{__('Feeding Instructions', 'truepaws')}</label>
                  <textarea
                    value={settings.feeding_instructions}
                    onChange={(e) => handleChange('feeding_instructions', e.target.value)}
                    rows="5"
                    placeholder={__('Default feeding instructions for handover packets...', 'truepaws')}
                  />
                  <p className="description">{__('Default feeding instructions included in handover packets', 'truepaws')}</p>
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Gemini API Key', 'truepaws')}</label>
                  <input
                    type="password"
                    value={settings.gemini_api_key}
                    onChange={(e) => handleChange('gemini_api_key', e.target.value)}
                    placeholder={settings.gemini_api_key === '********' ? __('Leave blank to keep current key', 'truepaws') : __('Enter your API key', 'truepaws')}
                    autoComplete="new-password"
                  />
                  <p className="description">{__('For AI care advice on animal profiles. Get one at', 'truepaws')} <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer">Google AI Studio</a></p>
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Webhook URL (Zapier / Make)', 'truepaws')}</label>
                  <input
                    type="url"
                    value={settings.webhook_url || ''}
                    onChange={(e) => handleChange('webhook_url', e.target.value)}
                    placeholder="https://hooks.zapier.com/..."
                  />
                  <p className="description">{__('Optional. Receive HTTP POST when events occur: new inquiry, sale recorded, or litter whelped. Use with Zapier, Make, or any webhook endpoint.', 'truepaws')}</p>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'breeder' && (
            <div className="settings-tab-content">
              <h3>{__('Breeder Information', 'truepaws')}</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>{__('Breeder Name', 'truepaws')}</label>
                  <input
                    type="text"
                    value={settings.breeder_name}
                    onChange={(e) => handleChange('breeder_name', e.target.value)}
                    placeholder={__('Your full name', 'truepaws')}
                  />
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Business Name', 'truepaws')}</label>
                  <input
                    type="text"
                    value={settings.business_name}
                    onChange={(e) => handleChange('business_name', e.target.value)}
                    placeholder={__('Your kennel/business name', 'truepaws')}
                  />
                </div>

                <div className="truepaws-form-group">
                  <label>{__('License Number', 'truepaws')}</label>
                  <input
                    type="text"
                    value={settings.license_number}
                    onChange={(e) => handleChange('license_number', e.target.value)}
                    placeholder={__('Breeder license number', 'truepaws')}
                  />
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Phone', 'truepaws')}</label>
                    <input
                      type="tel"
                      value={settings.breeder_phone}
                      onChange={(e) => handleChange('breeder_phone', e.target.value)}
                      placeholder={__('Phone number', 'truepaws')}
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Email', 'truepaws')}</label>
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
              <h3>{__('Breeds Management', 'truepaws')}</h3>
              <p className="description">{__('Manage the breeds you work with. These will be available when creating animals.', 'truepaws')}</p>
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
              <h3>{__('Communication & Address', 'truepaws')}</h3>
              <div className="settings-form">
                <div className="truepaws-form-group">
                  <label>{__('Street Address', 'truepaws')}</label>
                  <input
                    type="text"
                    value={settings.address_street}
                    onChange={(e) => handleChange('address_street', e.target.value)}
                    placeholder={__('Street address', 'truepaws')}
                  />
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('City', 'truepaws')}</label>
                    <input
                      type="text"
                      value={settings.address_city}
                      onChange={(e) => handleChange('address_city', e.target.value)}
                      placeholder={__('City', 'truepaws')}
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('State/Province', 'truepaws')}</label>
                    <input
                      type="text"
                      value={settings.address_state}
                      onChange={(e) => handleChange('address_state', e.target.value)}
                      placeholder={__('State or Province', 'truepaws')}
                    />
                  </div>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('ZIP/Postal Code', 'truepaws')}</label>
                    <input
                      type="text"
                      value={settings.address_zip}
                      onChange={(e) => handleChange('address_zip', e.target.value)}
                      placeholder={__('ZIP or Postal Code', 'truepaws')}
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Country', 'truepaws')}</label>
                    <input
                      type="text"
                      value={settings.address_country}
                      onChange={(e) => handleChange('address_country', e.target.value)}
                      placeholder={__('Country', 'truepaws')}
                    />
                  </div>
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Contact / Inquiry URL', 'truepaws')}</label>
                  <input
                    type="text"
                    value={settings.contact_url}
                    onChange={(e) => handleChange('contact_url', e.target.value)}
                    placeholder="/contact or #contact"
                  />
                  <p className="description">{__('Default link for "Inquire / Contact" button on animal shortcode pages', 'truepaws')}</p>
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
              {saving ? __('Saving...', 'truepaws') : __('Save Settings', 'truepaws')}
            </button>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default SettingsPage;
