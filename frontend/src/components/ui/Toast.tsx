import React, { useEffect, useState } from 'react';
import { XCircle, CheckCircle, AlertCircle, Info, X } from 'lucide-react';

export type ToastType = 'success' | 'error' | 'warning' | 'info';
export type ToastPosition = 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center';

export interface ToastProps {
  id: string;
  type?: ToastType;
  title?: string;
  message: string;
  duration?: number;
  onClose: (id: string) => void;
  position?: ToastPosition;
  showClose?: boolean;
}

const Toast: React.FC<ToastProps> = ({
  id,
  type = 'info',
  title,
  message,
  duration = 5000,
  onClose,
  position = 'top-right',
  showClose = true,
}) => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const appearTimer = setTimeout(() => setIsVisible(true), 10);
    return () => clearTimeout(appearTimer);
  }, []);

  useEffect(() => {
    if (duration > 0) {
      const timer = setTimeout(() => {
        setIsVisible(false);
        setTimeout(() => onClose(id), 300);
      }, duration);
      return () => clearTimeout(timer);
    }
  }, [id, duration, onClose]);

  const config = {
    success: {
      bgClass: 'bg-green-50',
      borderClass: 'border-green-400',
      textClass: 'text-green-800',
      icon: CheckCircle,
      iconClass: 'text-green-500',
    },
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
    info: {
      bgClass: 'bg-blue-50',
      borderClass: 'border-blue-400',
      textClass: 'text-blue-800',
      icon: Info,
      iconClass: 'text-blue-500',
    },
  };

  const { bgClass, borderClass, textClass, icon: Icon, iconClass } = config[type];

  const handleKeyDown = (event: React.KeyboardEvent) => {
    if (event.key === 'Escape') {
      setIsVisible(false);
      setTimeout(() => onClose(id), 300);
    }
  };

  return (
    <div
      className={`${bgClass} border ${borderClass} ${textClass} rounded-lg shadow-lg p-4 min-w-[320px] max-w-md transition-all duration-300 ease-in-out ${
        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'
      }`}
      role="alert"
      aria-live={type === 'error' || type === 'warning' ? 'assertive' : 'polite'}
      onKeyDown={handleKeyDown}
    >
      <div className="flex items-start">
        <Icon className={`h-5 w-5 ${iconClass} flex-shrink-0 mt-0.5`} aria-hidden="true" />
        <div className="ml-3 flex-1">
          {title && <h3 className="font-semibold mb-1">{title}</h3>}
          <p className="text-sm leading-5">{message}</p>
        </div>
        {showClose && (
          <button
            onClick={() => {
              setIsVisible(false);
              setTimeout(() => onClose(id), 300);
            }}
            className={`ml-3 flex-shrink-0 ${iconClass} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-current rounded p-1`}
            aria-label="Tutup notifikasi"
          >
            <X className="h-4 w-4" aria-hidden="true" />
          </button>
        )}
      </div>
    </div>
  );
};

export default Toast;
