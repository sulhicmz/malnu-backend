import React, { useState } from 'react';
import { Search, Filter, PlusCircle, Download, MoreHorizontal, Edit, Trash2, FileText, Video, Link as LinkIcon, Clock, Eye } from 'lucide-react';

const LearningMaterials: React.FC = () => {
  const [materials] = useState<LearningMaterial[]>(mockMaterials);
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
        <h1 className="text-2xl font-bold text-gray-800">Materi Pembelajaran</h1>
        <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
          <PlusCircle className="h-4 w-4 mr-2" />
          Upload Materi
        </button>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari materi..."
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
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Tipe</option>
            <option value="document">Dokumen</option>
            <option value="video">Video</option>
            <option value="link">Link</option>
          </select>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Materials List */}
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Materi
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Mata Pelajaran
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kelas
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Dilihat
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Terakhir Diperbarui
                </th>
                <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {materials.map((material, index) => (
                <tr key={material.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className={`flex-shrink-0 h-10 w-10 rounded-lg bg-${material.color}-100 flex items-center justify-center`}>
                        {material.type === 'document' && <FileText className={`h-5 w-5 text-${material.color}-600`} />}
                        {material.type === 'video' && <Video className={`h-5 w-5 text-${material.color}-600`} />}
                        {material.type === 'link' && <LinkIcon className={`h-5 w-5 text-${material.color}-600`} />}
                      </div>
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{material.title}</div>
                        <div className="text-sm text-gray-500">{material.description}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{material.subject}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{material.class}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center text-sm text-gray-500">
                      <Eye className="h-4 w-4 mr-1" />
                      {material.views}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      material.status === 'published' ? 'bg-green-100 text-green-800' : 
                      material.status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                      'bg-yellow-100 text-yellow-800'
                    }`}>
                      {material.status === 'published' ? 'Dipublikasi' : 
                       material.status === 'draft' ? 'Draft' : 
                       'Menunggu Review'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center text-sm text-gray-500">
                      <Clock className="h-4 w-4 mr-1" />
                      {material.lastUpdated}
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
                            Edit Materi
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            <Eye className="h-4 w-4 inline mr-2" />
                            Lihat Materi
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                            <Trash2 className="h-4 w-4 inline mr-2" />
                            Hapus Materi
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
      </div>

      {/* Pagination */}
      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Menampilkan 1-10 dari 45 materi
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

interface LearningMaterial {
  id: string;
  title: string;
  description: string;
  type: 'document' | 'video' | 'link';
  subject: string;
  class: string;
  views: number;
  status: 'published' | 'draft' | 'pending';
  lastUpdated: string;
  color: string;
}

const mockMaterials: LearningMaterial[] = [
  {
    id: '1',
    title: 'Pengenalan Aljabar',
    description: 'Materi dasar aljabar untuk kelas X',
    type: 'document',
    subject: 'Matematika',
    class: 'X-A',
    views: 156,
    status: 'published',
    lastUpdated: '2 jam yang lalu',
    color: 'blue'
  },
  {
    id: '2',
    title: 'Tutorial Praktikum Fisika',
    description: 'Video demonstrasi eksperimen fisika',
    type: 'video',
    subject: 'IPA',
    class: 'XI-A',
    views: 89,
    status: 'published',
    lastUpdated: '1 hari yang lalu',
    color: 'green'
  },
  {
    id: '3',
    title: 'Referensi Sastra Indonesia',
    description: 'Kumpulan link artikel sastra',
    type: 'link',
    subject: 'Bahasa Indonesia',
    class: 'X-B',
    views: 45,
    status: 'draft',
    lastUpdated: '3 hari yang lalu',
    color: 'purple'
  },
  {
    id: '4',
    title: 'Materi Sejarah Kemerdekaan',
    description: 'Dokumen lengkap sejarah kemerdekaan',
    type: 'document',
    subject: 'IPS',
    class: 'XII-A',
    views: 234,
    status: 'pending',
    lastUpdated: '5 hari yang lalu',
    color: 'orange'
  }
];

export default LearningMaterials;