import React, { useMemo } from 'react';
import { __ } from '@wordpress/i18n';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

function WeightGrowthChart({ timeline }) {
  const weightData = useMemo(() => {
    if (!timeline || !Array.isArray(timeline)) return [];

    return timeline
      .filter(e => e.event_type === 'weight')
      .map(e => {
        const meta = typeof e.meta_data === 'string' ? JSON.parse(e.meta_data) : (e.meta_data || {});
        const weight = parseFloat(meta.weight || meta.value || e.title?.match(/[\d.]+/)?.[0]);
        if (isNaN(weight)) return null;
        return {
          date: e.event_date,
          weight,
          unit: meta.unit || 'lbs',
        };
      })
      .filter(Boolean)
      .sort((a, b) => new Date(a.date) - new Date(b.date));
  }, [timeline]);

  if (weightData.length < 2) return null;

  const data = {
    labels: weightData.map(d => {
      const dt = new Date(d.date);
      return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }),
    datasets: [{
      label: `Weight (${weightData[0].unit})`,
      data: weightData.map(d => d.weight),
      borderColor: '#4bc0c8',
      backgroundColor: 'rgba(75, 192, 200, 0.15)',
      pointBackgroundColor: '#4bc0c8',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5,
      pointHoverRadius: 7,
      tension: 0.3,
      fill: true,
    }],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(15,32,39,0.95)',
        titleColor: '#fff',
        bodyColor: 'rgba(255,255,255,0.85)',
        padding: 12,
        cornerRadius: 8,
        callbacks: {
          label: (ctx) => ` ${ctx.parsed.y} ${weightData[0].unit}`,
        },
      },
    },
    scales: {
      x: {
        grid: { color: 'rgba(255,255,255,0.06)' },
        ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 12 } },
      },
      y: {
        grid: { color: 'rgba(255,255,255,0.06)' },
        ticks: {
          color: 'rgba(255,255,255,0.6)',
          font: { size: 12 },
          callback: (v) => `${v}`,
        },
        beginAtZero: true,
      },
    },
  };

  const latestWeight = weightData[weightData.length - 1];
  const firstWeight = weightData[0];
  const totalGain = (latestWeight.weight - firstWeight.weight).toFixed(1);

  return (
    <div className="weight-growth-card">
      <div className="weight-growth-header">
        <div className="weight-growth-title-group">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
          </svg>
          <h3 className="section-title">{__('Weight Growth', 'truepaws')}</h3>
        </div>
        <div className="weight-growth-summary">
          <span className="weight-current">{latestWeight.weight} {latestWeight.unit}</span>
          <span className={`weight-change ${parseFloat(totalGain) >= 0 ? 'positive' : 'negative'}`}>
            {parseFloat(totalGain) >= 0 ? '+' : ''}{totalGain} {latestWeight.unit}
          </span>
        </div>
      </div>
      <div style={{ height: 220, position: 'relative' }}>
        <Line data={data} options={options} />
      </div>
    </div>
  );
}

export default WeightGrowthChart;
