import React from 'react';

export type StatusType = 'active' | 'inactive' | 'pending' | 'warning' | 'error' | 'info' | 'success';

interface StatusBadgeProps {
  status: StatusType;
  label?: string;
  showIndicator?: boolean;
  size?: 'sm' | 'md';
}

const StatusBadge: React.FC<StatusBadgeProps> = ({
  status,
  label,
  showIndicator = true,
  size = 'sm',
}) => {
  const statusConfig = {
    active: {
      bgClass: 'bg-green-100',
      textClass: 'text-green-800',
      indicatorColor: 'text-green-600',
      defaultLabel: '● Aktif',
    },
    inactive: {
      bgClass: 'bg-red-100',
      textClass: 'text-red-800',
      indicatorColor: 'text-red-600',
      defaultLabel: '● Non-Aktif',
    },
    pending: {
      bgClass: 'bg-yellow-100',
      textClass: 'text-yellow-800',
      indicatorColor: 'text-yellow-600',
      defaultLabel: '● Pending',
    },
    warning: {
      bgClass: 'bg-yellow-100',
      textClass: 'text-yellow-800',
      indicatorColor: 'text-yellow-600',
      defaultLabel: '● Peringatan',
    },
    error: {
      bgClass: 'bg-red-100',
      textClass: 'text-red-800',
      indicatorColor: 'text-red-600',
      defaultLabel: '● Error',
    },
    info: {
      bgClass: 'bg-blue-100',
      textClass: 'text-blue-800',
      indicatorColor: 'text-blue-600',
      defaultLabel: '● Info',
    },
    success: {
      bgClass: 'bg-green-100',
      textClass: 'text-green-800',
      indicatorColor: 'text-green-600',
      defaultLabel: '● Berhasil',
    },
  };

  const config = statusConfig[status];
  const displayLabel = label || config.defaultLabel;
  const sizeStyles = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm';

  return (
    <span className={`inline-flex items-center leading-5 font-semibold rounded-full ${sizeStyles} ${config.bgClass} ${config.textClass}`}>
      {showIndicator && <span className={`mr-1 ${config.indicatorColor}`} aria-hidden="true">●</span>}
      {displayLabel}
    </span>
  );
};

export default StatusBadge;
