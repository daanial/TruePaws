import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { settingsAPI } from '../../api/client';

function BreedsManager({ breeds, onBreedsChange }) {
  const [newBreed, setNewBreed] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleAddBreed = async (e) => {
    e.preventDefault();
    
    if (!newBreed.trim()) {
      setError(__('Breed name is required', 'truepaws'));
      return;
    }

    // Check for duplicates locally
    const duplicate = breeds.some(
      breed => breed.name.toLowerCase() === newBreed.trim().toLowerCase()
    );
    
    if (duplicate) {
      setError(__('This breed already exists', 'truepaws'));
      return;
    }

    setLoading(true);
    setError('');

    try {
      const response = await settingsAPI.addBreed(newBreed.trim());
      if (response.data.success) {
        setNewBreed('');
        // Refresh breeds list
        const breedsResponse = await settingsAPI.getBreeds();
        if (breedsResponse.data.success) {
          onBreedsChange(breedsResponse.data.breeds);
        }
      }
    } catch (err) {
      setError(err.response?.data?.message || __('Failed to add breed', 'truepaws'));
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteBreed = async (id) => {
    if (!window.confirm(__('Are you sure you want to delete this breed?', 'truepaws'))) {
      return;
    }

    setLoading(true);
    setError('');

    try {
      const response = await settingsAPI.deleteBreed(id);
      if (response.data.success) {
        // Refresh breeds list
        const breedsResponse = await settingsAPI.getBreeds();
        if (breedsResponse.data.success) {
          onBreedsChange(breedsResponse.data.breeds);
        }
      }
    } catch (err) {
      setError(err.response?.data?.message || __('Failed to delete breed', 'truepaws'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="breeds-manager">
      <div className="breeds-add-form">
        <p className="description">
          {__('Manage your breed list. These breeds will be available in the dropdown when creating or editing animals.', 'truepaws')}
        </p>
        <form onSubmit={handleAddBreed}>
          <div className="truepaws-form-group">
            <label>{__('Add New Breed', 'truepaws')}</label>
            <div className="breed-input-group">
              <input
                type="text"
                value={newBreed}
                onChange={(e) => {
                  setNewBreed(e.target.value);
                  setError('');
                }}
                placeholder={__('Enter breed name (e.g., Golden Retriever)', 'truepaws')}
                disabled={loading}
                className={error ? 'error' : ''}
              />
              <button 
                type="submit" 
                className="truepaws-button"
                disabled={loading || !newBreed.trim()}
              >
                {loading ? __('Adding...', 'truepaws') : __('Add Breed', 'truepaws')}
              </button>
            </div>
            {error && <div className="truepaws-error">{error}</div>}
          </div>
        </form>
      </div>

      <div className="breeds-list">
        <h4>{sprintf(__('Your Breeds (%s)', 'truepaws'), breeds.length)}</h4>
        {breeds.length === 0 ? (
          <div className="breeds-empty">
            <p>{__('No breeds added yet. Add your first breed above.', 'truepaws')}</p>
          </div>
        ) : (
          <ul className="breeds-list-items">
            {breeds.map((breed) => (
              <li key={breed.id} className="breed-item">
                <span className="breed-name">
                  {breed.name}
                  {breed.species && <span className="breed-species"> ({breed.species})</span>}
                </span>
                <button
                  type="button"
                  className="truepaws-button danger small"
                  onClick={() => handleDeleteBreed(breed.id)}
                  disabled={loading}
                >
                  {__('Delete', 'truepaws')}
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}

export default BreedsManager;
