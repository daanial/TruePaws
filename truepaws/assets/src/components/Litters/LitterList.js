import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { littersAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';
import PregnancyTracker from '../shared/PregnancyTracker';

function LitterList() {
  const [litters, setLitters] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadLitters();
  }, []);

  const loadLitters = async () => {
    try {
      const response = await littersAPI.getAll();
      setLitters(response.data.litters || []);
    } catch (error) {
      console.error('Error loading litters:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingSpinner message={__('Loading litters...', 'truepaws')} />;
  }

  const pendingLitters = litters.filter(l => !l.actual_whelping_date && l.expected_whelping_date);

  return (
    <Layout
      title={__('Litters', 'truepaws')}
      actions={
        <Link to="/litters/new" className="truepaws-button">
          {__('Log New Mating', 'truepaws')}
        </Link>
      }
    >
      {pendingLitters.length > 0 && (
        <div className="pregnancy-trackers-section">
          {pendingLitters.map(litter => (
            <PregnancyTracker key={litter.id} litter={litter} />
          ))}
        </div>
      )}

      <div className="truepaws-table-container">
        <table className="truepaws-table">
          <thead>
            <tr>
              <th>{__('Litter Name', 'truepaws')}</th>
              <th>{__('Parents', 'truepaws')}</th>
              <th>{__('Mating Date', 'truepaws')}</th>
              <th>{__('Expected Whelping', 'truepaws')}</th>
              <th>{__('Status', 'truepaws')}</th>
              <th>{__('Actions', 'truepaws')}</th>
            </tr>
          </thead>
          <tbody>
            {litters.map((litter) => (
              <tr key={litter.id}>
                <td>{litter.litter_name}</td>
                <td>{litter.sire_name} × {litter.dam_name}</td>
                <td>{litter.mating_date}</td>
                <td>{litter.expected_whelping_date}</td>
                <td>
                  {litter.actual_whelping_date ? __('Whelped', 'truepaws') : __('Pending', 'truepaws')}
                </td>
                <td>
                  <Link to={`/litters/${litter.id}/whelp`} className="truepaws-button">
                    {litter.actual_whelping_date ? __('View Details', 'truepaws') : __('Whelp Litter', 'truepaws')}
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {litters.length === 0 && (
          <div className="truepaws-empty-state">
            <p>{__('No litters found.', 'truepaws')} <Link to="/litters/new">{__('Log your first mating', 'truepaws')}</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default LitterList;