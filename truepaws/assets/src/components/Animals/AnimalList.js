import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { animalsAPI, settingsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';
import AnimalImagePlaceholder from '../shared/AnimalImagePlaceholder';

function AnimalThumb({ animal }) {
  const [imgError, setImgError] = useState(false);
  const showPlaceholder = !animal.featured_image_url || imgError;

  return (
    <Link to={`/animals/${animal.id}`} className="animal-list-thumb-link">
      {showPlaceholder ? (
        <AnimalImagePlaceholder className="animal-list-thumb" alt={animal.name} />
      ) : (
        <img
          src={animal.featured_image_url}
          alt={animal.name}
          className="animal-list-thumb"
          onError={() => setImgError(true)}
        />
      )}
    </Link>
  );
}

function AnimalList() {
  const [animals, setAnimals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [breeds, setBreeds] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [microchipSearch, setMicrochipSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [breedFilter, setBreedFilter] = useState('');

  useEffect(() => {
    loadBreeds();
  }, []);

  useEffect(() => {
    loadAnimals();
  }, []);

  const loadBreeds = async () => {
    try {
      const response = await settingsAPI.getBreeds();
      if (response.data?.breeds) {
        setBreeds(response.data.breeds);
      }
    } catch (error) {
      console.error('Error loading breeds:', error);
    }
  };

  const loadAnimals = async () => {
    try {
      // Microchip lookup takes precedence for quick identification
      const searchParam = microchipSearch.trim()
        ? microchipSearch.trim()
        : searchTerm;
      const response = await animalsAPI.getAll({
        search: searchParam,
        status: statusFilter,
        breed: breedFilter || undefined
      });
      setAnimals(response.data.animals || []);
    } catch (error) {
      console.error('Error loading animals:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    loadAnimals();
  };

  const getStatusBadge = (status) => {
    const statusClasses = {
      active: 'truepaws-status active',
      retired: 'truepaws-status retired',
      sold: 'truepaws-status sold',
      deceased: 'truepaws-status deceased',
      'co-owned': 'truepaws-status'
    };
    const statusLabels = {
      active: __('Active', 'truepaws'),
      retired: __('Retired', 'truepaws'),
      sold: __('Sold', 'truepaws'),
      deceased: __('Deceased', 'truepaws'),
      'co-owned': __('Co-owned', 'truepaws'),
    };

    return (
      <span className={statusClasses[status] || 'truepaws-status'}>
        {statusLabels[status] || status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (loading) {
    return <LoadingSpinner message={__('Loading animals...', 'truepaws')} />;
  }

  return (
    <Layout
      title={__('Animals', 'truepaws')}
      actions={
        <Link to="/animals/new" className="truepaws-button">
          {__('Add New Animal', 'truepaws')}
        </Link>
      }
    >
      <div className="truepaws-filters">
        <form onSubmit={handleSearch} className="truepaws-form-row">
          <div className="truepaws-form-group">
            <input
              type="text"
              placeholder={__('Search name, registration...', 'truepaws')}
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="truepaws-form-control"
            />
          </div>
          <div className="truepaws-form-group">
            <input
              type="text"
              placeholder={__('Microchip #', 'truepaws')}
              value={microchipSearch}
              onChange={(e) => setMicrochipSearch(e.target.value)}
              className="truepaws-form-control"
              title={__('Quick lookup by microchip number', 'truepaws')}
            />
          </div>
          <div className="truepaws-form-group">
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">{__('All Statuses', 'truepaws')}</option>
              <option value="active">{__('Active', 'truepaws')}</option>
              <option value="retired">{__('Retired', 'truepaws')}</option>
              <option value="sold">{__('Sold', 'truepaws')}</option>
              <option value="deceased">{__('Deceased', 'truepaws')}</option>
              <option value="co-owned">{__('Co-owned', 'truepaws')}</option>
            </select>
          </div>
          <div className="truepaws-form-group">
            <select
              value={breedFilter}
              onChange={(e) => setBreedFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">{__('All Breeds', 'truepaws')}</option>
              {breeds.map((b) => (
                <option key={b.id} value={b.name}>{b.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <button type="submit" className="truepaws-button">{__('Search', 'truepaws')}</button>
          </div>
        </form>
      </div>

      <div className="truepaws-table-container">
        <table className="truepaws-table">
          <thead>
            <tr>
              <th className="truepaws-table-thumb">{__('Photo', 'truepaws')}</th>
              <th>{__('Name', 'truepaws')}</th>
              <th>{__('Registration', 'truepaws')}</th>
              <th>{__('Microchip', 'truepaws')}</th>
              <th>{__('Breed', 'truepaws')}</th>
              <th>{__('Sex', 'truepaws')}</th>
              <th>{__('Status', 'truepaws')}</th>
              <th>{__('Actions', 'truepaws')}</th>
            </tr>
          </thead>
          <tbody>
            {animals.map((animal) => (
              <tr key={animal.id}>
                <td className="truepaws-table-thumb">
                  <AnimalThumb animal={animal} />
                </td>
                <td>
                  <Link to={`/animals/${animal.id}`} className="truepaws-link">
                    {animal.name}
                  </Link>
                  {animal.call_name && (
                    <div className="animal-list-call-name">
                      "{animal.call_name}"
                    </div>
                  )}
                </td>
                <td>{animal.registration_number}</td>
                <td>{animal.microchip_id || '—'}</td>
                <td>{animal.breed}</td>
                <td>{animal.sex === 'M' ? __('Male', 'truepaws') : __('Female', 'truepaws')}</td>
                <td>{getStatusBadge(animal.status)}</td>
                <td>
                  <Link to={`/animals/${animal.id}`} className="truepaws-button secondary">
                    {__('View', 'truepaws')}
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {animals.length === 0 && (
          <div className="truepaws-empty-state">
            <p>{__('No animals found.', 'truepaws')} <Link to="/animals/new">{__('Add your first animal', 'truepaws')}</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default AnimalList;