import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { __, sprintf } from '@wordpress/i18n';
import { animalsAPI, settingsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import AnimalImagePlaceholder from '../shared/AnimalImagePlaceholder';
import LoadingSpinner from '../shared/LoadingSpinner';
import MultiPhotoUploader from './MultiPhotoUploader';

function MediaUploader({ value, onChange, label, imageUrlFallback }) {
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
      alert(__('WordPress media library not available', 'truepaws'));
      return;
    }

    const mediaFrame = window.wp.media({
      title: __('Select Featured Image', 'truepaws'),
      button: { text: __('Use this image', 'truepaws') },
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
      <label>{label || __('Featured Image', 'truepaws')}</label>
      <div className="media-uploader-content">
        {showPlaceholder ? (
          <div className="media-placeholder">
            <AnimalImagePlaceholder className="media-placeholder-image" />
            <button type="button" onClick={openMediaLibrary} className="truepaws-button">
              {__('Select Image', 'truepaws')}
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
                {__('Change', 'truepaws')}
              </button>
              <button type="button" onClick={removeImage} className="truepaws-button danger">
                {__('Remove', 'truepaws')}
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
      alert(sprintf(__('Error loading animal: %s', 'truepaws'), error.response?.data?.message || error.message));
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
      alert(sprintf(__('Error saving animal: %s', 'truepaws'), error.response?.data?.message || error.message));
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
    return <LoadingSpinner message={__('Loading animal...', 'truepaws')} />;
  }

  return (
    <Layout title={isEditMode ? __('Edit Animal', 'truepaws') : __('Add New Animal', 'truepaws')}>
      <form onSubmit={handleSubmit} className="truepaws-form">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('Name', 'truepaws')} *</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>{__('Call Name', 'truepaws')}</label>
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
            <label>{__('Registration Number', 'truepaws')}</label>
            <input
              type="text"
              name="registration_number"
              value={formData.registration_number}
              onChange={handleChange}
            />
          </div>
          <div className="truepaws-form-group">
            <label>{__('Microchip ID', 'truepaws')}</label>
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
            <label>{__('Breed', 'truepaws')}</label>
            <select name="breed" value={formData.breed} onChange={handleChange}>
              <option value="">{__('Select breed...', 'truepaws')}</option>
              {breeds.map((breed) => (
                <option key={breed.id} value={breed.name}>{breed.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <label>{__('Sex', 'truepaws')} *</label>
            <select name="sex" value={formData.sex} onChange={handleChange} required>
              <option value="M">{__('Male', 'truepaws')}</option>
              <option value="F">{__('Female', 'truepaws')}</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('Sire (Father)', 'truepaws')}</label>
            <select name="sire_id" value={formData.sire_id} onChange={handleChange}>
              <option value="">{__('Select sire...', 'truepaws')}</option>
              {getParentOptions(formData.dam_id).map(parent => (
                <option key={parent.id} value={parent.id}>{parent.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <label>{__('Dam (Mother)', 'truepaws')}</label>
            <select name="dam_id" value={formData.dam_id} onChange={handleChange}>
              <option value="">{__('Select dam...', 'truepaws')}</option>
              {getParentOptions(formData.sire_id).map(parent => (
                <option key={parent.id} value={parent.id}>{parent.name}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>{__('Birth Date', 'truepaws')}</label>
            <input
              type="date"
              name="birth_date"
              value={formData.birth_date}
              onChange={handleChange}
            />
          </div>
          <div className="truepaws-form-group">
            <label>{__('Status', 'truepaws')}</label>
            <select name="status" value={formData.status} onChange={handleChange}>
              <option value="active">{__('Active', 'truepaws')}</option>
              <option value="retired">{__('Retired', 'truepaws')}</option>
              <option value="sold">{__('Sold', 'truepaws')}</option>
              <option value="deceased">{__('Deceased', 'truepaws')}</option>
              <option value="co-owned">{__('Co-owned', 'truepaws')}</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>{__('Color/Markings', 'truepaws')}</label>
          <textarea
            name="color_markings"
            value={formData.color_markings}
            onChange={handleChange}
            rows="3"
          />
        </div>

        <div className="truepaws-form-group">
          <label>{__('Description', 'truepaws')}</label>
          <textarea
            name="description"
            value={formData.description}
            onChange={handleChange}
            rows="5"
            placeholder={__('Write a longer description about this animal...', 'truepaws')}
          />
        </div>

        <MediaUploader
          value={formData.featured_image_id}
          onChange={(id) => setFormData(prev => ({...prev, featured_image_id: id}))}
          imageUrlFallback={formData.featured_image_url}
          label={__('Featured Image', 'truepaws')}
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
            {loading ? (isEditMode ? __('Updating...', 'truepaws') : __('Creating...', 'truepaws')) : (isEditMode ? __('Update Animal', 'truepaws') : __('Create Animal', 'truepaws'))}
          </button>
          <button type="button" className="truepaws-button secondary" onClick={() => navigate(isEditMode ? `/animals/${id}` : '/animals')}>
            {__('Cancel', 'truepaws')}
          </button>
        </div>
      </form>
    </Layout>
  );
}

export default AnimalForm;