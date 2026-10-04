import React from 'react';
import { HashRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import Navbar from './components/Navbar';
import Footer from './components/Footer';

// Public Pages
import Home from './pages/Home';
import Browse from './pages/Browse';
import ProjectDetails from './pages/ProjectDetails';
import Rankings from './pages/Rankings';
import Login from './pages/Login';
import Register from './pages/Register';

// Student Pages
import StudentDashboard from './pages/StudentDashboard';
import SubmitProject from './pages/SubmitProject';
import MyProjects from './pages/MyProjects';
import StudentProfile from './pages/StudentProfile';

// Admin / Faculty Pages
import AdminDashboard from './pages/AdminDashboard';
import AdminProjects from './pages/AdminProjects';
import AdminEvaluate from './pages/AdminEvaluate';
import AdminCategories from './pages/AdminCategories';
import AdminStudents from './pages/AdminStudents';

// Route Protection Component
function ProtectedRoute({ children, roleRequired }) {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh' }}>
                <div className="spinner-border text-primary" role="status"></div>
                <p className="text-muted mt-2">Checking session...</p>
            </div>
        );
    }

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    if (roleRequired && user.role !== roleRequired) {
        return <Navigate to="/" replace />;
    }

    return children;
}

export default function App() {
    return (
        <AuthProvider>
            <HashRouter>
                <Navbar />
                <main>
                    <Routes>
                        {/* Public Routes */}
                        <Route path="/" element={<Home />} />
                        <Route path="/browse" element={<Browse />} />
                        <Route path="/project/:id" element={<ProjectDetails />} />
                        <Route path="/rankings" element={<Rankings />} />
                        <Route path="/login" element={<Login />} />
                        <Route path="/register" element={<Register />} />

                        {/* Student Routes */}
                        <Route path="/student" element={<ProtectedRoute roleRequired="student"><StudentDashboard /></ProtectedRoute>} />
                        <Route path="/student/dashboard" element={<ProtectedRoute roleRequired="student"><StudentDashboard /></ProtectedRoute>} />
                        <Route path="/student/submit" element={<ProtectedRoute roleRequired="student"><SubmitProject /></ProtectedRoute>} />
                        <Route path="/student/submit-project" element={<ProtectedRoute roleRequired="student"><SubmitProject /></ProtectedRoute>} />
                        <Route path="/student/projects" element={<ProtectedRoute roleRequired="student"><MyProjects /></ProtectedRoute>} />
                        <Route path="/student/my-projects" element={<ProtectedRoute roleRequired="student"><MyProjects /></ProtectedRoute>} />
                        <Route path="/student/my-evaluations" element={<ProtectedRoute roleRequired="student"><MyProjects /></ProtectedRoute>} />
                        <Route path="/student/profile" element={<ProtectedRoute roleRequired="student"><StudentProfile /></ProtectedRoute>} />

                        {/* Admin / Faculty Routes */}
                        <Route path="/admin" element={<ProtectedRoute roleRequired="admin"><AdminDashboard /></ProtectedRoute>} />
                        <Route path="/admin/dashboard" element={<ProtectedRoute roleRequired="admin"><AdminDashboard /></ProtectedRoute>} />
                        <Route path="/admin/projects" element={<ProtectedRoute roleRequired="admin"><AdminProjects /></ProtectedRoute>} />
                        <Route path="/admin/evaluate/:id" element={<ProtectedRoute roleRequired="admin"><AdminEvaluate /></ProtectedRoute>} />
                        <Route path="/admin/rankings" element={<ProtectedRoute roleRequired="admin"><Rankings /></ProtectedRoute>} />
                        <Route path="/admin/categories" element={<ProtectedRoute roleRequired="admin"><AdminCategories /></ProtectedRoute>} />
                        <Route path="/admin/students" element={<ProtectedRoute roleRequired="admin"><AdminStudents /></ProtectedRoute>} />
                        <Route path="/admin/feedback" element={<ProtectedRoute roleRequired="admin"><AdminProjects /></ProtectedRoute>} />

                        {/* Catch all fallback */}
                        <Route path="*" element={<Navigate to="/" replace />} />
                    </Routes>
                </main>
                <Footer />
            </HashRouter>
        </AuthProvider>
    );
}
