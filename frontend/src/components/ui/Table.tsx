import React, { useState } from 'react';
import { MoreHorizontal } from 'lucide-react';
import ActionMenu, { ActionMenuItem } from './ActionMenu';

export interface TableColumn<T = any> {
  key: string;
  header: string;
  width?: string;
  align?: 'left' | 'center' | 'right';
  sortable?: boolean;
  hidden?: boolean | ((index: number) => boolean);
  className?: string;
  render?: (value: any, row: T, index: number) => React.ReactNode;
}

export interface TableProps<T = any> {
  columns: TableColumn<T>[];
  data: T[];
  loading?: boolean;
  empty?: boolean;
  emptyMessage?: string;
  loadingMessage?: string;
  className?: string;
  hoverable?: boolean;
  striped?: boolean;
  getRowKey?: (row: T, index: number) => string | number;
  getRowClassName?: (row: T, index: number) => string;
  actions?: (row: T, index: number) => ActionMenuItem[];
  actionMenuLabel?: (row: T) => string;
}

const Table = <T extends Record<string, any>>({
  columns,
  data,
  loading = false,
  empty = false,
  emptyMessage = 'Tidak ada data tersedia',
  loadingMessage = 'Memuat data...',
  className = '',
  hoverable = true,
  striped = false,
  getRowKey = (row, index) => row.id || index,
  getRowClassName,
  actions,
  actionMenuLabel,
}: TableProps<T>) => {
  const [activeActionMenu, setActiveActionMenu] = useState<number | null>(null);

  const handleKeyDown = (event: React.KeyboardEvent, callback: () => void) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      callback();
    }
  };

  const getAlignmentClass = (align?: 'left' | 'center' | 'right') => {
    switch (align) {
      case 'left':
        return 'text-left';
      case 'center':
        return 'text-center';
      case 'right':
        return 'text-right';
      default:
        return 'text-left';
    }
  };

  const isColumnHidden = (column: TableColumn<T>, index: number) => {
    if (column.hidden === undefined) return false;
    if (typeof column.hidden === 'boolean') return column.hidden;
    return column.hidden(index);
  };

  const renderCell = (column: TableColumn<T>, row: T, index: number) => {
    const value = row[column.key];
    if (column.render) {
      return column.render(value, row, index);
    }
    return value;
  };

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center py-10" role="status" aria-live="polite" aria-busy="true">
        <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500" aria-hidden="true" />
        <span className="mt-4 text-gray-600">{loadingMessage}</span>
      </div>
    );
  }

  if (empty || data.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-10 text-center" role="status" aria-live="polite">
        <div className="text-gray-400 mb-2">
          <svg className="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <p className="text-gray-500">{emptyMessage}</p>
      </div>
    );
  }

  return (
    <div className="overflow-x-auto shadow-sm rounded-lg -mx-4 px-4 sm:mx-0 sm:px-0" role="region" aria-live="polite">
      <div className="inline-block min-w-full align-middle">
        <table className={`min-w-full divide-y divide-gray-200 bg-white ${className}`}>
          <thead className="bg-gray-50">
            <tr>
              {columns.map((column, index) => {
                if (isColumnHidden(column, index)) return null;
                return (
                  <th
                    key={column.key}
                    scope="col"
                    className={`px-3 sm:px-4 md:px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap ${getAlignmentClass(column.align)} ${column.className || ''}`}
                    style={{ width: column.width }}
                  >
                    {column.header}
                  </th>
                );
              })}
              {actions && (
                <th scope="col" className="px-3 sm:px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                  <span className="sr-only">Aksi</span>
                </th>
              )}
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {data.map((row, rowIndex) => {
              const rowKey = getRowKey(row, rowIndex);
              const rowClassName = getRowClassName ? getRowClassName(row, rowIndex) : '';
              
              return (
                <tr
                  key={rowKey}
                  className={`${hoverable ? 'hover:bg-gray-50' : ''} ${striped && rowIndex % 2 === 1 ? 'bg-gray-50' : ''} ${rowClassName}`}
                >
                  {columns.map((column, colIndex) => {
                    if (isColumnHidden(column, colIndex)) return null;
                    return (
                      <td
                        key={column.key}
                        className={`px-3 sm:px-4 md:px-6 py-4 text-sm text-gray-500 ${column.className || ''}`}
                      >
                        {renderCell(column, row, rowIndex)}
                      </td>
                    );
                  })}
                  {actions && (
                    <td className="px-3 sm:px-4 md:px-6 py-4 text-sm font-medium relative">
                      <button
                        onClick={() => setActiveActionMenu(activeActionMenu === rowIndex ? null : rowIndex)}
                        onKeyDown={(e) => handleKeyDown(e, () => setActiveActionMenu(activeActionMenu === rowIndex ? null : rowIndex))}
                        className="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-1 ml-auto"
                        aria-label={actionMenuLabel ? actionMenuLabel(row) : 'Tampilkan menu aksi'}
                        aria-expanded={activeActionMenu === rowIndex}
                        aria-controls={`action-menu-${rowKey}`}
                        aria-haspopup="true"
                      >
                        <MoreHorizontal className="h-5 w-5" aria-hidden="true" />
                      </button>
                      {activeActionMenu === rowIndex && (
                        <div className="relative z-10" id={`action-menu-${rowKey}`}>
                          <ActionMenu
                            isOpen={activeActionMenu === rowIndex}
                            onClose={() => setActiveActionMenu(null)}
                            items={actions(row, rowIndex)}
                            triggerLabel={actionMenuLabel ? actionMenuLabel(row) : 'Menu aksi'}
                          />
                        </div>
                      )}
                    </td>
                  )}
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Table;
