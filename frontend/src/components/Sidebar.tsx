import React, { useState } from 'react';
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

  const toggleMenu = (menu: string) => {
    setOpenMenus(prev => ({
      ...prev,
      [menu]: !prev[menu]
    }));
  };

  const handleKeyDown = (event: React.KeyboardEvent, menu: string) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      toggleMenu(menu);
    }
  };

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <div className="h-full flex flex-col bg-white border-r">
      <div className="p-4 border-b">
        <div className="flex items-center justify-center">
          <School className="h-8 w-8 text-blue-600" />
          <h1 className="ml-2 text-xl font-bold text-gray-800">SchoolAdmin</h1>
        </div>
      </div>
      
      <div className="flex-1 overflow-y-auto py-2">
        <nav className="space-y-1 px-2">
          {/* Dashboard Section */}
          <div>
            <button
              onClick={() => toggleMenu('dashboard')}
              onKeyDown={(e) => handleKeyDown(e, 'dashboard')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.dashboard}
              aria-controls="dashboard-menu"
            >
              <div className="flex items-center">
                <LayoutDashboard className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Dashboard</span>
              </div>
              <svg
                className={`${openMenus.dashboard ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`}
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
              <div id="dashboard-menu" className="mt-1 pl-8 space-y-1">
                <Link
                  to="/"
                  aria-current={isActive('/') ? 'page' : undefined}
                  className={`${isActive('/') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Overview Sekolah
                </Link>
                <Link
                  to="/dashboard/analytics"
                  className={`${isActive('/dashboard/analytics') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Analytics
                </Link>
                <Link
                  to="/dashboard/quick-actions"
                  className={`${isActive('/dashboard/quick-actions') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Quick Actions
                </Link>
              </div>
            )}
          </div>

          {/* School Management Section */}
          <div>
            <button
              onClick={() => toggleMenu('school')}
              onKeyDown={(e) => handleKeyDown(e, 'school')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.school}
              aria-controls="school-menu"
            >
              <div className="flex items-center">
                <School className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Manajemen Sekolah</span>
              </div>
              <svg
                className={`${openMenus.school ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`}
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
              <div id="school-menu" className="mt-1 pl-8 space-y-1">
                <Link
                  to="/school/students"
                  aria-current={isActive('/school/students') ? 'page' : undefined}
                  className={`${isActive('/school/students') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10`}
                >
                  Data Siswa
                </Link>
                <Link
                  to="/school/teachers"
                  className={`${isActive('/school/teachers') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Data Guru & Staff
                </Link>
                <Link
                  to="/school/classes"
                  className={`${isActive('/school/classes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Data Kelas
                </Link>
                <Link
                  to="/school/subjects"
                  className={`${isActive('/school/subjects') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Mata Pelajaran
                </Link>
                <Link
                  to="/school/schedule"
                  className={`${isActive('/school/schedule') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Jadwal Mengajar
                </Link>
                <Link
                  to="/school/inventory"
                  className={`${isActive('/school/inventory') ? 'bg-blue-50 text-blue-700' : 'text-gray-600'} group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700`}
                >
                  Inventaris Sekolah
                </Link>
              </div>
            )}
          </div>

          {/* PPDB Section */}
          <div>
            <button
              onClick={() => toggleMenu('ppdb')}
              onKeyDown={(e) => handleKeyDown(e, 'ppdb')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.ppdb}
              aria-controls="ppdb-menu"
            >
              <div className="flex items-center">
                <Users className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">PPDB Online</span>
                <span className="ml-2 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">New</span>
              </div>
              <svg
                className={`${openMenus.ppdb ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`}
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
              <div id="ppdb-menu" className="mt-1 pl-8 space-y-1">
                <Link
                  to="/ppdb/registration"
                  className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
                >
                  Form Pendaftaran
                </Link>
                <Link
                  to="/ppdb/verification"
                  className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700"
                >
                  Verifikasi Dokumen
                </Link>
                <Link
                  to="/ppdb/test"
                  className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700"
                >
                  Tes Online
                </Link>
                <Link
                  to="/ppdb/announcement"
                  className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700"
                >
                  Pengumuman
                </Link>
                <Link
                  to="/ppdb/statistics"
                  className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700"
                >
                  Statistik Penerimaan
                </Link>
              </div>
            )}
          </div>

          {/* Other sections follow the same pattern */}
          <div>
            <button
              onClick={() => toggleMenu('elearning')}
              onKeyDown={(e) => handleKeyDown(e, 'elearning')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.elearning}
              aria-controls="elearning-menu"
            >
              <div className="flex items-center">
                <BookOpen className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">E-Learning</span>
              </div>
              <svg className={`${openMenus.elearning ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.elearning && (
              <div id="elearning-menu" className="mt-1 pl-8 space-y-1">
                <Link to="/elearning/classes" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10">Kelas Virtual</Link>
                <Link to="/elearning/materials" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Materi Pembelajaran</Link>
                <Link to="/elearning/assignments" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Tugas & Quiz</Link>
                <Link to="/elearning/discussions" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Diskusi Online</Link>
                <Link to="/elearning/conferences" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Video Conference</Link>
              </div>
            )}
          </div>

          {/* Divider for Premium Features */}
          <div className="py-2">
            <hr className="border-gray-200" />
            <p className="px-3 py-2 text-xs font-medium text-gray-500">Fitur Premium</p>
          </div>

          {/* AI Learning Assistant */}
          <div>
            <button
              onClick={() => toggleMenu('ai')}
              onKeyDown={(e) => handleKeyDown(e, 'ai')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.ai}
              aria-controls="ai-menu"
            >
              <div className="flex items-center">
                <Bot className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">AI Learning Assistant</span>
                <span className="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">PRO</span>
              </div>
              <svg className={`${openMenus.ai ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.ai && (
              <div id="ai-menu" className="mt-1 pl-8 space-y-1">
                <Link to="/ai/tutor" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10">Tutor Virtual</Link>
                <Link to="/ai/content" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Pembuatan Materi Otomatis</Link>
                <Link to="/ai/assessment" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Penilaian Esai AI</Link>
                <Link to="/ai/recommendations" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Rekomendasi Pembelajaran</Link>
              </div>
            )}
          </div>

          {/* Admin Settings */}
          <div>
            <button
              onClick={() => toggleMenu('admin')}
              onKeyDown={(e) => handleKeyDown(e, 'admin')}
              className="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10"
              aria-expanded={openMenus.admin}
              aria-controls="admin-menu"
            >
              <div className="flex items-center">
                <Settings className="h-5 w-5 text-gray-500" aria-hidden="true" />
                <span className="ml-3">Administrasi</span>
              </div>
              <svg className={`${openMenus.admin ? 'transform rotate-90' : ''} h-4 w-4 text-gray-500`} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            {openMenus.admin && (
              <div id="admin-menu" className="mt-1 pl-8 space-y-1">
                <Link to="/admin/settings" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:z-10">Pengaturan Sistem</Link>
                <Link to="/admin/users" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Manajemen User</Link>
                <Link to="/admin/landing" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Template Landing Page</Link>
                <Link to="/admin/api" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Integrasi API</Link>
                <Link to="/admin/backup" className="text-gray-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-700">Backup Data</Link>
              </div>
            )}
          </div>
          
        </nav>
      </div>

      <div className="p-4 border-t">
        <div className="flex items-center">
          <div className="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
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