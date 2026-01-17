import React, { useState } from 'react';
import { Search, Filter, PlusCircle, Download, MoreHorizontal, Edit, Trash2, Video, Users, Book, Calendar } from 'lucide-react';

const VirtualClasses: React.FC = () => {
  const [classes] = useState<VirtualClass[]>(mockClasses);
  const [showActionMenu, setShowActionMenu] = useState<number | null>(null);

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
        <h1 className="text-2xl font-bold text-gray-800">Kelas Virtual</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <PlusCircle className="h-4 w-4 mr-2" />
          Buat Kelas Baru
        </button>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari kelas..."
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
            <option value="IPA">IPA</option>
            <option value="IPS">IPS</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Virtual Classes Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {classes.map((virtualClass, index) => (
          <div key={virtualClass.id} className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
            <div className="p-4">
              <div className="flex justify-between items-start">
                <div className="flex items-center">
                  <div className={`w-10 h-10 rounded-lg bg-${virtualClass.color}-100 flex items-center justify-center`}>
                    <Book className={`h-5 w-5 text-${virtualClass.color}-600`} />
                  </div>
                  <div className="ml-3">
                    <h3 className="text-lg font-medium text-gray-900">{virtualClass.name}</h3>
                    <p className="text-sm text-gray-500">{virtualClass.subject}</p>
                  </div>
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
                          Edit Kelas
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                          <Video className="h-4 w-4 inline mr-2" />
                          Mulai Meeting
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                          <Trash2 className="h-4 w-4 inline mr-2" />
                          Hapus Kelas
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              <div className="mt-4 space-y-3">
                <div className="flex items-center text-sm text-gray-500">
                  <Users className="h-4 w-4 mr-2" />
                  {virtualClass.studentCount} Siswa
                </div>
                <div className="flex items-center text-sm text-gray-500">
                  <Calendar className="h-4 w-4 mr-2" />
                  {virtualClass.schedule}
                </div>
              </div>

              <div className="mt-4">
                <div className="flex items-center">
                  <img
                    className="h-8 w-8 rounded-full"
                    src={virtualClass.teacherAvatar}
                    alt={virtualClass.teacherName}
                  />
                  <div className="ml-2">
                    <p className="text-sm font-medium text-gray-900">{virtualClass.teacherName}</p>
                    <p className="text-xs text-gray-500">Pengajar</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="border-t border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
              <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                virtualClass.status === 'active' ? 'bg-green-100 text-green-800' : 
                virtualClass.status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 
                'bg-gray-100 text-gray-800'
              }`}>
                {virtualClass.status === 'active' ? 'Aktif' : 
                 virtualClass.status === 'scheduled' ? 'Terjadwal' : 
                 'Selesai'}
              </span>
              <button className="text-sm text-blue-600 hover:text-blue-800">
                Masuk Kelas
              </button>
            </div>
          </div>
        ))}

        {/* Add New Class Card */}
        <div className="bg-gray-50 rounded-lg border border-dashed border-gray-300 flex items-center justify-center p-6 cursor-pointer hover:bg-gray-100 transition-colors">
          <div className="text-center">
            <PlusCircle className="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p className="text-sm text-gray-600">Buat Kelas Virtual Baru</p>
          </div>
        </div>
      </div>
    </div>
  );
};

interface VirtualClass {
  id: string;
  name: string;
  subject: string;
  teacherName: string;
  teacherAvatar: string;
  studentCount: number;
  schedule: string;
  status: 'active' | 'scheduled' | 'completed';
  color: string;
}

const mockClasses: VirtualClass[] = [
  {
    id: '1',
    name: 'Matematika Dasar',
    subject: 'Matematika',
    teacherName: 'Drs. Agus Supriyanto',
    teacherAvatar: 'https://randomuser.me/api/portraits/men/41.jpg',
    studentCount: 32,
    schedule: 'Senin & Rabu, 08:00 - 09:30',
    status: 'active',
    color: 'blue'
  },
  {
    id: '2',
    name: 'Bahasa Indonesia',
    subject: 'Bahasa Indonesia',
    teacherName: 'Dra. Budi Setiawati',
    teacherAvatar: 'https://randomuser.me/api/portraits/women/42.jpg',
    studentCount: 30,
    schedule: 'Selasa & Kamis, 10:00 - 11:30',
    status: 'scheduled',
    color: 'green'
  },
  {
    id: '3',
    name: 'Fisika Lanjutan',
    subject: 'IPA',
    teacherName: 'Dewi Anggraini, S.Si',
    teacherAvatar: 'https://randomuser.me/api/portraits/women/43.jpg',
    studentCount: 28,
    schedule: 'Rabu & Jumat, 13:00 - 14:30',
    status: 'active',
    color: 'purple'
  },
  {
    id: '4',
    name: 'Sejarah Indonesia',
    subject: 'IPS',
    teacherName: 'Cahyono, S.Pd',
    teacherAvatar: 'https://randomuser.me/api/portraits/men/44.jpg',
    studentCount: 35,
    schedule: 'Senin & Kamis, 09:30 - 11:00',
    status: 'completed',
    color: 'orange'
  }
];

export default VirtualClasses;