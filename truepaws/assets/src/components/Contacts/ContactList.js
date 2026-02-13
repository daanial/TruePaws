import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
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
    const headers = ['First Name', 'Last Name', 'Email', 'Phone', 'Status', 'Notes'];
    const rows = contacts.map((c) => [
      c.first_name || '',
      c.last_name || '',
      c.email || '',
      c.phone || '',
      c.status || '',
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
    return <LoadingSpinner message="Loading contacts..." />;
  }

  return (
    <Layout
      title="Contacts"
      actions={
        <>
          <Link to="/contacts/new" className="truepaws-button">
            Add New Contact
          </Link>
          <button
            type="button"
            className="truepaws-button secondary"
            onClick={handleExport}
            disabled={contacts.length === 0}
            title="Export contacts for Mailchimp, Constant Contact, etc."
          >
            Export CSV
          </button>
        </>
      }
    >
      <div className="truepaws-filters">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label htmlFor="contact-status-filter">Filter by status</label>
            <select
              id="contact-status-filter"
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">All Contacts</option>
              <option value="waitlist">Waitlist</option>
              <option value="reserved">Reserved</option>
              <option value="buyer">Buyer</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div className="truepaws-form-group">
            <span className="export-hint">
              {contacts.length} contact{contacts.length !== 1 ? 's' : ''} — Export for email marketing
            </span>
          </div>
        </div>
      </div>

      <div className="truepaws-table-container">
        <table className="truepaws-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
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
                    {contact.status.charAt(0).toUpperCase() + contact.status.slice(1)}
                  </span>
                </td>
                <td>{new Date(contact.created_at).toLocaleDateString()}</td>
                <td>
                  <div className="contact-actions">
                    <Link to={`/contacts/${contact.id}`} className="truepaws-button secondary">
                      View
                    </Link>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {contacts.length === 0 && (
          <div className="truepaws-empty-state">
            <p>No contacts found. <Link to="/contacts/new">Add your first contact</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default ContactList;