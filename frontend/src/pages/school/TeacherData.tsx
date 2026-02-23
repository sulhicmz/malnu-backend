import React, { useState, useEffect } from 'react';
import { Search, Filter, UserPlus, Download, MoreHorizontal, User, Edit, Trash2, Phone, Mail } from 'lucide-react';
import { teacherApi } from '../../services/api';
import useWebSocket from '../../hooks/useWebSocket';
import Pagination from '../../components/ui/Pagination';
import type { Teacher } from '../../types/api';

const TeacherData: React.FC = () => {
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [showActionMenu, setShowActionMenu] = useState<number | null>(null);

  // Handle real-time updates via WebSocket
  const handleWebSocketMessage = (data: unknown) => {
    const message = data as { type: string; teacher?: Teacher; teacherId?: string };
    if (message.type === 'teacher_update' && message.teacher) {
      setTeachers(prev => {
        const existingIndex = prev.findIndex(t => t.id === message.teacher!.id);
        if (existingIndex !== -1) {
          const updated = [...prev];
          updated[existingIndex] = message.teacher!;
          return updated;
        }
        return [...prev, message.teacher!];
      });
    } else if (message.type === 'teacher_delete' && message.teacherId) {
      setTeachers(prev => prev.filter(t => t.id !== message.teacherId));
    }
  };

  useWebSocket(handleWebSocketMessage);

  useEffect(() => {
    const fetchTeachers = async () => {
      try {
        setLoading(true);
        const response = await teacherApi.getAll();
        setTeachers(response.data.data.data || response.data.data); // Handle both paginated and non-paginated responses
      } catch {
        setError('Failed to fetch teachers');
      } finally {
        setLoading(false);
      }
    };

    fetchTeachers();
  }, []);

  const toggleActionMenu = (index: number) => {
    if (showActionMenu === index) {
      setShowActionMenu(null);
    } else {
      setShowActionMenu(index);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Data Guru & Staff</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <UserPlus className="h-4 w-4 mr-2" />
          Tambah Guru
        </button>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari guru..."
            className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2"
          />
        </div>
        <div className="flex space-x-2">
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Filter className="h-4 w-4 mr-2" />
            Filter
          </button>
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Mata Pelajaran</option>
            <option value="Matematika">Matematika</option>
            <option value="Bahasa Indonesia">Bahasa Indonesia</option>
            <option value="Bahasa Inggris">Bahasa Inggris</option>
            <option value="IPA">IPA</option>
            <option value="IPS">IPS</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Loading and Error States */}
      {loading && (
        <div className="flex justify-center items-center py-10">
          <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
        </div>
      )}

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert" aria-live="assertive">
          <div>
            <strong className="font-bold">Error: </strong>
            <span className="block sm:inline">{error}</span>
          </div>
          <button
            onClick={() => setError(null)}
            className="ml-4 p-1 rounded-full hover:bg-red-200 transition-colors"
            aria-label="Dismiss error"
          >
            <svg className="h-4 w-4 text-red-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
            </svg>
          </button>
        </div>
      )}

      {/* Teachers Grid View */}
      {!loading && !error && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {teachers.map((teacher, index) => (
            <div key={teacher.id} className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
              <div className="p-4">
                <div className="flex justify-between">
                  <div className="flex-shrink-0">
                    {teacher.avatar ? (
                      <img className="h-16 w-16 rounded-full" src={teacher.avatar} alt={teacher.name} />
                    ) : (
                      <div className="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                        <User className="h-8 w-8 text-gray-500" />
                      </div>
                    )}
                  </div>
                  <div className="relative">
                    <button 
                      onClick={() => toggleActionMenu(index)}
                      className="text-gray-500 hover:text-gray-700"
                    >
                      <MoreHorizontal className="h-5 w-5" />
                    </button>
                    {showActionMenu === index && (
                      <div className="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                        <div className="py-1" role="menu" aria-orientation="vertical">
                          <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            <Edit className="h-4 w-4 inline mr-2" />
                            Edit Data
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            <User className="h-4 w-4 inline mr-2" />
                            Lihat Detail
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                            <Trash2 className="h-4 w-4 inline mr-2" />
                            Hapus Data
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                <div className="mt-4">
                  <h3 className="text-lg font-medium text-gray-900">{teacher.name}</h3>
                  <p className="text-sm text-gray-500">{teacher.subject}</p>
                  <div className="mt-2 flex items-center text-sm text-gray-500">
                    <Mail className="h-4 w-4 mr-1" />
                    <span className="truncate">{teacher.email}</span>
                  </div>
                  <div className="mt-1 flex items-center text-sm text-gray-500">
                    <Phone className="h-4 w-4 mr-1" />
                    <span>{teacher.phone}</span>
                  </div>
                </div>
                <div className="mt-4">
                  <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    teacher.status === 'active' ? 'bg-green-100 text-green-800' : 
                    teacher.status === 'inactive' ? 'bg-red-100 text-red-800' : 
                    'bg-yellow-100 text-yellow-800'
                  }`}>
                    {teacher.status === 'active' ? 'Aktif' : 
                     teacher.status === 'inactive' ? 'Non-Aktif' : 
                     'Cuti'}
                  </span>
                  {teacher.isHomeroom && (
                    <span className="ml-2 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      Wali Kelas
                    </span>
                  )}
                </div>
              </div>
              <div className="bg-gray-50 px-4 py-3 text-right">
                <button className="text-sm text-blue-600 hover:text-blue-800">Jadwal Mengajar</button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Pagination - will be updated when we have actual pagination data */}
      {!loading && !error && teachers.length > 0 && (
        <Pagination
          currentPage={1}
          totalPages={3}
          onPageChange={(page) => console.log('Page changed to', page)}
          totalItems={teachers.length}
          itemsPerPage={teachers.length}
        />
      )}

      {/* Empty State */}
      {!loading && !error && teachers.length === 0 && (
        <div className="bg-white rounded-lg shadow-sm p-8 text-center">
          <div className="flex flex-col items-center">
            <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
              <User className="h-8 w-8 text-gray-400" />
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-1">Tidak ada data guru</h3>
            <p className="text-sm text-gray-500 mb-4">Belum ada data guru yang terdaftar. Tambahkan guru pertama untuk memulai.</p>
            <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
              <UserPlus className="h-4 w-4 mr-2" />
              Tambah Guru
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

interface Teacher {
  id: string;
  name: string;
  nip: string;
  subject: string;
  joinDate: string;
  status: 'active' | 'inactive' | 'leave';
  email: string;
  phone: string;
  isHomeroom: boolean;
  avatar?: string;
}

export default TeacherData;