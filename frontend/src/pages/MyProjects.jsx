import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiGetMyProjects, apiDeleteProject } from '../api';

export default function MyProjects() {
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [alert, setAlert] = useState(null);

    const loadProjects = () => {
        setLoading(true);
        apiGetMyProjects()
            .then((res) => {
                if (res.success) {
                    setProjects(res.projects || []);
                }
            })
            .catch((err) => console.error("Error loading my projects:", err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadProjects();
    }, []);

    const handleDelete = async (id, title) => {
        if (!window.confirm(`Are you sure you want to delete "${title}"?`)) return;

        try {
            const res = await apiDeleteProject(id);
            if (res.success) {
                setAlert({ type: 'success', message: 'Project deleted successfully.' });
                setProjects(projects.filter(p => p.id !== id));
            } else {
                setAlert({ type: 'danger', message: res.message || 'Failed to delete project.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Error deleting project.' });
        }
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Header */}
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 className="fw-bold mb-1">My Project Submissions</h2>
                        <p className="text-muted mb-0">Track the evaluation status and faculty feedback for your submissions.</p>
                    </div>
                    <Link to="/student/submit" className="btn btn-primary fw-semibold">
                        <i className="bi bi-cloud-arrow-up-fill me-1"></i> Submit New Project
                    </Link>
                </div>

                {alert && (
                    <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                        {alert.message}
                        <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                    </div>
                )}

                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-primary" role="status"></div>
                        <p className="text-muted mt-2">Loading your submissions...</p>
                    </div>
                ) : projects.length === 0 ? (
                    <div className="content-card text-center py-5 shadow-sm">
                        <i className="bi bi-folder-x fs-1 text-muted"></i>
                        <h4 className="mt-3 fw-bold">No Projects Found</h4>
                        <p className="text-muted">You have not submitted any academic projects yet.</p>
                        <Link to="/student/submit" className="btn btn-primary btn-sm">
                            <i className="bi bi-plus-lg me-1"></i> Submit Project Now
                        </Link>
                    </div>
                ) : (
                    <div className="content-card shadow-sm p-0 overflow-hidden">
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th>Project Details</th>
                                        <th>Domain / Category</th>
                                        <th>Evaluation Status</th>
                                        <th className="text-center">Score (/100)</th>
                                        <th className="text-center">Overall Rank</th>
                                        <th className="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {projects.map((proj) => (
                                        <tr key={proj.id}>
                                            <td style={{ maxWidth: '320px' }}>
                                                <Link to={`/project/${proj.id}`} className="fw-bold text-dark text-decoration-none d-block">
                                                    {proj.title}
                                                </Link>
                                                <small className="text-muted">
                                                    Year: {proj.academic_year || '2025-2026'} • Submitted: {new Date(proj.created_at || Date.now()).toLocaleDateString()}
                                                </small>
                                                {proj.rejection_reason && (
                                                    <div className="alert alert-danger py-1 px-2 mt-1 mb-0 small">
                                                        <strong>Faculty Remark:</strong> {proj.rejection_reason}
                                                    </div>
                                                )}
                                            </td>
                                            <td>
                                                <span className="category-pill">
                                                    {proj.category_name}
                                                </span>
                                            </td>
                                            <td>
                                                <span className={`badge ${proj.status === 'approved' ? 'bg-success-subtle text-success border border-success-subtle' : proj.status === 'rejected' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'}`}>
                                                    {proj.status ? proj.status.toUpperCase() : 'PENDING'}
                                                </span>
                                            </td>
                                            <td className="text-center">
                                                {proj.total_score ? (
                                                    <strong className="fs-5 text-primary">
                                                        {parseFloat(proj.total_score).toFixed(1)}
                                                    </strong>
                                                ) : (
                                                    <span className="text-muted small">Not graded</span>
                                                )}
                                            </td>
                                            <td className="text-center">
                                                {proj.overall_rank ? (
                                                    <span className="badge bg-warning text-dark fw-bold">
                                                        #{proj.overall_rank}
                                                    </span>
                                                ) : (
                                                    <span className="text-muted small">—</span>
                                                )}
                                            </td>
                                            <td className="text-end">
                                                <div className="btn-group btn-group-sm">
                                                    <Link to={`/project/${proj.id}`} className="btn btn-outline-secondary" title="View details">
                                                        <i className="bi bi-eye"></i>
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        className="btn btn-outline-danger"
                                                        onClick={() => handleDelete(proj.id, proj.title)}
                                                        title="Delete submission"
                                                    >
                                                        <i className="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
