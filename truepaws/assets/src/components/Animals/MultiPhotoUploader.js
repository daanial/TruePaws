import React, { useState } from 'react';
import { animalsAPI } from '../../api/client';

function MultiPhotoUploader({ animalId, photos = [], onPhotosChange }) {
  const [draggedIndex, setDraggedIndex] = useState(null);
  const [saving, setSaving] = useState(false);

  const openMediaLibrary = () => {
    if (!window.wp || !window.wp.media) {
      alert('WordPress media library not available');
      return;
    }

    const mediaFrame = window.wp.media({
      title: 'Select Photos',
      button: { text: 'Add Photos' },
      multiple: true,
      library: { type: 'image' },
    });

    mediaFrame.on('select', async () => {
      const selection = mediaFrame.state().get('selection');
      const ids = selection.map((a) => a.toJSON().id).filter(Boolean);
      if (ids.length === 0) return;

      setSaving(true);
      try {
        const res = await animalsAPI.addPhotos(animalId, ids);
        onPhotosChange(res.data.photos || []);
      } catch (err) {
        console.error('Error adding photos:', err);
        alert('Error adding photos: ' + (err.response?.data?.message || err.message));
      } finally {
        setSaving(false);
      }
    });

    mediaFrame.open();
  };

  const removePhoto = async (photoId) => {
    if (!confirm('Remove this photo?')) return;
    setSaving(true);
    try {
      const res = await animalsAPI.deletePhoto(animalId, photoId);
      onPhotosChange(res.data.photos || []);
    } catch (err) {
      console.error('Error removing photo:', err);
      alert('Error removing photo: ' + (err.response?.data?.message || err.message));
    } finally {
      setSaving(false);
    }
  };

  const setFeatured = async (photoId) => {
    const updated = photos.map((p, i) => ({
      id: p.id,
      sort_order: i,
      is_featured: p.id === photoId ? 1 : 0,
    }));
    setSaving(true);
    try {
      const res = await animalsAPI.reorderPhotos(animalId, updated);
      onPhotosChange(res.data.photos || []);
    } catch (err) {
      console.error('Error setting featured:', err);
      alert('Error setting featured: ' + (err.response?.data?.message || err.message));
    } finally {
      setSaving(false);
    }
  };

  const handleDragStart = (e, index) => {
    setDraggedIndex(index);
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', index);
    e.target.classList.add('dragging');
  };

  const handleDragEnd = (e) => {
    e.target.classList.remove('dragging');
    setDraggedIndex(null);
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = async (e, dropIndex) => {
    e.preventDefault();
    const dragIndex = parseInt(e.dataTransfer.getData('text/plain'), 10);
    if (dragIndex === dropIndex || isNaN(dragIndex)) return;

    const reordered = [...photos];
    const [removed] = reordered.splice(dragIndex, 1);
    reordered.splice(dropIndex, 0, removed);

    const updated = reordered.map((p, i) => ({
      id: p.id,
      sort_order: i,
      is_featured: p.is_featured || 0,
    }));

    setSaving(true);
    try {
      const res = await animalsAPI.reorderPhotos(animalId, updated);
      onPhotosChange(res.data.photos || []);
    } catch (err) {
      console.error('Error reordering:', err);
      alert('Error reordering: ' + (err.response?.data?.message || err.message));
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="multi-photo-uploader">
      <label>Photo Gallery</label>
      <p className="multi-photo-hint">Add multiple photos. Drag to reorder. Click the star to set as featured.</p>
      <div className="multi-photo-grid">
        {photos.map((photo, index) => (
          <div
            key={photo.id}
            className={`multi-photo-item ${draggedIndex === index ? 'dragging' : ''}`}
            draggable
            onDragStart={(e) => handleDragStart(e, index)}
            onDragEnd={handleDragEnd}
            onDragOver={handleDragOver}
            onDrop={(e) => handleDrop(e, index)}
          >
            <div className="multi-photo-thumb">
              <img src={photo.url || photo.url_large} alt="" onError={(e) => { e.target.style.display = 'none'; }} />
              {photo.is_featured ? (
                <span className="multi-photo-featured-badge" title="Featured">★</span>
              ) : null}
            </div>
            <div className="multi-photo-actions">
              {!photo.is_featured ? (
                <button type="button" className="multi-photo-btn star" onClick={() => setFeatured(photo.id)} title="Set as featured">
                  ☆
                </button>
              ) : null}
              <button type="button" className="multi-photo-btn remove" onClick={() => removePhoto(photo.id)} title="Remove">
                ×
              </button>
            </div>
          </div>
        ))}
        <div className="multi-photo-add" onClick={openMediaLibrary}>
          <span className="multi-photo-add-icon">+</span>
          <span>Add Photos</span>
        </div>
      </div>
      {saving && <span className="multi-photo-saving">Saving…</span>}
    </div>
  );
}

export default MultiPhotoUploader;
