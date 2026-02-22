import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { animalsAPI, settingsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import AnimalImagePlaceholder from '../shared/AnimalImagePlaceholder';
import LoadingSpinner from '../shared/LoadingSpinner';
import MultiPhotoUploader from './MultiPhotoUploader';

function MediaUploader({ value, onChange, label = "Featured Image", imageUrlFallback }) {
  const [imageUrl, setImageUrl] = useState('');
  const [imageError, setImageError] = useState(false);

  useEffect(() => {
    setImageError(false);
    if (value && imageUrlFallback) {
      setImageUrl(imageUrlFallback);
    } else if (value && window.wp && window.wp.media) {
      const attachment = window.wp.media.attachment(value);
      if (attachment) {
        const url = attachment.get('url');
        setImageUrl(url || '');
      }
    } else {
      setImageUrl('');
    }
  }, [value, imageUrlFallback]);

  const openMediaLibrary = () => {
    if (!window.wp || !window.wp.media) {
      alert('WordPress media library not available');
      return;
    }

    const mediaFrame = window.wp.media({
      title: 'Select Featured Image',
      button: { text: 'Use this image' },
      multiple: false
    });

    mediaFrame.on('select', () => {
      const attachment = mediaFrame.state().get('selection').first().toJSON();
      onChange(attachment.id);
      setImageUrl(attachment.url);
    });

    mediaFrame.open();
  };

  const removeImage = () => {
    onChange('');
    setImageUrl('');
  };

  const showPlaceholder = !imageUrl || imageError;

  return (
    <div className="media-uploader">
      <label>{label}</label>
      <div className="media-uploader-content">
        {showPlaceholder ? (
          <div className="media-placeholder">
            <AnimalImagePlaceholder className="media-placeholder-image" />
            <button type="button" onClick={openMediaLibrary} className="truepaws-button">
              Select Image
            </button>
          </div>
        ) : (
          <div className="media-preview">
            <img
              src={imageUrl}
              alt="Featured"
              className="media-image"
              onError={() => setImageError(true)}
            />
            <div className="media-actions">
              <button type="button" onClick={openMediaLibrary} className="truepaws-button secondary">
                Change
              </button>
              <button type="button" onClick={removeImage} className="truepaws-button danger">
                Remove
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

function AnimalForm() {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEditMode = Boolean(id);

  const [formData, setFormData] = useState({
    name: '',
    call_name: '',
    registration_number: '',
    microchip_id: '',
    breed: '',
    color_markings: '',
    description: '',
    sex: 'M',
    sire_id: '',
    dam_id: '',
    birth_date: '',
    status: 'active',
    featured_image_id: '',
    featured_image_url: ''
  });
  const [parents, setParents] = useState([]);
  const [breeds, setBreeds] = useState([]);
  const [photos, setPhotos] = useState([]);
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(isEditMode);

  useEffect(() => {
    loadParents();
    loadBreeds();
  }, []);

  useEffect(() => {
    if (isEditMode && id) {
      loadAnimal();
    }
  }, [id, isEditMode]);

  const loadAnimal = async () => {
    try {
      const response = await animalsAPI.getById(id);
      const animal = response.data;
      setFormData({
        name: animal.name || '',
        call_name: animal.call_name || '',
        registration_number: animal.registration_number || '',
        microchip_id: animal.microchip_id || '',
        breed: animal.breed || '',
        color_markings: animal.color_markings || '',
        description: animal.description || '',
        sex: animal.sex || 'M',
        sire_id: animal.sire_id || '',
        dam_id: animal.dam_id || '',
        birth_date: animal.birth_date ? animal.birth_date.split('T')[0] : '',
        status: animal.status || 'active',
        featured_image_id: animal.featured_image_id || '',
        featured_image_url: animal.featured_image_url || ''
      });
      setPhotos(animal.photos || []);
    } catch (error) {
      console.error('Error loading animal:', error);
      alert('Error loading animal: ' + (error.response?.data?.message || error.message));
      navigate('/animals');
    } finally {
      setInitialLoading(false);
    }
  };

  const loadParents = async () => {
    try {
      const response = await animalsAPI.getAll();
      setParents(response.data.animals || []);
    } catch (error) {
      console.error('Error loading parents:', error);
    }
  };

  const loadBreeds = async () => {
    try {
      const response = await settingsAPI.getBreeds();
      if (response.data.success) {
        setBreeds(response.data.breeds || []);
      }
    } catch (error) {
      console.error('Error loading breeds:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const { featured_image_url, ...submitData } = formData;
      const payload = {
        ...submitData,
        sire_id: formData.sire_id || null,
        dam_id: formData.dam_id || null,
        featured_image_id: formData.featured_image_id || null
      };

      if (isEditMode) {
        await animalsAPI.update(id, payload);
        navigate(`/animals/${id}`);
      } else {
        await animalsAPI.create(payload);
        navigate('/animals');
      }
    } catch (error) {
      console.error('Error saving animal:', error);
      alert('Error saving animal: ' + (error.response?.data?.message || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const getParentOptions = (excludeId = null) => {
    return parents.filter(parent => parent.id !== excludeId && parent.id !== parseInt(id, 10));
  };

  if (initialLoading) {
    return <LoadingSpinner message="Loading animal..." />;
  }

  return (
    <Layout title={isEditMode ? 'Edit Animal' : 'Add New Animal'}>
      <form onSubmit={handleSubmit} className="truepaws-form">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Name *</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>Call Name</label>
            <input
              type="text"
              name="call_name"
              value={formData.call_name}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Registration Number</label>
            <input
              type="text"
              name="registration_number"
              value={formData.registration_number}
              onChange={handleChange}
            />
          </div>
          <div className="truepaws-form-group">
            <label>Microchip ID</label>
            <input
              type="text"
              name="microchip_id"
              value={formData.microchip_id}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Breed</label>
            <select name="breed" value={formData.breed} onChange={handleChange}>
              <option value="">Select breed...</option>
              {breeds.map((breed) => (
                <option key={breed.id} value={breed.name}>{breed.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <label>Sex *</label>
            <select name="sex" value={formData.sex} onChange={handleChange} required>
              <option value="M">Male</option>
              <option value="F">Female</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Sire (Father)</label>
            <select name="sire_id" value={formData.sire_id} onChange={handleChange}>
              <option value="">Select sire...</option>
              {getParentOptions(formData.dam_id).map(parent => (
                <option key={parent.id} value={parent.id}>{parent.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <label>Dam (Mother)</label>
            <select name="dam_id" value={formData.dam_id} onChange={handleChange}>
              <option value="">Select dam...</option>
              {getParentOptions(formData.sire_id).map(parent => (
                <option key={parent.id} value={parent.id}>{parent.name}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Birth Date</label>
            <input
              type="date"
              name="birth_date"
              value={formData.birth_date}
              onChange={handleChange}
            />
          </div>
          <div className="truepaws-form-group">
            <label>Status</label>
            <select name="status" value={formData.status} onChange={handleChange}>
              <option value="active">Active</option>
              <option value="retired">Retired</option>
              <option value="sold">Sold</option>
              <option value="deceased">Deceased</option>
              <option value="co-owned">Co-owned</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>Color/Markings</label>
          <textarea
            name="color_markings"
            value={formData.color_markings}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-group">
          <label>Description</label>
          <textarea
            name="description"
            value={formData.description}
            onChange={handleChange}
            rows="5"
            placeholder="Write a longer description about this animal..."
          />
        </div>

        <MediaUploader
          value={formData.featured_image_id}
          onChange={(id) => setFormData(prev => ({...prev, featured_image_id: id}))}
          imageUrlFallback={formData.featured_image_url}
        />

        {isEditMode && id && (
          <MultiPhotoUploader
            animalId={id}
            photos={photos}
            onPhotosChange={setPhotos}
          />
        )}

        <div className="truepaws-form-actions">
          <button type="submit" className="truepaws-button" disabled={loading}>
            {loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Animal' : 'Create Animal')}
          </button>
          <button type="button" className="truepaws-button secondary" onClick={() => navigate(isEditMode ? `/animals/${id}` : '/animals')}>
            Cancel
          </button>
        </div>
      </form>
    </Layout>
  );
}

export default AnimalForm;