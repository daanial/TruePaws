import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { __, sprintf } from '@wordpress/i18n';
import { littersAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';

function WhelpingWizard() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [litter, setLitter] = useState(null);
  const [loading, setLoading] = useState(true);
  const [step, setStep] = useState(1);
  const [whelpingData, setWhelpingData] = useState({
    actual_date: '',
    male_count: 0,
    female_count: 0
  });
  const [whelpingResult, setWhelpingResult] = useState(null);

  useEffect(() => {
    loadLitter();
  }, [id]);

  const loadLitter = async () => {
    try {
      const response = await littersAPI.getById(id);
      setLitter(response.data);
    } catch (error) {
      console.error('Error loading litter:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleWhelpingSubmit = async (e) => {
    e.preventDefault();

    try {
      setLoading(true);
      const response = await littersAPI.whelp(id, whelpingData);
      setWhelpingResult(response.data);
      setStep(2);
    } catch (error) {
      console.error('Error logging whelping:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setWhelpingData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  if (loading && !litter) {
    return <LoadingSpinner message="Loading litter..." />;
  }

  if (!litter) {
    return <div>Litter not found</div>;
  }

  return (
    <Layout title={`Whelp Litter: ${litter.litter_name}`}>
      <div className="whelping-wizard">
        <div className="wizard-progress">
          <div className={`wizard-step ${step >= 1 ? 'active' : ''}`}>
            <span className="step-number">1</span>
            <span className="step-label">{__('Log Whelping', 'truepaws')}</span>
          </div>
          <div className={`wizard-step ${step >= 2 ? 'active' : ''}`}>
            <span className="step-number">2</span>
            <span className="step-label">{__('Results', 'truepaws')}</span>
          </div>
        </div>

        {step === 1 && (
          <div className="wizard-step-content">
            <div className="litter-info-summary">
              <h4>{__('Litter Information', 'truepaws')}</h4>
              <p><strong>{__('Litter:', 'truepaws')}</strong> {litter.litter_name}</p>
              <p><strong>{__('Parents:', 'truepaws')}</strong> {litter.sire_name} × {litter.dam_name}</p>
              <p><strong>{__('Mating Date:', 'truepaws')}</strong> {litter.mating_date}</p>
              <p><strong>{__('Expected Whelping:', 'truepaws')}</strong> {litter.expected_whelping_date}</p>
              {litter.actual_whelping_date && (
                <p><strong>{__('Already Whelped:', 'truepaws')}</strong> {litter.actual_whelping_date}</p>
              )}
            </div>

            {!litter.actual_whelping_date ? (
              <form onSubmit={handleWhelpingSubmit} className="whelping-form">
                <h4>{__('Log Whelping Details', 'truepaws')}</h4>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Actual Whelping Date', 'truepaws')} *</label>
                    <input
                      type="date"
                      name="actual_date"
                      value={whelpingData.actual_date}
                      onChange={handleChange}
                      required
                    />
                  </div>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Male Puppies', 'truepaws')}</label>
                    <input
                      type="number"
                      name="male_count"
                      value={whelpingData.male_count}
                      onChange={handleChange}
                      min="0"
                      max="20"
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Female Puppies', 'truepaws')}</label>
                    <input
                      type="number"
                      name="female_count"
                      value={whelpingData.female_count}
                      onChange={handleChange}
                      min="0"
                      max="20"
                    />
                  </div>
                </div>

                <div className="puppy-preview">
                  <h5>{__('Puppy Names Preview', 'truepaws')}</h5>
                  <div className="name-columns">
                    <div className="name-column">
                      <h6>{sprintf(__('Males (%s)', 'truepaws'), whelpingData.male_count)}</h6>
                      <ul>
                        {Array.from({ length: whelpingData.male_count }, (_, i) => (
                          <li key={i}>{litter.litter_name} Puppy {i + 1}</li>
                        ))}
                      </ul>
                    </div>
                    <div className="name-column">
                      <h6>{sprintf(__('Females (%s)', 'truepaws'), whelpingData.female_count)}</h6>
                      <ul>
                        {Array.from({ length: whelpingData.female_count }, (_, i) => (
                          <li key={i}>{litter.litter_name} Puppy {i + 1}</li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </div>

                <div className="truepaws-form-actions">
                  <button type="submit" className="truepaws-button light-bg" disabled={loading}>
                    {loading ? __('Creating Puppies...', 'truepaws') : __('Log Whelping & Create Puppies', 'truepaws')}
                  </button>
                  <button type="button" className="truepaws-button secondary light-bg" onClick={() => navigate('/litters')}>
                    {__('Cancel', 'truepaws')}
                  </button>
                </div>
              </form>
            ) : (
              <div className="already-whelped">
                <p>{sprintf(__('This litter has already been whelped on %s.', 'truepaws'), litter.actual_whelping_date)}</p>
                <p>{sprintf(__('Total puppies: %s (%s male, %s female)', 'truepaws'), litter.puppy_count_male + litter.puppy_count_female, litter.puppy_count_male, litter.puppy_count_female)}</p>
                <button className="truepaws-button light-bg" onClick={() => navigate('/litters')}>
                  {__('Back to Litters', 'truepaws')}
                </button>
              </div>
            )}
          </div>
        )}

        {step === 2 && whelpingResult && (
          <div className="wizard-step-content">
            <div className="whelping-success">
              <h4>✅ {__('Whelping Logged Successfully!', 'truepaws')}</h4>

              <div className="result-summary">
                <p><strong>{__('Date:', 'truepaws')}</strong> {whelpingData.actual_date}</p>
                <p><strong>{__('Total Puppies Created:', 'truepaws')}</strong> {whelpingResult.total_created}</p>
                <p><strong>{__('Males:', 'truepaws')}</strong> {whelpingData.male_count}</p>
                <p><strong>{__('Females:', 'truepaws')}</strong> {whelpingData.female_count}</p>
              </div>

              <div className="next-steps">
                <h5>{__('Next Steps:', 'truepaws')}</h5>
                <ul>
                  <li>{__('Check the Animals page to see the new puppies', 'truepaws')}</li>
                  <li>{__('Edit puppy names and add photos as needed', 'truepaws')}</li>
                  <li>{__('Log health events like vaccinations and vet visits', 'truepaws')}</li>
                  <li>{__('Generate pedigree certificates when ready', 'truepaws')}</li>
                </ul>
              </div>

              <div className="truepaws-form-actions">
                <button className="truepaws-button light-bg" onClick={() => navigate('/animals')}>
                  {__('View New Puppies', 'truepaws')}
                </button>
                <button className="truepaws-button secondary light-bg" onClick={() => navigate('/litters')}>
                  {__('Back to Litters', 'truepaws')}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
}

export default WhelpingWizard;