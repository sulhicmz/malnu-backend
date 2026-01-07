import React from 'react';
import { AlertCircle, XCircle, CheckCircle, Info } from 'lucide-react';

export type ErrorType = 'error' | 'warning' | 'success' | 'info';

interface AlertProps {
  type?: ErrorType;
  title?: string;
  message: string;
  dismissible?: boolean;
  onDismiss?: () => void;
}

const Alert: React.FC<AlertProps> = ({
  type = 'error',
  title,
  message,
  dismissible = false,
  onDismiss,
}) => {
  const config = {
    error: {
      bgClass: 'bg-red-50',
      borderClass: 'border-red-400',
      textClass: 'text-red-800',
      icon: XCircle,
      iconClass: 'text-red-500',
    },
    warning: {
      bgClass: 'bg-yellow-50',
      borderClass: 'border-yellow-400',
      textClass: 'text-yellow-800',
      icon: AlertCircle,
      iconClass: 'text-yellow-500',
    },
    success: {
      bgClass: 'bg-green-50',
      borderClass: 'border-green-400',
      textClass: 'text-green-800',
      icon: CheckCircle,
      iconClass: 'text-green-500',
    },
    info: {
      bgClass: 'bg-blue-50',
      borderClass: 'border-blue-400',
      textClass: 'text-blue-800',
      icon: Info,
      iconClass: 'text-blue-500',
    },
  };

  const { bgClass, borderClass, textClass, icon: Icon, iconClass } = config[type];

  return (
    <div
      className={`${bgClass} border ${borderClass} ${textClass} px-4 py-3 rounded relative`}
      role="alert"
      aria-live="assertive"
    >
      <div className="flex items-start">
        <Icon className={`h-5 w-5 ${iconClass} mr-3 flex-shrink-0`} aria-hidden="true" />
        <div className="flex-1">
          {title && <h3 className="font-bold mb-1">{title}</h3>}
          <span className="block sm:inline">{message}</span>
        </div>
        {dismissible && onDismiss && (
          <button
            onClick={onDismiss}
            className={`ml-3 text-${type === 'error' ? 'red' : type === 'warning' ? 'yellow' : type === 'success' ? 'green' : 'blue'}-500 hover:text-opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-${type === 'error' ? 'red' : type === 'warning' ? 'yellow' : type === 'success' ? 'green' : 'blue'}-500`}
            aria-label="Close alert"
          >
            <XCircle className="h-5 w-5" />
          </button>
        )}
      </div>
    </div>
  );
};

export default Alert;
