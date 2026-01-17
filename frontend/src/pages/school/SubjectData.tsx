import React, { useState } from 'react';
import { Search, PlusCircle, Download, Book, Clock, GraduationCap, MoreHorizontal, Edit, Trash2, User, Layers } from 'lucide-react';

const SubjectData: React.FC = () => {
  const [subjects] = useState<Subject[]>(mockSubjects);
  const [showActionMenu, setShowActionMenu] = useState<number | null>(null);

  const toggleActionMenu = (index: number) => {
    if (showActionMenu === index) {
      setShowActionMenu(null);
    } else {
      setShowActionMenu(index);
    }
  };

  const getTotalTeachers = (subject: Subject) => {
    // In a real app, you would count unique teachers assigned to this subject
    return subject.teachersCount;
  };

  const getAssignedClasses = (subject: Subject) => {
    // In a real app, you would count classes assigned to this subject
    return subject.classesCount;
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Mata Pelajaran</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <PlusCircle className="h-4 w-4 mr-2" />
          Tambah Mata Pelajaran
        </button>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari mata pelajaran..."
            className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2"
          />
        </div>
        <div className="flex space-x-2">
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Kategori</option>
            <option value="Wajib">Mata Pelajaran Wajib</option>
            <option value="Peminatan">Mata Pelajaran Peminatan</option>
            <option value="Muatan Lokal">Muatan Lokal</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Subjects Table */}
      <div className="overflow-x-auto shadow-sm rounded-lg">
        <table className="min-w-full divide-y divide-gray-200 bg-white">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Mata Pelajaran
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kode
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kategori
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jam Kredit
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Guru Pengajar
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kelas
              </th>
              <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {subjects.map((subject, index) => (
              <tr key={subject.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className={`flex-shrink-0 h-10 w-10 rounded-md bg-${subject.color}-100 flex items-center justify-center`}>
                      <Book className={`h-5 w-5 text-${subject.color}-600`} />
                    </div>
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">{subject.name}</div>
                      <div className="text-xs text-gray-500">{subject.description}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {subject.code}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    subject.category === 'Wajib' ? 'bg-blue-100 text-blue-800' : 
                    subject.category === 'Peminatan' ? 'bg-purple-100 text-purple-800' : 
                    'bg-green-100 text-green-800'
                  }`}>
                    {subject.category}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center text-sm text-gray-500">
                    <Clock className="h-4 w-4 mr-1" />
                    {subject.creditHours} jam
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center text-sm text-gray-500">
                    <GraduationCap className="h-4 w-4 mr-1" />
                    {getTotalTeachers(subject)} guru
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center text-sm text-gray-500">
                    <Layers className="h-4 w-4 mr-1" />
                    {getAssignedClasses(subject)} kelas
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium relative">
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
                          Edit Mata Pelajaran
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                          <User className="h-4 w-4 inline mr-2" />
                          Atur Pengajar
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                          <Trash2 className="h-4 w-4 inline mr-2" />
                          Hapus Mata Pelajaran
                        </button>
                      </div>
                    </div>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Menampilkan 1-10 dari 15 mata pelajaran
        </div>
        <div className="flex space-x-2">
          <button className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Sebelumnya
          </button>
          <button className="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
            1
          </button>
          <button className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50">
            2
          </button>
          <button className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50">
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  );
};

interface Subject {
  id: string;
  code: string;
  name: string;
  description: string;
  category: 'Wajib' | 'Peminatan' | 'Muatan Lokal';
  creditHours: number;
  teachersCount: number;
  classesCount: number;
  color: string;
}

// Mock data
const mockSubjects: Subject[] = [
  {
    id: '1',
    code: 'MTK',
    name: 'Matematika',
    description: 'Mata pelajaran wajib untuk semua kelas',
    category: 'Wajib',
    creditHours: 4,
    teachersCount: 3,
    classesCount: 7,
    color: 'blue'
  },
  {
    id: '2',
    code: 'BIN',
    name: 'Bahasa Indonesia',
    description: 'Mata pelajaran wajib untuk semua kelas',
    category: 'Wajib',
    creditHours: 4,
    teachersCount: 2,
    classesCount: 7,
    color: 'red'
  },
  {
    id: '3',
    code: 'BIG',
    name: 'Bahasa Inggris',
    description: 'Mata pelajaran wajib untuk semua kelas',
    category: 'Wajib',
    creditHours: 4,
    teachersCount: 2,
    classesCount: 7,
    color: 'purple'
  },
  {
    id: '4',
    code: 'FIS',
    name: 'Fisika',
    description: 'Mata pelajaran peminatan MIPA',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 3,
    color: 'orange'
  },
  {
    id: '5',
    code: 'KIM',
    name: 'Kimia',
    description: 'Mata pelajaran peminatan MIPA',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 3,
    color: 'green'
  },
  {
    id: '6',
    code: 'BIO',
    name: 'Biologi',
    description: 'Mata pelajaran peminatan MIPA',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 3,
    color: 'teal'
  },
  {
    id: '7',
    code: 'SEJ',
    name: 'Sejarah',
    description: 'Mata pelajaran peminatan IPS',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 2,
    color: 'amber'
  },
  {
    id: '8',
    code: 'GEO',
    name: 'Geografi',
    description: 'Mata pelajaran peminatan IPS',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 2,
    color: 'cyan'
  },
  {
    id: '9',
    code: 'EKO',
    name: 'Ekonomi',
    description: 'Mata pelajaran peminatan IPS',
    category: 'Peminatan',
    creditHours: 3,
    teachersCount: 1,
    classesCount: 2,
    color: 'indigo'
  },
  {
    id: '10',
    code: 'BTJ',
    name: 'Bahasa Jawa',
    description: 'Mata pelajaran muatan lokal',
    category: 'Muatan Lokal',
    creditHours: 2,
    teachersCount: 1,
    classesCount: 7,
    color: 'pink'
  }
];

export default SubjectData;