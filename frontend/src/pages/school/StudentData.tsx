import React, { useState } from 'react';
import { Search, Filter, UserPlus, Download, MoreHorizontal, User, Edit, Trash2 } from 'lucide-react';

const StudentData: React.FC = () => {
  const [students] = useState<Student[]>(mockStudents);
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
        <h1 className="text-2xl font-bold text-gray-800">Data Siswa</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <UserPlus className="h-4 w-4 mr-2" />
          Tambah Siswa
        </button>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari siswa..."
            className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2"
          />
        </div>
        <div className="flex space-x-2">
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Filter className="h-4 w-4 mr-2" />
            Filter
          </button>
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Kelas</option>
            <option value="X-A">X-A</option>
            <option value="X-B">X-B</option>
            <option value="XI-A">XI-A</option>
            <option value="XI-B">XI-B</option>
            <option value="XII-A">XII-A</option>
            <option value="XII-B">XII-B</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Students Table */}
      <div className="overflow-x-auto shadow-sm rounded-lg">
        <table className="min-w-full divide-y divide-gray-200 bg-white">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Siswa
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                NISN
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kelas
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tahun Masuk
              </th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {students.map((student, index) => (
              <tr key={student.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="flex items-center">
                    <div className="flex-shrink-0 h-10 w-10">
                      {student.avatar ? (
                        <img className="h-10 w-10 rounded-full" src={student.avatar} alt={student.name} />
                      ) : (
                        <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                          <User className="h-5 w-5 text-gray-500" />
                        </div>
                      )}
                    </div>
                    <div className="ml-4">
                      <div className="text-sm font-medium text-gray-900">{student.name}</div>
                      <div className="text-sm text-gray-500">{student.email}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {student.nisn}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {student.class}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {student.enrollmentYear}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    student.status === 'active' ? 'bg-green-100 text-green-800' : 
                    student.status === 'inactive' ? 'bg-red-100 text-red-800' : 
                    'bg-yellow-100 text-yellow-800'
                  }`}>
                    {student.status === 'active' ? 'Aktif' : 
                     student.status === 'inactive' ? 'Non-Aktif' : 
                     'Cuti'}
                  </span>
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
                          Edit Siswa
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                          <User className="h-4 w-4 inline mr-2" />
                          Lihat Detail
                        </button>
                        <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                          <Trash2 className="h-4 w-4 inline mr-2" />
                          Hapus Siswa
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
          Menampilkan 1-10 dari 120 siswa
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
            3
          </button>
          <button className="px-3 py-1 border border-gray-300 rounded-md bg-white text-gray-700 text-sm hover:bg-gray-50">
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  );
};

interface Student {
  id: string;
  name: string;
  nisn: string;
  class: string;
  enrollmentYear: string;
  status: 'active' | 'inactive' | 'leave';
  email: string;
  avatar?: string;
}

// Mock data
const mockStudents: Student[] = [
  {
    id: '1',
    name: 'Ahmad Rizky',
    nisn: '10119321',
    class: 'X-A',
    enrollmentYear: '2024',
    status: 'active',
    email: 'ahmad.rizky@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/men/1.jpg'
  },
  {
    id: '2',
    name: 'Siti Nurhaliza',
    nisn: '10119322',
    class: 'X-A',
    enrollmentYear: '2024',
    status: 'active',
    email: 'siti.nurhaliza@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/women/2.jpg'
  },
  {
    id: '3',
    name: 'Budi Santoso',
    nisn: '10119323',
    class: 'X-B',
    enrollmentYear: '2024',
    status: 'active',
    email: 'budi.santoso@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/men/3.jpg'
  },
  {
    id: '4',
    name: 'Diana Putri',
    nisn: '10119324',
    class: 'X-B',
    enrollmentYear: '2024',
    status: 'leave',
    email: 'diana.putri@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/women/4.jpg'
  },
  {
    id: '5',
    name: 'Eko Prasetyo',
    nisn: '10119325',
    class: 'XI-A',
    enrollmentYear: '2023',
    status: 'active',
    email: 'eko.prasetyo@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/men/5.jpg'
  },
  {
    id: '6',
    name: 'Fitriani Dewi',
    nisn: '10119326',
    class: 'XI-A',
    enrollmentYear: '2023',
    status: 'inactive',
    email: 'fitriani.dewi@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/women/6.jpg'
  },
  {
    id: '7',
    name: 'Galih Pratama',
    nisn: '10119327',
    class: 'XI-B',
    enrollmentYear: '2023',
    status: 'active',
    email: 'galih.pratama@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/men/7.jpg'
  },
  {
    id: '8',
    name: 'Hana Safira',
    nisn: '10119328',
    class: 'XI-B',
    enrollmentYear: '2023',
    status: 'active',
    email: 'hana.safira@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/women/8.jpg'
  },
  {
    id: '9',
    name: 'Irfan Hidayat',
    nisn: '10119329',
    class: 'XII-A',
    enrollmentYear: '2022',
    status: 'active',
    email: 'irfan.hidayat@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/men/9.jpg'
  },
  {
    id: '10',
    name: 'Jasmine Putri',
    nisn: '10119330',
    class: 'XII-A',
    enrollmentYear: '2022',
    status: 'active',
    email: 'jasmine.putri@student.school.com',
    avatar: 'https://randomuser.me/api/portraits/women/10.jpg'
  }
];

export default StudentData;