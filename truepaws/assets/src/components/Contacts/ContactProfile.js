import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { __, _n, sprintf } from '@wordpress/i18n';
import { contactsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';

function ContactProfile() {
  const { id } = useParams();
  const [contact, setContact] = useState(null);
  const [soldAnimals, setSoldAnimals] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadContactData();
  }, [id]);

  const loadContactData = async () => {
    try {
      const [contactResponse, purchasesResponse] = await Promise.all([
        contactsAPI.getById(id),
        contactsAPI.getPurchases(id)
      ]);
      setContact(contactResponse.data);
      setSoldAnimals(purchasesResponse.data?.purchases || []);
    } catch (error) {
      console.error('Error loading contact:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingSpinner message={__('Loading contact...', 'truepaws')} />;
  }

  if (!contact) {
    return <div>{__('Contact not found', 'truepaws')}</div>;
  }

  return (
    <Layout
      title={`${contact.first_name} ${contact.last_name}`}
      actions={
        <Link to={`/contacts/${id}/edit`} className="truepaws-button secondary">
          {__('Edit Contact', 'truepaws')}
        </Link>
      }
    >
      <div className="contact-profile">
        {/* Contact Information Card */}
        <div className="contact-info-card">
          <h3 className="section-title">{__('Contact Information', 'truepaws')}</h3>
          <div className="contact-info-grid">
            <div className="info-item">
              <span className="info-label">{__('Name', 'truepaws')}</span>
              <span className="info-value">{contact.first_name} {contact.last_name}</span>
            </div>
            <div className="info-item">
              <span className="info-label">{__('Email', 'truepaws')}</span>
              <a href={`mailto:${contact.email}`} className="info-value info-link">{contact.email}</a>
            </div>
            {contact.phone && (
              <div className="info-item">
                <span className="info-label">{__('Phone', 'truepaws')}</span>
                <a href={`tel:${contact.phone}`} className="info-value info-link">{contact.phone}</a>
              </div>
            )}
            <div className="info-item">
              <span className="info-label">{__('Status', 'truepaws')}</span>
              <span className={`contact-status status-${contact.status}`}>
                {contact.status === 'waitlist' ? __('Waitlist', 'truepaws') : contact.status === 'reserved' ? __('Reserved', 'truepaws') : contact.status === 'buyer' ? __('Buyer', 'truepaws') : contact.status === 'inactive' ? __('Inactive', 'truepaws') : contact.status.charAt(0).toUpperCase() + contact.status.slice(1)}
              </span>
            </div>
            <div className="info-item">
              <span className="info-label">{__('Added', 'truepaws')}</span>
              <span className="info-value">{new Date(contact.created_at).toLocaleDateString()}</span>
            </div>
          </div>
        </div>

        {/* Address Card */}
        {contact.address && (
          <div className="contact-detail-card">
            <h3 className="section-title">{__('Address', 'truepaws')}</h3>
            <p className="card-content" style={{ whiteSpace: 'pre-line' }}>{contact.address}</p>
          </div>
        )}

        {/* Notes Card */}
        {contact.notes && (
          <div className="contact-detail-card">
            <h3 className="section-title">{__('Notes', 'truepaws')}</h3>
            <p className="card-content" style={{ whiteSpace: 'pre-wrap' }}>{contact.notes}</p>
          </div>
        )}

        {/* Inquired About Section - Animals from inquiry form shortcode */}
        {(contact.inquiry_animals || []).length > 0 && (
          <div className="contact-detail-card inquiry-animals-card">
            <h3 className="section-title">{__('Inquired About', 'truepaws')}</h3>
            <p className="card-description">{__('This contact submitted an inquiry about the following animal(s) via the website form.', 'truepaws')}</p>
            <div className="inquiry-animals-grid">
              {(contact.inquiry_animals || []).map((animal) => (
                <Link key={animal.id} to={`/animals/${animal.id}`} className="inquiry-animal-card">
                  <span className="inquiry-animal-name">{animal.name}</span>
                  {animal.breed && <span className="inquiry-animal-breed">{animal.breed}</span>}
                  <span className="inquiry-animal-id">{__('ID:', 'truepaws')} {animal.id}</span>
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Purchased Animals Section - Distinct Styling */}
        <div className="purchased-animals-section">
          <div className="section-header">
            <h3 className="section-title">{__('Purchased Animals', 'truepaws')}</h3>
            <span className="purchase-count">{sprintf(_n('%s Animal', '%s Animals', soldAnimals.length, 'truepaws'), soldAnimals.length)}</span>
          </div>
          {soldAnimals.length > 0 ? (
            <div className="purchased-animals-grid">
              {soldAnimals.map(animal => (
                <div key={`${animal.id}-${animal.sale_date}`} className="purchased-animal-card">
                  <Link to={`/animals/${animal.id}`} className="animal-card-link">
                    <div className="animal-card-header">
                      <h4 className="animal-card-name">{animal.name}</h4>
                      <span className="animal-card-breed">{animal.breed || __('Mixed Breed', 'truepaws')}</span>
                    </div>
                    <div className="animal-card-footer">
                      {animal.sale_date && (
                        <span className="animal-sale-date">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                          </svg>
                          {new Date(animal.sale_date).toLocaleDateString()}
                        </span>
                      )}
                      {animal.sale_price && (
                        <span className="animal-sale-price">${Number(animal.sale_price).toLocaleString()}</span>
                      )}
                    </div>
                  </Link>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p>{__('No animals purchased yet.', 'truepaws')}</p>
            </div>
          )}
        </div>

        {/* Quick Actions Card */}
        <div className="contact-actions-card">
          <h3 className="section-title">{__('Quick Actions', 'truepaws')}</h3>
          <div className="action-buttons-horizontal">
            <a href={`mailto:${contact.email}`} className="truepaws-button action-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              {__('Send Email', 'truepaws')}
            </a>
            {contact.phone && (
              <a href={`tel:${contact.phone}`} className="truepaws-button secondary action-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                </svg>
                {__('Call', 'truepaws')}
              </a>
            )}
            <Link to="/animals" className="truepaws-button secondary action-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2a7 7 0 100 14 7 7 0 000-14z"></path>
                <path d="M12 9.5a2.5 2.5 0 110 5 2.5 2.5 0 010-5z"></path>
              </svg>
              {__('View Available Animals', 'truepaws')}
            </Link>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default ContactProfile;