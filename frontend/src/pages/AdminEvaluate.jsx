import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { apiGetProjectDetails, apiSubmitEvaluation, getAssetUrl } from '../api';

export default function AdminEvaluate() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [project, setProject] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [alert, setAlert] = useState(null);

    // 100-Mark Rubric Marks State
    const [innovation, setInnovation] = useState(15);
    const [functionality, setFunctionality] = useState(25);
    const [uiUx, setUiUx] = useState(16);
    const [techStack, setTechStack] = useState(12);
    const [presentation, setPresentation] = useState(12);
    const [feedback, setFeedback] = useState('');

    useEffect(() => {
        apiGetProjectDetails(id)
            .then((res) => {
                if (res.success && res.project) {
                    setProject(res.project);
                    if (res.evaluations && res.evaluations.length > 0) {
                        const prev = res.evaluations[0];
                        setInnovation(prev.innovation_score || 0);
                        setFunctionality(prev.functionality_score || 0);
                        setUiUx(prev.ui_ux_score || 0);
                        setTechStack(prev.tech_stack_score || 0);
                        setPresentation(prev.presentation_score || 0);
                        setFeedback(prev.feedback || '');
                    }
                }
            })
            .catch((err) => console.error("Error loading project for evaluation:", err))
            .finally(() => setLoading(false));
    }, [id]);

    const totalScore = (
        (Number(innovation) || 0) +
        (Number(functionality) || 0) +
        (Number(uiUx) || 0) +
        (Number(techStack) || 0) +
        (Number(presentation) || 0)
    );

    const getGradeInfo = (score) => {
        if (score >= 90) return { label: 'Outstanding (A+)', color: 'success' };
        if (score >= 75) return { label: 'Excellent (A)', color: 'primary' };
        if (score >= 60) return { label: 'Very Good (B+)', color: 'info' };
        if (score >= 50) return { label: 'Satisfactory (B)', color: 'warning' };
        return { label: 'Needs Improvement (C)', color: 'danger' };
    };

    const grade = getGradeInfo(totalScore);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setAlert(null);
        setSubmitting(true);

        try {
            const evalData = {
                project_id: id,
                innovation_score: Number(innovation),
                functionality_score: Number(functionality),
                ui_ux_score: Number(uiUx),
                tech_stack_score: Number(techStack),
                presentation_score: Number(presentation),
                feedback: feedback.trim()
            };

            const res = await apiSubmitEvaluation(evalData);
            if (res.success) {
                setAlert({ type: 'success', message: 'Evaluation submitted successfully! Total score recorded and rankings updated.' });
                setTimeout(() => {
                    navigate('/admin/projects');
                }, 1500);
            } else {
                setAlert({ type: 'danger', message: res.message || 'Failed to submit evaluation.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Error submitting evaluation score.' });
        } finally {
            setSubmitting(false);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    if (loading) {
        return (
            <div className="container py-5 text-center" style={{ minHeight: '60vh' }}>
                <div className="spinner-border text-primary" role="status"></div>
                <p className="text-muted mt-2">Loading project submission...</p>
            </div>
        );
    }

    if (!project) {
        return (
            <div className="container py-5 text-center">
                <div className="content-card py-5">
                    <i className="bi bi-exclamation-circle text-danger fs-1"></i>
                    <h4 className="mt-3">Project Not Found</h4>
                    <Link to="/admin/projects" className="btn btn-primary mt-2">Back to Projects</Link>
                </div>
            </div>
        );
    }

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Breadcrumb */}
                <nav aria-label="breadcrumb" className="mb-3">
                    <ol className="breadcrumb">
                        <li className="breadcrumb-item"><Link to="/admin" className="text-decoration-none">Dashboard</Link></li>
                        <li className="breadcrumb-item"><Link to="/admin/projects" className="text-decoration-none">Projects</Link></li>
                        <li className="breadcrumb-item active">100-Mark Rubric Evaluation</li>
                    </ol>
                </nav>

                <div className="row g-4">
                    {/* Left: Project Artifacts & Private Code Access */}
                    <div className="col-lg-5">
                        <div className="content-card shadow-sm mb-4">
                            <span className="category-pill mb-2">{project.category_name}</span>
                            <h4 className="fw-bold text-dark mb-2">{project.title}</h4>
                            <p className="text-muted small mb-3">
                                <strong>Student:</strong> {project.student_name} (Roll: {project.roll_number || 'N/A'})
                            </p>

                            <div className="p-3 bg-light rounded-3 border mb-3">
                                <strong className="d-block text-dark small mb-1">Abstract:</strong>
                                <p className="text-muted small mb-0">{project.abstract || project.problem_statement}</p>
                            </div>

                            {/* Evaluator Artifact Links */}
                            <h6 className="fw-bold mb-2">Academic Submission Artifacts:</h6>
                            <div className="d-grid gap-2">
                                {project.source_code_path ? (
                                    <a href={getAssetUrl(project.source_code_path)} download className="btn btn-warning text-dark fw-bold">
                                        <i className="bi bi-file-earmark-zip-fill me-2"></i> Download Private Source Code (.zip)
                                    </a>
                                ) : (
                                    <button className="btn btn-light border text-muted" disabled>
                                        <i className="bi bi-file-earmark-zip me-2"></i> No Source Code ZIP Uploaded
                                    </button>
                                )}

                                {project.synopsis_file && (
                                    <a href={getAssetUrl(project.synopsis_file)} target="_blank" rel="noreferrer" className="btn btn-outline-danger">
                                        <i className="bi bi-file-earmark-pdf-fill me-2"></i> Inspect Project Synopsis (PDF)
                                    </a>
                                )}

                                {project.demo_url && (
                                    <a href={project.demo_url} target="_blank" rel="noreferrer" className="btn btn-outline-primary">
                                        <i className="bi bi-box-arrow-up-right me-2"></i> Test Live Demo
                                    </a>
                                )}

                                {project.github_repo_url && (
                                    <a href={project.github_repo_url} target="_blank" rel="noreferrer" className="btn btn-outline-dark">
                                        <i className="bi bi-github me-2"></i> Faculty GitHub Repository Link
                                    </a>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Right: 100-Mark Rubric Scoring Form */}
                    <div className="col-lg-7">
                        <div className="content-card shadow-sm">
                            <div className="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <h4 className="fw-bold mb-0">Academic Evaluation Rubric</h4>
                                <span className={`badge bg-${grade.color} fs-6 px-3 py-1`}>
                                    Total: {totalScore} / 100
                                </span>
                            </div>

                            {alert && (
                                <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                                    {alert.message}
                                    <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                                </div>
                            )}

                            {/* Live Score Progress Bar */}
                            <div className="mb-4">
                                <div className="d-flex justify-content-between small text-muted mb-1">
                                    <span>Score: {totalScore}/100</span>
                                    <span className="fw-bold">{grade.label}</span>
                                </div>
                                <div className="progress" style={{ height: '12px' }}>
                                    <div
                                        className={`progress-bar bg-${grade.color}`}
                                        role="progressbar"
                                        style={{ width: `${Math.min(totalScore, 100)}%` }}
                                    ></div>
                                </div>
                            </div>

                            <form onSubmit={handleSubmit}>
                                {/* Criterion 1: Innovation (20) */}
                                <div className="mb-3 p-3 bg-light rounded-3 border">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label fw-bold mb-0">
                                            1. Innovation, Creativity & Problem Formulation
                                        </label>
                                        <span className="badge bg-primary fs-6">{innovation} / 20</span>
                                    </div>
                                    <small className="text-muted d-block mb-2">Originality of concept, problem relevance, and novelty.</small>
                                    <input
                                        type="range"
                                        className="form-range"
                                        min="0"
                                        max="20"
                                        value={innovation}
                                        onChange={(e) => setInnovation(Number(e.target.value))}
                                    />
                                </div>

                                {/* Criterion 2: Functionality (30) */}
                                <div className="mb-3 p-3 bg-light rounded-3 border">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label fw-bold mb-0">
                                            2. Technical Implementation & Working Functionality
                                        </label>
                                        <span className="badge bg-primary fs-6">{functionality} / 30</span>
                                    </div>
                                    <small className="text-muted d-block mb-2">Working demonstration, core features, stability, and error handling.</small>
                                    <input
                                        type="range"
                                        className="form-range"
                                        min="0"
                                        max="30"
                                        value={functionality}
                                        onChange={(e) => setFunctionality(Number(e.target.value))}
                                    />
                                </div>

                                {/* Criterion 3: UI/UX (20) */}
                                <div className="mb-3 p-3 bg-light rounded-3 border">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label fw-bold mb-0">
                                            3. User Interface, UX & Design Aesthetics
                                        </label>
                                        <span className="badge bg-primary fs-6">{uiUx} / 20</span>
                                    </div>
                                    <small className="text-muted d-block mb-2">Visual appeal, responsiveness, accessibility, and modern UI practices.</small>
                                    <input
                                        type="range"
                                        className="form-range"
                                        min="0"
                                        max="20"
                                        value={uiUx}
                                        onChange={(e) => setUiUx(Number(e.target.value))}
                                    />
                                </div>

                                {/* Criterion 4: Tech Stack (15) */}
                                <div className="mb-3 p-3 bg-light rounded-3 border">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label fw-bold mb-0">
                                            4. Technology Stack & Source Code Architecture
                                        </label>
                                        <span className="badge bg-primary fs-6">{techStack} / 15</span>
                                    </div>
                                    <small className="text-muted d-block mb-2">Clean coding standards, database schema design, and modular structure.</small>
                                    <input
                                        type="range"
                                        className="form-range"
                                        min="0"
                                        max="15"
                                        value={techStack}
                                        onChange={(e) => setTechStack(Number(e.target.value))}
                                    />
                                </div>

                                {/* Criterion 5: Presentation & Documentation (15) */}
                                <div className="mb-3 p-3 bg-light rounded-3 border">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label fw-bold mb-0">
                                            5. Presentation, Documentation & Synopsis (PDF)
                                        </label>
                                        <span className="badge bg-primary fs-6">{presentation} / 15</span>
                                    </div>
                                    <small className="text-muted d-block mb-2">Clarity of report, viva readiness, and technical documentation.</small>
                                    <input
                                        type="range"
                                        className="form-range"
                                        min="0"
                                        max="15"
                                        value={presentation}
                                        onChange={(e) => setPresentation(Number(e.target.value))}
                                    />
                                </div>

                                {/* Evaluator Feedback */}
                                <div className="mb-4">
                                    <label className="form-label fw-bold">Evaluator Feedback / Remarks for Student</label>
                                    <textarea
                                        className="form-control"
                                        rows="3"
                                        placeholder="e.g. Excellent system architecture and clean UI. Recommend adding automated test suites and Redis caching in Phase 2."
                                        value={feedback}
                                        onChange={(e) => setFeedback(e.target.value)}
                                        required
                                    ></textarea>
                                </div>

                                {/* Actions */}
                                <div className="d-flex justify-content-end gap-2">
                                    <Link to="/admin/projects" className="btn btn-light border px-4">
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        className="btn btn-success px-5 fw-bold"
                                        disabled={submitting}
                                    >
                                        {submitting ? 'Saving Marks...' : 'Approve & Save Evaluation'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
