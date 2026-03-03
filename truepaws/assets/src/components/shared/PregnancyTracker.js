import React from 'react';
import { __, sprintf } from '@wordpress/i18n';

const getMilestones = () => [
  { day: 7, label: __('Implantation begins', 'truepaws') },
  { day: 21, label: __('Heartbeat detectable', 'truepaws') },
  { day: 28, label: __('Ultrasound possible', 'truepaws') },
  { day: 45, label: __('X-ray possible', 'truepaws') },
  { day: 56, label: __('Nesting behavior', 'truepaws') },
  { day: 58, label: __('Prepare whelping box', 'truepaws') },
];

function PregnancyTracker({ litter }) {
  if (!litter || !litter.mating_date || !litter.expected_whelping_date || litter.actual_whelping_date) {
    return null;
  }

  const matingDate = new Date(litter.mating_date);
  const expectedDate = new Date(litter.expected_whelping_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const totalDays = Math.round((expectedDate - matingDate) / (1000 * 60 * 60 * 24));
  const elapsedDays = Math.round((today - matingDate) / (1000 * 60 * 60 * 24));
  const remainingDays = Math.max(0, totalDays - elapsedDays);
  const progress = Math.min(100, Math.max(0, (elapsedDays / totalDays) * 100));

  let trimester, trimesterLabel;
  if (elapsedDays <= 21) {
    trimester = 1;
    trimesterLabel = __('Early (Weeks 1-3)', 'truepaws');
  } else if (elapsedDays <= 42) {
    trimester = 2;
    trimesterLabel = __('Mid (Weeks 4-6)', 'truepaws');
  } else {
    trimester = 3;
    trimesterLabel = __('Late (Weeks 7-9)', 'truepaws');
  }

  const MILESTONES_DOG = getMilestones();
  const upcomingMilestones = MILESTONES_DOG.filter(m => m.day > elapsedDays && m.day <= totalDays);

  return (
    <div className="pregnancy-tracker-card">
      <div className="pregnancy-tracker-header">
        <div className="pregnancy-tracker-title">
          <span className="pregnancy-icon">🤰</span>
          <span>{__('Pregnancy Progress', 'truepaws')}</span>
        </div>
        <span className="pregnancy-trimester-badge" data-trimester={trimester}>
          {trimesterLabel}
        </span>
      </div>

      <div className="pregnancy-progress-bar-container">
        <div className="pregnancy-progress-bar">
          <div
            className="pregnancy-progress-fill"
            style={{ width: `${progress}%` }}
          />
          {MILESTONES_DOG.filter(m => m.day <= totalDays).map((m, i) => (
            <div
              key={i}
              className={`pregnancy-milestone-dot ${m.day <= elapsedDays ? 'passed' : ''}`}
              style={{ left: `${(m.day / totalDays) * 100}%` }}
              title={sprintf(__('Day %s: %s', 'truepaws'), m.day, m.label)}
            />
          ))}
        </div>
        <div className="pregnancy-progress-labels">
          <span>{sprintf(__('Day %s', 'truepaws'), elapsedDays)}</span>
          <span>{sprintf(__('Day %s', 'truepaws'), totalDays)}</span>
        </div>
      </div>

      <div className="pregnancy-stats-row">
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{remainingDays}</span>
          <span className="pregnancy-stat-label">{__('Days Left', 'truepaws')}</span>
        </div>
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{Math.round(progress)}%</span>
          <span className="pregnancy-stat-label">{__('Complete', 'truepaws')}</span>
        </div>
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{litter.expected_whelping_date}</span>
          <span className="pregnancy-stat-label">{__('Due Date', 'truepaws')}</span>
        </div>
      </div>

      {upcomingMilestones.length > 0 && (
        <div className="pregnancy-milestones">
          <span className="pregnancy-milestones-title">{__('Upcoming Milestones', 'truepaws')}</span>
          {upcomingMilestones.slice(0, 3).map((m, i) => (
            <div key={i} className="pregnancy-milestone-item">
              <span className="milestone-day">{sprintf(__('Day %s', 'truepaws'), m.day)}</span>
              <span className="milestone-label">{m.label}</span>
              <span className="milestone-in">{sprintf(__('%sd away', 'truepaws'), m.day - elapsedDays)}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default PregnancyTracker;
