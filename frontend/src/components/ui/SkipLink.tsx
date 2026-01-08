import React from 'react';

export interface SkipLinkProps {
  href?: string;
  label?: string;
  className?: string;
}

const SkipLink: React.FC<SkipLinkProps> = ({
  href = '#main-content',
  label = 'Lanjut ke konten utama',
  className = '',
}) => {
  return (
    <a
      href={href}
      className={`sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded focus:font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ${className}`}
    >
      {label}
    </a>
  );
};

export default SkipLink;
