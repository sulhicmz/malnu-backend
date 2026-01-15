import React from 'react';
import { AlignJustify, Bell, Search, Settings, User } from 'lucide-react';

interface NavbarProps {
  toggleSidebar: () => void;
}

const Navbar: React.FC<NavbarProps> = ({ toggleSidebar }) => {
  return (
    <header className="bg-white shadow-sm z-10">
      <div className="flex items-center justify-between p-4">
        <div className="flex items-center gap-4">
          <button
            onClick={toggleSidebar}
            className="p-1 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 md:hidden"
          >
            <AlignJustify className="h-6 w-6" />
          </button>
          <div className="hidden md:flex items-center rounded-md bg-gray-100 py-2 px-3">
            <Search className="h-4 w-4 text-gray-500" />
            <input
              type="text"
              placeholder="Search..."
              className="bg-transparent border-none focus:outline-none focus:ring-0 ml-2 text-sm text-gray-600 w-40 lg:w-60"
            />
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button 
            className="p-1 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            aria-label="Notifications"
          >
            <Bell className="h-5 w-5" />
          </button>
          <button 
            className="p-1 rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
            aria-label="Settings"
          >
            <Settings className="h-5 w-5" />
          </button>
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center" aria-hidden="true">
              <User className="h-5 w-5 text-white" />
            </div>
            <span className="hidden md:inline-block text-sm font-medium text-gray-700">Admin User</span>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Navbar;