import React, { useState } from 'react';
import { Search, PlusCircle, Download, Users, Book, GraduationCap, MoreHorizontal, Edit, Trash2 } from 'lucide-react';

const ClassData: React.FC = () => {
  const [classes] = useState<ClassData[]>(mockClasses);
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
        <h1 className="text-2xl font-bold text-gray-800">Data Kelas</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <PlusCircle className="h-4 w-4 mr-2" />
          Tambah Kelas
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
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Tingkat</option>
            <option value="X">Kelas X</option>
            <option value="XI">Kelas XI</option>
            <option value="XII">Kelas XII</option>
          </select>
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="2024-2025">Tahun Ajaran 2024/2025</option>
            <option value="2023-2024">Tahun Ajaran 2023/2024</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Classes Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {classes.map((classItem, index) => (
          <div key={classItem.id} className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
            <div className="bg-blue-600 px-4 py-3 text-white flex justify-between items-center">
              <h3 className="text-lg font-semibold">{classItem.name}</h3>
              <div className="relative">
                <button 
                  onClick={() => toggleActionMenu(index)}
                  className="text-white hover:text-gray-200"
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
                        <Users className="h-4 w-4 inline mr-2" />
                        Lihat Siswa
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
            <div className="p-4">
              <div className="mb-4">
                <p className="text-gray-500 text-sm">{classItem.level} | {classItem.academicYear}</p>
                <p className="mt-1 flex items-center text-sm">
                  <GraduationCap className="h-4 w-4 mr-2 text-gray-500" />
                  Wali Kelas: <span className="font-medium ml-1">{classItem.homeroomTeacher}</span>
                </p>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between items-center py-2 border-b border-gray-100">
                  <div className="flex items-center">
                    <Users className="h-5 w-5 text-blue-500 mr-2" />
                    <span className="text-sm text-gray-600">Jumlah Siswa</span>
                  </div>
                  <span className="text-sm font-semibold">{classItem.studentCount} / {classItem.capacity}</span>
                </div>
                
                <div className="flex justify-between items-center py-2 border-b border-gray-100">
                  <div className="flex items-center">
                    <Book className="h-5 w-5 text-green-500 mr-2" />
                    <span className="text-sm text-gray-600">Mata Pelajaran</span>
                  </div>
                  <span className="text-sm font-semibold">{classItem.subjectCount}</span>
                </div>
              </div>
            </div>
            
            <div className="bg-gray-50 px-4 py-3 flex justify-between">
              <button className="text-sm text-blue-600 hover:text-blue-800">Jadwal Pelajaran</button>
              <button className="text-sm text-blue-600 hover:text-blue-800">Daftar Siswa</button>
            </div>
          </div>
        ))}
      </div>

      {/* Add New Class Card */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div className="bg-gray-50 rounded-lg border border-dashed border-gray-300 flex items-center justify-center p-6 h-48 cursor-pointer hover:bg-gray-100 transition-colors">
          <div className="text-center">
            <PlusCircle className="h-8 w-8 text-gray-400 mx-auto mb-2" />
            <p className="text-sm text-gray-600">Tambah Kelas Baru</p>
          </div>
        </div>
      </div>
    </div>
  );
};

interface ClassData {
  id: string;
  name: string;
  level: string;
  homeroomTeacher: string;
  academicYear: string;
  studentCount: number;
  capacity: number;
  subjectCount: number;
}

// Mock data
const mockClasses: ClassData[] = [
  {
    id: '1',
    name: 'X-A',
    level: 'Kelas X',
    homeroomTeacher: 'Drs. Agus Supriyanto',
    academicYear: '2024/2025',
    studentCount: 32,
    capacity: 35,
    subjectCount: 12
  },
  {
    id: '2',
    name: 'X-B',
    level: 'Kelas X',
    homeroomTeacher: 'Dra. Budi Setiawati, M.Pd',
    academicYear: '2024/2025',
    studentCount: 30,
    capacity: 35,
    subjectCount: 12
  },
  {
    id: '3',
    name: 'X-C',
    level: 'Kelas X',
    homeroomTeacher: 'Dewi Anggraini, S.Si',
    academicYear: '2024/2025',
    studentCount: 33,
    capacity: 35,
    subjectCount: 12
  },
  {
    id: '4',
    name: 'XI-A',
    level: 'Kelas XI',
    homeroomTeacher: 'Heni Purwanti, S.Pd',
    academicYear: '2024/2025',
    studentCount: 31,
    capacity: 35,
    subjectCount: 14
  },
  {
    id: '5',
    name: 'XI-B',
    level: 'Kelas XI',
    homeroomTeacher: 'Fitri Handayani, S.Pd',
    academicYear: '2024/2025',
    studentCount: 29,
    capacity: 35,
    subjectCount: 14
  },
  {
    id: '6',
    name: 'XII-A',
    level: 'Kelas XII',
    homeroomTeacher: 'Cahyono, S.Pd',
    academicYear: '2024/2025',
    studentCount: 28,
    capacity: 35,
    subjectCount: 12
  },
  {
    id: '7',
    name: 'XII-B',
    level: 'Kelas XII',
    homeroomTeacher: 'Gunawan Wibisono, S.Kom',
    academicYear: '2024/2025',
    studentCount: 27,
    capacity: 35,
    subjectCount: 12
  }
];

export default ClassData;