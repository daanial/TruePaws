import React from 'react';
import { Link } from 'react-router-dom';
import { format, formatDistanceToNow } from 'date-fns';

function LatestEvents({ events, loading }) {
  const getEventIcon = (eventType) => {
    const icons = {
      birth: '👶',
      vaccine: '💉',
      heat: '🔥',
      mating: '💕',
      whelping: '🐾',
      weight: '⚖️',
      vet_visit: '🏥',
      note: '📝',
      sold: '💰',
      registration: '📋',
    };
    return icons[eventType] || '📌';
  };

  const getEventTypeLabel = (eventType) => {
    const labels = {
      birth: 'Birth',
      vaccine: 'Vaccination',
      heat: 'Heat Cycle',
      mating: 'Mating',
      whelping: 'Whelping',
      weight: 'Weight',
      vet_visit: 'Vet Visit',
      note: 'Note',
      sold: 'Sold',
      registration: 'Registration',
    };
    return labels[eventType] || eventType;
  };

  const getEventColor = (eventType) => {
    const colors = {
      birth: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
      vaccine: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      heat: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
      mating: 'linear-gradient(135deg, #ff9a56 0%, #ffce54 100%)',
      whelping: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      weight: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      vet_visit: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      note: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
      sold: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
      registration: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    };
    return colors[eventType] || 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';
  };

  if (loading) {
    return (
      <div className="latest-events">
        <h3 className="card-title">Latest Events</h3>
        <div className="events-loading">
          <p>Loading events...</p>
        </div>
      </div>
    );
  }

  if (!events || events.length === 0) {
    return (
      <div className="latest-events">
        <h3 className="card-title">Latest Events</h3>
        <div className="events-empty">
          <p>No recent events to display.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="latest-events">
      <h3 className="card-title">Latest Events</h3>
      <div className="events-list">
        {events.map((event) => {
          const eventDate = new Date(event.date);
          const timeAgo = formatDistanceToNow(eventDate, { addSuffix: true });
          
          return (
            <div key={event.id} className="event-item">
              <div 
                className="event-icon" 
                style={{ background: getEventColor(event.event_type) }}
              >
                {getEventIcon(event.event_type)}
              </div>
              <div className="event-content">
                <div className="event-header">
                  <h4 className="event-title">{event.title}</h4>
                  <span className="event-type">{getEventTypeLabel(event.event_type)}</span>
                </div>
                <div className="event-details">
                  {event.animal_id ? (
                    <Link 
                      to={`/animals/${event.animal_id}`} 
                      className="event-animal-link"
                    >
                      {event.animal_call_name || event.animal_name}
                    </Link>
                  ) : (
                    <span className="event-animal">{event.animal_name}</span>
                  )}
                  {event.meta_data && event.meta_data.puppy_count && (
                    <span className="event-meta">
                      {event.meta_data.puppy_count} {event.meta_data.puppy_count === 1 ? 'puppy' : 'puppies'}
                    </span>
                  )}
                </div>
                <div className="event-footer">
                  <span className="event-date">{format(eventDate, 'MMM d, yyyy')}</span>
                  <span className="event-time-ago">{timeAgo}</span>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default LatestEvents;
