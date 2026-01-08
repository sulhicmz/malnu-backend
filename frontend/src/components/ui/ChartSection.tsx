import React from 'react';

export interface ChartSectionProps {
  title: string;
  children: React.ReactNode;
  actions?: React.ReactNode;
  height?: string | number;
  className?: string;
  loading?: boolean;
}

const ChartSection: React.FC<ChartSectionProps> = ({
  title,
  children,
  actions,
  height = '16rem',
  className = '',
  loading = false,
}) => {
  const heightClass = typeof height === 'number' ? `${height}px` : height;

  return (
    <div className={`bg-white p-5 rounded-lg shadow-sm ${className}`}>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-lg font-medium text-gray-800">{title}</h2>
        {actions && <div className="flex items-center space-x-2">{actions}</div>}
      </div>
      {loading ? (
        <div className="flex items-center justify-center" style={{ height: heightClass }} role="status" aria-live="polite" aria-busy="true">
          <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500" aria-hidden="true"></div>
        </div>
      ) : (
        <div style={{ height: heightClass }} aria-hidden="true">
          {children}
        </div>
      )}
    </div>
  );
};

export default ChartSection;
