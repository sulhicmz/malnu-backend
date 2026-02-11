import React, { useState, useEffect } from 'react';
import { Search, Filter, Download, X } from 'lucide-react';

const useDebounce = (value: string, delay: number): string => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
};

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
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue, 300);

  useEffect(() => {
    onSearchChange?.(debouncedSearchValue);
  }, [debouncedSearchValue, onSearchChange]);

  const handleClearSearch = () => {
    setSearchValue('');
  };

  return (
    <div className={`flex flex-col md:flex-row justify-between gap-4 ${className}`}>
      <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600 transition-colors">
        <Search className="h-4 w-4 text-gray-500" aria-hidden="true" />
        <input
          type="text"
          placeholder={searchPlaceholder}
          className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2 bg-transparent"
          value={searchValue}
          onChange={(e) => setSearchValue(e.target.value)}
          aria-label="Search"
        />
        {searchValue && (
          <button
            onClick={handleClearSearch}
            className="ml-2 p-1 rounded-full hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-label="Clear search"
            type="button"
          >
            <X className="h-3 w-3 text-gray-400" />
          </button>
        )}
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
