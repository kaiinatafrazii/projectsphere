import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiGetCategories, apiSubmitProject } from '../api';

export default function SubmitProject() {
    const navigate = useNavigate();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [alert, setAlert] = useState(null);

    // Form fields
    const [title, setTitle] = useState('');
    const [categoryId, setCategoryId] = useState('');
    const [academicYear, setAcademicYear] = useState('2025-2026');
    const [guideName, setGuideName] = useState('');
    const [problemStatement, setProblemStatement] = useState('');
    const [objectives, setObjectives] = useState('');
    const [features, setFeatures] = useState('');
    const [technologies, setTechnologies] = useState('');
    const [demoUrl, setDemoUrl] = useState('');
    const [videoUrl, setVideoUrl] = useState('');
    const [githubRepoUrl, setGithubRepoUrl] = useState('');

    // Dynamic Team Members
    const [teamMembers, setTeamMembers] = useState([
        { student_name: '', roll_number: '', role: 'Team Member' }
    ]);

    // Files
    const [featuredImage, setFeaturedImage] = useState(null);
    const [synopsisFile, setSynopsisFile] = useState(null);
    const [sourceCodeFile, setSourceCodeFile] = useState(null);

    useEffect(() => {
        apiGetCategories().then((res) => {
            if (res.success && res.categories) {
                setCategories(res.categories);
                if (res.categories.length > 0) {
                    setCategoryId(res.categories[0].id);
                }
            }
        });
    }, []);

    const addTeamMember = () => {
        if (teamMembers.length < 5) {
            setTeamMembers([...teamMembers, { student_name: '', roll_number: '', role: 'Team Member' }]);
        }
    };

    const removeTeamMember = (index) => {
        setTeamMembers(teamMembers.filter((_, i) => i !== index));
    };

    const updateTeamMember = (index, field, value) => {
        const updated = [...teamMembers];
        updated[index][field] = value;
        setTeamMembers(updated);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setAlert(null);

        if (!title.trim() || !problemStatement.trim() || !categoryId) {
            setAlert({ type: 'danger', message: 'Please fill in all required fields (Title, Domain, Problem Statement).' });
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        setLoading(true);
        const formData = new FormData();
        formData.append('title', title.trim());
        formData.append('category_id', categoryId);
        formData.append('academic_year', academicYear);
        formData.append('guide_name', guideName.trim());
        formData.append('problem_statement', problemStatement.trim());
        formData.append('objectives', objectives.trim());
        formData.append('features', features.trim());
        formData.append('technologies', technologies.trim());
        formData.append('demo_url', demoUrl.trim());
        formData.append('video_url', videoUrl.trim());
        formData.append('github_repo_url', githubRepoUrl.trim());

        // Valid team members
        const validTeam = teamMembers.filter(m => m.student_name.trim() !== '');
        formData.append('team_members', JSON.stringify(validTeam));

        if (featuredImage) formData.append('featured_image', featuredImage);
        if (synopsisFile) formData.append('synopsis_file', synopsisFile);
        if (sourceCodeFile) formData.append('source_code', sourceCodeFile);

        try {
            const res = await apiSubmitProject(formData);
            if (res.success) {
                setAlert({ type: 'success', message: 'Project submitted successfully! It has been placed in the faculty review queue.' });
                setTimeout(() => {
                    navigate('/student/projects');
                }, 1500);
            } else {
                setAlert({ type: 'danger', message: res.message || 'Submission failed. Please check inputs.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Error submitting project. Make sure server is reachable.' });
        } finally {
            setLoading(false);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-lg-9">
                        {/* Page Header */}
                        <div className="mb-4">
                            <h2 className="fw-bold">Submit Academic Project</h2>
                            <p className="text-muted">
                                Enter your diploma/degree capstone project details for evaluation by the department faculty.
                            </p>
                        </div>

                        {alert && (
                            <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                                <i className={`bi ${alert.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2`}></i>
                                {alert.message}
                                <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                            </div>
                        )}

                        <form onSubmit={handleSubmit}>
                            {/* Section 1: Basic Information */}
                            <div className="content-card shadow-sm mb-4">
                                <h5 className="fw-bold mb-3 border-bottom pb-2">
                                    <i className="bi bi-info-circle-fill text-primary me-2"></i> 1. Basic Project Details
                                </h5>

                                <div className="row g-3">
                                    <div className="col-12">
                                        <label className="form-label">Project Title <span className="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="e.g. Smart Campus Navigation & Event Management System"
                                            value={title}
                                            onChange={(e) => setTitle(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Domain / Category <span className="text-danger">*</span></label>
                                        <select
                                            className="form-select"
                                            value={categoryId}
                                            onChange={(e) => setCategoryId(e.target.value)}
                                            required
                                        >
                                            {categories.map((c) => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="col-md-3">
                                        <label className="form-label">Academic Year</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="2025-2026"
                                            value={academicYear}
                                            onChange={(e) => setAcademicYear(e.target.value)}
                                        />
                                    </div>

                                    <div className="col-md-3">
                                        <label className="form-label">Project Guide / Mentor</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="Prof. K. Sharma"
                                            value={guideName}
                                            onChange={(e) => setGuideName(e.target.value)}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Section 2: Technical Description & Scope */}
                            <div className="content-card shadow-sm mb-4">
                                <h5 className="fw-bold mb-3 border-bottom pb-2">
                                    <i className="bi bi-file-earmark-text-fill text-primary me-2"></i> 2. Abstract & Technical Specifications
                                </h5>

                                <div className="row g-3">
                                    <div className="col-12">
                                        <label className="form-label">Problem Statement / Abstract <span className="text-danger">*</span></label>
                                        <textarea
                                            className="form-control"
                                            rows="4"
                                            placeholder="Describe the real-world problem your project addresses and the solution overview..."
                                            value={problemStatement}
                                            onChange={(e) => setProblemStatement(e.target.value)}
                                            required
                                        ></textarea>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Key Objectives</label>
                                        <textarea
                                            className="form-control"
                                            rows="3"
                                            placeholder="• Real-time notifications&#10;• Offline support&#10;• QR attendance"
                                            value={objectives}
                                            onChange={(e) => setObjectives(e.target.value)}
                                        ></textarea>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Key Features & Modules</label>
                                        <textarea
                                            className="form-control"
                                            rows="3"
                                            placeholder="• Student login portal&#10;• Faculty dashboard&#10;• PDF export report"
                                            value={features}
                                            onChange={(e) => setFeatures(e.target.value)}
                                        ></textarea>
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">Technologies & Tools Used (Comma separated)</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="PHP, MySQL, React, Bootstrap, Apache, REST API"
                                            value={technologies}
                                            onChange={(e) => setTechnologies(e.target.value)}
                                        />
                                        <small className="text-muted">Separate technologies with commas for tag generation.</small>
                                    </div>
                                </div>
                            </div>

                            {/* Section 3: Live Demos & Repository */}
                            <div className="content-card shadow-sm mb-4">
                                <h5 className="fw-bold mb-3 border-bottom pb-2">
                                    <i className="bi bi-link-45deg text-primary me-2"></i> 3. Project Links & Demos
                                </h5>

                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Live Demo Website URL (Optional)</label>
                                        <input
                                            type="url"
                                            className="form-control"
                                            placeholder="https://myproject.verce.app"
                                            value={demoUrl}
                                            onChange={(e) => setDemoUrl(e.target.value)}
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">YouTube / Loom Video Demo Link (Optional)</label>
                                        <input
                                            type="url"
                                            className="form-control"
                                            placeholder="https://youtube.com/watch?v=..."
                                            value={videoUrl}
                                            onChange={(e) => setVideoUrl(e.target.value)}
                                        />
                                    </div>

                                    <div className="col-12">
                                        <label className="form-label">GitHub Repository URL (Optional, Admin review only)</label>
                                        <input
                                            type="url"
                                            className="form-control"
                                            placeholder="https://github.com/myusername/project-repo"
                                            value={githubRepoUrl}
                                            onChange={(e) => setGithubRepoUrl(e.target.value)}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Section 4: Team Members */}
                            <div className="content-card shadow-sm mb-4">
                                <div className="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 className="fw-bold mb-0">
                                        <i className="bi bi-people-fill text-primary me-2"></i> 4. Project Team Members (Optional)
                                    </h5>
                                    {teamMembers.length < 5 && (
                                        <button type="button" className="btn btn-outline-primary btn-sm" onClick={addTeamMember}>
                                            <i className="bi bi-plus-lg me-1"></i> Add Member
                                        </button>
                                    )}
                                </div>

                                {teamMembers.map((member, idx) => (
                                    <div key={idx} className="row g-2 align-items-center mb-2 p-2 rounded bg-light border">
                                        <div className="col-md-4">
                                            <input
                                                type="text"
                                                className="form-control form-control-sm"
                                                placeholder="Student Name"
                                                value={member.student_name}
                                                onChange={(e) => updateTeamMember(idx, 'student_name', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-md-3">
                                            <input
                                                type="text"
                                                className="form-control form-control-sm"
                                                placeholder="Roll / PRN Number"
                                                value={member.roll_number}
                                                onChange={(e) => updateTeamMember(idx, 'roll_number', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-md-4">
                                            <input
                                                type="text"
                                                className="form-control form-control-sm"
                                                placeholder="Role (e.g. Backend Lead, UI Designer)"
                                                value={member.role}
                                                onChange={(e) => updateTeamMember(idx, 'role', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-md-1 text-center">
                                            {teamMembers.length > 1 && (
                                                <button
                                                    type="button"
                                                    className="btn btn-outline-danger btn-sm p-1"
                                                    onClick={() => removeTeamMember(idx)}
                                                >
                                                    <i className="bi bi-trash"></i>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Section 5: File Uploads & Security Guarantee */}
                            <div className="content-card shadow-sm mb-4">
                                <h5 className="fw-bold mb-3 border-bottom pb-2">
                                    <i className="bi bi-cloud-arrow-up-fill text-primary me-2"></i> 5. File Uploads
                                </h5>

                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Featured Screenshot (JPG, PNG)</label>
                                        <input
                                            type="file"
                                            className="form-control"
                                            accept="image/*"
                                            onChange={(e) => setFeaturedImage(e.target.files[0])}
                                        />
                                        <small className="text-muted">Displayed in the showcase gallery for peer learning.</small>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Project Synopsis (PDF)</label>
                                        <input
                                            type="file"
                                            className="form-control"
                                            accept=".pdf,application/pdf"
                                            onChange={(e) => setSynopsisFile(e.target.files[0])}
                                        />
                                        <small className="text-muted">Academic report / presentation summary.</small>
                                    </div>

                                    <div className="col-12 mt-3">
                                        <div className="p-3 rounded-3 border-start border-4 border-warning bg-light">
                                            <label className="form-label fw-bold text-dark d-flex align-items-center gap-2">
                                                <i className="bi bi-shield-lock-fill text-warning fs-5"></i>
                                                Source Code Archive (.ZIP) — Strictly Confidential
                                            </label>
                                            <input
                                                type="file"
                                                className="form-control mb-1"
                                                accept=".zip,application/zip,application/x-zip-compressed"
                                                onChange={(e) => setSourceCodeFile(e.target.files[0])}
                                            />
                                            <small className="text-muted d-block">
                                                <i className="bi bi-check-circle-fill text-success me-1"></i>
                                                Your source code is <strong>strictly private</strong> and will never be shared with other students. Only department faculty evaluators can inspect it for rubric grading.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Submit Button */}
                            <div className="d-flex justify-content-end gap-3 mb-5">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary px-4"
                                    onClick={() => navigate('/student')}
                                    disabled={loading}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="btn btn-primary px-5 fw-bold shadow-sm"
                                    disabled={loading}
                                >
                                    {loading ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Uploading & Submitting...
                                        </>
                                    ) : (
                                        <>Submit for Faculty Review <i className="bi bi-send-fill ms-2"></i></>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}
