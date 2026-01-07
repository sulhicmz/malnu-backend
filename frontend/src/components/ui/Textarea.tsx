import React, { forwardRef, ReactNode } from 'react';

export interface TextareaProps extends Omit<React.TextareaHTMLAttributes<HTMLTextAreaElement>, 'size'> {
  label?: string;
  error?: string;
  hint?: string;
  helperText?: string;
  fullWidth?: boolean;
  size?: 'sm' | 'md' | 'lg';
  rows?: number;
  resize?: 'none' | 'both' | 'horizontal' | 'vertical';
  maxLength?: number;
  showCharCount?: boolean;
}

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(
  (
    {
      label,
      error,
      hint,
      helperText,
      fullWidth = false,
      size = 'md',
      rows = 3,
      resize = 'vertical',
      maxLength,
      showCharCount = false,
      value,
      className = '',
      id,
      required,
      onChange,
      ...props
    },
    ref
  ) => {
    const textareaId = id || `textarea-${Math.random().toString(36).substr(2, 9)}`;
    const errorId = `${textareaId}-error`;
    const hintId = `${textareaId}-hint`;
    const helperId = `${textareaId}-helper`;

    const sizeClasses = {
      sm: 'px-3 py-1.5 text-sm',
      md: 'px-4 py-2 text-sm',
      lg: 'px-5 py-3 text-base',
    };

    const resizeClasses = {
      none: 'resize-none',
      both: 'resize',
      horizontal: 'resize-x',
      vertical: 'resize-y',
    };

    const wrapperClassName = fullWidth ? 'w-full' : '';
    const charCount = value ? String(value).length : 0;

    return (
      <div className={`flex flex-col gap-1.5 ${wrapperClassName}`}>
        {label && (
          <label
            htmlFor={textareaId}
            className="block text-sm font-medium text-gray-700"
          >
            {label}
            {required && <span className="text-red-500 ml-1" aria-label="required">*</span>}
          </label>
        )}
        
        <textarea
          ref={ref}
          id={textareaId}
          rows={rows}
          maxLength={maxLength}
          value={value}
          onChange={onChange}
          className={`
            block rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm
            ${sizeClasses[size]}
            ${resizeClasses[resize]}
            ${error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''}
            ${fullWidth ? 'w-full' : ''}
            ${className}
          `}
          aria-invalid={error ? 'true' : 'false'}
          aria-describedby={error ? errorId : hint ? hintId : helperText ? helperId : undefined}
          aria-required={required}
          {...props}
        />

        {(error || hint || helperText || (showCharCount && maxLength)) && (
          <div className="flex flex-col gap-1">
            {error && (
              <p id={errorId} className="text-sm text-red-600 flex items-center" role="alert">
                <svg className="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                  <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                </svg>
                {error}
              </p>
            )}

            {hint && !error && (
              <p id={hintId} className="text-sm text-gray-500">
                {hint}
              </p>
            )}

            {helperText && !error && !hint && (
              <p id={helperId} className="text-sm text-gray-500">
                {helperText}
              </p>
            )}

            {showCharCount && maxLength && !error && (
              <p className="text-xs text-gray-500 text-right">
                {charCount} / {maxLength} karakter
              </p>
            )}
          </div>
        )}
      </div>
    );
  }
);

Textarea.displayName = 'Textarea';

export default Textarea;
