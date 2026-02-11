import React from 'react';
import { Search, Filter, Download, LucideIcon } from 'lucide-react';

export interface FilterOption {
  value: string;
  label: string;
}

interface SearchFilterProps {
  searchPlaceholder?: string;
  filterOptions?: FilterOption[];
  filterLabel?: string;
  showExport?: boolean;
  onSearchChange?: (value: string) => void;
  onFilterChange?: (value: string) => void;
  onExportClick?: () => void;
  className?: string;
}

const SearchFilter: React.FC<SearchFilterProps> = ({
  searchPlaceholder = 'Search...',
  filterOptions,
  filterLabel = 'All',
  showExport = false,
  onSearchChange,
  onFilterChange,
  onExportClick,
  className = ''
}) => {
  return (
    <div className={`flex flex-col md:flex-row justify-between gap-4 ${className}`}>
      <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600 transition-colors">
        <Search className="h-4 w-4 text-gray-500" aria-hidden="true" />
        <input
          type="text"
          placeholder={searchPlaceholder}
          className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2 bg-transparent"
          onChange={(e) => onSearchChange?.(e.target.value)}
          aria-label="Search"
        />
      </div>
      <div className="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
        <button
          className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center justify-center hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
          aria-label="Filter options"
        >
          <Filter className="h-4 w-4 mr-2" aria-hidden="true" />
          Filter
        </button>
        {filterOptions && (
          <select
            className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500"
            onChange={(e) => onFilterChange?.(e.target.value)}
            aria-label={filterLabel}
          >
            <option value="">{filterLabel}</option>
            {filterOptions.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        )}
        {showExport && (
          <button
            onClick={onExportClick}
            className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center justify-center hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            aria-label="Export data"
          >
            <Download className="h-4 w-4 mr-2" aria-hidden="true" />
            Export
          </button>
        )}
      </div>
    </div>
  );
};

export default SearchFilter;
