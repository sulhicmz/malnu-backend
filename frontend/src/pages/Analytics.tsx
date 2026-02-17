import React from 'react';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const Analytics: React.FC = () => {
  // Mock data for charts
  const attendanceData = [
    { month: 'Jan', percentage: 95 },
    { month: 'Feb', percentage: 92 },
    { month: 'Mar', percentage: 94 },
    { month: 'Apr', percentage: 97 },
    { month: 'May', percentage: 93 },
    { month: 'Jun', percentage: 90 },
  ];

  const subjectData = [
    { name: 'Matematika', nilai: 75 },
    { name: 'B. Indonesia', nilai: 82 },
    { name: 'B. Inggris', nilai: 78 },
    { name: 'IPA', nilai: 85 },
    { name: 'IPS', nilai: 80 },
  ];

  const genderData = [
    { name: 'Laki-laki', value: 540 },
    { name: 'Perempuan', value: 620 },
  ];

  const COLORS = ['#0088FE', '#FF8042'];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Analytics</h1>
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

      {/* Filter Cards */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <FilterCard title="Semua Kelas" isActive={true} />
        <FilterCard title="Kelas X" isActive={false} />
        <FilterCard title="Kelas XI" isActive={false} />
        <FilterCard title="Kelas XII" isActive={false} />
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Attendance Chart */}
        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Grafik Kehadiran</h2>
            <select className="bg-white border border-gray-300 rounded-md px-2 py-1 text-sm">
              <option>6 Bulan Terakhir</option>
              <option>1 Tahun Terakhir</option>
            </select>
          </div>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={attendanceData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="month" tickLine={false} axisLine={false} />
                <YAxis domain={[60, 100]} tickLine={false} axisLine={false} />
                <Tooltip />
                <Line type="monotone" dataKey="percentage" stroke="#3b82f6" strokeWidth={2} dot={{ r: 4 }} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Subject Performance Chart */}
        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Performa per Mata Pelajaran</h2>
            <select className="bg-white border border-gray-300 rounded-md px-2 py-1 text-sm">
              <option>Semester Ganjil</option>
              <option>Semester Genap</option>
            </select>
          </div>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={subjectData}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="name" tickLine={false} axisLine={false} />
                <YAxis domain={[0, 100]} tickLine={false} axisLine={false} />
                <Tooltip />
                <Bar dataKey="nilai" fill="#8884d8" radius={[5, 5, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Demographics Chart */}
        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Demografi Siswa</h2>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="h-48">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={genderData}
                    cx="50%"
                    cy="50%"
                    outerRadius={60}
                    fill="#8884d8"
                    dataKey="value"
                    label={({name, percent}) => `${name}: ${(percent * 100).toFixed(0)}%`}
                  >
                    {genderData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="flex flex-col justify-center">
              <h3 className="text-sm font-medium text-gray-700 mb-3">Gender Ratio</h3>
              {genderData.map((item, index) => (
                <div key={index} className="flex items-center mb-2">
                  <div className="w-3 h-3 rounded-full mr-2" style={{ backgroundColor: COLORS[index] }}></div>
                  <span className="text-sm text-gray-600">{item.name}: {item.value} siswa</span>
                </div>
              ))}
              <p className="text-xs text-gray-500 mt-2">Total: 1,160 siswa</p>
            </div>
          </div>
        </div>

        {/* Academic Progress */}
        <div className="bg-white p-5 rounded-lg shadow-sm">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-medium text-gray-800">Progres Akademik</h2>
            <select className="bg-white border border-gray-300 rounded-md px-2 py-1 text-sm">
              <option>Kelas X</option>
              <option>Kelas XI</option>
              <option>Kelas XII</option>
            </select>
          </div>
          <div className="space-y-4">
            <ProgressItem subject="Matematika" value={75} color="blue" />
            <ProgressItem subject="B. Indonesia" value={82} color="green" />
            <ProgressItem subject="B. Inggris" value={78} color="indigo" />
            <ProgressItem subject="IPA" value={85} color="purple" />
            <ProgressItem subject="IPS" value={80} color="pink" />
          </div>
        </div>
      </div>
    </div>
  );
};

interface FilterCardProps {
  title: string;
  isActive: boolean;
}

const FilterCard: React.FC<FilterCardProps> = ({ title, isActive }) => {
  return (
    <div className={`p-3 rounded-md cursor-pointer ${isActive ? 'bg-blue-50 border-2 border-blue-500' : 'bg-white border border-gray-200'}`}>
      <p className={`text-center text-sm font-medium ${isActive ? 'text-blue-700' : 'text-gray-700'}`}>{title}</p>
    </div>
  );
};

interface ProgressItemProps {
  subject: string;
  value: number;
  color: string;
}

const ProgressItem: React.FC<ProgressItemProps> = ({ subject, value, color }) => {
  return (
    <div>
      <div className="flex justify-between items-center mb-1">
        <span className="text-sm text-gray-700">{subject}</span>
        <span className="text-sm font-medium text-gray-700">{value}%</span>
      </div>
      <div className="w-full bg-gray-200 rounded-full h-2.5">
        <div className={`bg-${color}-500 h-2.5 rounded-full`} style={{ width: `${value}%` }}></div>
      </div>
    </div>
  );
};

export default Analytics;