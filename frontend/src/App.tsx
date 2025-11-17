import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Analytics from './pages/Analytics';
import QuickActions from './pages/QuickActions';
import StudentData from './pages/school/StudentData';
import TeacherData from './pages/school/TeacherData';
import ClassData from './pages/school/ClassData';
import SubjectData from './pages/school/SubjectData';
import TeachingSchedule from './pages/school/TeachingSchedule';
import SchoolInventory from './pages/school/SchoolInventory';
import VirtualClasses from './pages/elearning/VirtualClasses';
import LearningMaterials from './pages/elearning/LearningMaterials';
import Assignments from './pages/elearning/Assignments';
import './index.css';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          {/* Dashboard Routes */}
          <Route path="/" element={<Dashboard />} />
          <Route path="/dashboard/analytics" element={<Analytics />} />
          <Route path="/dashboard/quick-actions" element={<QuickActions />} />
          
          {/* School Management Routes */}
          <Route path="/school/students" element={<StudentData />} />
          <Route path="/school/teachers" element={<TeacherData />} />
          <Route path="/school/classes" element={<ClassData />} />
          <Route path="/school/subjects" element={<SubjectData />} />
          <Route path="/school/schedule" element={<TeachingSchedule />} />
          <Route path="/school/inventory" element={<SchoolInventory />} />
          
          {/* E-Learning Routes */}
          <Route path="/elearning/classes" element={<VirtualClasses />} />
          <Route path="/elearning/materials" element={<LearningMaterials />} />
          <Route path="/elearning/assignments" element={<Assignments />} />
          
          {/* Default Route */}
          <Route path="*" element={<Dashboard />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;