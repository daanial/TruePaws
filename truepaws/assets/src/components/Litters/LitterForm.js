import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { littersAPI, animalsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import { format, addDays } from 'date-fns';

function LitterForm() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    sire_id: '',
    dam_id: '',
    mating_date: '',
    mating_method: 'natural',
    notes: ''
  });
  const [animals, setAnimals] = useState([]);
  const [loading, setLoading] = useState(false);
  const [expectedWhelpingDate, setExpectedWhelpingDate] = useState('');
  const [showCalculator, setShowCalculator] = useState(false);

  useEffect(() => {
    loadAnimals();
  }, []);

  const loadAnimals = async () => {
    try {
      const response = await animalsAPI.getAll();
      setAnimals(response.data.animals || []);
    } catch (error) {
      console.error('Error loading animals:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      await littersAPI.create(formData);
      navigate('/litters');
    } catch (error) {
      console.error('Error creating litter:', error);
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

    // Calculate expected whelping date when mating date changes
    if (name === 'mating_date' && value) {
      const matingDate = new Date(value);
      const whelpingDate = addDays(matingDate, 63); // Default 63 days for dogs
      setExpectedWhelpingDate(format(whelpingDate, 'yyyy-MM-dd'));
    }
  };

  const getParentOptions = (sex, excludeId = null) => {
    return animals.filter(animal => animal.sex === sex && animal.id !== excludeId);
  };

  return (
    <Layout title="Log New Mating">
      <form onSubmit={handleSubmit} className="truepaws-form">
        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Sire (Father) *</label>
            <select name="sire_id" value={formData.sire_id} onChange={handleChange} required>
              <option value="">Select sire...</option>
              {getParentOptions('M', formData.dam_id).map(animal => (
                <option key={animal.id} value={animal.id}>{animal.name}</option>
              ))}
            </select>
          </div>
          <div className="truepaws-form-group">
            <label>Dam (Mother) *</label>
            <select name="dam_id" value={formData.dam_id} onChange={handleChange} required>
              <option value="">Select dam...</option>
              {getParentOptions('F', formData.sire_id).map(animal => (
                <option key={animal.id} value={animal.id}>{animal.name}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="truepaws-form-row">
          <div className="truepaws-form-group">
            <label>Mating Date *</label>
            <input
              type="date"
              name="mating_date"
              value={formData.mating_date}
              onChange={handleChange}
              required
            />
          </div>
          <div className="truepaws-form-group">
            <label>Method</label>
            <select name="mating_method" value={formData.mating_method} onChange={handleChange}>
              <option value="natural">Natural</option>
              <option value="ai">Artificial Insemination</option>
            </select>
          </div>
        </div>

        <div className="truepaws-form-group">
          <label>Notes</label>
          <textarea
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            rows="3"
          />
        </div>

        {formData.mating_date && expectedWhelpingDate && (
          <div className="pregnancy-calculator">
            <h4>Expected Whelping Date</h4>
            <div className="calculator-result">
              <p><strong>{format(new Date(expectedWhelpingDate), 'MMMM d, yyyy')}</strong></p>
              <p className="calculator-note">
                Based on 63-day gestation period (typical for dogs).
                Actual whelping may vary ±7 days.
              </p>
            </div>
          </div>
        )}

        <div className="truepaws-form-actions">
          <button type="submit" className="truepaws-button" disabled={loading}>
            {loading ? 'Creating...' : 'Create Litter'}
          </button>
          <button type="button" className="truepaws-button secondary" onClick={() => navigate('/litters')}>
            Cancel
          </button>
        </div>
      </form>
    </Layout>
  );
}

export default LitterForm;