import React, { useState, useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import {
  Chart as ChartJS,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend,
} from 'chart.js';
import { Doughnut, Bar } from 'react-chartjs-2';
import { dashboardAPI } from '../../api/client';

ChartJS.register(ArcElement, CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const CHART_COLORS = [
  '#4bc0c8', '#6366f1', '#f59e0b', '#ef4444', '#10b981',
  '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#06b6d4',
];

function BreedDistributionChart({ breedsByCount }) {
  if (!breedsByCount || breedsByCount.length === 0) {
    return <p className="breed-empty">{__('No breed data yet. Add animals to see distribution.', 'truepaws')}</p>;
  }

  const data = {
    labels: breedsByCount.map(b => b.breed || 'Unknown'),
    datasets: [{
      data: breedsByCount.map(b => parseInt(b.count, 10)),
      backgroundColor: breedsByCount.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]),
      borderColor: 'rgba(255,255,255,0.15)',
      borderWidth: 2,
      hoverOffset: 8,
    }],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: 'rgba(255,255,255,0.85)',
          padding: 16,
          usePointStyle: true,
          pointStyleWidth: 12,
          font: { size: 13 },
        },
      },
      tooltip: {
        backgroundColor: 'rgba(15,32,39,0.95)',
        titleColor: '#fff',
        bodyColor: 'rgba(255,255,255,0.85)',
        padding: 12,
        cornerRadius: 8,
        callbacks: {
          label: (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
          },
        },
      },
    },
  };

  return (
    <div style={{ height: 280, position: 'relative' }}>
      <Doughnut data={data} options={options} />
    </div>
  );
}

function SalesChart() {
  const [salesData, setSalesData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      try {
        const res = await dashboardAPI.getSalesReport();
        if (res.data?.success) setSalesData(res.data.report);
      } catch (err) {
        console.error('Error loading sales chart:', err);
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  if (loading) return <p style={{ color: 'rgba(255,255,255,0.6)', fontSize: 14 }}>{__('Loading sales data...', 'truepaws')}</p>;
  if (!salesData || !salesData.salesByMonth || salesData.salesByMonth.length === 0) {
    return <p className="breed-empty">{__('No sales data yet.', 'truepaws')}</p>;
  }

  const months = [...salesData.salesByMonth].reverse().slice(-12);

  const data = {
    labels: months.map(m => {
      const [y, mo] = m.month.split('-');
      const d = new Date(parseInt(y, 10), parseInt(mo, 10) - 1);
      return d.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
    }),
    datasets: [{
      label: __('Revenue', 'truepaws'),
      data: months.map(m => m.revenue),
      backgroundColor: 'rgba(75, 192, 200, 0.6)',
      borderColor: '#4bc0c8',
      borderWidth: 2,
      borderRadius: 6,
      hoverBackgroundColor: 'rgba(75, 192, 200, 0.85)',
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
          label: (ctx) => ` $${ctx.parsed.y.toLocaleString()}`,
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
          callback: (v) => `$${v}`,
        },
        beginAtZero: true,
      },
    },
  };

  return (
    <div>
      <div className="sales-chart-summary">
        <span className="sales-chart-total">${salesData.totalRevenue?.toLocaleString() || 0}</span>
        <span className="sales-chart-label">{sprintf(__('Total Revenue (%s sales)', 'truepaws'), salesData.totalSales || 0)}</span>
      </div>
      <div style={{ height: 220, position: 'relative' }}>
        <Bar data={data} options={options} />
      </div>
    </div>
  );
}

export { BreedDistributionChart, SalesChart };
