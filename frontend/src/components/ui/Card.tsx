import React from 'react';

export interface CardProps {
  children: React.ReactNode;
  className?: string;
  padding?: 'none' | 'sm' | 'md' | 'lg';
  shadow?: 'none' | 'sm' | 'md' | 'lg';
  hover?: boolean;
  focusable?: boolean;
  onClick?: () => void;
  role?: string;
  'aria-label'?: string;
}

const Card: React.FC<CardProps> = ({
  children,
  className = '',
  padding = 'md',
  shadow = 'sm',
  hover = false,
  focusable = false,
  onClick,
  role,
  'aria-label': ariaLabel,
}) => {
  const baseClasses = 'bg-white rounded-lg transition-all duration-200';

  const paddingClasses = {
    none: '',
    sm: 'p-4',
    md: 'p-5',
    lg: 'p-6',
  };

  const shadowClasses = {
    none: '',
    sm: 'shadow-sm',
    md: 'shadow-md',
    lg: 'shadow-lg',
  };

  const interactiveClasses = onClick || focusable
    ? `${hover ? 'hover:shadow-md' : ''} ${focusable ? 'focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer' : ''}`
    : '';

  const cardElement = onClick ? 'button' : 'div';

  const cardProps = {
    className: `${baseClasses} ${paddingClasses[padding]} ${shadowClasses[shadow]} ${interactiveClasses} ${className}`,
    onClick: onClick && focusable ? onClick : undefined,
    role: role || (onClick ? 'button' : undefined),
    'aria-label': ariaLabel,
  };

  const Component = cardElement as keyof JSX.IntrinsicElements;

  return (
    <Component {...cardProps}>
      {children}
    </Component>
  );
};

export interface CardHeaderProps {
  children: React.ReactNode;
  className?: string;
}

export const CardHeader: React.FC<CardHeaderProps> = ({ children, className = '' }) => {
  return (
    <div className={`border-b border-gray-200 pb-4 mb-4 ${className}`}>
      {children}
    </div>
  );
};

export interface CardTitleProps {
  children: React.ReactNode;
  className?: string;
  level?: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
}

export const CardTitle: React.FC<CardTitleProps> = ({ children, className = '', level = 'h3' }) => {
  const Heading = level;
  const sizeClasses = {
    h1: 'text-2xl',
    h2: 'text-xl',
    h3: 'text-lg',
    h4: 'text-base',
    h5: 'text-sm',
    h6: 'text-xs',
  };

  return (
    <Heading className={`font-medium text-gray-800 ${sizeClasses[level]} ${className}`}>
      {children}
    </Heading>
  );
};

export interface CardContentProps {
  children: React.ReactNode;
  className?: string;
}

export const CardContent: React.FC<CardContentProps> = ({ children, className = '' }) => {
  return (
    <div className={className}>
      {children}
    </div>
  );
};

export interface CardFooterProps {
  children: React.ReactNode;
  className?: string;
  align?: 'left' | 'center' | 'right' | 'space-between' | 'space-around';
}

export const CardFooter: React.FC<CardFooterProps> = ({ children, className = '', align = 'right' }) => {
  const alignClasses = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-end',
    'space-between': 'justify-between',
    'space-around': 'justify-around',
  };

  return (
    <div className={`border-t border-gray-200 pt-4 mt-4 flex ${alignClasses[align]} ${className}`}>
      {children}
    </div>
  );
};

export default Card;
