import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiGetStats, apiGetMyProjects } from '../api';
import { useAuth } from '../context/AuthContext';

export default function StudentDashboard() {
    const { user } = useAuth();
    const [stats, setStats] = useState({ total_submitted: 0, approved: 0, pending: 0, rejected: 0, highest_score: 0 });
    const [myProjects, setMyProjects] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);

        Promise.all([apiGetStats('student'), apiGetMyProjects()])
            .then(([statsRes, projRes]) => {
                if (!isMounted) return;
                if (statsRes.success) setStats(statsRes.stats || {});
                if (projRes.success) setMyProjects(projRes.projects || []);
            })
            .catch((err) => console.error("Error loading student dashboard:", err))
            .finally(() => {
                if (isMounted) setLoading(false);
            });

        return () => { isMounted = false; };
    }, []);

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Welcome Banner */}
                <div className="content-card mb-4 p-4 shadow-sm bg-primary text-white border-0">
                    <div className="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <span className="badge bg-white text-primary fw-bold mb-2">Student Portal</span>
                            <h2 className="fw-bold text-white mb-1">Welcome back, {user?.name || 'Student'}!</h2>
                            <p className="text-white-50 mb-0">
                                Track your academic project submissions, view faculty rubric marks, and monitor leaderboard rankings.
                            </p>
                        </div>
                        <div>
                            <Link to="/student/submit" className="btn btn-warning text-dark fw-bold shadow-sm">
                                <i className="bi bi-cloud-plus-fill me-1"></i> Submit New Project
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Metrics Cards */}
                <div className="row g-3 mb-4">
                    <div className="col-6 col-md-3">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-primary">
                            <small className="text-muted fw-semibold d-block">Total Submissions</small>
                            <h3 className="fw-bold text-primary mt-1 mb-0">{stats.total_submitted || myProjects.length}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-3">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-success">
                            <small className="text-muted fw-semibold d-block">Approved Projects</small>
                            <h3 className="fw-bold text-success mt-1 mb-0">{stats.approved || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-3">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-warning">
                            <small className="text-muted fw-semibold d-block">Pending Faculty Review</small>
                            <h3 className="fw-bold text-warning mt-1 mb-0">{stats.pending || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-3">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-info">
                            <small className="text-muted fw-semibold d-block">Best Rubric Score</small>
                            <h3 className="fw-bold text-info mt-1 mb-0">{stats.highest_score ? `${stats.highest_score}/100` : '—'}</h3>
                        </div>
                    </div>
                </div>

                {/* Main Content Area */}
                <div className="row g-4">
                    {/* Left: Recent Projects Table */}
                    <div className="col-lg-8">
                        <div className="content-card shadow-sm p-0 overflow-hidden">
                            <div className="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                                <h5 className="fw-bold mb-0">My Project Submissions</h5>
                                <Link to="/student/projects" className="btn btn-outline-primary btn-sm">
                                    View All
                                </Link>
                            </div>

                            {loading ? (
                                <div className="text-center py-5">
                                    <div className="spinner-border text-primary" role="status"></div>
                                    <p className="text-muted mt-2">Loading projects...</p>
                                </div>
                            ) : myProjects.length === 0 ? (
                                <div className="text-center py-5 p-3">
                                    <i className="bi bi-folder-plus fs-1 text-muted"></i>
                                    <h5 className="mt-3 fw-bold">No Projects Submitted Yet</h5>
                                    <p className="text-muted small">Submit your diploma/degree capstone project to get evaluated by faculty.</p>
                                    <Link to="/student/submit" className="btn btn-primary btn-sm">
                                        <i className="bi bi-cloud-upload me-1"></i> Submit First Project
                                    </Link>
                                </div>
                            ) : (
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="table-light">
                                            <tr>
                                                <th>Project Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Total Score</th>
                                                <th className="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {myProjects.map((p) => (
                                                <tr key={p.id}>
                                                    <td>
                                                        <strong className="d-block text-dark">{p.title}</strong>
                                                        <small className="text-muted">{p.academic_year || '2025-2026'}</small>
                                                    </td>
                                                    <td>
                                                        <span className="badge bg-light text-primary border">
                                                            {p.category_name}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span className={`badge ${p.status === 'approved' ? 'bg-success-subtle text-success' : p.status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning'}`}>
                                                            {p.status ? p.status.toUpperCase() : 'PENDING'}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {p.total_score ? (
                                                            <strong className="text-primary">{parseFloat(p.total_score).toFixed(1)} / 100</strong>
                                                        ) : (
                                                            <span className="text-muted small">Pending Eval</span>
                                                        )}
                                                    </td>
                                                    <td className="text-end">
                                                        <Link to={`/project/${p.id}`} className="btn btn-sm btn-outline-secondary me-1">
                                                            View
                                                        </Link>
                                                        <Link to={`/student/projects`} className="btn btn-sm btn-outline-primary">
                                                            Manage
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: Quick Guide & Viva Tips */}
                    <div className="col-lg-4">
                        <div className="content-card shadow-sm mb-4">
                            <h5 className="fw-bold mb-3 border-bottom pb-2">
                                <i className="bi bi-clipboard2-check text-success me-2"></i> 100-Mark Rubric Breakdown
                            </h5>
                            <p className="text-muted small">Keep these weightages in mind when submitting your documentation and code:</p>
                            <ul className="list-group list-group-flush small">
                                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i className="bi bi-lightbulb me-2 text-warning"></i>Innovation & Problem Solving</span>
                                    <span className="badge bg-light text-dark border">20 Marks</span>
                                </li>
                                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i className="bi bi-gear-wide-connected me-2 text-primary"></i>Functionality & Working Demo</span>
                                    <span className="badge bg-light text-dark border">30 Marks</span>
                                </li>
                                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i className="bi bi-palette me-2 text-info"></i>UI/UX & Responsiveness</span>
                                    <span className="badge bg-light text-dark border">20 Marks</span>
                                </li>
                                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i className="bi bi-code-slash me-2 text-secondary"></i>Technology Stack & Code Quality</span>
                                    <span className="badge bg-light text-dark border">15 Marks</span>
                                </li>
                                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><i className="bi bi-file-earmark-text me-2 text-danger"></i>Presentation & PDF Synopsis</span>
                                    <span className="badge bg-light text-dark border">15 Marks</span>
                                </li>
                            </ul>
                        </div>

                        <div className="content-card shadow-sm border-start border-4 border-info">
                            <h6 className="fw-bold mb-2"><i className="bi bi-shield-check text-info me-1"></i> Code Privacy Notice</h6>
                            <p className="text-muted small mb-0">
                                Your source code ZIP is strictly protected on our server. Only college faculty evaluators can download it for academic evaluation.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
