import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { __, sprintf } from '@wordpress/i18n';
import { contactsAPI } from '../../api/client';
import Layout from '../shared/Layout';

function ContactForm() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
    status: 'waitlist'
  });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await contactsAPI.create(formData);
      navigate('/contacts');
    } catch (error) {
      console.error('Error creating contact:', error);
      alert(sprintf(__('Error creating contact: %s', 'truepaws'), error.response?.data?.message || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  return (
    <Layout title={__('Add New Contact', 'truepaws')}>
      <form onSubmit={handleSubmit} className="truepaws-form">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('First Name', 'truepaws')} *</label>
            <input
              type="text"
              name="first_name"
              value={formData.first_name}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>{__('Last Name', 'truepaws')}</label>
            <input
              type="text"
              name="last_name"
              value={formData.last_name}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('Email', 'truepaws')} *</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>{__('Phone', 'truepaws')}</label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>{__('Address', 'truepaws')}</label>
          <textarea
            name="address"
            value={formData.address}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('Status', 'truepaws')}</label>
            <select name="status" value={formData.status} onChange={handleChange}>
              <option value="waitlist">{__('Waitlist', 'truepaws')}</option>
              <option value="reserved">{__('Reserved', 'truepaws')}</option>
              <option value="buyer">{__('Buyer', 'truepaws')}</option>
              <option value="inactive">{__('Inactive', 'truepaws')}</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>{__('Notes', 'truepaws')}</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-actions">
          <button type="submit" className="truepaws-button" disabled={loading}>
            {loading ? __('Creating...', 'truepaws') : __('Create Contact', 'truepaws')}
          </button>
          <button type="button" className="truepaws-button secondary" onClick={() => navigate('/contacts')}>
            {__('Cancel', 'truepaws')}
          </button>
        </div>
      </form>
    </Layout>
  );
}

export default ContactForm;