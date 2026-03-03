import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { __, _n, sprintf } from '@wordpress/i18n';
import { contactsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';

function escapeCsv(value) {
  if (value == null) return '';
  const str = String(value);
  if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
    return '"' + str.replace(/"/g, '""') + '"';
  }
  return str;
}

function ContactList() {
  const [contacts, setContacts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    loadContacts();
  }, [statusFilter]);

  const loadContacts = async () => {
    try {
      const response = await contactsAPI.getAll({
        status: statusFilter || undefined
      });
      setContacts(response.data.contacts || []);
    } catch (error) {
      console.error('Error loading contacts:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleExport = () => {
    const headers = [__('First Name', 'truepaws'), __('Last Name', 'truepaws'), __('Email', 'truepaws'), __('Phone', 'truepaws'), __('Status', 'truepaws'), __('Inquired About', 'truepaws'), __('Notes', 'truepaws')];
    const rows = contacts.map((c) => [
      c.first_name || '',
      c.last_name || '',
      c.email || '',
      c.phone || '',
      c.status || '',
      (c.inquiry_animals || []).map((a) => a.name).join('; ') || '',
      c.notes || ''
    ]);
    const csvContent = [
      headers.map(escapeCsv).join(','),
      ...rows.map((row) => row.map(escapeCsv).join(','))
    ].join('\r\n');
    const bom = '\uFEFF';
    const blob = new Blob([bom + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `truepaws-contacts-${statusFilter || 'all'}-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  };

  if (loading) {
    return <LoadingSpinner message={__('Loading contacts...', 'truepaws')} />;
  }

  return (
    <Layout
      title={__('Contacts', 'truepaws')}
      actions={
        <>
          <Link to="/contacts/new" className="truepaws-button">
            {__('Add New Contact', 'truepaws')}
          </Link>
          <button
            type="button"
            className="truepaws-button secondary"
            onClick={handleExport}
            disabled={contacts.length === 0}
            title={__('Export contacts for Mailchimp, Constant Contact, etc.', 'truepaws')}
          >
            {__('Export CSV', 'truepaws')}
          </button>
        </>
      }
    >
      <div className="truepaws-filters">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label htmlFor="contact-status-filter">{__('Filter by status', 'truepaws')}</label>
            <select
              id="contact-status-filter"
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">{__('All Contacts', 'truepaws')}</option>
              <option value="waitlist">{__('Waitlist', 'truepaws')}</option>
              <option value="reserved">{__('Reserved', 'truepaws')}</option>
              <option value="buyer">{__('Buyer', 'truepaws')}</option>
              <option value="inactive">{__('Inactive', 'truepaws')}</option>
            </select>
          </div>
          <div className="truepaws-form-group">
            <span className="export-hint">
              {sprintf(_n('%s contact', '%s contacts', contacts.length, 'truepaws'), contacts.length)} — {__('Export for email marketing', 'truepaws')}
            </span>
          </div>
        </div>
      </div>

      <div className="truepaws-table-container">
        <table className="truepaws-table">
          <thead>
            <tr>
              <th>{__('Name', 'truepaws')}</th>
              <th>{__('Email', 'truepaws')}</th>
              <th>{__('Phone', 'truepaws')}</th>
              <th>{__('Status', 'truepaws')}</th>
              <th>{__('Inquired About', 'truepaws')}</th>
              <th>{__('Created', 'truepaws')}</th>
              <th>{__('Actions', 'truepaws')}</th>
            </tr>
          </thead>
          <tbody>
            {contacts.map((contact) => (
              <tr key={contact.id}>
                <td>
                  <div className="contact-name">
                    <strong>{contact.first_name} {contact.last_name}</strong>
                    {contact.notes && (
                      <div className="contact-notes-preview">
                        {contact.notes.length > 50 ? contact.notes.substring(0, 50) + '...' : contact.notes}
                      </div>
                    )}
                  </div>
                </td>
                <td>
                  {contact.email && (
                    <a href={`mailto:${contact.email}`} className="contact-link">
                      {contact.email}
                    </a>
                  )}
                </td>
                <td>
                  {contact.phone && (
                    <a href={`tel:${contact.phone}`} className="contact-link">
                      {contact.phone}
                    </a>
                  )}
                </td>
                <td>
                  <span className={`contact-status status-${contact.status}`}>
                    {contact.status === 'waitlist' ? __('Waitlist', 'truepaws') : contact.status === 'reserved' ? __('Reserved', 'truepaws') : contact.status === 'buyer' ? __('Buyer', 'truepaws') : contact.status === 'inactive' ? __('Inactive', 'truepaws') : contact.status.charAt(0).toUpperCase() + contact.status.slice(1)}
                  </span>
                </td>
                <td>
                  {(contact.inquiry_animals || []).length > 0 ? (
                    <div className="contact-inquiry-animals">
                      {(contact.inquiry_animals || []).map((a) => (
                        <Link key={a.id} to={`/animals/${a.id}`} className="inquiry-animal-link">
                          {a.name}
                          {a.breed ? ` (${a.breed})` : ''}
                        </Link>
                      ))}
                    </div>
                  ) : (
                    <span className="text-muted">—</span>
                  )}
                </td>
                <td>{new Date(contact.created_at).toLocaleDateString()}</td>
                <td>
                  <div className="contact-actions">
                    <Link to={`/contacts/${contact.id}`} className="truepaws-button secondary">
                      {__('View', 'truepaws')}
                    </Link>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {contacts.length === 0 && (
          <div className="truepaws-empty-state">
            <p>{__('No contacts found.', 'truepaws')} <Link to="/contacts/new">{__('Add your first contact', 'truepaws')}</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default ContactList;