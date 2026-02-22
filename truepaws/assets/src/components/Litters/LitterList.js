import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
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
    return <LoadingSpinner message="Loading litters..." />;
  }

  const pendingLitters = litters.filter(l => !l.actual_whelping_date && l.expected_whelping_date);

  return (
    <Layout
      title="Litters"
      actions={
        <Link to="/litters/new" className="truepaws-button">
          Log New Mating
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
              <th>Litter Name</th>
              <th>Parents</th>
              <th>Mating Date</th>
              <th>Expected Whelping</th>
              <th>Status</th>
              <th>Actions</th>
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
                  {litter.actual_whelping_date ? 'Whelped' : 'Pending'}
                </td>
                <td>
                  <Link to={`/litters/${litter.id}/whelp`} className="truepaws-button">
                    {litter.actual_whelping_date ? 'View Details' : 'Whelp Litter'}
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {litters.length === 0 && (
          <div className="truepaws-empty-state">
            <p>No litters found. <Link to="/litters/new">Log your first mating</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default LitterList;