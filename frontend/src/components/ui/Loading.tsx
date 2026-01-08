import React from 'react';

interface LoadingProps {
  size?: 'sm' | 'md' | 'lg';
  message?: string;
  fullScreen?: boolean;
}

const Loading: React.FC<LoadingProps> = ({ size = 'md', message, fullScreen = false }) => {
  const sizeStyles = {
    sm: 'h-6 w-6 border-2',
    md: 'h-10 w-10 border-t-2 border-b-2',
    lg: 'h-16 w-16 border-4',
  };

  const containerStyles = fullScreen
    ? 'fixed inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50'
    : 'flex items-center justify-center';

  return (
    <div className={containerStyles} role="status" aria-live="polite" aria-busy="true">
      <div className="flex flex-col items-center">
        <div className={`animate-spin rounded-full border-blue-500 ${sizeStyles[size]}`} aria-hidden="true" />
        {message && <span className="mt-4 text-gray-600">{message}</span>}
      </div>
    </div>
  );
};

export default Loading;
