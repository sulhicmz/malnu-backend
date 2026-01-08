import React, { useState, useEffect, useRef } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { LayoutDashboard, School, Users, BookOpen, Bot, Settings, User } from 'lucide-react';

const Sidebar: React.FC = () => {
  const location = useLocation();
  const [openMenus, setOpenMenus] = useState<Record<string, boolean>>({
    dashboard: true,
    school: false,
    ppdb: false,
    elearning: false,
    grades: false,
    exams: false,
    library: false,
    ai: false,
    career: false,
    parents: false,
    monetization: false,
    reports: false,
    admin: false
  });
  const menuRefs = useRef<Record<string, HTMLDivElement | null>>({});

  const toggleMenu = (menu: string) => {
    setOpenMenus(prev => ({
      ...prev,
      [menu]: !prev[menu]
    }));
  };

  const handleMenuKeyDown = (e: React.KeyboardEvent, menu: string) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggleMenu(menu);
    } else if (e.key === 'Escape') {
      setOpenMenus(prev => ({ ...prev, [menu]: false }));
    }
  };

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      Object.keys(menuRefs.current).forEach(menu => {
        if (menuRefs.current[menu] && !menuRefs.current[menu]?.contains(event.target as Node)) {
          setOpenMenus(prev => ({ ...prev, [menu]: false }));
        }
      });
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="h-full flex flex-col bg-white border-r" role="navigation" aria-label="Main navigation">
      <div className="p-4 border-b">
        <h1 className="flex items-center justify-center text-xl font-bold text-gray-800">
          <School className="h-8 w-8 text-blue-600" aria-hidden="true" />
          <span className="sr-only">School Admin</span>
          <span className="ml-2">SchoolAdmin</span>
        </h1>
      </div>

      <div className="flex-1 overflow-y-auto py-2">
        <nav className="space-y-1 px-2" aria-label="Main menu">
          {/* Dashboard Section */}
          <div ref={(el) => menuRefs.current['dashboard'] = el}>
            <button
              onClick={() => toggleMenu('dashboard')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'dashboard')}
              aria-expanded={openMenus.dashboard}
              aria-controls="dashboard-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <LayoutDashboard className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Dashboard</span>
              </div>
              <svg
                className={`${openMenus.dashboard ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.dashboard && (
              <ul id="dashboard-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/"
                    aria-current={isActive('/') ? 'page' : undefined}
                    className={`${isActive('/') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Overview Sekolah
                  </Link>
                </li>
                <li>
                  <Link
                    to="/dashboard/analytics"
                    aria-current={isActive('/dashboard/analytics') ? 'page' : undefined}
                    className={`${isActive('/dashboard/analytics') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Analytics
                  </Link>
                </li>
                <li>
                  <Link
                    to="/dashboard/quick-actions"
                    aria-current={isActive('/dashboard/quick-actions') ? 'page' : undefined}
                    className={`${isActive('/dashboard/quick-actions') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Quick Actions
                  </Link>
                </li>
              </ul>
            )}
          </div>

          {/* School Management Section */}
          <div ref={(el) => menuRefs.current['school'] = el}>
            <button
              onClick={() => toggleMenu('school')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'school')}
              aria-expanded={openMenus.school}
              aria-controls="school-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <School className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Manajemen Sekolah</span>
              </div>
              <svg
                className={`${openMenus.school ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.school && (
              <ul id="school-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/school/students"
                    aria-current={isActive('/school/students') ? 'page' : undefined}
                    className={`${isActive('/school/students') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Data Siswa
                  </Link>
                </li>
                <li>
                  <Link
                    to="/school/teachers"
                    aria-current={isActive('/school/teachers') ? 'page' : undefined}
                    className={`${isActive('/school/teachers') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Data Guru & Staff
                  </Link>
                </li>
                <li>
                  <Link
                    to="/school/classes"
                    aria-current={isActive('/school/classes') ? 'page' : undefined}
                    className={`${isActive('/school/classes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Data Kelas
                  </Link>
                </li>
                <li>
                  <Link
                    to="/school/subjects"
                    aria-current={isActive('/school/subjects') ? 'page' : undefined}
                    className={`${isActive('/school/subjects') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Mata Pelajaran
                  </Link>
                </li>
                <li>
                  <Link
                    to="/school/schedule"
                    aria-current={isActive('/school/schedule') ? 'page' : undefined}
                    className={`${isActive('/school/schedule') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Jadwal Mengajar
                  </Link>
                </li>
                <li>
                  <Link
                    to="/school/inventory"
                    aria-current={isActive('/school/inventory') ? 'page' : undefined}
                    className={`${isActive('/school/inventory') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500`}
                  >
                    Inventaris Sekolah
                  </Link>
                </li>
              </ul>
            )}
          </div>

          {/* PPDB Section */}
          <div ref={(el) => menuRefs.current['ppdb'] = el}>
            <button
              onClick={() => toggleMenu('ppdb')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'ppdb')}
              aria-expanded={openMenus.ppdb}
              aria-controls="ppdb-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <Users className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">PPDB Online</span>
                <span className="ml-2 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">New</span>
              </div>
              <svg
                className={`${openMenus.ppdb ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.ppdb && (
              <ul id="ppdb-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/ppdb/registration"
                    aria-current={isActive('/ppdb/registration') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Form Pendaftaran
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ppdb/verification"
                    aria-current={isActive('/ppdb/verification') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Verifikasi Dokumen
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ppdb/test"
                    aria-current={isActive('/ppdb/test') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Tes Online
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ppdb/announcement"
                    aria-current={isActive('/ppdb/announcement') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Pengumuman
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ppdb/statistics"
                    aria-current={isActive('/ppdb/statistics') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Statistik Penerimaan
                  </Link>
                </li>
              </ul>
            )}
          </div>

          {/* E-Learning Section */}
          <div ref={(el) => menuRefs.current['elearning'] = el}>
            <button
              onClick={() => toggleMenu('elearning')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'elearning')}
              aria-expanded={openMenus.elearning}
              aria-controls="elearning-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <BookOpen className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">E-Learning</span>
              </div>
              <svg
                className={`${openMenus.elearning ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.elearning && (
              <ul id="elearning-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/elearning/classes"
                    aria-current={isActive('/elearning/classes') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Kelas Virtual
                  </Link>
                </li>
                <li>
                  <Link
                    to="/elearning/materials"
                    aria-current={isActive('/elearning/materials') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Materi Pembelajaran
                  </Link>
                </li>
                <li>
                  <Link
                    to="/elearning/assignments"
                    aria-current={isActive('/elearning/assignments') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Tugas & Quiz
                  </Link>
                </li>
                <li>
                  <Link
                    to="/elearning/discussions"
                    aria-current={isActive('/elearning/discussions') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Diskusi Online
                  </Link>
                </li>
                <li>
                  <Link
                    to="/elearning/conferences"
                    aria-current={isActive('/elearning/conferences') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Video Conference
                  </Link>
                </li>
              </ul>
            )}
          </div>

          {/* Divider for Premium Features */}
          <div className="py-2">
            <hr className="border-gray-200" />
            <p className="px-3 py-2 text-xs font-medium text-gray-500">Fitur Premium</p>
          </div>

          {/* AI Learning Assistant */}
          <div ref={(el) => menuRefs.current['ai'] = el}>
            <button
              onClick={() => toggleMenu('ai')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'ai')}
              aria-expanded={openMenus.ai}
              aria-controls="ai-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <Bot className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">AI Learning Assistant</span>
                <span className="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">PRO</span>
              </div>
              <svg
                className={`${openMenus.ai ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.ai && (
              <ul id="ai-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/ai/tutor"
                    aria-current={isActive('/ai/tutor') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Tutor Virtual
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ai/content"
                    aria-current={isActive('/ai/content') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Pembuatan Materi Otomatis
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ai/assessment"
                    aria-current={isActive('/ai/assessment') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Penilaian Esai AI
                  </Link>
                </li>
                <li>
                  <Link
                    to="/ai/recommendations"
                    aria-current={isActive('/ai/recommendations') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Rekomendasi Pembelajaran
                  </Link>
                </li>
              </ul>
            )}
          </div>

          {/* Admin Settings */}
          <div ref={(el) => menuRefs.current['admin'] = el}>
            <button
              onClick={() => toggleMenu('admin')}
              onKeyDown={(e) => handleMenuKeyDown(e, 'admin')}
              aria-expanded={openMenus.admin}
              aria-controls="admin-menu"
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <div className="flex items-center">
                <Settings className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Administrasi</span>
              </div>
              <svg
                className={`${openMenus.admin ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500 transition-transform`}
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.admin && (
              <ul id="admin-menu" className="mt-1 pl-8 space-y-1" role="list">
                <li>
                  <Link
                    to="/admin/settings"
                    aria-current={isActive('/admin/settings') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Pengaturan Sistem
                  </Link>
                </li>
                <li>
                  <Link
                    to="/admin/users"
                    aria-current={isActive('/admin/users') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Manajemen User
                  </Link>
                </li>
                <li>
                  <Link
                    to="/admin/landing"
                    aria-current={isActive('/admin/landing') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Template Landing Page
                  </Link>
                </li>
                <li>
                  <Link
                    to="/admin/api"
                    aria-current={isActive('/admin/api') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Integrasi API
                  </Link>
                </li>
                <li>
                  <Link
                    to="/admin/backup"
                    aria-current={isActive('/admin/backup') ? 'page' : undefined}
                    className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    Backup Data
                  </Link>
                </li>
              </ul>
            )}
          </div>
          
        </nav>
      </div>

      <div className="p-4 border-t">
        <div className="flex items-center" role="button" tabIndex={0} aria-label="User profile menu">
          <div className="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center" aria-hidden="true">
            <User className="h-5 w-5 text-white" />
          </div>
          <div className="ml-3">
            <p className="text-sm font-medium text-gray-700">Admin User</p>
            <p className="text-xs text-gray-500">admin@school.com</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Sidebar;