import React, { useState } from 'react';
import { Calendar, PlusCircle, Download, Edit, Trash2, Clock, GraduationCap, User, MapPin } from 'lucide-react';

const TeachingSchedule: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'class' | 'teacher'>('class');
  const [selectedClass, setSelectedClass] = useState<string>('X-A');
  const [selectedTeacher, setSelectedTeacher] = useState<string>('1');
  const timeSlots = ['07:30 - 08:15', '08:15 - 09:00', '09:00 - 09:45', '10:00 - 10:45', '10:45 - 11:30', '11:30 - 12:15', '13:00 - 13:45', '13:45 - 14:30'];
  const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at'];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Jadwal Mengajar</h1>
        <div className="flex space-x-2">
          <button className="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center">
            <PlusCircle className="h-4 w-4 mr-2" />
            Tambah Jadwal
          </button>
          <button className="px-4 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm font-medium flex items-center hover:bg-gray-50">
            <Download className="h-4 w-4 mr-2" />
            Export Jadwal
          </button>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <nav className="-mb-px flex space-x-6">
          <button
            onClick={() => setActiveTab('class')}
            className={`py-4 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'class'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Jadwal Per Kelas
          </button>
          <button
            onClick={() => setActiveTab('teacher')}
            className={`py-4 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'teacher'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Jadwal Per Guru
          </button>
        </nav>
      </div>

      {/* Selector */}
      <div className="bg-white p-4 rounded-lg shadow-sm">
        {activeTab === 'class' ? (
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <Calendar className="h-5 w-5 text-gray-500" />
              <span className="text-gray-700 font-medium">Pilih Kelas:</span>
            </div>
            <select
              value={selectedClass}
              onChange={(e) => setSelectedClass(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="X-A">X-A</option>
              <option value="X-B">X-B</option>
              <option value="X-C">X-C</option>
              <option value="XI-A">XI-A</option>
              <option value="XI-B">XI-B</option>
              <option value="XII-A">XII-A</option>
              <option value="XII-B">XII-B</option>
            </select>
            <span className="text-sm text-gray-500">
              Wali Kelas: <span className="font-medium">Drs. Agus Supriyanto</span>
            </span>
          </div>
        ) : (
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <User className="h-5 w-5 text-gray-500" />
              <span className="text-gray-700 font-medium">Pilih Guru:</span>
            </div>
            <select
              value={selectedTeacher}
              onChange={(e) => setSelectedTeacher(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="1">Drs. Agus Supriyanto (Matematika)</option>
              <option value="2">Dra. Budi Setiawati, M.Pd (Bahasa Indonesia)</option>
              <option value="3">Cahyono, S.Pd (Bahasa Inggris)</option>
              <option value="4">Dewi Anggraini, S.Si (IPA - Fisika)</option>
              <option value="5">Eko Prasetyo, S.Pd, M.Pd (IPA - Biologi)</option>
              <option value="6">Fitri Handayani, S.Pd (IPS - Geografi)</option>
            </select>
            <span className="text-sm text-gray-500">
              Beban Mengajar: <span className="font-medium">24 jam / minggu</span>
            </span>
          </div>
        )}
      </div>

      {/* Schedule Timetable */}
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                  Jam
                </th>
                {days.map((day) => (
                  <th key={day} className="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {day}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {timeSlots.map((timeSlot, timeIndex) => (
                <tr key={timeSlot} className={timeIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                  <td className="px-6 py-3 whitespace-nowrap text-sm text-gray-500 font-medium">
                    <div className="flex items-center">
                      <Clock className="h-4 w-4 mr-2 text-gray-400" />
                      {timeSlot}
                    </div>
                  </td>
                  {days.map((day) => {
                    // Determine if there's a class scheduled for this slot
                    const hasClass = Math.random() > 0.5; // Mock data logic

                    // Generate random class data
                    const subjectColors = ['blue', 'green', 'purple', 'red', 'orange', 'indigo'];
                    const randomSubject = ['Matematika', 'B. Indonesia', 'B. Inggris', 'Fisika', 'Kimia', 'Biologi'][
                      Math.floor(Math.random() * 6)
                    ];
                    const randomTeacher = ['Agus S.', 'Budi S.', 'Cahyono', 'Dewi A.', 'Eko P.', 'Fitri H.'][
                      Math.floor(Math.random() * 6)
                    ];
                    const randomClass = ['X-A', 'X-B', 'X-C', 'XI-A', 'XI-B', 'XII-A', 'XII-B'][
                      Math.floor(Math.random() * 7)
                    ];
                    const randomRoom = ['R-101', 'R-102', 'R-103', 'Lab-1', 'Lab-2', 'Perpus'][
                      Math.floor(Math.random() * 6)
                    ];
                    const colorIndex = Math.floor(Math.random() * subjectColors.length);
                    const color = subjectColors[colorIndex];

                    return (
                      <td key={`${day}-${timeSlot}`} className="px-6 py-3 whitespace-nowrap text-sm">
                        {(hasClass && 
                          // Only show if viewing class schedule OR if teacher matches selected teacher
                          ((activeTab === 'class' && randomClass === selectedClass) || 
                           (activeTab === 'teacher' && randomTeacher === 'Agus S.' && parseInt(selectedTeacher) === 1))) ? (
                          <div className={`rounded-md p-2 bg-${color}-50 border border-${color}-200`}>
                            <div className="font-medium text-gray-800">{randomSubject}</div>
                            <div className="flex items-center mt-1">
                              {activeTab === 'class' ? (
                                <div className="flex items-center text-xs text-gray-500">
                                  <GraduationCap className="h-3 w-3 mr-1" />
                                  {randomTeacher}
                                </div>
                              ) : (
                                <div className="flex items-center text-xs text-gray-500">
                                  <User className="h-3 w-3 mr-1" />
                                  {randomClass}
                                </div>
                              )}
                              <div className="flex items-center text-xs text-gray-500 ml-3">
                                <MapPin className="h-3 w-3 mr-1" />
                                {randomRoom}
                              </div>
                            </div>
                            <div className="flex justify-end mt-1 space-x-1">
                              <button className="p-1 rounded-md hover:bg-gray-100">
                                <Edit className="h-3 w-3 text-gray-500" />
                              </button>
                              <button className="p-1 rounded-md hover:bg-gray-100">
                                <Trash2 className="h-3 w-3 text-gray-500" />
                              </button>
                            </div>
                          </div>
                        ) : (
                          <div className="h-16 flex items-center justify-center text-gray-300 italic">
                            {/* Empty slot */}
                          </div>
                        )}
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default TeachingSchedule;