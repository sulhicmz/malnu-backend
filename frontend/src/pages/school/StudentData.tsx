import React, { useState, useEffect } from 'react';
import { UserPlus, User, Edit, Trash2 } from 'lucide-react';
import { studentApi } from '../../services/api';
import useWebSocket from '../../hooks/useWebSocket';
import SearchFilter from '../../components/ui/SearchFilter';
import ActionMenu from '../../components/ui/ActionMenu';
import Pagination from '../../components/ui/Pagination';
import type { Student } from '../../types/api';

const StudentData: React.FC = () => {
  const [students, setStudents] = useState<Student[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const handleWebSocketMessage = (data: unknown) => {
    const message = data as { type: string; student?: Student; studentId?: string };
    if (message.type === 'student_update' && message.student) {
      setStudents(prev => {
        const existingIndex = prev.findIndex(s => s.id === message.student!.id);
        if (existingIndex !== -1) {
          const updated = [...prev];
          updated[existingIndex] = message.student!;
          return updated;
        }
        return [...prev, message.student!];
      });
    } else if (message.type === 'student_delete' && message.studentId) {
      setStudents(prev => prev.filter(s => s.id !== message.studentId));
    }
  };

  useWebSocket(handleWebSocketMessage);

  useEffect(() => {
    const fetchStudents = async () => {
      try {
        setLoading(true);
        const response = await studentApi.getAll();
        setStudents(response.data.data.data || response.data.data);
      } catch {
        setError('Failed to fetch students');
      } finally {
        setLoading(false);
      }
    };

    fetchStudents();
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 className="text-2xl font-bold text-gray-800">Data Siswa</h1>
        <button className="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-blue-500">
          <UserPlus className="h-4 w-4 mr-2" aria-hidden="true" />
          Tambah Siswa
        </button>
      </div>

      <SearchFilter
        searchPlaceholder="Cari siswa..."
        filterOptions={[
          { value: 'X-A', label: 'X-A' },
          { value: 'X-B', label: 'X-B' },
          { value: 'XI-A', label: 'XI-A' },
          { value: 'XI-B', label: 'XI-B' },
          { value: 'XII-A', label: 'XII-A' },
          { value: 'XII-B', label: 'XII-B' },
        ]}
        filterLabel="Semua Kelas"
        showExport={true}
        className="mb-6"
      />

      {loading && (
        <div className="flex justify-center items-center py-10">
          <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"></div>
        </div>
      )}

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert" aria-live="assertive">
          <strong className="font-bold">Error: </strong>
          <span className="block sm:inline">{error}</span>
        </div>
      )}

      {!loading && !error && (
        <div className="overflow-x-auto shadow-sm rounded-lg">
          <div className="inline-block min-w-full align-middle">
            <table className="min-w-full divide-y divide-gray-200 bg-white" role="grid" aria-label="Students data table">
              <caption className="sr-only">Table listing all students with their details. Scroll horizontally to see all columns.</caption>
              <thead className="bg-gray-50" role="rowgroup">
                <tr role="row">
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    Siswa
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    NISN
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    Kelas
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    Tahun Masuk
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    Status
                  </th>
                  <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" role="columnheader">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200" role="rowgroup">
                {students.map((student) => (
                  <tr key={student.id} className="hover:bg-gray-50" role="row">
                    <td className="px-6 py-4 whitespace-nowrap" role="gridcell">
                      <div className="flex items-center">
                        <div className="flex-shrink-0 h-10 w-10" aria-hidden="true">
                          {student.avatar ? (
                            <img className="h-10 w-10 rounded-full" src={student.avatar} alt={`Avatar of ${student.name}`} />
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
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.nisn}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.class}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.enrollmentYear}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap" role="gridcell">
                      <span 
                        className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                          student.status === 'active' ? 'bg-green-100 text-green-800' : 
                          student.status === 'inactive' ? 'bg-red-100 text-red-800' : 
                          'bg-yellow-100 text-yellow-800'
                        }`}
                        aria-label={`Status: ${student.status}`}
                      >
                        {student.status === 'active' ? 'Aktif' : 
                         student.status === 'inactive' ? 'Non-Aktif' : 
                         'Cuti'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium relative" role="gridcell">
                      <ActionMenu
                        label={`Actions for ${student.name}`}
                        actions={[
                          { label: 'Edit Siswa', icon: Edit, onClick: () => console.log('Edit', student.id) },
                          { label: 'Lihat Detail', icon: User, onClick: () => console.log('View', student.id) },
                          { label: 'Hapus Siswa', icon: Trash2, onClick: () => console.log('Delete', student.id), variant: 'danger' },
                        ]}
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {!loading && !error && students.length > 0 && (
        <Pagination
          currentPage={1}
          totalPages={3}
          onPageChange={(page) => console.log('Page changed to', page)}
          totalItems={students.length}
          itemsPerPage={students.length}
        />
      )}
    </div>
  );
};

export default StudentData;
