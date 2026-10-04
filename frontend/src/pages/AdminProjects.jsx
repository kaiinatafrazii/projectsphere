import React, { useState, useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { apiGetAllAdminProjects, apiUpdateProjectStatus, apiDeleteProject, getAssetUrl } from '../api';

export default function AdminProjects() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [alert, setAlert] = useState(null);

    // Reject Modal state
    const [rejectModalOpen, setRejectModalOpen] = useState(false);
    const [rejectProjectId, setRejectProjectId] = useState(null);
    const [rejectionReason, setRejectionReason] = useState('');
    const [statusProcessing, setStatusProcessing] = useState(false);

    const statusFilter = searchParams.get('status') || '';
    const searchQuery = searchParams.get('search') || '';

    const loadProjects = () => {
        setLoading(true);
        const params = {};
        if (statusFilter) params.status = statusFilter;
        if (searchQuery) params.search = searchQuery;

        apiGetAllAdminProjects(params)
            .then((res) => {
                if (res.success) {
                    setProjects(res.projects || []);
                }
            })
            .catch((err) => console.error("Error loading admin projects:", err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadProjects();
    }, [statusFilter, searchQuery]);

    const handleFilterChange = (key, val) => {
        const next = new URLSearchParams(searchParams);
        if (val) {
            next.set(key, val);
        } else {
            next.delete(key);
        }
        setSearchParams(next);
    };

    const handleQuickApprove = async (id) => {
        try {
            const res = await apiUpdateProjectStatus(id, 'approved');
            if (res.success) {
                setAlert({ type: 'success', message: 'Project status marked as Approved!' });
                loadProjects();
            } else {
                setAlert({ type: 'danger', message: res.message || 'Error updating status' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Network error updating status' });
        }
    };

    const openRejectModal = (id) => {
        setRejectProjectId(id);
        setRejectionReason('');
        setRejectModalOpen(true);
    };

    const handleConfirmReject = async (e) => {
        e.preventDefault();
        if (!rejectProjectId) return;

        setStatusProcessing(true);
        try {
            const res = await apiUpdateProjectStatus(rejectProjectId, 'rejected', rejectionReason.trim());
            if (res.success) {
                setAlert({ type: 'success', message: 'Project marked as Rejected with feedback sent to student.' });
                setRejectModalOpen(false);
                loadProjects();
            } else {
                setAlert({ type: 'danger', message: res.message || 'Error rejecting project.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Network error rejecting project.' });
        } finally {
            setStatusProcessing(false);
        }
    };

    const handleDelete = async (id, title) => {
        if (!window.confirm(`Are you sure you want to permanently delete project: "${title}"?`)) return;

        try {
            const res = await apiDeleteProject(id);
            if (res.success) {
                setAlert({ type: 'success', message: 'Project deleted permanently.' });
                setProjects(projects.filter(p => p.id !== id));
            } else {
                setAlert({ type: 'danger', message: res.message || 'Error deleting project.' });
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
                        <h2 className="fw-bold mb-1">Manage All Student Projects</h2>
                        <p className="text-muted mb-0">Evaluate submissions, inspect source code archives, and approve or reject submissions.</p>
                    </div>
                </div>

                {alert && (
                    <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                        {alert.message}
                        <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                    </div>
                )}

                {/* Filter and Search Bar */}
                <div className="content-card shadow-sm p-3 mb-4">
                    <div className="row g-2 align-items-center">
                        <div className="col-12 col-md-5">
                            <div className="input-group">
                                <span className="input-group-text bg-white border-end-0 text-muted">
                                    <i className="bi bi-search"></i>
                                </span>
                                <input
                                    type="text"
                                    className="form-control border-start-0 ps-0"
                                    placeholder="Search by title, student name, roll..."
                                    value={searchQuery}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="col-6 col-md-4">
                            <div className="btn-group w-100" role="group">
                                <button
                                    type="button"
                                    className={`btn btn-sm ${!statusFilter ? 'btn-primary' : 'btn-outline-secondary'}`}
                                    onClick={() => handleFilterChange('status', '')}
                                >
                                    All ({projects.length})
                                </button>
                                <button
                                    type="button"
                                    className={`btn btn-sm ${statusFilter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'}`}
                                    onClick={() => handleFilterChange('status', 'pending')}
                                >
                                    Pending
                                </button>
                                <button
                                    type="button"
                                    className={`btn btn-sm ${statusFilter === 'approved' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary'}`}
                                    onClick={() => handleFilterChange('status', 'approved')}
                                >
                                    Approved
                                </button>
                                <button
                                    type="button"
                                    className={`btn btn-sm ${statusFilter === 'rejected' ? 'btn-danger text-white fw-bold' : 'btn-outline-secondary'}`}
                                    onClick={() => handleFilterChange('status', 'rejected')}
                                >
                                    Rejected
                                </button>
                            </div>
                        </div>

                        <div className="col-6 col-md-3 text-end">
                            <button className="btn btn-outline-secondary btn-sm" onClick={loadProjects}>
                                <i className="bi bi-arrow-clockwise me-1"></i> Refresh List
                            </button>
                        </div>
                    </div>
                </div>

                {/* Submissions Table */}
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-primary" role="status"></div>
                        <p className="text-muted mt-2">Loading submissions...</p>
                    </div>
                ) : projects.length === 0 ? (
                    <div className="content-card text-center py-5">
                        <i className="bi bi-folder-x fs-1 text-muted"></i>
                        <h4 className="mt-3 fw-bold">No Projects Found</h4>
                        <p className="text-muted">No student projects matched your filter criteria.</p>
                    </div>
                ) : (
                    <div className="content-card shadow-sm p-0 overflow-hidden">
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th>Project Details</th>
                                        <th>Student & Roll</th>
                                        <th>Domain</th>
                                        <th>Status</th>
                                        <th className="text-center">Score</th>
                                        <th>Code & Files</th>
                                        <th className="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {projects.map((proj) => (
                                        <tr key={proj.id}>
                                            <td style={{ maxWidth: '280px' }}>
                                                <Link to={`/project/${proj.id}`} className="fw-bold text-dark text-decoration-none d-block">
                                                    {proj.title}
                                                </Link>
                                                <small className="text-muted">
                                                    Year: {proj.academic_year || '2025-2026'} • {new Date(proj.created_at || Date.now()).toLocaleDateString()}
                                                </small>
                                            </td>
                                            <td>
                                                <div className="fw-semibold text-dark">{proj.student_name}</div>
                                                <small className="text-muted">Roll: {proj.roll_number || 'N/A'}</small>
                                            </td>
                                            <td>
                                                <span className="category-pill">{proj.category_name}</span>
                                            </td>
                                            <td>
                                                <span className={`badge ${proj.status === 'approved' ? 'bg-success-subtle text-success border border-success-subtle' : proj.status === 'rejected' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'}`}>
                                                    {proj.status ? proj.status.toUpperCase() : 'PENDING'}
                                                </span>
                                            </td>
                                            <td className="text-center">
                                                {proj.total_score ? (
                                                    <span className="fw-bold text-primary fs-6">
                                                        {parseFloat(proj.total_score).toFixed(1)} / 100
                                                    </span>
                                                ) : (
                                                    <span className="text-muted small">Ungraded</span>
                                                )}
                                            </td>
                                            <td>
                                                <div className="d-flex gap-1 flex-wrap">
                                                    {proj.source_code_path ? (
                                                        <a
                                                            href={getAssetUrl(proj.source_code_path)}
                                                            download
                                                            className="btn btn-sm btn-outline-warning text-dark p-1"
                                                            title="Download Source Code (.zip)"
                                                        >
                                                            <i className="bi bi-file-earmark-zip-fill text-warning"></i> ZIP
                                                        </a>
                                                    ) : (
                                                        <span className="text-muted small" title="No zip uploaded">—</span>
                                                    )}
                                                    {proj.synopsis_file && (
                                                        <a
                                                            href={getAssetUrl(proj.synopsis_file)}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="btn btn-sm btn-outline-danger p-1"
                                                            title="View Synopsis PDF"
                                                        >
                                                            <i className="bi bi-file-earmark-pdf-fill"></i> PDF
                                                        </a>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="text-end">
                                                <div className="btn-group btn-group-sm">
                                                    <Link to={`/admin/evaluate/${proj.id}`} className="btn btn-primary" title="Grade with 100-mark rubric">
                                                        <i className="bi bi-pencil-square"></i> Grade
                                                    </Link>

                                                    {proj.status !== 'approved' && (
                                                        <button
                                                            type="button"
                                                            className="btn btn-outline-success"
                                                            onClick={() => handleQuickApprove(proj.id)}
                                                            title="Quick Approve"
                                                        >
                                                            <i className="bi bi-check-lg"></i>
                                                        </button>
                                                    )}

                                                    {proj.status !== 'rejected' && (
                                                        <button
                                                            type="button"
                                                            className="btn btn-outline-warning text-dark"
                                                            onClick={() => openRejectModal(proj.id)}
                                                            title="Reject with Remarks"
                                                        >
                                                            <i className="bi bi-x-lg"></i>
                                                        </button>
                                                    )}

                                                    <button
                                                        type="button"
                                                        className="btn btn-outline-danger"
                                                        onClick={() => handleDelete(proj.id, proj.title)}
                                                        title="Delete Project"
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

                {/* Reject Reason Modal */}
                {rejectModalOpen && (
                    <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                        <div className="modal-dialog modal-dialog-centered">
                            <div className="modal-content">
                                <form onSubmit={handleConfirmReject}>
                                    <div className="modal-header">
                                        <h5 className="modal-title fw-bold">Reject Academic Submission</h5>
                                        <button type="button" className="btn-close" onClick={() => setRejectModalOpen(false)}></button>
                                    </div>
                                    <div className="modal-body">
                                        <p className="text-muted small">
                                            Please provide constructive feedback so the student knows why their submission was rejected or needs revision.
                                        </p>
                                        <div className="mb-3">
                                            <label className="form-label">Rejection Reason / Revision Notes</label>
                                            <textarea
                                                className="form-control"
                                                rows="4"
                                                placeholder="e.g. Synopsis documentation is incomplete. Please include full system architecture diagram and re-submit."
                                                value={rejectionReason}
                                                onChange={(e) => setRejectionReason(e.target.value)}
                                                required
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-light border" onClick={() => setRejectModalOpen(false)}>
                                            Cancel
                                        </button>
                                        <button type="submit" className="btn btn-danger" disabled={statusProcessing}>
                                            {statusProcessing ? 'Rejecting...' : 'Confirm Rejection'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
