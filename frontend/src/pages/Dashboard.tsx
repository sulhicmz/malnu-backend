import React from 'react';
import { Users, GraduationCap, BookOpen, Calendar, ArrowUp, ArrowDown } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';

const Dashboard: React.FC = () => {
  // Mock data for charts
  const attendanceData = [
    { name: 'Jan', students: 95, teachers: 98 },
    { name: 'Feb', students: 92, teachers: 96 },
    { name: 'Mar', students: 94, teachers: 99 },
    { name: 'Apr', students: 97, teachers: 97 },
    { name: 'May', students: 93, teachers: 98 },
    { name: 'Jun', students: 90, teachers: 95 },
  ];

  const gradeData = [
    { name: 'Class A', nilai: 85 },
    { name: 'Class B', nilai: 78 },
    { name: 'Class C', nilai: 82 },
    { name: 'Class D', nilai: 88 },
    { name: 'Class E', nilai: 76 },
  ];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Dashboard Sekolah</h1>
        <div className="flex items-center space-x-2">
          <select className="bg-white border border-gray-300 rounded-md px-3 py-1.5 text-sm">
            <option>Tahun Ajaran 2024/2025</option>
            <option>Tahun Ajaran 2023/2024</option>
          </select>
          <button className="bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
            Export Data
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard 
          title="Total Siswa" 
          value="1,245" 
          change="+5.2%" 
          isIncrease={true}
          icon={<Users className="h-6 w-6 text-blue-500" />}
          color="blue"
        />
        <StatCard 
          title="Total Guru" 
          value="78" 
          change="+2.1%" 
          isIncrease={true}
          icon={<GraduationCap className="h-6 w-6 text-purple-500" />}
          color="purple"
        />
        <StatCard 
          title="Kelas Aktif" 
          value="42" 
          change="+0.0%" 
          isIncrease={false}
          icon={<BookOpen className="h-6 w-6 text-green-500" />}
          color="green"
        />
        <StatCard 
          title="Kehadiran" 
          value="97.3%" 
          change="-1.2%" 
          isIncrease={false}
          icon={<Calendar className="h-6 w-6 text-orange-500" />}
          color="orange"
        />
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Kehadiran Bulanan</h2>
            <div className="flex items-center space-x-2">
              <div className="flex items-center">
                <div className="w-3 h-3 rounded-full bg-blue-500 mr-1"></div>
                <span className="text-xs text-gray-600">Siswa</span>
              </div>
              <div className="flex items-center">
                <div className="w-3 h-3 rounded-full bg-green-500 mr-1"></div>
                <span className="text-xs text-gray-600">Guru</span>
              </div>
            </div>
          </div>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={attendanceData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="name" tickLine={false} axisLine={false} />
                <YAxis tickLine={false} axisLine={false} domain={[75, 100]} />
                <Tooltip />
                <Line type="monotone" dataKey="students" stroke="#3b82f6" strokeWidth={2} dot={{ r: 4 }} />
                <Line type="monotone" dataKey="teachers" stroke="#22c55e" strokeWidth={2} dot={{ r: 4 }} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Nilai Rata-Rata Kelas</h2>
            <select className="bg-white border border-gray-300 rounded-md px-2 py-1 text-sm">
              <option>Semester Ganjil</option>
              <option>Semester Genap</option>
            </select>
          </div>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={gradeData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="name" tickLine={false} axisLine={false} />
                <YAxis tickLine={false} axisLine={false} domain={[0, 100]} />
                <Tooltip />
                <Bar dataKey="nilai" fill="#8884d8" radius={[5, 5, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>

      {/* Recent Activities & Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white p-5 rounded-lg shadow-sm">
          <h2 className="text-lg font-medium text-gray-800 mb-4">Aktivitas Terbaru</h2>
          <div className="space-y-4">
            {activities.map((activity, index) => (
              <div key={index} className="flex items-start">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center mr-4 bg-${activity.color}-100`}>
                  {activity.icon}
                </div>
                <div>
                  <p className="text-sm font-medium text-gray-800">{activity.title}</p>
                  <p className="text-xs text-gray-500">{activity.time}</p>
                </div>
              </div>
            ))}
          </div>
          <button className="w-full mt-4 text-center text-sm text-blue-600 hover:text-blue-800">
            Lihat Semua Aktivitas
          </button>
        </div>

        <div className="bg-white p-5 rounded-lg shadow-sm">
          <h2 className="text-lg font-medium text-gray-800 mb-4">Aksi Cepat</h2>
          <div className="space-y-3">
            <button className="w-full py-2 px-4 bg-blue-50 text-blue-700 text-sm font-medium rounded hover:bg-blue-100 flex items-center">
              <Users className="h-4 w-4 mr-2" />
              Tambah Siswa Baru
            </button>
            <button className="w-full py-2 px-4 bg-purple-50 text-purple-700 text-sm font-medium rounded hover:bg-purple-100 flex items-center">
              <GraduationCap className="h-4 w-4 mr-2" />
              Tambah Data Guru
            </button>
            <button className="w-full py-2 px-4 bg-green-50 text-green-700 text-sm font-medium rounded hover:bg-green-100 flex items-center">
              <BookOpen className="h-4 w-4 mr-2" />
              Upload Materi Baru
            </button>
            <button className="w-full py-2 px-4 bg-orange-50 text-orange-700 text-sm font-medium rounded hover:bg-orange-100 flex items-center">
              <Calendar className="h-4 w-4 mr-2" />
              Jadwalkan Acara
            </button>
          </div>
          <button className="w-full mt-4 text-center text-sm text-blue-600 hover:text-blue-800">
            Lihat Semua Aksi
          </button>
        </div>
      </div>
    </div>
  );
};

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
          <h3 className="text-2xl font-bold text-gray-800 my-1">{value}</h3>
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

// Mock data for recent activities
const activities = [
  {
    icon: <Users className="h-5 w-5 text-blue-600" />,
    title: 'Siswa baru ditambahkan ke kelas X-A',
    time: '35 menit yang lalu',
    color: 'blue'
  },
  {
    icon: <GraduationCap className="h-5 w-5 text-purple-600" />,
    title: 'Ibu Siti mengunggah nilai UTS Matematika',
    time: '1 jam yang lalu',
    color: 'purple'
  },
  {
    icon: <BookOpen className="h-5 w-5 text-green-600" />,
    title: 'Materi pembelajaran baru ditambahkan untuk Fisika',
    time: '3 jam yang lalu',
    color: 'green'
  },
  {
    icon: <Calendar className="h-5 w-5 text-orange-600" />,
    title: 'Jadwal UAS semester ganjil diperbarui',
    time: '5 jam yang lalu',
    color: 'orange'
  }
];

export default Dashboard;