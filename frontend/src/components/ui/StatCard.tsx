import React from 'react';
import { LucideIcon } from 'lucide-react';
import { ArrowUp, ArrowDown, Minus } from 'lucide-react';

export type TrendDirection = 'up' | 'down' | 'neutral';

export type StatCardColor = 'blue' | 'purple' | 'green' | 'orange' | 'red' | 'yellow' | 'indigo' | 'pink';

export interface StatCardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: TrendDirection;
  trendLabel?: string;
  icon?: React.ReactNode | LucideIcon;
  color?: StatCardColor;
  onClick?: () => void;
  loading?: boolean;
}

const StatCard: React.FC<StatCardProps> = ({
  title,
  value,
  change,
  trend = 'neutral',
  trendLabel = 'vs bulan lalu',
  icon,
  color = 'blue',
  onClick,
  loading = false,
}) => {
  const colorConfig: Record<StatCardColor, { bg: string; text: string; iconText: string }> = {
    blue: { bg: 'bg-blue-100', text: 'text-blue-600', iconText: 'text-blue-500' },
    purple: { bg: 'bg-purple-100', text: 'text-purple-600', iconText: 'text-purple-500' },
    green: { bg: 'bg-green-100', text: 'text-green-600', iconText: 'text-green-500' },
    orange: { bg: 'bg-orange-100', text: 'text-orange-600', iconText: 'text-orange-500' },
    red: { bg: 'bg-red-100', text: 'text-red-600', iconText: 'text-red-500' },
    yellow: { bg: 'bg-yellow-100', text: 'text-yellow-600', iconText: 'text-yellow-500' },
    indigo: { bg: 'bg-indigo-100', text: 'text-indigo-600', iconText: 'text-indigo-500' },
    pink: { bg: 'bg-pink-100', text: 'text-pink-600', iconText: 'text-pink-500' },
  };

  const styles = colorConfig[color];

  const getTrendIcon = (direction: TrendDirection) => {
    switch (direction) {
      case 'up':
        return <ArrowUp className="h-3 w-3 mr-1" aria-hidden="true" />;
      case 'down':
        return <ArrowDown className="h-3 w-3 mr-1" aria-hidden="true" />;
      case 'neutral':
        return <Minus className="h-3 w-3 mr-1" aria-hidden="true" />;
    }
  };

  const getTrendColor = (direction: TrendDirection) => {
    switch (direction) {
      case 'up':
        return 'text-green-600';
      case 'down':
        return 'text-red-600';
      case 'neutral':
        return 'text-gray-600';
    }
  };

  if (loading) {
    return (
      <div className="bg-white p-6 rounded-lg shadow-sm">
        <div className="animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
          <div className="h-8 bg-gray-200 rounded w-3/4 mb-2"></div>
          <div className="h-4 bg-gray-200 rounded w-1/3"></div>
        </div>
      </div>
    );
  }

  const cardContent = (
    <div className="bg-white p-6 rounded-lg shadow-sm">
      <div className="flex justify-between items-start">
        <div className="flex-1">
          <p className="text-sm text-gray-500">{title}</p>
          <h3 className="text-2xl font-bold text-gray-800 my-1">{value}</h3>
          {change && (
            <div className="flex items-center">
              <span className={`text-xs font-medium flex items-center ${getTrendColor(trend)}`}>
                {getTrendIcon(trend)}
                {change}
              </span>
              {trendLabel && <span className="text-xs text-gray-500 ml-1">{trendLabel}</span>}
            </div>
          )}
        </div>
        {icon && (
          <div className={`p-3 rounded-full ${styles.bg} flex-shrink-0`}>
            {React.isValidElement(icon) ? icon : React.createElement(icon as LucideIcon, { className: `h-6 w-6 ${styles.iconText}`, 'aria-hidden': true })}
          </div>
        )}
      </div>
    </div>
  );

  if (onClick) {
    return (
      <div
        onClick={onClick}
        role="button"
        tabIndex={0}
        onKeyDown={(e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            onClick();
          }
        }}
        className="cursor-pointer hover:shadow-md transition-shadow duration-200"
        aria-label={`${title}: ${value}${change ? `, ${change} ${trendLabel}` : ''}`}
      >
        {cardContent}
      </div>
    );
  }

  return cardContent;
};

export default StatCard;
