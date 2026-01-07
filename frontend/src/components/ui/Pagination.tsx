import React from 'react';

export interface PaginationProps {
  currentPage: number;
  totalPages: number;
  totalItems: number;
  itemsPerPage: number;
  onPageChange: (page: number) => void;
  showInfo?: boolean;
  infoLabel?: string;
  maxVisiblePages?: number;
}

const Pagination: React.FC<PaginationProps> = ({
  currentPage,
  totalPages,
  totalItems,
  itemsPerPage,
  onPageChange,
  showInfo = true,
  infoLabel = 'Menampilkan',
  maxVisiblePages = 5,
}) => {
  const getVisiblePages = () => {
    const pages: (number | string)[] = [];
    const startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (startPage > 1) {
      pages.push(1);
      if (startPage > 2) {
        pages.push('...');
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      pages.push(i);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        pages.push('...');
      }
      pages.push(totalPages);
    }

    return pages;
  };

  const visiblePages = getVisiblePages();

  const handleKeyDown = (event: React.KeyboardEvent, page: number | string) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      if (typeof page === 'number') {
        onPageChange(page);
      }
    }
  };

  const startItem = (currentPage - 1) * itemsPerPage + 1;
  const endItem = Math.min(currentPage * itemsPerPage, totalItems);

  return (
    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4" role="navigation" aria-label="Pagination">
      {showInfo && totalItems > 0 && (
        <div className="text-sm text-gray-500">
          {infoLabel} {startItem} sampai {endItem} dari {totalItems} item
        </div>
      )}
      
      {totalPages > 1 && (
        <div className="flex flex-wrap justify-center gap-2" role="list">
          <button
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage === 1}
            onKeyDown={(e) => handleKeyDown(e, 'prev')}
            className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            role="listitem"
            aria-label="Halaman sebelumnya"
            aria-disabled={currentPage === 1}
            tabIndex={currentPage === 1 ? -1 : 0}
          >
            &larr; Sebelumnya
          </button>

          {visiblePages.map((page, index) => (
            <button
              key={index}
              onClick={() => typeof page === 'number' && onPageChange(page)}
              disabled={typeof page !== 'number'}
              onKeyDown={(e) => handleKeyDown(e, page)}
              className={`px-3 py-1 rounded-md text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 ${
                page === currentPage
                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                  : typeof page === 'number'
                  ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                  : 'border border-transparent bg-transparent text-gray-500 cursor-default'
              } ${typeof page !== 'number' ? 'cursor-default' : ''}`}
              role="listitem"
              aria-label={typeof page === 'number' ? `Halaman ${page}` : undefined}
              aria-current={page === currentPage ? 'page' : undefined}
              aria-disabled={typeof page !== 'number'}
              tabIndex={typeof page !== 'number' || page === currentPage ? -1 : 0}
            >
              {page}
            </button>
          ))}

          <button
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
            onKeyDown={(e) => handleKeyDown(e, 'next')}
            className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            role="listitem"
            aria-label="Halaman selanjutnya"
            aria-disabled={currentPage === totalPages}
            tabIndex={currentPage === totalPages ? -1 : 0}
          >
            Selanjutnya &rarr;
          </button>
        </div>
      )}
    </div>
  );
};

export default Pagination;
