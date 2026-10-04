import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiGetStats, apiGetAllAdminProjects, apiRecalculateRankings, getAssetUrl } from '../api';
import { useAuth } from '../context/AuthContext';

export default function AdminDashboard() {
    const { user } = useAuth();
    const [stats, setStats] = useState({});
    const [pendingProjects, setPendingProjects] = useState([]);
    const [recalculating, setRecalculating] = useState(false);
    const [alert, setAlert] = useState(null);
    const [loading, setLoading] = useState(true);

    const loadData = async () => {
        setLoading(true);
        try {
            const [statsRes, projRes] = await Promise.all([
                apiGetStats('admin'),
                apiGetAllAdminProjects({ status: 'pending', limit: 5 })
            ]);
            if (statsRes.success) setStats(statsRes.stats || {});
            if (projRes.success) setPendingProjects(projRes.projects || []);
        } catch (err) {
            console.error("Error loading admin dashboard:", err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadData();
    }, []);

    const handleRecalculate = async () => {
        setRecalculating(true);
        setAlert(null);
        try {
            const res = await apiRecalculateRankings();
            if (res.success) {
                setAlert({ type: 'success', message: 'All project ranks and leaderboards have been successfully recalculated based on current rubric scores.' });
                loadData();
            } else {
                setAlert({ type: 'danger', message: res.message || 'Rank recalculation failed.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Network error recalculating ranks.' });
        } finally {
            setRecalculating(false);
        }
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Admin Header */}
                <div className="content-card mb-4 p-4 shadow-sm bg-dark text-white border-0">
                    <div className="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <span className="badge bg-warning text-dark fw-bold mb-2">
                                <i className="bi bi-shield-check me-1"></i> Faculty & Evaluator Control Center
                            </span>
                            <h2 className="fw-bold text-white mb-1">Welcome, {user?.name || 'Administrator'}</h2>
                            <p className="text-secondary mb-0">
                                Review academic submissions, grade with the 100-mark rubric, download student source code, and govern leaderboards.
                            </p>
                        </div>
                        <div className="d-flex gap-2 flex-wrap">
                            <button
                                type="button"
                                className="btn btn-outline-light btn-sm"
                                onClick={handleRecalculate}
                                disabled={recalculating}
                            >
                                <i className={`bi bi-arrow-repeat me-1 ${recalculating ? 'spin' : ''}`}></i>
                                {recalculating ? 'Recalculating...' : 'Recalculate Ranks'}
                            </button>
                            <Link to="/admin/projects" className="btn btn-primary btn-sm fw-bold">
                                <i className="bi bi-folder-check me-1"></i> Review Submissions
                            </Link>
                        </div>
                    </div>
                </div>

                {alert && (
                    <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                        <i className={`bi ${alert.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2`}></i>
                        {alert.message}
                        <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                    </div>
                )}

                {/* 6 Key Metrics */}
                <div className="row g-3 mb-4">
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-primary">
                            <small className="text-muted fw-semibold d-block">Total Projects</small>
                            <h3 className="fw-bold text-primary mt-1 mb-0">{stats.total_projects || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-warning">
                            <small className="text-muted fw-semibold d-block">Pending Review</small>
                            <h3 className="fw-bold text-warning mt-1 mb-0">{stats.pending_projects || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-success">
                            <small className="text-muted fw-semibold d-block">Approved Projects</small>
                            <h3 className="fw-bold text-success mt-1 mb-0">{stats.approved_projects || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-info">
                            <small className="text-muted fw-semibold d-block">Total Students</small>
                            <h3 className="fw-bold text-info mt-1 mb-0">{stats.total_students || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-secondary">
                            <small className="text-muted fw-semibold d-block">Categories</small>
                            <h3 className="fw-bold text-dark mt-1 mb-0">{stats.total_categories || 0}</h3>
                        </div>
                    </div>
                    <div className="col-6 col-md-4 col-lg-2">
                        <div className="content-card p-3 mb-0 shadow-sm border-start border-4 border-danger">
                            <small className="text-muted fw-semibold d-block">Avg Score</small>
                            <h3 className="fw-bold text-danger mt-1 mb-0">
                                {stats.average_score ? `${parseFloat(stats.average_score).toFixed(1)}` : '—'}
                            </h3>
                        </div>
                    </div>
                </div>

                {/* Main Split Grid */}
                <div className="row g-4">
                    {/* Left: Pending Submissions Queue */}
                    <div className="col-lg-8">
                        <div className="content-card shadow-sm p-0 overflow-hidden">
                            <div className="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                                <div className="d-flex align-items-center gap-2">
                                    <span className="badge bg-warning text-dark">Queue</span>
                                    <h5 className="fw-bold mb-0">Submissions Awaiting Faculty Evaluation</h5>
                                </div>
                                <Link to="/admin/projects?status=pending" className="btn btn-outline-primary btn-sm">
                                    View All Pending
                                </Link>
                            </div>

                            {loading ? (
                                <div className="text-center py-5">
                                    <div className="spinner-border text-primary" role="status"></div>
                                    <p className="text-muted mt-2">Loading review queue...</p>
                                </div>
                            ) : pendingProjects.length === 0 ? (
                                <div className="text-center py-5">
                                    <i className="bi bi-check-circle-fill text-success fs-1"></i>
                                    <h5 className="fw-bold mt-2">All Caught Up!</h5>
                                    <p className="text-muted small">No pending student projects waiting for evaluation.</p>
                                </div>
                            ) : (
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="table-light">
                                            <tr>
                                                <th>Project & Student</th>
                                                <th>Category</th>
                                                <th>Submitted</th>
                                                <th className="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {pendingProjects.map((p) => (
                                                <tr key={p.id}>
                                                    <td>
                                                        <strong className="d-block text-dark">{p.title}</strong>
                                                        <small className="text-muted">
                                                            By: <strong>{p.student_name}</strong> (Roll: {p.roll_number || 'N/A'})
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span className="category-pill">{p.category_name}</span>
                                                    </td>
                                                    <td>
                                                        <small className="text-muted">
                                                            {new Date(p.created_at || Date.now()).toLocaleDateString()}
                                                        </small>
                                                    </td>
                                                    <td className="text-end">
                                                        <Link to={`/admin/evaluate/${p.id}`} className="btn btn-sm btn-primary fw-semibold">
                                                            <i className="bi bi-pencil-square me-1"></i> Grade & Evaluate
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

                    {/* Right: Quick Action Hub & Navigation */}
                    <div className="col-lg-4">
                        <div className="content-card shadow-sm mb-4">
                            <h5 className="fw-bold mb-3 border-bottom pb-2">Admin Quick Links</h5>
                            <div className="d-flex flex-column gap-2">
                                <Link to="/admin/projects" className="btn btn-light border text-start d-flex align-items-center justify-content-between p-3">
                                    <div className="d-flex align-items-center gap-2">
                                        <i className="bi bi-folder2-open text-primary fs-5"></i>
                                        <span className="fw-semibold">All Student Projects</span>
                                    </div>
                                    <i className="bi bi-chevron-right text-muted"></i>
                                </Link>

                                <Link to="/admin/categories" className="btn btn-light border text-start d-flex align-items-center justify-content-between p-3">
                                    <div className="d-flex align-items-center gap-2">
                                        <i className="bi bi-tags text-success fs-5"></i>
                                        <span className="fw-semibold">Manage Categories & Domains</span>
                                    </div>
                                    <i className="bi bi-chevron-right text-muted"></i>
                                </Link>

                                <Link to="/admin/students" className="btn btn-light border text-start d-flex align-items-center justify-content-between p-3">
                                    <div className="d-flex align-items-center gap-2">
                                        <i className="bi bi-people text-info fs-5"></i>
                                        <span className="fw-semibold">Student Directory</span>
                                    </div>
                                    <i className="bi bi-chevron-right text-muted"></i>
                                </Link>

                                <Link to="/rankings" className="btn btn-light border text-start d-flex align-items-center justify-content-between p-3">
                                    <div className="d-flex align-items-center gap-2">
                                        <i className="bi bi-trophy text-warning fs-5"></i>
                                        <span className="fw-semibold">Live Public Leaderboard</span>
                                    </div>
                                    <i className="bi bi-chevron-right text-muted"></i>
                                </Link>
                            </div>
                        </div>

                        {/* Academic Rubric Standard Reminder */}
                        <div className="content-card shadow-sm bg-light border-start border-4 border-primary">
                            <h6 className="fw-bold text-dark mb-1">
                                <i className="bi bi-info-circle-fill text-primary me-1"></i> Evaluation Policy
                            </h6>
                            <p className="text-muted small mb-0">
                                When you grade a project in the evaluation form, it is automatically marked as <strong>Approved</strong> and the total scores and leaderboard rankings are refreshed immediately.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
