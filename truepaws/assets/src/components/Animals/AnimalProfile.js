import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { animalsAPI, eventsAPI, contactsAPI } from '../../api/client';
import Layout from '../shared/Layout';
import LoadingSpinner from '../shared/LoadingSpinner';
import Timeline from './Timeline';
import AnimalImagePlaceholder from '../shared/AnimalImagePlaceholder';
import MultiPhotoUploader from './MultiPhotoUploader';
import WeightGrowthChart from '../shared/WeightGrowthChart';
import DOMPurify from 'dompurify';

function AnimalProfile() {
  const { id } = useParams();
  const [animal, setAnimal] = useState(null);
  const [timeline, setTimeline] = useState([]);
  const [pedigree, setPedigree] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showEventForm, setShowEventForm] = useState(false);
  const [eventFormData, setEventFormData] = useState({
    event_type: 'note',
    event_date: new Date().toISOString().split('T')[0],
    title: '',
    meta_data: {}
  });
  const [imageError, setImageError] = useState(false);
  const [imageUrl, setImageUrl] = useState(null);
  const [showSalesModal, setShowSalesModal] = useState(false);
  const [contacts, setContacts] = useState([]);
  const [salesFormData, setSalesFormData] = useState({
    contact_id: '',
    sale_date: new Date().toISOString().split('T')[0],
    price: '',
    notes: ''
  });
  const [aiCareAdvice, setAiCareAdvice] = useState(null);
  const [aiLoading, setAiLoading] = useState(false);
  const [aiError, setAiError] = useState('');
  const [shortcodeCopied, setShortcodeCopied] = useState(false);
  const [marketingBio, setMarketingBio] = useState(null);
  const [bioLoading, setBioLoading] = useState(false);
  const [bioError, setBioError] = useState('');
  const [bioCopied, setBioCopied] = useState(false);

  useEffect(() => {
    setImageError(false);
    setImageUrl(null);
    loadAnimalData();
  }, [id]);

  useEffect(() => {
    if (!animal) return;
    const url = animal.featured_image_url;
    if (url) {
      setImageUrl(url);
      return;
    }
    if (!animal.featured_image_id) return;
    const apiUrl = window.truepawsData?.apiUrl || '';
    const wpMediaBase = apiUrl.replace(/truepaws\/v1\/?$/, 'wp/v2/');
    if (!wpMediaBase) return;
    fetch(`${wpMediaBase}media/${animal.featured_image_id}`, {
      headers: { 'X-WP-Nonce': window.truepawsData?.nonce || '' }
    })
      .then(r => r.json())
      .then(data => {
        if (data && data.source_url) setImageUrl(data.source_url);
      })
      .catch(() => {});
  }, [animal?.id, animal?.featured_image_id, animal?.featured_image_url]);

  useEffect(() => {
    if (!animal) return;
    loadAICareAdvice();
  }, [animal?.id]);

  const loadAICareAdvice = async () => {
    setAiLoading(true);
    setAiError('');
    setAiCareAdvice(null);
    try {
      const response = await animalsAPI.getAICareAdvice(id);
      const data = response.data;
      setAiCareAdvice(data);
    } catch (error) {
      setAiError(error.response?.data?.message || __('Failed to load AI advice', 'truepaws'));
      setAiCareAdvice({ enabled: false });
    } finally {
      setAiLoading(false);
    }
  };

  const stripAIPreamble = (text) => {
    if (!text) return text;
    let t = text.trim();
    const patterns = [
      /^(?:Okay|Ok|Sure|Certainly|Of course|Absolutely|Great|Alright|Right)[,!.]?\s*/i,
      /^(?:Here(?:'s| is| are)|I've (?:prepared|compiled|put together|created|generated))\s+[^.:\n]{0,120}[.:]\s*/i,
      /^(?:Below (?:is|are)|The following (?:is|are)|Let me (?:provide|share|give))\s+[^.:\n]{0,120}[.:]\s*/i,
      /^(?:This is|These are)\s+[^.:\n]{0,80}[.:]\s*/i,
    ];
    for (let pass = 0; pass < 3; pass++) {
      let changed = false;
      for (const p of patterns) {
        const cleaned = t.replace(p, '');
        if (cleaned !== t) { t = cleaned.trimStart(); changed = true; break; }
      }
      if (!changed) break;
    }
    return t;
  };

  const generateMarketingBio = async () => {
    setBioLoading(true);
    setBioError('');
    try {
      const response = await animalsAPI.getMarketingBio(id);
      const data = response.data;
      if (data.enabled === false) {
        setBioError(data.message || __('Configure Gemini API key in Settings.', 'truepaws'));
      } else if (data.error) {
        setBioError(data.error);
      } else {
        setMarketingBio(stripAIPreamble(data.content));
      }
    } catch (error) {
        setBioError(error.response?.data?.message || __('Failed to generate bio', 'truepaws'));
    } finally {
      setBioLoading(false);
    }
  };

  const loadAnimalData = async () => {
    try {
      const [animalResponse, timelineResponse, pedigreeResponse] = await Promise.all([
        animalsAPI.getById(id),
        animalsAPI.getTimeline(id),
        animalsAPI.getPedigree(id)
      ]);
      setAnimal(animalResponse.data);
      setTimeline(timelineResponse.data.events || []);
      setPedigree(pedigreeResponse.data);
    } catch (error) {
      console.error('Error loading animal:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadContacts = async () => {
    try {
      const response = await contactsAPI.getAll();
      setContacts(response.data.contacts || []);
    } catch (error) {
      console.error('Error loading contacts:', error);
    }
  };

  const handleSalesModalOpen = () => {
    setShowSalesModal(true);
    loadContacts();
  };

  const handleSalesSubmit = async (e) => {
    e.preventDefault();

    try {
      // Update animal status to sold
      await animalsAPI.update(id, {
        status: 'sold'
      });

      // Add sale event
      await eventsAPI.create(id, {
        event_type: 'note',
        event_date: salesFormData.sale_date,
        title: __('Sold', 'truepaws'),
        meta_data: {
          contact_id: salesFormData.contact_id,
          price: salesFormData.price,
          notes: salesFormData.notes,
          sale_type: 'sale'
        }
      });

      setShowSalesModal(false);
      loadAnimalData(); // Refresh data
    } catch (error) {
      console.error('Error processing sale:', error);
    }
  };

  const handleAddEvent = async (e) => {
    e.preventDefault();

    try {
      await eventsAPI.create(id, eventFormData);
      setShowEventForm(false);
      setEventFormData({
        event_type: 'note',
        event_date: new Date().toISOString().split('T')[0],
        title: '',
        meta_data: {}
      });
      loadAnimalData(); // Reload timeline
    } catch (error) {
      console.error('Error adding event:', error);
    }
  };

  const applyBold = (s) => s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

  const getIconForTitle = (title) => {
    const t = (title || '').toLowerCase();
    if (t.includes('vaccin') || t.includes('immun')) return 'syringe';
    if (t.includes('deworm') || t.includes('worm') || t.includes('parasite')) return 'pill';
    if (t.includes('care') || t.includes('health') || t.includes('wellness')) return 'heart';
    if (t.includes('feed') || t.includes('diet') || t.includes('nutrition')) return 'food';
    if (t.includes('groom') || t.includes('hygiene')) return 'sparkles';
    if (t.includes('common') || t.includes('general') || t.includes('important') || t.includes('other')) return 'info';
    return 'document';
  };

  const sectionIcons = {
    syringe: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2v4h-4"/><path d="M18 22l-4-4"/><path d="M18 8a8 8 0 01-8 8"/><path d="M18 8a8 8 0 00-8-8"/></svg>',
    pill: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 2.5a8 8 0 0111.5 11.5l-8 8a8 8 0 01-11.5-11.5z"/><path d="M13.5 10.5l-3 3"/><path d="M10.5 13.5l3-3"/></svg>',
    heart: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
    food: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><path d="M6 2v4"/><path d="M10 2v4"/><path d="M14 2v4"/></svg>',
    sparkles: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/><path d="M19 12l.75 2.25L22 15l-2.25.75L19 18l-.75-2.25L16 15l2.25-.75L19 12z"/><path d="M5 12l.75 2.25L8 15l-2.25.75L5 18l-.75-2.25L2 15l2.25-.75L5 12z"/></svg>',
    info: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
    document: '<svg class="ai-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>',
  };

  const formatAIContent = (text) => {
    if (!text) return '';
    text = stripAIPreamble(text);
    const escape = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const lines = text.split('\n');
    const sections = [];
    let currentSection = { title: null, bullets: [], paragraphs: [] };
    const flushSection = () => {
      if (currentSection.title || currentSection.bullets.length || currentSection.paragraphs.length) {
        sections.push({ ...currentSection });
        currentSection = { title: null, bullets: [], paragraphs: [] };
      }
    };
    for (let i = 0; i < lines.length; i++) {
      const line = lines[i];
      const trimmed = line.trim();
      if (!trimmed) {
        flushSection();
        continue;
      }
      const boldMatch = trimmed.match(/^\*\*(.+?)\*\*:?\s*$/);
      const numMatch = trimmed.match(/^(\d+)[\.\)]\s*(.+)/);
      if (boldMatch) {
        flushSection();
        currentSection.title = applyBold(escape(boldMatch[1]));
      } else if (numMatch) {
        flushSection();
        currentSection.title = applyBold(escape(numMatch[2].trim()));
      } else if (trimmed.startsWith('- ') || trimmed.startsWith('• ') || trimmed.startsWith('* ')) {
        const content = applyBold(escape(trimmed.slice(2)));
        currentSection.bullets.push(content);
      } else if (currentSection.bullets.length === 0 && !currentSection.title) {
        currentSection.title = applyBold(escape(trimmed));
      } else {
        const content = applyBold(escape(trimmed));
        currentSection.paragraphs.push(content);
      }
    }
    flushSection();
    return sections.map(({ title, bullets, paragraphs }) => {
      const iconKey = getIconForTitle(title);
      const iconSvg = sectionIcons[iconKey] || sectionIcons.document;
      const paraHtml = paragraphs.length
        ? paragraphs.map(p => `<p class="ai-section-p">${p}</p>`).join('')
        : '';
      const listHtml = bullets.length
        ? `<ul class="ai-section-list">${bullets.map(item => `<li>${item}</li>`).join('')}</ul>`
        : '';
      return `<section class="ai-section">${title ? `<h5 class="ai-section-title"><span class="ai-section-icon">${iconSvg}</span>${title}</h5>` : ''}${paraHtml}${listHtml}</section>`;
    }).join('');
  };

  const shortcode = `[truepaws_animal id="${id}"]`;

  const copyShortcode = () => {
    navigator.clipboard.writeText(shortcode).then(() => {
      setShortcodeCopied(true);
      setTimeout(() => setShortcodeCopied(false), 2000);
    });
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
    return <LoadingSpinner message={__('Loading animal...', 'truepaws')} />;
  }

  if (!animal) {
    return <div>{__('Animal not found', 'truepaws')}</div>;
  }

  return (
    <Layout
      title={`${animal.name} - Profile`}
      actions={
        <div className="animal-profile-actions">
          <Link to={`/animals/${id}/edit`} className="truepaws-button secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            {__('Edit', 'truepaws')}
          </Link>
          {animal && animal.status !== 'sold' && (
            <button
              className="truepaws-button"
              onClick={handleSalesModalOpen}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"></path>
              </svg>
              {__('Sell/Reserve', 'truepaws')}
            </button>
          )}
          {animal && animal.status === 'sold' && (
            <button
              className="truepaws-button"
              onClick={async () => {
                try {
                  const apiUrl = window.truepawsData?.apiUrl || '';
                  const response = await fetch(`${apiUrl}animals/${id}/generate-handover`, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'X-WP-Nonce': window.truepawsData?.nonce || ''
                    }
                  });
                  const data = await response.json();

                  if (response.ok && (data.pdf || data.html)) {
                    const isPdf = Boolean(data.pdf);
                    const content = isPdf ? data.pdf : data.html;
                    const decoded = isPdf ? Uint8Array.from(atob(content), c => c.charCodeAt(0)) : content;
                    const mimeType = isPdf ? 'application/pdf' : 'text/html';
                    const blob = new Blob([decoded], { type: mimeType });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = data.filename || `handover-${animal.name}-${new Date().toISOString().split('T')[0]}.${isPdf ? 'pdf' : 'html'}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                  } else {
                    alert(data.message || 'Error generating handover document. Please try again.');
                  }
                } catch (error) {
                  console.error('Error:', error);
                  alert('Error generating handover document. Please try again.');
                }
              }}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              {__('Generate Handover PDF', 'truepaws')}
            </button>
          )}
          <button
            className="truepaws-button secondary"
            onClick={() => setShowEventForm(!showEventForm)}
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            {__('Add Event', 'truepaws')}
          </button>
        </div>
      }
    >
      <div className="animal-profile">
        {/* Main Animal Info Card */}
        <div className="animal-info-card">
          <div className="animal-header-content">
            <div className="animal-image-section">
              {(imageUrl && !imageError) ? (
                <img
                  src={imageUrl}
                  alt={animal.name}
                  className="animal-featured-image"
                  onError={() => setImageError(true)}
                />
              ) : (
                <AnimalImagePlaceholder alt={`No photo of ${animal.name}`} />
              )}
            </div>

            <div className="animal-info-details">
              <div className="animal-name-section">
                <h2 className="animal-name-primary">{animal.name}</h2>
                {animal.call_name && <p className="animal-call-name">"{animal.call_name}"</p>}
                <div className="animal-status-badge">{getStatusBadge(animal.status)}</div>
              </div>

              <div className="animal-info-grid">
                <div className="info-item">
                  <span className="info-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7z"></path>
                      <circle cx="12" cy="9" r="2"></circle>
                    </svg>
                  </span>
                  <div>
                    <span className="info-label">{__('Breed', 'truepaws')}</span>
                    <span className="info-value">{animal.breed || __('Not specified', 'truepaws')}</span>
                  </div>
                </div>

                <div className="info-item">
                  <span className="info-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      {animal.sex === 'M' ? (
                        <path d="M10 14a4 4 0 100-8 4 4 0 000 8zM21 3l-6 6m6-6v4.5m0-4.5h-4.5"></path>
                      ) : (
                        <path d="M12 16a4 4 0 100-8 4 4 0 000 8zM12 16v6m0 0h3m-3 0H9"></path>
                      )}
                    </svg>
                  </span>
                  <div>
                    <span className="info-label">{__('Sex', 'truepaws')}</span>
                    <span className="info-value">{animal.sex === 'M' ? __('Male', 'truepaws') : __('Female', 'truepaws')}</span>
                  </div>
                </div>

                {animal.birth_date && (
                  <div className="info-item">
                    <span className="info-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                      </svg>
                    </span>
                    <div>
                      <span className="info-label">{__('Born', 'truepaws')}</span>
                      <span className="info-value">{animal.birth_date}</span>
                    </div>
                  </div>
                )}

                {animal.registration_number && (
                  <div className="info-item">
                    <span className="info-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                      </svg>
                    </span>
                    <div>
                      <span className="info-label">{__('Registration', 'truepaws')}</span>
                      <span className="info-value">{animal.registration_number}</span>
                    </div>
                  </div>
                )}

                {animal.microchip_id && (
                  <div className="info-item">
                    <span className="info-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <line x1="6" y1="10" x2="6.01" y2="10"></line>
                        <line x1="10" y1="10" x2="14" y2="10"></line>
                      </svg>
                    </span>
                    <div>
                      <span className="info-label">{__('Microchip', 'truepaws')}</span>
                      <span className="info-value">{animal.microchip_id}</span>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Photo Gallery */}
        <div className="animal-photos-card">
          <h3 className="section-title">{__('Photo Gallery', 'truepaws')}</h3>
          <MultiPhotoUploader
            animalId={id}
            photos={animal.photos || []}
            onPhotosChange={(newPhotos) => {
              setAnimal((prev) => prev ? { ...prev, photos: newPhotos } : null);
              const featured = newPhotos.find((p) => p.is_featured);
              if (featured && (featured.url || featured.url_large)) {
                setImageUrl(featured.url || featured.url_large);
              }
            }}
          />
        </div>

        {/* Shortcode Card */}
        <div className="animal-shortcode-card">
          <div className="shortcode-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <polyline points="16 18 22 12 16 6"></polyline>
              <polyline points="8 6 2 12 8 18"></polyline>
            </svg>
            <h3 className="section-title">{__('WordPress Shortcode', 'truepaws')}</h3>
          </div>
          <p className="shortcode-description">{__('Add this shortcode to any WordPress page or post to display this animal on your front-end.', 'truepaws')}</p>
          <div className="shortcode-box">
            <code className="shortcode-text">{shortcode}</code>
            <button
              type="button"
              className="truepaws-button secondary shortcode-copy-btn"
              onClick={copyShortcode}
            >
              {shortcodeCopied ? (
                <>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  {__('Copied!', 'truepaws')}
                </>
              ) : (
                <>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
                  </svg>
                  {__('Copy', 'truepaws')}
                </>
              )}
            </button>
          </div>
        </div>

        {/* Two Column Layout for Parents and Description */}
        <div className="animal-details-grid">
          {(animal.sire_name || animal.dam_name) && (
            <div className="animal-detail-card">
              <h3 className="section-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 010 7.75"></path>
                </svg>
                {__('Parents', 'truepaws')}
              </h3>
              <div className="parents-info">
                <div className="parent-item">
                  <span className="parent-label">{__('Sire', 'truepaws')}</span>
                  <span className="parent-value">{animal.sire_name || __('Unknown', 'truepaws')}</span>
                </div>
                <div className="parent-item">
                  <span className="parent-label">{__('Dam', 'truepaws')}</span>
                  <span className="parent-value">{animal.dam_name || __('Unknown', 'truepaws')}</span>
                </div>
              </div>
            </div>
          )}

          {animal.color_markings && (
            <div className="animal-detail-card">
              <h3 className="section-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M12 6v6l4 2"></path>
                </svg>
                {__('Color/Markings', 'truepaws')}
              </h3>
              <p className="card-content">{animal.color_markings}</p>
            </div>
          )}

          {animal.description && (
            <div className="animal-detail-card">
              <h3 className="section-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                  <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                {__('Description', 'truepaws')}
              </h3>
              <p className="card-content" style={{ whiteSpace: 'pre-wrap' }}>{animal.description}</p>
            </div>
          )}
        </div>

        {showEventForm && (
          <div className="event-form-modal">
            <div className="event-form-overlay" onClick={() => setShowEventForm(false)}></div>
            <div className="event-form-content">
              <h4>{__('Add Event', 'truepaws')}</h4>
              <form onSubmit={handleAddEvent}>
                <div className="truepaws-form-group">
                  <label>{__('Event Type', 'truepaws')}</label>
                  <select
                    value={eventFormData.event_type}
                    onChange={(e) => setEventFormData({...eventFormData, event_type: e.target.value})}
                  >
                    <option value="note">{__('Note', 'truepaws')}</option>
                    <option value="vaccine">{__('Vaccine', 'truepaws')}</option>
                    <option value="vet_visit">{__('Vet Visit', 'truepaws')}</option>
                    <option value="heat">{__('Heat Cycle', 'truepaws')}</option>
                    <option value="weight">{__('Weight', 'truepaws')}</option>
                  </select>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Date', 'truepaws')}</label>
                    <input
                      type="date"
                      value={eventFormData.event_date}
                      onChange={(e) => setEventFormData({...eventFormData, event_date: e.target.value})}
                      required
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Title', 'truepaws')}</label>
                    <input
                      type="text"
                      value={eventFormData.title}
                      onChange={(e) => setEventFormData({...eventFormData, title: e.target.value})}
                      placeholder={__('Event title', 'truepaws')}
                      required
                    />
                  </div>
                </div>

                <div className="truepaws-form-actions">
                  <button type="submit" className="truepaws-button light-bg">{__('Add Event', 'truepaws')}</button>
                  <button type="button" className="truepaws-button secondary light-bg" onClick={() => setShowEventForm(false)}>
                    {__('Cancel', 'truepaws')}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {showSalesModal && (
          <div className="sales-modal">
            <div className="sales-modal-overlay" onClick={() => setShowSalesModal(false)}></div>
            <div className="sales-modal-content">
              <h4>{__('Sell or Reserve', 'truepaws')} {animal.name}</h4>
              <form onSubmit={handleSalesSubmit}>
                <div className="truepaws-form-group">
                  <label>{__('Select Contact', 'truepaws')} *</label>
                  <select
                    value={salesFormData.contact_id}
                    onChange={(e) => setSalesFormData({...salesFormData, contact_id: e.target.value})}
                    required
                  >
                    <option value="">{__('Choose a contact...', 'truepaws')}</option>
                    {contacts.map(contact => (
                      <option key={contact.id} value={contact.id}>
                        {contact.first_name} {contact.last_name} ({contact.email})
                      </option>
                    ))}
                  </select>
                </div>

                <div className="truepaws-form-row">
                  <div className="truepaws-form-group">
                    <label>{__('Sale Date', 'truepaws')} *</label>
                    <input
                      type="date"
                      value={salesFormData.sale_date}
                      onChange={(e) => setSalesFormData({...salesFormData, sale_date: e.target.value})}
                      required
                    />
                  </div>
                  <div className="truepaws-form-group">
                    <label>{__('Price', 'truepaws')}</label>
                    <input
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      value={salesFormData.price}
                      onChange={(e) => setSalesFormData({...salesFormData, price: e.target.value})}
                    />
                  </div>
                </div>

                <div className="truepaws-form-group">
                  <label>{__('Notes', 'truepaws')}</label>
                  <textarea
                    value={salesFormData.notes}
                    onChange={(e) => setSalesFormData({...salesFormData, notes: e.target.value})}
                    rows="3"
                    placeholder={__('Additional notes about the sale...', 'truepaws')}
                  />
                </div>

                <div className="truepaws-form-actions">
                  <button type="submit" className="truepaws-button light-bg">
                    {__('Mark as Sold', 'truepaws')}
                  </button>
                  <button type="button" className="truepaws-button secondary light-bg" onClick={() => setShowSalesModal(false)}>
                    {__('Cancel', 'truepaws')}
                  </button>
                </div>
              </form>

              <div className="sales-modal-footer">
                <p className="sales-note">
                  <strong>{__('Note:', 'truepaws')}</strong> {__('This will change the animal\'s status to "Sold" and add a sale event to the timeline. You can generate a handover PDF once the sale is complete.', 'truepaws')}
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Weight Growth Chart */}
        <WeightGrowthChart timeline={timeline} />

        {/* AI Marketing Bio */}
        <div className="animal-marketing-bio-card">
          <div className="marketing-bio-header">
            <div className="marketing-bio-title-group">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"></path>
              </svg>
              <h3 className="section-title">{__('AI Marketing Bio', 'truepaws')}</h3>
            </div>
            <button
              className="truepaws-button secondary"
              onClick={generateMarketingBio}
              disabled={bioLoading}
            >
              {bioLoading ? (
                <><span className="truepaws-spinner"></span> {__('Generating...', 'truepaws')}</>
              ) : marketingBio ? (
                <><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg> {__('Regenerate', 'truepaws')}</>
              ) : (
                <><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> {__('Generate Bio', 'truepaws')}</>
              )}
            </button>
          </div>
          {bioError && <p className="marketing-bio-error">{bioError}</p>}
          {marketingBio && (
            <div className="marketing-bio-content">
              <p style={{ whiteSpace: 'pre-wrap', margin: 0 }}>{marketingBio}</p>
              <button
                className={`truepaws-button secondary marketing-bio-copy ${bioCopied ? 'copied' : ''}`}
                onClick={() => {
                  navigator.clipboard.writeText(marketingBio).then(() => {
                    setBioCopied(true);
                    setTimeout(() => setBioCopied(false), 2000);
                  });
                }}
              >
                {bioCopied ? (
                  <>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    {__('Copied!', 'truepaws')}
                  </>
                ) : (
                  <>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                      <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
                    </svg>
                    {__('Copy to Clipboard', 'truepaws')}
                  </>
                )}
              </button>
            </div>
          )}
          {!marketingBio && !bioError && !bioLoading && (
            <p className="marketing-bio-hint">{__('Generate an AI-written marketing description for this animal, ready to use on your website or listings.', 'truepaws')}</p>
          )}
        </div>

        {/* Timeline Section - Distinct Styling */}
        <div className="animal-timeline-section">
          <div className="section-header-with-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <h3 className="section-title">{__('Timeline & Events', 'truepaws')}</h3>
          </div>
          <Timeline events={timeline} />
        </div>

        {/* Pedigree Section */}
        {pedigree && (
          <div className="animal-pedigree-card">
            <div className="pedigree-card-header">
              <h3 className="section-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M5.52 19c.64-2.2 1.84-3 3.22-3h6.52c1.38 0 2.58.8 3.22 3"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                  <circle cx="12" cy="10" r="1"></circle>
                  <path d="M12 13v6"></path>
                  <path d="M12 3v4"></path>
                  <circle cx="12" cy="19" r="1"></circle>
                </svg>
                {__('Pedigree', 'truepaws')}
              </h3>
              <button
                type="button"
                className="truepaws-button secondary"
                onClick={async () => {
                  try {
                    const apiUrl = (window.truepawsData && window.truepawsData.apiUrl) || '';
                    const response = await fetch(`${apiUrl}animals/${id}/generate-pedigree-pdf`, {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': (window.truepawsData && window.truepawsData.nonce) || ''
                      }
                    });
                    const data = await response.json();

                    if (response.ok && (data.pdf || data.html)) {
                      const isPdf = Boolean(data.pdf);
                      const content = isPdf ? data.pdf : data.html;
                      const decoded = isPdf ? Uint8Array.from(atob(content), c => c.charCodeAt(0)) : content;
                      const mimeType = isPdf ? 'application/pdf' : 'text/html';
                      const blob = new Blob([decoded], { type: mimeType });
                      const url = URL.createObjectURL(blob);
                      const a = document.createElement('a');
                      a.href = url;
                      a.download = data.filename || `pedigree-${(pedigree.animal && pedigree.animal.name) || animal.name}-${new Date().toISOString().split('T')[0]}.${isPdf ? 'pdf' : 'html'}`;
                      document.body.appendChild(a);
                      a.click();
                      document.body.removeChild(a);
                      URL.revokeObjectURL(url);
                    } else {
                      alert(data.message || 'Error generating pedigree certificate. Please try again.');
                    }
                  } catch (err) {
                    console.error('Error:', err);
                    alert('Error generating pedigree certificate. Please try again.');
                  }
                }}
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                {__('Download Pedigree Certificate', 'truepaws')}
              </button>
            </div>
            <div className="pedigree-info">
              <div className="pedigree-item primary">
                <span className="pedigree-label">{__('Animal', 'truepaws')}</span>
                <span className="pedigree-value">{pedigree.animal.name}</span>
              </div>
              {pedigree.sire && (
                <div className="pedigree-item">
                  <span className="pedigree-label">{__('Sire', 'truepaws')}</span>
                  <span className="pedigree-value">{pedigree.sire.name}</span>
                </div>
              )}
              {pedigree.dam && (
                <div className="pedigree-item">
                  <span className="pedigree-label">{__('Dam', 'truepaws')}</span>
                  <span className="pedigree-value">{pedigree.dam.name}</span>
                </div>
              )}
            </div>
          </div>
        )}

        {/* AI Information Section - Distinct Styling */}
        <div className="animal-ai-section">
          <div className="ai-header">
            <div className="ai-title-group">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
              </svg>
              <h3 className="section-title">{__('AI Care Recommendations', 'truepaws')}</h3>
            </div>
            {aiCareAdvice?.cached && (
              <span className="ai-cached-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                {__('Cached for 30 days', 'truepaws')}
              </span>
            )}
          </div>
          {aiLoading && (
            <div className="ai-loading-state">
              <LoadingSpinner message={__('Loading AI advice...', 'truepaws')} />
            </div>
          )}
          {aiError && (
            <div className="ai-error-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <p>{aiError}</p>
            </div>
          )}
          {!aiLoading && !aiError && aiCareAdvice?.content && (
            <div className="ai-content" dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(formatAIContent(aiCareAdvice.content)) }} />
          )}
          {!aiLoading && !aiError && aiCareAdvice?.enabled === false && (
            <div className="ai-disabled-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <p>{__('Configure Gemini API key in Settings to enable AI recommendations.', 'truepaws')}</p>
            </div>
          )}
          {!aiLoading && !aiError && aiCareAdvice?.enabled === true && aiCareAdvice?.error && (
            <div className="ai-error-state">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
              </svg>
              <p>{aiCareAdvice.error}</p>
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
}

export default AnimalProfile;