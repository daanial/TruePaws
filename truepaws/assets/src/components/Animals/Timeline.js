import React from 'react';
import { format } from 'date-fns';

function Timeline({ events }) {
  const getEventIcon = (eventType) => {
    const icons = {
      birth: '👶',
      vaccine: '💉',
      heat: '🔥',
      mating: '💕',
      whelping: '🐾',
      weight: '⚖️',
      vet_visit: '🏥',
      note: '📝'
    };
    return icons[eventType] || '📌';
  };

  const getEventTypeLabel = (eventType) => {
    const labels = {
      birth: 'Birth',
      vaccine: 'Vaccine',
      heat: 'Heat Cycle',
      mating: 'Mating',
      whelping: 'Whelping',
      weight: 'Weight',
      vet_visit: 'Vet Visit',
      note: 'Note'
    };
    return labels[eventType] || eventType;
  };

  const parseMetaData = (metaData) => {
    if (!metaData) return {};
    if (typeof metaData === 'string') {
      try {
        const parsed = JSON.parse(metaData);
        return typeof parsed === 'object' && parsed !== null ? parsed : {};
      } catch {
        return {};
      }
    }
    return typeof metaData === 'object' ? metaData : {};
  };

  const formatMetaDisplay = (metaData, eventType) => {
    const meta = parseMetaData(metaData);
    if (Object.keys(meta).length === 0) return null;

    const labels = {
      price: 'Price',
      notes: 'Notes',
      contact_id: 'Contact',
      sale_type: 'Sale Type',
      puppy_count: 'Puppies',
      litter_id: 'Litter'
    };

    return Object.entries(meta)
      .filter(([, value]) => value !== '' && value !== null)
      .map(([key, value]) => ({
        label: labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
        value
      }));
  };

  if (!events || events.length === 0) {
    return <p className="timeline-empty">No events recorded yet.</p>;
  }

  return (
    <div className="timeline">
      {events.map(event => (
        <div key={event.id} className="timeline-item">
          <div className="timeline-marker">
            <span className="timeline-icon">{getEventIcon(event.event_type)}</span>
          </div>
          <div className="timeline-content">
            <div className="timeline-header">
              <h5 className="timeline-title">{event.title}</h5>
              <span className="timeline-type">{getEventTypeLabel(event.event_type)}</span>
            </div>
            <div className="timeline-date">
              {format(new Date(event.event_date), 'MMM d, yyyy')}
            </div>
            {(() => {
              const metaItems = formatMetaDisplay(event.meta_data, event.event_type);
              if (!metaItems || metaItems.length === 0) return null;
              return (
                <div className="timeline-meta">
                  {metaItems.map(({ label, value }) => (
                    <span key={label} className="meta-item">
                      <strong>{label}:</strong> {value}
                    </span>
                  ))}
                </div>
              );
            })()}
          </div>
        </div>
      ))}
    </div>
  );
}

export default Timeline;