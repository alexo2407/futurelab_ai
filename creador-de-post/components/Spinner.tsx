import React from 'react';

interface SpinnerProps {
  size?: 'sm' | 'md' | 'lg';
}

const Spinner: React.FC<SpinnerProps> = ({ size = 'md' }) => {
  const sizeClasses = {
    sm: 'w-5 h-5',
    md: 'w-6 h-6',
    lg: 'w-12 h-12',
  };

  return (
    <div
      className={`animate-spin rounded-full border-4 ${sizeClasses[size]} border-gray-300 border-t-blue-600`}
      role="status"
      aria-live="polite"
    >
      <span className="sr-only">Cargando...</span>
    </div>
  );
};

export default Spinner;