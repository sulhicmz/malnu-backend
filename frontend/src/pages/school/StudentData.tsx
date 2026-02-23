import React, { useState, useEffect, useCallback } from 'react';
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
  const [deleteLoading, setDeleteLoading] = useState<string | null>(null);

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


  const handleDeleteStudent = useCallback(async (student: Student) => {
    const confirmed = window.confirm(
      `Apakah Anda yakin ingin menghapus siswa "${student.name}"?\n\nTindakan ini tidak dapat dibatalkan.`
    );
    if (!confirmed) return;

    try {
      setDeleteLoading(student.id);
      await studentApi.delete(student.id);
      setStudents(prev => prev.filter(s => s.id !== student.id));
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Gagal menghapus siswa';
      alert(`Error: ${errorMessage}`);
    } finally {
      setDeleteLoading(null);
    }
  }, []);


  const handleViewStudent = useCallback((student: Student) => {
    const statusLabel = student.status === 'active' ? 'Aktif' :
                       student.status === 'inactive' ? 'Non-Aktif' :
                       student.status === 'graduated' ? 'Lulus' :
                       student.status === 'dropped_out' ? 'Keluar' : 'Cuti';
    const details = [
      `Nama: ${student.name}`,
      `NISN: ${student.nisn}`,
      `Status: ${statusLabel}`,
      student.class ? `Kelas: ${student.class}` : '',
      student.email ? `Email: ${student.email}` : '',
      student.birth_date ? `Tanggal Lahir: ${new Date(student.birth_date).toLocaleDateString('id-ID')}` : '',
      student.enrollment_date ? `Tanggal Daftar: ${new Date(student.enrollment_date).toLocaleDateString('id-ID')}` : '',
      student.enrollmentYear ? `Tahun Masuk: ${student.enrollmentYear}` : '',
    ].filter(Boolean).join('\n');
    
    alert(`Detail Siswa\n\n${details}`);
  }, []);


  const handleEditStudent = useCallback((student: Student) => {
    // TODO: Implement edit functionality with modal or navigation

    alert(`Fitur edit untuk "${student.name}" belum tersedia.\nSilakan hubungi administrator untuk perubahan data.`);
  }, []);


  const getStatusStyles = (status: Student['status']) => {
    switch (status) {
      case 'active':
        return 'bg-green-100 text-green-800';
      case 'inactive':
        return 'bg-red-100 text-red-800';
      case 'graduated':
        return 'bg-blue-100 text-blue-800';
      case 'dropped_out':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-yellow-100 text-yellow-800';
    }
  };


  const getStatusLabel = (status: Student['status']) => {
    switch (status) {
      case 'active':
        return 'Aktif';
      case 'inactive':
        return 'Non-Aktif';
      case 'graduated':
        return 'Lulus';
      case 'dropped_out':
        return 'Keluar';
      default:
        return 'Cuti';
    }
  };

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
                          <div className="text-sm text-gray-500">{student.email || '-'}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.nisn}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.class || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500" role="gridcell">
                      {student.enrollmentYear || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap" role="gridcell">
                      <span 
                        className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusStyles(student.status)}`}
                        aria-label={`Status: ${getStatusLabel(student.status)}`}
                      >
                        {getStatusLabel(student.status)}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium relative" role="gridcell">
                      <ActionMenu
                        label={`Actions for ${student.name}`}
                        actions={[
                          { 
                            label: deleteLoading === student.id ? 'Menghapus...' : 'Edit Siswa', 
                            icon: Edit, 
                            onClick: () => handleEditStudent(student),
                            disabled: deleteLoading === student.id
                          },
                          { 
                            label: 'Lihat Detail', 
                            icon: User, 
                            onClick: () => handleViewStudent(student),
                            disabled: deleteLoading === student.id
                          },
                          { 
                            label: deleteLoading === student.id ? 'Menghapus...' : 'Hapus Siswa', 
                            icon: Trash2, 
                            onClick: () => handleDeleteStudent(student), 
                            variant: 'danger',
                            disabled: deleteLoading === student.id
                          },
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

      {!loading && !error && students.length === 0 && (
        <div className="bg-white rounded-lg shadow-sm p-8 text-center">
          <div className="flex flex-col items-center">
            <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
              <User className="h-8 w-8 text-gray-400" />
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-1">Tidak ada data siswa</h3>
            <p className="text-sm text-gray-500 mb-4">Belum ada data siswa yang terdaftar. Tambahkan siswa pertama untuk memulai.</p>
            <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
              <UserPlus className="h-4 w-4 mr-2" />
              Tambah Siswa
            </button>
          </div>
        </div>
      )}

      {!loading && !error && students.length > 0 && (
        <Pagination
          currentPage={1}
          totalPages={Math.ceil(students.length / 10)}
          onPageChange={(page) => {
            // TODO: Implement real pagination with API call
            console.info('Page change requested:', page);
          }}
          totalItems={students.length}
          itemsPerPage={10}
        />
      )}
    </div>
  );
};

export default StudentData;
