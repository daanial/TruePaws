import React from 'react';

/**
 * Placeholder shown when an animal has no image or when the image fails to load.
 * Uses an SVG with a dog silhouette icon.
 */
const PLACEHOLDER_SVG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect fill='rgba(255,255,255,0.05)' width='200' height='200' rx='16'/%3E%3Cpath fill='rgba(255,255,255,0.25)' d='M100 60c-22 0-40 18-40 40s18 40 40 40 40-18 40-40-18-40-40-40zm0 65c-14 0-25-11-25-25s11-25 25-25 25 11 25 25-11 25-25 25z'/%3E%3Cpath fill='rgba(255,255,255,0.15)' d='M70 155c0-17 13-30 30-30s30 13 30 30v5H70v-5z'/%3E%3Ctext x='50%25' y='85%25' dominant-baseline='middle' text-anchor='middle' font-family='system-ui,sans-serif' font-size='12' fill='rgba(255,255,255,0.5)'%3ENo Photo%3C/text%3E%3C/svg%3E";

function AnimalImagePlaceholder({ className = 'animal-featured-image', alt = 'No photo' }) {
  return (
    <img
      src={PLACEHOLDER_SVG}
      alt={alt}
      className={className}
      role="img"
    />
  );
}

export default AnimalImagePlaceholder;
export { PLACEHOLDER_SVG };
