import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
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
            <span className="step-label">Log Whelping</span>
          </div>
          <div className={`wizard-step ${step >= 2 ? 'active' : ''}`}>
            <span className="step-number">2</span>
            <span className="step-label">Results</span>
          </div>
        </div>

        {step === 1 && (
          <div className="wizard-step-content">
            <div className="litter-info-summary">
              <h4>Litter Information</h4>
              <p><strong>Litter:</strong> {litter.litter_name}</p>
              <p><strong>Parents:</strong> {litter.sire_name} × {litter.dam_name}</p>
              <p><strong>Mating Date:</strong> {litter.mating_date}</p>
              <p><strong>Expected Whelping:</strong> {litter.expected_whelping_date}</p>
              {litter.actual_whelping_date && (
                <p><strong>Already Whelped:</strong> {litter.actual_whelping_date}</p>
              )}
            </div>

            {!litter.actual_whelping_date ? (
              <form onSubmit={handleWhelpingSubmit} className="whelping-form">
                <h4>Log Whelping Details</h4>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>Actual Whelping Date *</label>
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
                    <label>Male Puppies</label>
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
                    <label>Female Puppies</label>
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
                  <h5>Puppy Names Preview</h5>
                  <div className="name-columns">
                    <div className="name-column">
                      <h6>Males ({whelpingData.male_count})</h6>
                      <ul>
                        {Array.from({ length: whelpingData.male_count }, (_, i) => (
                          <li key={i}>{litter.litter_name} Puppy {i + 1}</li>
                        ))}
                      </ul>
                    </div>
                    <div className="name-column">
                      <h6>Females ({whelpingData.female_count})</h6>
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
                    {loading ? 'Creating Puppies...' : 'Log Whelping & Create Puppies'}
                  </button>
                  <button type="button" className="truepaws-button secondary light-bg" onClick={() => navigate('/litters')}>
                    Cancel
                  </button>
                </div>
              </form>
            ) : (
              <div className="already-whelped">
                <p>This litter has already been whelped on {litter.actual_whelping_date}.</p>
                <p>Total puppies: {litter.puppy_count_male + litter.puppy_count_female} ({litter.puppy_count_male} male, {litter.puppy_count_female} female)</p>
                <button className="truepaws-button light-bg" onClick={() => navigate('/litters')}>
                  Back to Litters
                </button>
              </div>
            )}
          </div>
        )}

        {step === 2 && whelpingResult && (
          <div className="wizard-step-content">
            <div className="whelping-success">
              <h4>✅ Whelping Logged Successfully!</h4>

              <div className="result-summary">
                <p><strong>Date:</strong> {whelpingData.actual_date}</p>
                <p><strong>Total Puppies Created:</strong> {whelpingResult.total_created}</p>
                <p><strong>Males:</strong> {whelpingData.male_count}</p>
                <p><strong>Females:</strong> {whelpingData.female_count}</p>
              </div>

              <div className="next-steps">
                <h5>Next Steps:</h5>
                <ul>
                  <li>Check the Animals page to see the new puppies</li>
                  <li>Edit puppy names and add photos as needed</li>
                  <li>Log health events like vaccinations and vet visits</li>
                  <li>Generate pedigree certificates when ready</li>
                </ul>
              </div>

              <div className="truepaws-form-actions">
                <button className="truepaws-button light-bg" onClick={() => navigate('/animals')}>
                  View New Puppies
                </button>
                <button className="truepaws-button secondary light-bg" onClick={() => navigate('/litters')}>
                  Back to Litters
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