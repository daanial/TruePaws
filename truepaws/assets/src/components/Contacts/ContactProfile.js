import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
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
    return <LoadingSpinner message="Loading contact..." />;
  }

  if (!contact) {
    return <div>Contact not found</div>;
  }

  return (
    <Layout
      title={`${contact.first_name} ${contact.last_name}`}
      actions={
        <Link to={`/contacts/${id}/edit`} className="truepaws-button secondary">
          Edit Contact
        </Link>
      }
    >
      <div className="contact-profile">
        {/* Contact Information Card */}
        <div className="contact-info-card">
          <h3 className="section-title">Contact Information</h3>
          <div className="contact-info-grid">
            <div className="info-item">
              <span className="info-label">Name</span>
              <span className="info-value">{contact.first_name} {contact.last_name}</span>
            </div>
            <div className="info-item">
              <span className="info-label">Email</span>
              <a href={`mailto:${contact.email}`} className="info-value info-link">{contact.email}</a>
            </div>
            {contact.phone && (
              <div className="info-item">
                <span className="info-label">Phone</span>
                <a href={`tel:${contact.phone}`} className="info-value info-link">{contact.phone}</a>
              </div>
            )}
            <div className="info-item">
              <span className="info-label">Status</span>
              <span className={`contact-status status-${contact.status}`}>
                {contact.status.charAt(0).toUpperCase() + contact.status.slice(1)}
              </span>
            </div>
            <div className="info-item">
              <span className="info-label">Added</span>
              <span className="info-value">{new Date(contact.created_at).toLocaleDateString()}</span>
            </div>
          </div>
        </div>

        {/* Address Card */}
        {contact.address && (
          <div className="contact-detail-card">
            <h3 className="section-title">Address</h3>
            <p className="card-content" style={{ whiteSpace: 'pre-line' }}>{contact.address}</p>
          </div>
        )}

        {/* Notes Card */}
        {contact.notes && (
          <div className="contact-detail-card">
            <h3 className="section-title">Notes</h3>
            <p className="card-content">{contact.notes}</p>
          </div>
        )}

        {/* Purchased Animals Section - Distinct Styling */}
        <div className="purchased-animals-section">
          <div className="section-header">
            <h3 className="section-title">Purchased Animals</h3>
            <span className="purchase-count">{soldAnimals.length} {soldAnimals.length === 1 ? 'Animal' : 'Animals'}</span>
          </div>
          {soldAnimals.length > 0 ? (
            <div className="purchased-animals-grid">
              {soldAnimals.map(animal => (
                <div key={`${animal.id}-${animal.sale_date}`} className="purchased-animal-card">
                  <Link to={`/animals/${animal.id}`} className="animal-card-link">
                    <div className="animal-card-header">
                      <h4 className="animal-card-name">{animal.name}</h4>
                      <span className="animal-card-breed">{animal.breed || 'Mixed Breed'}</span>
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
              <p>No animals purchased yet.</p>
            </div>
          )}
        </div>

        {/* Quick Actions Card */}
        <div className="contact-actions-card">
          <h3 className="section-title">Quick Actions</h3>
          <div className="action-buttons-horizontal">
            <a href={`mailto:${contact.email}`} className="truepaws-button action-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              Send Email
            </a>
            {contact.phone && (
              <a href={`tel:${contact.phone}`} className="truepaws-button secondary action-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                </svg>
                Call
              </a>
            )}
            <Link to="/animals" className="truepaws-button secondary action-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2a7 7 0 100 14 7 7 0 000-14z"></path>
                <path d="M12 9.5a2.5 2.5 0 110 5 2.5 2.5 0 010-5z"></path>
              </svg>
              View Available Animals
            </Link>
          </div>
        </div>
      </div>
    </Layout>
  );
}

export default ContactProfile;