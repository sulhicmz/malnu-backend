import React from 'react';
import { ArrowUp, ArrowDown, LucideIcon } from 'lucide-react';

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
        <div className={`p-3 rounded-full bg-${color}-100`} aria-hidden="true">
          {icon}
        </div>
      </div>
    </div>
  );
};

export default StatCard;
