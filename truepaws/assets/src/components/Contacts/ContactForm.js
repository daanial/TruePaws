import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
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

    // #region agent log
    fetch('http://127.0.0.1:7244/ingest/631041ab-453e-49a6-a050-6d6b6bc20d6e',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ContactForm.js:handleSubmit:entry',message:'Contact form submission started',data:{formData:formData},timestamp:Date.now(),sessionId:'debug-session',hypothesisId:'F,G'})}).catch(()=>{});
    // #endregion

    try {
      const response = await contactsAPI.create(formData);
      
      // #region agent log
      fetch('http://127.0.0.1:7244/ingest/631041ab-453e-49a6-a050-6d6b6bc20d6e',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ContactForm.js:handleSubmit:success',message:'Contact created',data:{response:response.data},timestamp:Date.now(),sessionId:'debug-session',hypothesisId:'G,H'})}).catch(()=>{});
      // #endregion
      
      navigate('/contacts');
    } catch (error) {
      // #region agent log
      fetch('http://127.0.0.1:7244/ingest/631041ab-453e-49a6-a050-6d6b6bc20d6e',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ContactForm.js:handleSubmit:error',message:'Contact creation failed',data:{error:error.message,response:error.response?.data},timestamp:Date.now(),sessionId:'debug-session',hypothesisId:'H'})}).catch(()=>{});
      // #endregion
      
      console.error('Error creating contact:', error);
      alert('Error creating contact: ' + (error.response?.data?.message || error.message));
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
    <Layout title="Add New Contact">
      <form onSubmit={handleSubmit} className="truepaws-form">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>First Name *</label>
            <input
              type="text"
              name="first_name"
              value={formData.first_name}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>Last Name</label>
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
            <label>Email *</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>Phone</label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>Address</label>
          <textarea
            name="address"
            value={formData.address}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Status</label>
            <select name="status" value={formData.status} onChange={handleChange}>
              <option value="waitlist">Waitlist</option>
              <option value="reserved">Reserved</option>
              <option value="buyer">Buyer</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>Notes</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-actions">
          <button type="submit" className="truepaws-button" disabled={loading}>
            {loading ? 'Creating...' : 'Create Contact'}
          </button>
          <button type="button" className="truepaws-button secondary" onClick={() => navigate('/contacts')}>
            Cancel
          </button>
        </div>
      </form>
    </Layout>
  );
}

export default ContactForm;