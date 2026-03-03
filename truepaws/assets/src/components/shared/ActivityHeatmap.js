import React, { useState, useEffect } from 'react';
import { __, sprintf, _n } from '@wordpress/i18n';
import { dashboardAPI } from '../../api/client';

function ActivityHeatmap() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [tooltip, setTooltip] = useState(null);

  useEffect(() => {
    (async () => {
      try {
        const res = await dashboardAPI.getActivityHeatmap();
        if (res.data?.success) setData(res.data.activity);
      } catch (err) {
        console.error('Error loading heatmap:', err);
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  if (loading) return null;
  if (!data || Object.keys(data).length === 0) return null;

  const today = new Date();
  const startDate = new Date(today);
  startDate.setDate(startDate.getDate() - 364);
  startDate.setDate(startDate.getDate() - startDate.getDay());

  const weeks = [];
  let current = new Date(startDate);
  while (current <= today) {
    const week = [];
    for (let d = 0; d < 7; d++) {
      const dateStr = current.toISOString().split('T')[0];
      week.push({
        date: dateStr,
        count: data[dateStr] || 0,
        future: current > today,
      });
      current.setDate(current.getDate() + 1);
    }
    weeks.push(week);
  }

  const maxCount = Math.max(1, ...Object.values(data).map(Number));

  const getColor = (count, future) => {
    if (future) return 'rgba(255,255,255,0.03)';
    if (count === 0) return 'rgba(255,255,255,0.06)';
    const intensity = Math.min(count / maxCount, 1);
    if (intensity <= 0.25) return 'rgba(75, 192, 200, 0.25)';
    if (intensity <= 0.5) return 'rgba(75, 192, 200, 0.45)';
    if (intensity <= 0.75) return 'rgba(75, 192, 200, 0.7)';
    return 'rgba(75, 192, 200, 0.95)';
  };

  const monthLabels = [];
  weeks.forEach((week, i) => {
    const firstDay = new Date(week[0].date);
    if (firstDay.getDate() <= 7) {
      monthLabels.push({
        index: i,
        label: firstDay.toLocaleDateString('en-US', { month: 'short' }),
      });
    }
  });

  const totalEvents = Object.values(data).reduce((a, b) => a + Number(b), 0);

  return (
    <div className="activity-heatmap">
      <div className="heatmap-header">
        <h3 className="card-title">{__('Kennel Activity', 'truepaws')}</h3>
        <span className="heatmap-total">{sprintf(_n('%s event this year', '%s events this year', totalEvents, 'truepaws'), totalEvents)}</span>
      </div>
      <div className="heatmap-scroll">
        <div className="heatmap-months">
          {monthLabels.map((m, i) => (
            <span key={i} className="heatmap-month-label" style={{ gridColumnStart: m.index + 2 }}>
              {m.label}
            </span>
          ))}
        </div>
        <div className="heatmap-grid" style={{ position: 'relative' }}>
          <div className="heatmap-days-labels">
            <span></span><span>Mon</span><span></span><span>Wed</span><span></span><span>Fri</span><span></span>
          </div>
          <div className="heatmap-cells">
            {weeks.map((week, wi) => (
              <div key={wi} className="heatmap-week">
                {week.map((day, di) => (
                  <div
                    key={di}
                    className="heatmap-cell"
                    style={{ backgroundColor: getColor(day.count, day.future) }}
                    onMouseEnter={(e) => {
                      const rect = e.target.getBoundingClientRect();
                      setTooltip({
                        text: sprintf(_n('%s event on %s', '%s events on %s', day.count, 'truepaws'), day.count, day.date),
                        x: rect.left + rect.width / 2,
                        y: rect.top - 8,
                      });
                    }}
                    onMouseLeave={() => setTooltip(null)}
                  />
                ))}
              </div>
            ))}
          </div>
        </div>
        <div className="heatmap-legend">
          <span className="heatmap-legend-label">{__('Less', 'truepaws')}</span>
          <div className="heatmap-cell" style={{ backgroundColor: 'rgba(255,255,255,0.06)' }} />
          <div className="heatmap-cell" style={{ backgroundColor: 'rgba(75, 192, 200, 0.25)' }} />
          <div className="heatmap-cell" style={{ backgroundColor: 'rgba(75, 192, 200, 0.45)' }} />
          <div className="heatmap-cell" style={{ backgroundColor: 'rgba(75, 192, 200, 0.7)' }} />
          <div className="heatmap-cell" style={{ backgroundColor: 'rgba(75, 192, 200, 0.95)' }} />
          <span className="heatmap-legend-label">{__('More', 'truepaws')}</span>
        </div>
      </div>
      {tooltip && (
        <div
          className="heatmap-tooltip"
          style={{
            position: 'fixed',
            left: tooltip.x,
            top: tooltip.y,
            transform: 'translate(-50%, -100%)',
          }}
        >
          {tooltip.text}
        </div>
      )}
    </div>
  );
}

export default ActivityHeatmap;
