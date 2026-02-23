import React, { useRef, useEffect, useState } from 'react';
import { MoreHorizontal, LucideIcon } from 'lucide-react';

export interface ActionItem {
  label: string;
  icon: LucideIcon;
  onClick: () => void;
  variant?: 'default' | 'danger';
  disabled?: boolean;
}

interface ActionMenuProps {
  label: string;
  actions: ActionItem[];
  triggerClassName?: string;
}

const ActionMenu: React.FC<ActionMenuProps> = ({ label, actions, triggerClassName = '' }) => {
  const [isOpen, setIsOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  const toggleMenu = () => {
    setIsOpen(!isOpen);
  };

  const closeMenu = () => {
    setIsOpen(false);
  };

  const handleKeyDown = (e: React.KeyboardEvent, index: number) => {
    if (e.key === 'Escape') {
      closeMenu();
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      const nextIndex = (index + 1) % actions.length;
      const buttons = menuRef.current?.querySelectorAll('button[role="menuitem"]');
      buttons?.[nextIndex]?.focus();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      const prevIndex = (index - 1 + actions.length) % actions.length;
      const buttons = menuRef.current?.querySelectorAll('button[role="menuitem"]');
      buttons?.[prevIndex]?.focus();
    }
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        closeMenu();
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => document.removeEventListener('mousedown', handleClickOutside);
    }
  }, [isOpen]);

  return (
    <div className="relative" ref={menuRef}>
      <button
        onClick={toggleMenu}
        aria-expanded={isOpen}
        aria-haspopup="menu"
        aria-label={label}
        className={`text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded p-1 ${triggerClassName}`}
      >
        <MoreHorizontal className="h-5 w-5" />
      </button>

      {isOpen && (
        <div
          className="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
          role="menu"
          aria-label={label}
          aria-orientation="vertical"
        >
          <div className="py-1" role="none">
            {actions.map((action, index) => (
              <button
                key={index}
                onClick={() => {
                  if (!action.disabled) {
                    action.onClick();
                    closeMenu();
                  }
                }}
                disabled={action.disabled}
                className={`w-full text-left block px-4 py-2 text-sm transition-colors ${
                  action.disabled 
                    ? 'text-gray-400 cursor-not-allowed' 
                    : action.variant === 'danger' 
                      ? 'text-red-600 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-inset' 
                      : 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset'
                }`}
                role="menuitem"
                tabIndex={index === 0 ? 0 : -1}
                onKeyDown={(e) => handleKeyDown(e, index)}
              >
                <action.icon className="h-4 w-4 inline mr-2" aria-hidden="true" />
                {action.label}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default ActionMenu;
