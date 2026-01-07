import React, { createContext, useContext, useState, useCallback, useEffect, ReactNode } from 'react';
import Toast, { ToastProps, ToastType, ToastPosition } from './Toast';

export interface ToastItem extends Omit<ToastProps, 'onClose'> {
  id: string;
}

interface ToastContextValue {
  showToast: (message: string, type?: ToastType, options?: Partial<ToastItem>) => void;
  showSuccess: (message: string, options?: Partial<ToastItem>) => void;
  showError: (message: string, options?: Partial<ToastItem>) => void;
  showWarning: (message: string, options?: Partial<ToastItem>) => void;
  showInfo: (message: string, options?: Partial<ToastItem>) => void;
  removeToast: (id: string) => void;
}

const ToastContext = createContext<ToastContextValue | undefined>(undefined);

export const useToast = () => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within ToastProvider');
  }
  return context;
};

export interface ToastProviderProps {
  children: React.ReactNode;
  defaultPosition?: ToastPosition;
  defaultDuration?: number;
}

export const ToastProvider: React.FC<ToastProviderProps> = ({
  children,
  defaultPosition = 'top-right',
  defaultDuration = 5000,
}) => {
  const [toasts, setToasts] = useState<ToastItem[]>([]);
  const [position, setPosition] = useState<ToastPosition>(defaultPosition);

  const removeToast = useCallback((id: string) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  }, []);

  const showToast = useCallback(
    (
      message: string,
      type: ToastType = 'info',
      options: Partial<ToastItem> = {}
    ) => {
      const id = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      const newToast: ToastItem = {
        id,
        message,
        type,
        duration: options.duration ?? defaultDuration,
        position: options.position ?? position,
        title: options.title,
        showClose: options.showClose ?? true,
      };
      setToasts((prev) => [...prev, newToast]);
    },
    [defaultDuration, position]
  );

  const showSuccess = useCallback(
    (message: string, options: Partial<ToastItem> = {}) => {
      showToast(message, 'success', options);
    },
    [showToast]
  );

  const showError = useCallback(
    (message: string, options: Partial<ToastItem> = {}) => {
      showToast(message, 'error', options);
    },
    [showToast]
  );

  const showWarning = useCallback(
    (message: string, options: Partial<ToastItem> = {}) => {
      showToast(message, 'warning', options);
    },
    [showToast]
  );

  const showInfo = useCallback(
    (message: string, options: Partial<ToastItem> = {}) => {
      showToast(message, 'info', options);
    },
    [showToast]
  );

  const value: ToastContextValue = {
    showToast,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeToast,
  };

  const getPositionClasses = (pos: ToastPosition) => {
    const baseClasses = 'fixed z-50 p-4 flex flex-col gap-2';
    switch (pos) {
      case 'top-right':
        return `${baseClasses} top-0 right-0`;
      case 'top-left':
        return `${baseClasses} top-0 left-0`;
      case 'bottom-right':
        return `${baseClasses} bottom-0 right-0`;
      case 'bottom-left':
        return `${baseClasses} bottom-0 left-0`;
      case 'top-center':
        return `${baseClasses} top-0 left-1/2 -translate-x-1/2`;
      case 'bottom-center':
        return `${baseClasses} bottom-0 left-1/2 -translate-x-1/2`;
      default:
        return `${baseClasses} top-0 right-0`;
    }
  };

  const groupedToasts = toasts.reduce<Record<ToastPosition, ToastItem[]>>(
    (acc, toast) => {
      const pos = toast.position || defaultPosition;
      if (!acc[pos]) {
        acc[pos] = [];
      }
      acc[pos].push(toast);
      return acc;
    },
    {} as Record<ToastPosition, ToastItem[]>
  );

  return (
    <ToastContext.Provider value={value}>
      {children}
      {Object.entries(groupedToasts).map(([pos, posToasts]) => (
        <div key={pos} className={getPositionClasses(pos as ToastPosition)} role="region" aria-live="polite" aria-label="Notifikasi">
          {posToasts.map((toast) => (
            <Toast
              key={toast.id}
              {...toast}
              onClose={removeToast}
            />
          ))}
        </div>
      ))}
    </ToastContext.Provider>
  );
};

export default ToastProvider;
