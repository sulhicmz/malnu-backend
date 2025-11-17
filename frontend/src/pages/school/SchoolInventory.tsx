import React, { useState } from 'react';
import { Search, Filter, PlusCircle, Download, Edit, Trash2, Package, Clock, MoreHorizontal, ArrowUp, ArrowDown } from 'lucide-react';

const SchoolInventory: React.FC = () => {
  const [inventoryItems] = useState<InventoryItem[]>(mockInventoryItems);
  const [showActionMenu, setShowActionMenu] = useState<number | null>(null);
  const [currentView, setCurrentView] = useState<'table' | 'card'>('table');

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
        <h1 className="text-2xl font-bold text-gray-800">Inventaris Sekolah</h1>
        <div className="flex space-x-2">
          <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
            <PlusCircle className="h-4 w-4 mr-2" />
            Tambah Inventaris
          </button>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export
          </button>
        </div>
      </div>

      {/* Filter and Search */}
      <div className="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div className="flex flex-1 max-w-md items-center rounded-md border border-gray-300 px-3 py-2 focus-within:ring-2 focus-within:ring-blue-600 focus-within:border-blue-600">
          <Search className="h-4 w-4 text-gray-500" />
          <input
            type="text"
            placeholder="Cari inventaris..."
            className="w-full border-0 focus:outline-none focus:ring-0 text-sm text-gray-600 ml-2"
          />
        </div>
        <div className="flex space-x-2">
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Filter className="h-4 w-4 mr-2" />
            Filter
          </button>
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Kategori</option>
            <option value="Furniture">Furniture</option>
            <option value="Electronics">Elektronik</option>
            <option value="Lab Equipment">Peralatan Lab</option>
            <option value="Sports Equipment">Peralatan Olahraga</option>
            <option value="Office">Perlengkapan Kantor</option>
          </select>
          <select className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium">
            <option value="">Semua Lokasi</option>
            <option value="Classroom">Ruang Kelas</option>
            <option value="Office">Kantor</option>
            <option value="Lab">Laboratorium</option>
            <option value="Library">Perpustakaan</option>
            <option value="Sports Hall">Aula Olahraga</option>
          </select>
          <div className="flex rounded-md overflow-hidden">
            <button
              onClick={() => setCurrentView('table')}
              className={`px-3 py-2 border border-r-0 ${
                currentView === 'table'
                  ? 'bg-blue-50 text-blue-600 border-blue-300'
                  : 'bg-white text-gray-500 border-gray-300'
              }`}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
              </svg>
            </button>
            <button
              onClick={() => setCurrentView('card')}
              className={`px-3 py-2 border ${
                currentView === 'card'
                  ? 'bg-blue-50 text-blue-600 border-blue-300'
                  : 'bg-white text-gray-500 border-gray-300'
              }`}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* Inventory Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard 
          title="Total Inventaris" 
          value="854" 
          change="+5.2%" 
          isIncrease={true}
          icon={<Package className="h-6 w-6 text-blue-500" />}
          color="blue"
        />
        <StatCard 
          title="Total Nilai Aset" 
          value="Rp 756.450.000" 
          change="+2.8%" 
          isIncrease={true}
          icon={<svg className="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>}
          color="green"
        />
        <StatCard 
          title="Stok Rendah" 
          value="12" 
          change="+50%" 
          isIncrease={false}
          icon={<svg className="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>}
          color="orange"
        />
        <StatCard 
          title="Perlu Pemeliharaan" 
          value="24" 
          change="-8.4%" 
          isIncrease={true}
          icon={<svg className="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
          </svg>}
          color="red"
        />
      </div>

      {/* Inventory List */}
      {currentView === 'table' ? (
        <div className="overflow-x-auto shadow-sm rounded-lg">
          <table className="min-w-full divide-y divide-gray-200 bg-white">
            <thead className="bg-gray-50">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Item
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kategori
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lokasi
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jumlah
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kondisi
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
              {inventoryItems.map((item, index) => (
                <tr key={item.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="flex-shrink-0 h-10 w-10">
                        <Package className="h-10 w-10 text-gray-400" />
                      </div>
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{item.name}</div>
                        <div className="text-xs text-gray-500">ID: {item.id}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {item.category}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {item.location}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {item.quantity} {item.quantity <= 10 && (
                      <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">
                        Stok Rendah
                      </span>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      item.condition === 'Baik' ? 'bg-green-100 text-green-800' : 
                      item.condition === 'Rusak Ringan' ? 'bg-yellow-100 text-yellow-800' : 
                      'bg-red-100 text-red-800'
                    }`}>
                      {item.condition}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div className="flex items-center">
                      <Clock className="h-4 w-4 mr-1" />
                      {item.lastUpdated}
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
                            Edit Inventaris
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            <svg className="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Catat Pemeliharaan
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                            <Trash2 className="h-4 w-4 inline mr-2" />
                            Hapus Inventaris
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
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {inventoryItems.map((item, index) => (
            <div key={item.id} className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
              <div className="p-4">
                <div className="flex justify-between items-start">
                  <div className="flex items-center">
                    <Package className="h-8 w-8 text-gray-400" />
                    <h3 className="ml-2 text-lg font-medium text-gray-900">{item.name}</h3>
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
                            Edit Inventaris
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            <svg className="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Catat Pemeliharaan
                          </button>
                          <button className="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem">
                            <Trash2 className="h-4 w-4 inline mr-2" />
                            Hapus Inventaris
                          </button>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
                
                <div className="mt-4 space-y-3">
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-500">Kategori:</span>
                    <span className="text-sm font-medium">{item.category}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-500">Lokasi:</span>
                    <span className="text-sm font-medium">{item.location}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-500">Jumlah:</span>
                    <span className="text-sm font-medium">
                      {item.quantity} {item.quantity <= 10 && (
                        <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">
                          Stok Rendah
                        </span>
                      )}
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-500">Kondisi:</span>
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      item.condition === 'Baik' ? 'bg-green-100 text-green-800' : 
                      item.condition === 'Rusak Ringan' ? 'bg-yellow-100 text-yellow-800' : 
                      'bg-red-100 text-red-800'
                    }`}>
                      {item.condition}
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-gray-500">Update Terakhir:</span>
                    <span className="text-sm">{item.lastUpdated}</span>
                  </div>
                </div>
              </div>
              <div className="bg-gray-50 px-4 py-3 flex justify-end">
                <button className="text-sm text-blue-600 hover:text-blue-800">Lihat Detail</button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-500">
          Menampilkan 1-10 dari 854 item
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

interface InventoryItem {
  id: string;
  name: string;
  category: string;
  quantity: number;
  location: string;
  condition: 'Baik' | 'Rusak Ringan' | 'Rusak Berat';
  purchaseDate?: string;
  lastUpdated: string;
}

interface StatCardProps {
  title: string;
  value: string;
  change: string;
  isIncrease: boolean;
  icon: React.ReactNode;
  color: string;
}

const StatCard: React.FC<StatCardProps> = ({ title, value, change, isIncrease, icon, color }) => {
  return (
    <div className="bg-white p-6 rounded-lg shadow-sm">
      <div className="flex justify-between items-start">
        <div>
          <p className="text-sm text-gray-500">{title}</p>
          <h3 className="text-xl font-bold text-gray-800 my-1">{value}</h3>
          <div className="flex items-center">
            <span className={`text-xs ${isIncrease ? 'text-green-600' : 'text-red-600'} font-medium flex items-center`}>
              {isIncrease ? <ArrowUp className="h-3 w-3 mr-1" /> : <ArrowDown className="h-3 w-3 mr-1" />}
              {change}
            </span>
            <span className="text-xs text-gray-500 ml-1">vs bulan lalu</span>
          </div>
        </div>
        <div className={`p-3 rounded-full bg-${color}-100`}>
          {icon}
        </div>
      </div>
    </div>
  );
};

// Mock data
const mockInventoryItems: InventoryItem[] = [
  {
    id: 'INV-001',
    name: 'Meja Siswa',
    category: 'Furniture',
    quantity: 150,
    location: 'Ruang Kelas',
    condition: 'Baik',
    purchaseDate: '2023-05-15',
    lastUpdated: '2024-02-10'
  },
  {
    id: 'INV-002',
    name: 'Kursi Siswa',
    category: 'Furniture',
    quantity: 150,
    location: 'Ruang Kelas',
    condition: 'Baik',
    purchaseDate: '2023-05-15',
    lastUpdated: '2024-02-10'
  },
  {
    id: 'INV-003',
    name: 'Proyektor',
    category: 'Electronics',
    quantity: 10,
    location: 'Ruang Kelas',
    condition: 'Baik',
    purchaseDate: '2022-08-20',
    lastUpdated: '2024-01-15'
  },
  {
    id: 'INV-004',
    name: 'Laptop',
    category: 'Electronics',
    quantity: 25,
    location: 'Lab Komputer',
    condition: 'Baik',
    purchaseDate: '2023-01-10',
    lastUpdated: '2024-02-20'
  },
  {
    id: 'INV-005',
    name: 'Mikroskop',
    category: 'Lab Equipment',
    quantity: 15,
    location: 'Lab Biologi',
    condition: 'Rusak Ringan',
    purchaseDate: '2021-11-05',
    lastUpdated: '2024-01-25'
  },
  {
    id: 'INV-006',
    name: 'Bola Basket',
    category: 'Sports Equipment',
    quantity: 8,
    location: 'Gudang Olahraga',
    condition: 'Rusak Ringan',
    purchaseDate: '2022-09-15',
    lastUpdated: '2023-12-05'
  },
  {
    id: 'INV-007',
    name: 'Bola Voli',
    category: 'Sports Equipment',
    quantity: 10,
    location: 'Gudang Olahraga',
    condition: 'Baik',
    purchaseDate: '2023-03-20',
    lastUpdated: '2023-12-05'
  },
  {
    id: 'INV-008',
    name: 'Printer',
    category: 'Electronics',
    quantity: 5,
    location: 'Ruang Guru',
    condition: 'Rusak Berat',
    purchaseDate: '2020-06-10',
    lastUpdated: '2024-01-30'
  },
  {
    id: 'INV-009',
    name: 'Alat Peraga Matematika',
    category: 'Lab Equipment',
    quantity: 20,
    location: 'Lab Matematika',
    condition: 'Baik',
    purchaseDate: '2023-07-12',
    lastUpdated: '2024-02-15'
  },
  {
    id: 'INV-010',
    name: 'Lemari Buku',
    category: 'Furniture',
    quantity: 12,
    location: 'Perpustakaan',
    condition: 'Baik',
    purchaseDate: '2022-04-25',
    lastUpdated: '2023-11-20'
  }
];

export default SchoolInventory;