import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
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

    return (
      <span className={statusClasses[status] || 'truepaws-status'}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (loading) {
    return <LoadingSpinner message="Loading animals..." />;
  }

  return (
    <Layout
      title="Animals"
      actions={
        <Link to="/animals/new" className="truepaws-button">
          Add New Animal
        </Link>
      }
    >
      <div className="truepaws-filters">
        <form onSubmit={handleSearch} className="truepaws-form-row">
          <div className="truepaws-form-group">
            <input
              type="text"
              placeholder="Search name, registration..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="truepaws-form-control"
            />
          </div>
          <div className="truepaws-form-group">
            <input
              type="text"
              placeholder="Microchip #"
              value={microchipSearch}
              onChange={(e) => setMicrochipSearch(e.target.value)}
              className="truepaws-form-control"
              title="Quick lookup by microchip number"
            />
          </div>
          <div className="truepaws-form-group">
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="retired">Retired</option>
              <option value="sold">Sold</option>
              <option value="deceased">Deceased</option>
              <option value="co-owned">Co-owned</option>
            </select>
          </div>
          <div className="truepaws-form-group">
            <select
              value={breedFilter}
              onChange={(e) => setBreedFilter(e.target.value)}
              className="truepaws-form-control"
            >
              <option value="">All Breeds</option>
              {breeds.map((b) => (
                <option key={b.id} value={b.name}>{b.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <button type="submit" className="truepaws-button">Search</button>
          </div>
        </form>
      </div>

      <div className="truepaws-table-container">
        <table className="truepaws-table">
          <thead>
            <tr>
              <th className="truepaws-table-thumb">Photo</th>
              <th>Name</th>
              <th>Registration</th>
              <th>Microchip</th>
              <th>Breed</th>
              <th>Sex</th>
              <th>Status</th>
              <th>Actions</th>
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
                <td>{animal.sex === 'M' ? 'Male' : 'Female'}</td>
                <td>{getStatusBadge(animal.status)}</td>
                <td>
                  <Link to={`/animals/${animal.id}`} className="truepaws-button secondary">
                    View
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {animals.length === 0 && (
          <div className="truepaws-empty-state">
            <p>No animals found. <Link to="/animals/new">Add your first animal</Link></p>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default AnimalList;