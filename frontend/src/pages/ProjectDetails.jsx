import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiGetProjectDetails, apiPostFeedback, getAssetUrl } from '../api';
import { useAuth } from '../context/AuthContext';

export default function ProjectDetails() {
    const { id } = useParams();
    const { user } = useAuth();
    const [project, setProject] = useState(null);
    const [team, setTeam] = useState([]);
    const [images, setImages] = useState([]);
    const [evaluations, setEvaluations] = useState([]);
    const [feedbackList, setFeedbackList] = useState([]);
    const [activeImage, setActiveImage] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Feedback Form State
    const [comment, setComment] = useState('');
    const [submittingFeedback, setSubmittingFeedback] = useState(false);
    const [feedbackAlert, setFeedbackAlert] = useState(null);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        setError(null);

        apiGetProjectDetails(id)
            .then((res) => {
                if (!isMounted) return;
                if (res.success && res.project) {
                    setProject(res.project);
                    setTeam(res.team_members || []);
                    setImages(res.images || []);
                    setEvaluations(res.evaluations || []);
                    setFeedbackList(res.feedback || []);
                    if (res.project.featured_image) {
                        setActiveImage(res.project.featured_image);
                    } else if (res.images && res.images.length > 0) {
                        setActiveImage(res.images[0].image_path);
                    }
                } else {
                    setError(res.message || 'Project not found');
                }
            })
            .catch((err) => {
                if (isMounted) setError('Network error loading project');
            })
            .finally(() => {
                if (isMounted) setLoading(false);
            });

        return () => { isMounted = false; };
    }, [id]);

    const handleFeedbackSubmit = async (e) => {
        e.preventDefault();
        if (!comment.trim()) return;

        setSubmittingFeedback(true);
        setFeedbackAlert(null);
        try {
            const res = await apiPostFeedback({
                project_id: id,
                comment: comment.trim(),
                is_faculty: user && user.role === 'admin' ? 1 : 0
            });
            if (res.success) {
                setFeedbackAlert({ type: 'success', message: 'Comment submitted successfully!' });
                setComment('');
                // Append locally or reload
                setFeedbackList([
                    {
                        id: Date.now(),
                        comment: comment.trim(),
                        user_name: user ? user.name : 'Guest User',
                        user_role: user ? user.role : 'guest',
                        created_at: 'Just now'
                    },
                    ...feedbackList
                ]);
            } else {
                setFeedbackAlert({ type: 'danger', message: res.message || 'Failed to post feedback' });
            }
        } catch (err) {
            setFeedbackAlert({ type: 'danger', message: 'Error submitting comment' });
        } finally {
            setSubmittingFeedback(false);
        }
    };

    if (loading) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh' }}>
                <div className="spinner-border text-primary" role="status"></div>
                <p className="text-muted mt-2">Loading project details...</p>
            </div>
        );
    }

    if (error || !project) {
        return (
            <div className="container py-5 text-center">
                <div className="content-card py-5 max-w-lg mx-auto">
                    <i className="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>
                    <h3 className="fw-bold mt-3">Project Unavailable</h3>
                    <p className="text-muted">{error || 'The requested project could not be found or has not been approved yet.'}</p>
                    <Link to="/browse" className="btn btn-primary mt-2">
                        <i className="bi bi-arrow-left me-1"></i> Back to Showcase
                    </Link>
                </div>
            </div>
        );
    }

    const latestEval = evaluations.length > 0 ? evaluations[0] : null;

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Breadcrumb */}
                <nav aria-label="breadcrumb" className="mb-3">
                    <ol className="breadcrumb">
                        <li className="breadcrumb-item"><Link to="/" className="text-decoration-none">Home</Link></li>
                        <li className="breadcrumb-item"><Link to="/browse" className="text-decoration-none">Projects</Link></li>
                        <li className="breadcrumb-item active text-truncate" style={{ maxWidth: '300px' }}>{project.title}</li>
                    </ol>
                </nav>

                <div className="row g-4">
                    {/* Left Column: Details & Content */}
                    <div className="col-lg-8">
                        {/* Main Header Card */}
                        <div className="content-card mb-4 shadow-sm">
                            <div className="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-3">
                                <div>
                                    <span className="category-pill mb-2">
                                        <i className="bi bi-tag-fill"></i> {project.category_name}
                                    </span>
                                    <span className="badge bg-light text-dark border ms-2">
                                        <i className="bi bi-calendar3 me-1"></i> {project.academic_year || '2025-2026'}
                                    </span>
                                    <span className={`badge ms-2 ${project.status === 'approved' ? 'bg-success-subtle text-success' : project.status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning'}`}>
                                        {project.status ? project.status.toUpperCase() : 'PENDING'}
                                    </span>
                                </div>

                                {project.overall_rank && project.overall_rank <= 10 && (
                                    <span className={`badge-rank ${project.overall_rank === 1 ? 'rank-1' : project.overall_rank === 2 ? 'rank-2' : project.overall_rank === 3 ? 'rank-3' : 'rank-other'} position-static`}>
                                        <i className="bi bi-trophy-fill"></i> Overall Rank #{project.overall_rank}
                                    </span>
                                )}
                            </div>

                            <h1 className="h2 fw-bold text-dark mb-3">{project.title}</h1>

                            {/* Author & College Bar */}
                            <div className="d-flex align-items-center gap-3 p-3 rounded-3 bg-light border mb-4 flex-wrap">
                                <div className="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style={{ width: 44, height: 44, fontSize: '1.2rem' }}>
                                    {project.student_name ? project.student_name.charAt(0).toUpperCase() : 'S'}
                                </div>
                                <div>
                                    <h6 className="fw-bold mb-0 text-dark">{project.student_name}</h6>
                                    <small className="text-muted">
                                        Roll No: <strong>{project.roll_number || 'N/A'}</strong> | {project.department || 'Computer Engineering'}
                                    </small>
                                </div>
                                {project.guide_name && (
                                    <div className="ms-md-auto text-md-end">
                                        <small className="text-muted d-block">Project Guide / Faculty Mentor:</small>
                                        <strong className="text-dark"><i className="bi bi-person-badge me-1"></i>{project.guide_name}</strong>
                                    </div>
                                )}
                            </div>

                            {/* Image Showcase */}
                            {activeImage && (
                                <div className="mb-4">
                                    <div className="rounded-3 overflow-hidden border bg-dark text-center" style={{ maxHeight: '420px' }}>
                                        <img
                                            src={getAssetUrl(activeImage)}
                                            alt={project.title}
                                            className="img-fluid"
                                            style={{ maxHeight: '420px', width: '100%', objectFit: 'contain' }}
                                            onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200&auto=format&fit=crop&q=80'; }}
                                        />
                                    </div>

                                    {/* Gallery Thumbnails */}
                                    {images.length > 1 && (
                                        <div className="d-flex gap-2 mt-2 overflow-auto py-1">
                                            {images.map((img) => (
                                                <img
                                                    key={img.id}
                                                    src={getAssetUrl(img.image_path)}
                                                    alt="Thumbnail"
                                                    onClick={() => setActiveImage(img.image_path)}
                                                    className={`rounded border cursor-pointer ${activeImage === img.image_path ? 'border-primary border-3' : 'opacity-75'}`}
                                                    style={{ width: '80px', height: '56px', objectFit: 'cover' }}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Abstract / Problem Statement */}
                            <div className="mb-4">
                                <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                    <i className="bi bi-file-earmark-text text-primary me-2"></i> Problem Statement & Abstract
                                </h4>
                                <div className="text-secondary leading-relaxed" style={{ whiteSpace: 'pre-line' }}>
                                    {project.abstract || project.problem_statement || 'No abstract provided.'}
                                </div>
                            </div>

                            {/* Objectives */}
                            {project.objectives && (
                                <div className="mb-4">
                                    <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                        <i className="bi bi-bullseye text-primary me-2"></i> Project Objectives
                                    </h4>
                                    <div className="text-secondary leading-relaxed" style={{ whiteSpace: 'pre-line' }}>
                                        {project.objectives}
                                    </div>
                                </div>
                            )}

                            {/* Features */}
                            {project.features && (
                                <div className="mb-4">
                                    <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                        <i className="bi bi-check2-all text-primary me-2"></i> Key Features
                                    </h4>
                                    <div className="text-secondary leading-relaxed" style={{ whiteSpace: 'pre-line' }}>
                                        {project.features}
                                    </div>
                                </div>
                            )}

                            {/* Technologies Used */}
                            {project.technologies && (
                                <div className="mb-4">
                                    <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                        <i className="bi bi-code-slash text-primary me-2"></i> Technologies & Tools
                                    </h4>
                                    <div>
                                        {project.technologies.split(',').map((tech, idx) => (
                                            <span key={idx} className="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 me-2 mb-2 fs-6">
                                                {tech.trim()}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Team Members List */}
                            {team.length > 0 && (
                                <div className="mb-4">
                                    <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                        <i className="bi bi-people-fill text-primary me-2"></i> Project Team Members
                                    </h4>
                                    <div className="row g-2">
                                        {team.map((member, idx) => (
                                            <div key={idx} className="col-sm-6">
                                                <div className="p-2 border rounded-3 bg-light d-flex align-items-center gap-2">
                                                    <i className="bi bi-person-check text-primary fs-5"></i>
                                                    <div>
                                                        <strong className="d-block text-dark">{member.student_name}</strong>
                                                        <small className="text-muted">
                                                            {member.roll_number ? `Roll: ${member.roll_number}` : ''} {member.role ? `• ${member.role}` : ''}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Public Comments & Remarks Section */}
                        <div className="content-card shadow-sm">
                            <h4 className="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i className="bi bi-chat-left-text-fill text-primary me-2"></i> Discussion & Remarks ({feedbackList.length})
                            </h4>

                            {feedbackAlert && (
                                <div className={`alert alert-${feedbackAlert.type} alert-dismissible fade show`} role="alert">
                                    {feedbackAlert.message}
                                    <button type="button" className="btn-close" onClick={() => setFeedbackAlert(null)}></button>
                                </div>
                            )}

                            {/* Comment Form */}
                            <form onSubmit={handleFeedbackSubmit} className="mb-4">
                                <div className="mb-2">
                                    <textarea
                                        className="form-control"
                                        rows="3"
                                        placeholder={user ? "Share academic feedback, query, or appreciation..." : "Login or enter remarks..."}
                                        value={comment}
                                        onChange={(e) => setComment(e.target.value)}
                                        required
                                    ></textarea>
                                </div>
                                <button type="submit" className="btn btn-primary btn-sm" disabled={submittingFeedback || !comment.trim()}>
                                    {submittingFeedback ? 'Posting...' : 'Post Remark'}
                                </button>
                            </form>

                            {/* Comments Stream */}
                            {feedbackList.length === 0 ? (
                                <p className="text-muted text-center py-3 mb-0">No remarks yet. Be the first to share your thoughts!</p>
                            ) : (
                                <div className="d-flex flex-column gap-3">
                                    {feedbackList.map((f, i) => (
                                        <div key={f.id || i} className={`p-3 rounded-3 border ${f.is_faculty ? 'bg-warning-subtle border-warning' : 'bg-light'}`}>
                                            <div className="d-flex justify-content-between align-items-center mb-1">
                                                <div className="d-flex align-items-center gap-2">
                                                    <strong className="text-dark">{f.user_name || 'Faculty / Peer'}</strong>
                                                    {f.is_faculty ? (
                                                        <span className="badge bg-warning text-dark">Faculty Review</span>
                                                    ) : (
                                                        <span className="badge bg-secondary-subtle text-secondary">Student</span>
                                                    )}
                                                </div>
                                                <small className="text-muted">{f.created_at}</small>
                                            </div>
                                            <p className="mb-0 text-secondary">{f.comment}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right Column: Actions, Evaluation Rubric & Code Privacy */}
                    <div className="col-lg-4">
                        {/* Live Demo & Artifacts Action Card */}
                        <div className="content-card mb-4 shadow-sm">
                            <h5 className="fw-bold mb-3 border-bottom pb-2">Project Artifacts</h5>
                            <div className="d-grid gap-2">
                                {project.demo_url ? (
                                    <a href={project.demo_url} target="_blank" rel="noreferrer" className="btn btn-primary">
                                        <i className="bi bi-box-arrow-up-right me-2"></i> Open Live Demo
                                    </a>
                                ) : (
                                    <button className="btn btn-light border text-muted" disabled>
                                        <i className="bi bi-globe me-2"></i> No Live URL
                                    </button>
                                )}

                                {project.video_url && (
                                    <a href={project.video_url} target="_blank" rel="noreferrer" className="btn btn-outline-danger">
                                        <i className="bi bi-youtube me-2"></i> Watch Video Demo
                                    </a>
                                )}

                                {project.synopsis_file ? (
                                    <a href={getAssetUrl(project.synopsis_file)} target="_blank" rel="noreferrer" className="btn btn-outline-secondary">
                                        <i className="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Download Synopsis (PDF)
                                    </a>
                                ) : (
                                    <button className="btn btn-light border text-muted" disabled>
                                        <i className="bi bi-file-earmark-pdf me-2"></i> No Synopsis PDF
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* SOURCE CODE SECURITY NOTICE & EVALUATOR ACCESS */}
                        <div className="content-card mb-4 shadow-sm border-start border-4 border-warning">
                            <div className="d-flex align-items-center gap-2 mb-2">
                                <i className="bi bi-shield-lock-fill text-warning fs-4"></i>
                                <h5 className="fw-bold mb-0">Source Code Access</h5>
                            </div>
                            
                            {user && user.role === 'admin' ? (
                                <div>
                                    <div className="alert alert-warning py-2 mb-3 small">
                                        <i className="bi bi-person-badge-fill me-1"></i>
                                        <strong>Faculty Evaluator Privilege:</strong> You have administrative access to inspect the student's submission code.
                                    </div>
                                    <div className="d-grid gap-2">
                                        {project.source_code_path ? (
                                            <a href={getAssetUrl(project.source_code_path)} download className="btn btn-warning text-dark fw-bold">
                                                <i className="bi bi-file-earmark-zip-fill me-2"></i> Download Source Code (.zip)
                                            </a>
                                        ) : (
                                            <div className="text-muted small">No ZIP file uploaded by student.</div>
                                        )}
                                        {project.github_repo_url && (
                                            <a href={project.github_repo_url} target="_blank" rel="noreferrer" className="btn btn-outline-dark btn-sm">
                                                <i className="bi bi-github me-1"></i> Open GitHub Repository
                                            </a>
                                        )}
                                        <Link to={`/admin/evaluate/${project.id}`} className="btn btn-success fw-bold mt-2">
                                            <i className="bi bi-award-fill me-1"></i> Grade in 100-Mark Rubric
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div>
                                    <p className="text-muted small mb-2">
                                        As per academic policy, student source code is kept strictly confidential and accessible only to authorized college evaluators for grading.
                                    </p>
                                    <div className="badge bg-secondary-subtle text-secondary w-100 p-2 text-wrap">
                                        <i className="bi bi-lock-fill me-1"></i> Protected Academic Asset
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* 100-MARK EVALUATION SCORECARD */}
                        <div className="content-card mb-4 shadow-sm">
                            <div className="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <h5 className="fw-bold mb-0">Evaluation Rubric</h5>
                                {project.total_score ? (
                                    <span className="badge bg-primary fs-6 px-3 py-1">
                                        {parseFloat(project.total_score).toFixed(1)} / 100
                                    </span>
                                ) : (
                                    <span className="badge bg-warning text-dark">Awaiting Marks</span>
                                )}
                            </div>

                            {latestEval ? (
                                <div>
                                    <div className="row g-2 mb-3">
                                        <div className="col-6">
                                            <div className="eval-metric-box">
                                                <div className="eval-metric-score">{latestEval.innovation_score}</div>
                                                <div className="eval-metric-max">/ 20 Marks</div>
                                                <div className="eval-metric-title">Innovation</div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="eval-metric-box">
                                                <div className="eval-metric-score">{latestEval.functionality_score}</div>
                                                <div className="eval-metric-max">/ 30 Marks</div>
                                                <div className="eval-metric-title">Functionality</div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="eval-metric-box">
                                                <div className="eval-metric-score">{latestEval.ui_ux_score}</div>
                                                <div className="eval-metric-max">/ 20 Marks</div>
                                                <div className="eval-metric-title">UI & UX</div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="eval-metric-box">
                                                <div className="eval-metric-score">{latestEval.tech_stack_score}</div>
                                                <div className="eval-metric-max">/ 15 Marks</div>
                                                <div className="eval-metric-title">Tech Usage</div>
                                            </div>
                                        </div>
                                        <div className="col-12">
                                            <div className="eval-metric-box">
                                                <div className="eval-metric-score">{latestEval.presentation_score}</div>
                                                <div className="eval-metric-max">/ 15 Marks</div>
                                                <div className="eval-metric-title">Presentation & Docs</div>
                                            </div>
                                        </div>
                                    </div>

                                    {latestEval.feedback && (
                                        <div className="p-3 bg-light rounded-3 border">
                                            <strong className="d-block text-dark small mb-1">
                                                <i className="bi bi-chat-quote-fill text-primary me-1"></i> Evaluator Feedback:
                                            </strong>
                                            <p className="text-secondary small mb-1">{latestEval.feedback}</p>
                                            <small className="text-muted d-block text-end">— Evaluated on {latestEval.evaluated_at || 'Recent'}</small>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="text-center py-4 bg-light rounded-3">
                                    <i className="bi bi-hourglass-split text-warning fs-1"></i>
                                    <h6 className="fw-bold mt-2">Under Academic Review</h6>
                                    <p className="text-muted small px-2 mb-0">
                                        Faculty members are reviewing this submission according to the 100-mark rubric. Scores will be published once approved.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
