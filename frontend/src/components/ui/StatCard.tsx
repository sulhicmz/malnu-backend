import React from 'react';
import { ArrowUp, ArrowDown } from 'lucide-react';
// Color mapping for dynamic backgrounds (Tailwind can't process dynamic classes)
const colorMap: Record<string, string> = {
  blue: '#dbeafe',
  purple: '#f3e8ff',
  green: '#dcfce7',
  orange: '#ffedd5',
  red: '#fee2e2',
  yellow: '#fef9c3',
  indigo: '#e0e7ff',
  pink: '#fce7f3',
};

interface StatCardProps {
  title: string;
  value: string;
  change: string;
  isIncrease: boolean | null;
  icon: React.ReactNode;
  color: string;
}

const StatCard: React.FC<StatCardProps> = ({ title, value, change, isIncrease, icon, color }) => {
  return (
    <div className="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
      <div className="flex justify-between items-start">
        <div>
          <p className="text-sm text-gray-500">{title}</p>
          <h3 className="text-2xl font-bold text-gray-800 my-1">{value}</h3>
          {isIncrease !== null && (
            <div className="flex items-center">
              <span className={`text-xs ${isIncrease ? 'text-green-600' : 'text-red-600'} font-medium flex items-center`} aria-label={`${isIncrease ? 'Increased' : 'Decreased'} by ${change} compared to last month`}>
                {isIncrease ? <ArrowUp className="h-3 w-3 mr-1" aria-hidden="true" /> : <ArrowDown className="h-3 w-3 mr-1" aria-hidden="true" />}
                {change}
              </span>
              <span className="text-xs text-gray-500 ml-1">vs last month</span>
            </div>
          )}
        </div>
        <div
          className="p-3 rounded-full"
          style={{ backgroundColor: colorMap[color] || colorMap.blue }}
          aria-hidden="true"
        >
          {icon}
        </div>
      </div>
    </div>
  );
};

export default StatCard;
