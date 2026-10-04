import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiGetStats, apiGetProjects, apiGetRankings, apiGetCategories, getAssetUrl } from '../api';
import { useAuth } from '../context/AuthContext';

export default function Home() {
    const { user } = useAuth();
    const [stats, setStats] = useState({ total_projects: 0, total_students: 0, evaluated_projects: 0, total_categories: 0 });
    const [featuredProjects, setFeaturedProjects] = useState([]);
    const [topRankings, setTopRankings] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function loadHomeData() {
            try {
                const [statsRes, projRes, rankRes, catRes] = await Promise.all([
                    apiGetStats('public'),
                    apiGetProjects({ sort: 'latest', limit: 6 }),
                    apiGetRankings(0),
                    apiGetCategories()
                ]);

                if (statsRes.success) setStats(statsRes.stats);
                if (projRes.success) setFeaturedProjects(projRes.projects || []);
                if (rankRes.success) setTopRankings((rankRes.rankings || []).slice(0, 3));
                if (catRes.success) setCategories(catRes.categories || []);
            } catch (err) {
                console.error("Home loading error:", err);
            } finally {
                setLoading(false);
            }
        }
        loadHomeData();
    }, []);

    return (
        <div>
            {/* Hero Section */}
            <section className="hero-section text-center">
                <div className="container py-3">
                    <span className="hero-badge">
                        <i className="bi bi-mortarboard-fill text-primary"></i>
                        Department of Computer Engineering & IT
                    </span>
                    <h1 className="display-4 fw-bold mb-3">
                        Student Project Showcase & <br />
                        <span className="text-gradient">Evaluation Portal</span>
                    </h1>
                    <p className="lead text-muted mx-auto mb-4" style={{ maxWidth: '720px' }}>
                        A centralized platform for Diploma & Degree Computer Science students to submit academic projects, get peer inspiration, and receive 100-mark rubric evaluations from faculty.
                    </p>
                    <div className="d-flex justify-content-center gap-3 flex-wrap">
                        <Link to="/browse" className="btn btn-primary btn-lg shadow-sm">
                            <i className="bi bi-grid-3x3-gap-fill me-2"></i> Explore Projects
                        </Link>
                        <Link to={user ? (user.role === 'admin' ? '/admin' : '/student/submit') : '/login'} className="btn btn-outline-primary btn-lg">
                            <i className="bi bi-cloud-arrow-up-fill me-2"></i> Submit Your Project
                        </Link>
                        <Link to="/rankings" className="btn btn-light btn-lg border">
                            <i className="bi bi-trophy-fill text-warning me-2"></i> Leaderboard
                        </Link>
                    </div>

                    {/* Stats Counter Bar */}
                    <div className="row g-3 justify-content-center mt-5">
                        <div className="col-6 col-md-3">
                            <div className="content-card py-3 mb-0 text-center">
                                <h3 className="fw-bold text-primary mb-0">{stats.total_projects || '15+'}</h3>
                                <small className="text-muted fw-semibold">Projects Submitted</small>
                            </div>
                        </div>
                        <div className="col-6 col-md-3">
                            <div className="content-card py-3 mb-0 text-center">
                                <h3 className="fw-bold text-success mb-0">{stats.evaluated_projects || '10+'}</h3>
                                <small className="text-muted fw-semibold">Faculty Evaluated</small>
                            </div>
                        </div>
                        <div className="col-6 col-md-3">
                            <div className="content-card py-3 mb-0 text-center">
                                <h3 className="fw-bold text-info mb-0">{stats.total_students || '50+'}</h3>
                                <small className="text-muted fw-semibold">Enrolled Students</small>
                            </div>
                        </div>
                        <div className="col-6 col-md-3">
                            <div className="content-card py-3 mb-0 text-center">
                                <h3 className="fw-bold text-warning mb-0">{stats.total_categories || '6'}</h3>
                                <small className="text-muted fw-semibold">Tech Domains</small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Top 3 Podium Leaderboard */}
            {topRankings.length > 0 && (
                <section className="py-5 bg-white border-bottom">
                    <div className="container">
                        <div className="text-center mb-5">
                            <span className="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold mb-2">
                                <i className="bi bi-award-fill me-1"></i> Academic Honors
                            </span>
                            <h2 className="fw-bold">Top Ranked Projects</h2>
                            <p className="text-muted">Highest scoring college projects evaluated by department faculty.</p>
                        </div>

                        <div className="row g-4 justify-content-center align-items-stretch">
                            {topRankings.map((item, idx) => {
                                const rankColors = ['rank-1', 'rank-2', 'rank-3'];
                                const medalIcons = ['bi-trophy-fill text-warning', 'bi-award-fill text-secondary', 'bi-medal-fill text-danger'];
                                return (
                                    <div key={item.id} className="col-md-4">
                                        <div className="project-card h-100 position-relative">
                                            <div className="project-card-img-wrap">
                                                <img
                                                    src={item.featured_image ? getAssetUrl(item.featured_image) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80'}
                                                    alt={item.title}
                                                    className="project-card-img"
                                                    onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80'; }}
                                                />
                                                <div className={`badge-rank ${rankColors[idx] || 'rank-other'}`}>
                                                    <i className={`bi ${medalIcons[idx] || 'bi-star-fill'}`}></i>
                                                    Rank #{item.overall_rank || (idx + 1)}
                                                </div>
                                                <div className="position-absolute bottom-0 end-0 m-2">
                                                    <span className="badge-score">
                                                        <i className="bi bi-check-circle-fill text-success me-1"></i>
                                                        {parseFloat(item.total_score || 0).toFixed(1)} / 100
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="project-card-body">
                                                <span className="category-pill mb-2 align-self-start">
                                                    <i className="bi bi-tag-fill"></i> {item.category_name}
                                                </span>
                                                <h5 className="project-card-title">
                                                    <Link to={`/project/${item.id}`}>{item.title}</Link>
                                                </h5>
                                                <p className="project-card-desc">{item.abstract || item.problem_statement}</p>
                                                <div className="project-card-footer d-flex justify-content-between align-items-center">
                                                    <small className="text-muted">
                                                        <i className="bi bi-person-fill me-1"></i> {item.student_name}
                                                    </small>
                                                    <Link to={`/project/${item.id}`} className="btn btn-sm btn-outline-primary">
                                                        View Showcase <i className="bi bi-arrow-right"></i>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="text-center mt-4">
                            <Link to="/rankings" className="btn btn-outline-dark">
                                View Full Leaderboard <i className="bi bi-chevron-right ms-1"></i>
                            </Link>
                        </div>
                    </div>
                </section>
            )}

            {/* Why ProjectSphere Features */}
            <section className="py-5" style={{ backgroundColor: '#f8fafc' }}>
                <div className="container">
                    <div className="text-center mb-5">
                        <span className="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">Core Workflow</span>
                        <h2 className="fw-bold">Built Specifically for Engineering Colleges</h2>
                        <p className="text-muted">Streamlining project submission, transparent evaluation, and student peer learning.</p>
                    </div>

                    <div className="row g-4">
                        <div className="col-md-4">
                            <div className="content-card h-100 p-4">
                                <div className="rounded-3 bg-primary-subtle text-primary p-3 d-inline-block mb-3" style={{ fontSize: '1.75rem' }}>
                                    <i className="bi bi-lightbulb-fill"></i>
                                </div>
                                <h5 className="fw-bold">Showcase & Inspire</h5>
                                <p className="text-muted mb-0">
                                    Junior students can explore verified project documentation, screenshots, and live demos to get ideas for their academic mini and major capstone projects.
                                </p>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="content-card h-100 p-4">
                                <div className="rounded-3 bg-success-subtle text-success p-3 d-inline-block mb-3" style={{ fontSize: '1.75rem' }}>
                                    <i className="bi bi-clipboard2-check-fill"></i>
                                </div>
                                <h5 className="fw-bold">100-Mark Rubric Grading</h5>
                                <p className="text-muted mb-0">
                                    Faculty members evaluate submissions across 5 standardized academic criteria: Innovation (20), Functionality (30), UI/UX (20), Tech Stack (15), and Documentation (15).
                                </p>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="content-card h-100 p-4">
                                <div className="rounded-3 bg-danger-subtle text-danger p-3 d-inline-block mb-3" style={{ fontSize: '1.75rem' }}>
                                    <i className="bi bi-shield-lock-fill"></i>
                                </div>
                                <h5 className="fw-bold">Source Code Privacy</h5>
                                <p className="text-muted mb-0">
                                    Student source code ZIP files and repositories are strictly protected. Only authorized faculty evaluators can download source code for grading.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Categories Domain Grid */}
            {categories.length > 0 && (
                <section className="py-5 bg-white border-top">
                    <div className="container">
                        <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                            <div>
                                <h3 className="fw-bold mb-1">Browse by Domain</h3>
                                <p className="text-muted mb-0">Find projects in Web Development, Mobile Apps, Cloud, IoT, and more.</p>
                            </div>
                            <Link to="/browse" className="btn btn-outline-primary btn-sm mt-2 mt-md-0">
                                View All Categories <i className="bi bi-arrow-right"></i>
                            </Link>
                        </div>

                        <div className="row g-3">
                            {categories.map((cat) => (
                                <div key={cat.id} className="col-6 col-md-4 col-lg-2">
                                    <Link to={`/browse?category=${cat.id}`} className="text-decoration-none">
                                        <div className="content-card text-center p-3 h-100 hover-shadow transition-all border">
                                            <div className="fs-2 text-primary mb-2">
                                                <i className="bi bi-folder2-open"></i>
                                            </div>
                                            <h6 className="fw-bold text-dark mb-1 text-truncate">{cat.name}</h6>
                                            <span className="badge bg-secondary-subtle text-secondary small">
                                                {cat.project_count || 0} Projects
                                            </span>
                                        </div>
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Recent Submissions Grid */}
            <section className="py-5 bg-light border-top">
                <div className="container">
                    <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <div>
                            <h3 className="fw-bold mb-1">Recently Approved Projects</h3>
                            <p className="text-muted mb-0">Fresh student work approved by college evaluators.</p>
                        </div>
                        <Link to="/browse" className="btn btn-primary btn-sm mt-2 mt-md-0">
                            Explore All Submissions <i className="bi bi-arrow-right"></i>
                        </Link>
                    </div>

                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-primary" role="status"></div>
                            <p className="text-muted mt-2">Loading projects...</p>
                        </div>
                    ) : featuredProjects.length === 0 ? (
                        <div className="text-center py-5 content-card">
                            <i className="bi bi-folder-x fs-1 text-muted"></i>
                            <h5 className="mt-3">No Approved Projects Yet</h5>
                            <p className="text-muted">Be the first student to submit an academic project!</p>
                            <Link to="/student/submit" className="btn btn-primary btn-sm">Submit Project</Link>
                        </div>
                    ) : (
                        <div className="row g-4">
                            {featuredProjects.map((proj) => (
                                <div key={proj.id} className="col-md-6 col-lg-4">
                                    <div className="project-card">
                                        <div className="project-card-img-wrap">
                                            <img
                                                src={proj.featured_image ? getAssetUrl(proj.featured_image) : 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80'}
                                                alt={proj.title}
                                                className="project-card-img"
                                                onError={(e) => { e.target.src = 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&auto=format&fit=crop&q=80'; }}
                                            />
                                            {proj.overall_rank && proj.overall_rank <= 10 && (
                                                <div className="badge-rank rank-other">
                                                    <i className="bi bi-trophy"></i> Rank #{proj.overall_rank}
                                                </div>
                                            )}
                                            {proj.total_score && (
                                                <div className="position-absolute bottom-0 end-0 m-2">
                                                    <span className="badge-score">
                                                        {parseFloat(proj.total_score).toFixed(1)} / 100
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="project-card-body">
                                            <div className="d-flex justify-content-between align-items-center mb-2">
                                                <span className="category-pill">
                                                    <i className="bi bi-tag-fill"></i> {proj.category_name}
                                                </span>
                                                <small className="text-muted">{proj.academic_year || '2025-2026'}</small>
                                            </div>
                                            <h5 className="project-card-title">
                                                <Link to={`/project/${proj.id}`}>{proj.title}</Link>
                                            </h5>
                                            <p className="project-card-desc">{proj.abstract || proj.problem_statement}</p>
                                            
                                            {/* Tech Pills */}
                                            {proj.technologies && (
                                                <div className="mb-3">
                                                    {proj.technologies.split(',').slice(0, 3).map((t, i) => (
                                                        <span key={i} className="tech-tag">{t.trim()}</span>
                                                    ))}
                                                </div>
                                            )}

                                            <div className="project-card-footer d-flex justify-content-between align-items-center">
                                                <small className="text-muted text-truncate" style={{ maxWidth: '140px' }}>
                                                    <i className="bi bi-person-fill me-1"></i> {proj.student_name}
                                                </small>
                                                <Link to={`/project/${proj.id}`} className="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </div>
    );
}
