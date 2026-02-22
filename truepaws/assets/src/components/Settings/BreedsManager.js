import React, { useState, useEffect } from 'react';
import { settingsAPI } from '../../api/client';

function BreedsManager({ breeds, onBreedsChange }) {
  const [newBreed, setNewBreed] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleAddBreed = async (e) => {
    e.preventDefault();
    
    if (!newBreed.trim()) {
      setError('Breed name is required');
      return;
    }

    // Check for duplicates locally
    const duplicate = breeds.some(
      breed => breed.name.toLowerCase() === newBreed.trim().toLowerCase()
    );
    
    if (duplicate) {
      setError('This breed already exists');
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
      setError(err.response?.data?.message || 'Failed to add breed');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteBreed = async (id) => {
    if (!window.confirm('Are you sure you want to delete this breed?')) {
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
      setError(err.response?.data?.message || 'Failed to delete breed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="breeds-manager">
      <div className="breeds-add-form">
        <p className="description">
          Manage your breed list. These breeds will be available in the dropdown when creating or editing animals.
        </p>
        <form onSubmit={handleAddBreed}>
          <div className="truepaws-form-group">
            <label>Add New Breed</label>
            <div className="breed-input-group">
              <input
                type="text"
                value={newBreed}
                onChange={(e) => {
                  setNewBreed(e.target.value);
                  setError('');
                }}
                placeholder="Enter breed name (e.g., Golden Retriever)"
                disabled={loading}
                className={error ? 'error' : ''}
              />
              <button 
                type="submit" 
                className="truepaws-button"
                disabled={loading || !newBreed.trim()}
              >
                {loading ? 'Adding...' : 'Add Breed'}
              </button>
            </div>
            {error && <div className="truepaws-error">{error}</div>}
          </div>
        </form>
      </div>

      <div className="breeds-list">
        <h4>Your Breeds ({breeds.length})</h4>
        {breeds.length === 0 ? (
          <div className="breeds-empty">
            <p>No breeds added yet. Add your first breed above.</p>
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
                  Delete
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
