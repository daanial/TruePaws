import React from 'react';

const MILESTONES_DOG = [
  { day: 7, label: 'Implantation begins' },
  { day: 21, label: 'Heartbeat detectable' },
  { day: 28, label: 'Ultrasound possible' },
  { day: 45, label: 'X-ray possible' },
  { day: 56, label: 'Nesting behavior' },
  { day: 58, label: 'Prepare whelping box' },
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
    trimesterLabel = 'Early (Weeks 1-3)';
  } else if (elapsedDays <= 42) {
    trimester = 2;
    trimesterLabel = 'Mid (Weeks 4-6)';
  } else {
    trimester = 3;
    trimesterLabel = 'Late (Weeks 7-9)';
  }

  const upcomingMilestones = MILESTONES_DOG.filter(m => m.day > elapsedDays && m.day <= totalDays);

  return (
    <div className="pregnancy-tracker-card">
      <div className="pregnancy-tracker-header">
        <div className="pregnancy-tracker-title">
          <span className="pregnancy-icon">🤰</span>
          <span>Pregnancy Progress</span>
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
              title={`Day ${m.day}: ${m.label}`}
            />
          ))}
        </div>
        <div className="pregnancy-progress-labels">
          <span>Day {elapsedDays}</span>
          <span>Day {totalDays}</span>
        </div>
      </div>

      <div className="pregnancy-stats-row">
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{remainingDays}</span>
          <span className="pregnancy-stat-label">Days Left</span>
        </div>
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{Math.round(progress)}%</span>
          <span className="pregnancy-stat-label">Complete</span>
        </div>
        <div className="pregnancy-stat">
          <span className="pregnancy-stat-value">{litter.expected_whelping_date}</span>
          <span className="pregnancy-stat-label">Due Date</span>
        </div>
      </div>

      {upcomingMilestones.length > 0 && (
        <div className="pregnancy-milestones">
          <span className="pregnancy-milestones-title">Upcoming Milestones</span>
          {upcomingMilestones.slice(0, 3).map((m, i) => (
            <div key={i} className="pregnancy-milestone-item">
              <span className="milestone-day">Day {m.day}</span>
              <span className="milestone-label">{m.label}</span>
              <span className="milestone-in">{m.day - elapsedDays}d away</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default PregnancyTracker;
