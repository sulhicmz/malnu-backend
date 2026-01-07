import React from 'react';

export type StatusType = 'active' | 'inactive' | 'pending' | 'warning' | 'error' | 'info' | 'success';

interface StatusBadgeProps {
  status: StatusType;
  label?: string;
  showIndicator?: boolean;
  size?: 'sm' | 'md';
  role?: 'status' | 'alert' | 'none';
}

const StatusBadge: React.FC<StatusBadgeProps> = ({
  status,
  label,
  showIndicator = true,
  size = 'sm',
  role = 'status',
}) => {
  const statusConfig = {
    active: {
      bgClass: 'bg-green-100',
      textClass: 'text-green-800',
      indicatorBg: 'bg-green-600',
      defaultLabel: 'Aktif',
      ariaLabel: 'Status: Aktif',
    },
    inactive: {
      bgClass: 'bg-red-100',
      textClass: 'text-red-800',
      indicatorBg: 'bg-red-600',
      defaultLabel: 'Non-Aktif',
      ariaLabel: 'Status: Non-Aktif',
    },
    pending: {
      bgClass: 'bg-yellow-100',
      textClass: 'text-yellow-800',
      indicatorBg: 'bg-yellow-600',
      defaultLabel: 'Pending',
      ariaLabel: 'Status: Pending',
    },
    warning: {
      bgClass: 'bg-yellow-100',
      textClass: 'text-yellow-800',
      indicatorBg: 'bg-yellow-600',
      defaultLabel: 'Peringatan',
      ariaLabel: 'Status: Peringatan',
    },
    error: {
      bgClass: 'bg-red-100',
      textClass: 'text-red-800',
      indicatorBg: 'bg-red-600',
      defaultLabel: 'Error',
      ariaLabel: 'Status: Error',
    },
    info: {
      bgClass: 'bg-blue-100',
      textClass: 'text-blue-800',
      indicatorBg: 'bg-blue-600',
      defaultLabel: 'Info',
      ariaLabel: 'Status: Info',
    },
    success: {
      bgClass: 'bg-green-100',
      textClass: 'text-green-800',
      indicatorBg: 'bg-green-600',
      defaultLabel: 'Berhasil',
      ariaLabel: 'Status: Berhasil',
    },
  };

  const config = statusConfig[status];
  const displayLabel = label || config.defaultLabel;
  const sizeStyles = size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-3 py-1 text-sm';

  return (
    <span
      className={`inline-flex items-center leading-5 font-semibold rounded-full ${sizeStyles} ${config.bgClass} ${config.textClass}`}
      role={role === 'none' ? undefined : role}
      aria-label={role !== 'none' ? (label ? config.ariaLabel : config.ariaLabel) : undefined}
    >
      {showIndicator && (
        <span
          className={`mr-1.5 w-2 h-2 rounded-full ${config.indicatorBg}`}
          aria-hidden="true"
        />
      )}
      {displayLabel}
    </span>
  );
};

export default StatusBadge;
