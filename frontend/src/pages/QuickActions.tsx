import React from 'react';
import { 
  User, 
  Users, 
  BookOpen, 
  Briefcase, 
  Bell, 
  Calendar, 
  FileText, 
  MessageCircle, 
  Mail,
  PenSquare,
  GraduationCap,
  PackageOpen,
  Download,
  BarChart
} from 'lucide-react';

const QuickActions: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Aksi Cepat</h1>
      </div>

      <p className="text-gray-600">
        Gunakan menu aksi cepat untuk mengakses fungsi-fungsi sistem yang sering digunakan.
      </p>

      {/* Student Management */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-medium text-gray-800 mb-4">Manajemen Siswa</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ActionCard 
            icon={<User className="h-6 w-6 text-blue-500" />} 
            title="Tambah Siswa" 
            description="Daftarkan siswa baru ke sistem" 
            color="blue"
          />
          <ActionCard 
            icon={<Users className="h-6 w-6 text-indigo-500" />} 
            title="Kelola Kelas" 
            description="Atur penempatan kelas siswa" 
            color="indigo"
          />
          <ActionCard 
            icon={<GraduationCap className="h-6 w-6 text-purple-500" />} 
            title="Input Nilai" 
            description="Masukkan nilai siswa" 
            color="purple"
          />
          <ActionCard 
            icon={<FileText className="h-6 w-6 text-pink-500" />} 
            title="Cetak Raport" 
            description="Generate raport siswa" 
            color="pink"
          />
        </div>
      </div>

      {/* Teacher Management */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-medium text-gray-800 mb-4">Manajemen Guru & Staff</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ActionCard 
            icon={<User className="h-6 w-6 text-green-500" />} 
            title="Tambah Guru" 
            description="Daftarkan guru baru ke sistem" 
            color="green"
          />
          <ActionCard 
            icon={<Briefcase className="h-6 w-6 text-teal-500" />} 
            title="Tugas Mengajar" 
            description="Kelola jadwal dan beban mengajar" 
            color="teal"
          />
          <ActionCard 
            icon={<Calendar className="h-6 w-6 text-cyan-500" />} 
            title="Presensi Guru" 
            description="Kelola kehadiran guru" 
            color="cyan"
          />
          <ActionCard 
            icon={<PackageOpen className="h-6 w-6 text-emerald-500" />} 
            title="Kelola Staff" 
            description="Kelola data staff non-pengajar" 
            color="emerald"
          />
        </div>
      </div>

      {/* Learning Management */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-medium text-gray-800 mb-4">Pembelajaran</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ActionCard 
            icon={<BookOpen className="h-6 w-6 text-red-500" />} 
            title="Upload Materi" 
            description="Tambahkan materi pembelajaran baru" 
            color="red"
          />
          <ActionCard 
            icon={<PenSquare className="h-6 w-6 text-orange-500" />} 
            title="Buat Ujian" 
            description="Siapkan ujian online baru" 
            color="orange"
          />
          <ActionCard 
            icon={<MessageCircle className="h-6 w-6 text-amber-500" />} 
            title="Diskusi" 
            description="Buat forum diskusi baru" 
            color="amber"
          />
          <ActionCard 
            icon={<BarChart className="h-6 w-6 text-yellow-500" />} 
            title="Analisis Hasil" 
            description="Analisis hasil belajar siswa" 
            color="yellow"
          />
        </div>
      </div>

      {/* Communication */}
      <div className="bg-white rounded-lg shadow-sm p-6">
        <h2 className="text-lg font-medium text-gray-800 mb-4">Komunikasi</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ActionCard 
            icon={<Bell className="h-6 w-6 text-blue-500" />} 
            title="Pengumuman" 
            description="Buat pengumuman penting" 
            color="blue"
          />
          <ActionCard 
            icon={<Mail className="h-6 w-6 text-indigo-500" />} 
            title="Email Massal" 
            description="Kirim email ke grup tertentu" 
            color="indigo"
          />
          <ActionCard 
            icon={<Download className="h-6 w-6 text-violet-500" />} 
            title="Laporan" 
            description="Unduh laporan-laporan penting" 
            color="violet"
          />
          <ActionCard 
            icon={<Calendar className="h-6 w-6 text-purple-500" />} 
            title="Jadwalkan" 
            description="Buat jadwal acara sekolah" 
            color="purple"
          />
        </div>
      </div>
    </div>
  );
};

interface ActionCardProps {
  icon: React.ReactNode;
  title: string;
  description: string;
  color: string;
}

const ActionCard: React.FC<ActionCardProps> = ({ icon, title, description, color }) => {
  return (
    <div className={`border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer hover:border-${color}-200`}>
      <div className={`w-12 h-12 rounded-full bg-${color}-100 flex items-center justify-center mb-3`}>
        {icon}
      </div>
      <h3 className="text-sm font-medium text-gray-800 mb-1">{title}</h3>
      <p className="text-xs text-gray-500">{description}</p>
    </div>
  );
};

export default QuickActions;