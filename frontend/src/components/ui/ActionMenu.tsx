import React, { useRef, useEffect } from 'react';

export interface ActionMenuItem {
  label: string;
  icon?: React.ReactNode;
  onClick: () => void;
  destructive?: boolean;
  disabled?: boolean;
}

export interface ActionMenuProps {
  isOpen: boolean;
  onClose: () => void;
  items: ActionMenuItem[];
  triggerLabel?: string;
  position?: 'right' | 'left';
}

const ActionMenu: React.FC<ActionMenuProps> = ({
  isOpen,
  onClose,
  items,
  triggerLabel = 'Menu',
  position = 'right',
}) => {
  const menuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        onClose();
      }
    };

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onClose();
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [isOpen, onClose]);

  const handleKeyDown = (event: React.KeyboardEvent, onClick: () => void) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      onClick();
    }
  };

  if (!isOpen) return null;

  const positionClasses = position === 'right' ? 'right-0' : 'left-0';

  return (
    <div
      ref={menuRef}
      className={`absolute ${positionClasses} mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10`}
      role="menu"
      aria-orientation="vertical"
      aria-label={triggerLabel}
    >
      <div className="py-1">
        {items.map((item, index) => (
          <button
            key={index}
            onClick={() => {
              if (!item.disabled) {
                item.onClick();
                onClose();
              }
            }}
            onKeyDown={(e) => handleKeyDown(e, item.onClick)}
            disabled={item.disabled}
            role="menuitem"
            className={`w-full text-left block px-4 py-2 text-sm font-medium flex items-center transition-colors ${
              item.destructive
                ? 'text-red-600 hover:bg-red-50 focus:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:z-10'
                : 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10'
            } ${item.disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
            tabIndex={0}
            aria-disabled={item.disabled}
          >
            {item.icon && <span className="mr-2" aria-hidden="true">{item.icon}</span>}
            {item.label}
          </button>
        ))}
      </div>
    </div>
  );
};

export default ActionMenu;
