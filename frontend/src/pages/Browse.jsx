import React, { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { apiGetProjects, apiGetCategories, getAssetUrl } from '../api';

export default function Browse() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [projects, setProjects] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    const search = searchParams.get('search') || '';
    const category = searchParams.get('category') || '';
    const tech = searchParams.get('tech') || '';
    const sort = searchParams.get('sort') || 'rank';

    useEffect(() => {
        apiGetCategories().then((res) => {
            if (res.success) setCategories(res.categories || []);
        });
    }, []);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);

        const params = {};
        if (search) params.search = search;
        if (category) params.category = category;
        if (tech) params.tech = tech;
        if (sort) params.sort = sort;

        apiGetProjects(params)
            .then((res) => {
                if (isMounted && res.success) {
                    setProjects(res.projects || []);
                }
            })
            .catch((err) => console.error("Error loading projects:", err))
            .finally(() => {
                if (isMounted) setLoading(false);
            });

        return () => { isMounted = false; };
    }, [search, category, tech, sort]);

    const handleFilterChange = (key, value) => {
        const next = new URLSearchParams(searchParams);
        if (value) {
            next.set(key, value);
        } else {
            next.delete(key);
        }
        setSearchParams(next);
    };

    const clearFilters = () => {
        setSearchParams({});
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '80vh' }}>
            <div className="container">
                {/* Page Title & Breadcrumb */}
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 className="fw-bold mb-1">Explore Student Projects</h2>
                        <p className="text-muted mb-0">Browse through peer-reviewed academic projects across all branches and domains.</p>
                    </div>
                    <div>
                        <Link to="/rankings" className="btn btn-outline-warning text-dark fw-semibold">
                            <i className="bi bi-trophy-fill text-warning me-1"></i> View Leaderboard
                        </Link>
                    </div>
                </div>

                {/* Filter & Search Bar Card */}
                <div className="content-card p-3 mb-4 shadow-sm">
                    <div className="row g-2 align-items-center">
                        {/* Search Input */}
                        <div className="col-12 col-md-4">
                            <div className="input-group">
                                <span className="input-group-text bg-white border-end-0 text-muted">
                                    <i className="bi bi-search"></i>
                                </span>
                                <input
                                    type="text"
                                    className="form-control border-start-0 ps-0"
                                    placeholder="Search by title, abstract, student..."
                                    value={search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                />
                                {search && (
                                    <button className="btn btn-outline-secondary" onClick={() => handleFilterChange('search', '')}>
                                        <i className="bi bi-x"></i>
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Category Dropdown */}
                        <div className="col-6 col-md-3">
                            <select
                                className="form-select"
                                value={category}
                                onChange={(e) => handleFilterChange('category', e.target.value)}
                            >
                                <option value="">All Categories / Domains</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name} ({c.project_count || 0})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Tech Stack Input */}
                        <div className="col-6 col-md-3">
                            <input
                                type="text"
                                className="form-control"
                                placeholder="Technology (e.g. PHP, React)"
                                value={tech}
                                onChange={(e) => handleFilterChange('tech', e.target.value)}
                            />
                        </div>

                        {/* Sort Dropdown */}
                        <div className="col-12 col-md-2">
                            <select
                                className="form-select"
                                value={sort}
                                onChange={(e) => handleFilterChange('sort', e.target.value)}
                            >
                                <option value="rank">Top Ranked</option>
                                <option value="score">Highest Score</option>
                                <option value="latest">Newest First</option>
                                <option value="title">Title (A-Z)</option>
                            </select>
                        </div>
                    </div>

                    {/* Active Filters Bar */}
                    {(search || category || tech || (sort && sort !== 'rank')) && (
                        <div className="d-flex align-items-center gap-2 mt-3 pt-2 border-top flex-wrap">
                            <small className="text-muted fw-semibold">Active Filters:</small>
                            {search && (
                                <span className="badge bg-light text-dark border">
                                    Keyword: "{search}" <i className="bi bi-x ms-1 cursor-pointer" onClick={() => handleFilterChange('search', '')}></i>
                                </span>
                            )}
                            {category && (
                                <span className="badge bg-light text-dark border">
                                    Domain: {categories.find(c => String(c.id) === String(category))?.name || category}
                                    <i className="bi bi-x ms-1 cursor-pointer" onClick={() => handleFilterChange('category', '')}></i>
                                </span>
                            )}
                            {tech && (
                                <span className="badge bg-light text-dark border">
                                    Tech: {tech} <i className="bi bi-x ms-1 cursor-pointer" onClick={() => handleFilterChange('tech', '')}></i>
                                </span>
                            )}
                            <button className="btn btn-link btn-sm text-danger p-0 ms-auto text-decoration-none" onClick={clearFilters}>
                                Reset All Filters
                            </button>
                        </div>
                    )}
                </div>

                {/* Results Count */}
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <span className="text-muted small">
                        Showing <strong>{projects.length}</strong> approved {projects.length === 1 ? 'project' : 'projects'}
                    </span>
                </div>

                {/* Projects Grid */}
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-primary" role="status"></div>
                        <p className="text-muted mt-2">Filtering projects...</p>
                    </div>
                ) : projects.length === 0 ? (
                    <div className="content-card text-center py-5">
                        <i className="bi bi-search fs-1 text-muted"></i>
                        <h4 className="mt-3 fw-bold">No Projects Found</h4>
                        <p className="text-muted">No projects matched your filter criteria. Try adjusting your search keywords or clear filters.</p>
                        <button className="btn btn-outline-primary btn-sm" onClick={clearFilters}>
                            Clear Filters
                        </button>
                    </div>
                ) : (
                    <div className="row g-4">
                        {projects.map((proj) => (
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
                                            <div className={`badge-rank ${proj.overall_rank === 1 ? 'rank-1' : proj.overall_rank === 2 ? 'rank-2' : proj.overall_rank === 3 ? 'rank-3' : 'rank-other'}`}>
                                                <i className="bi bi-trophy-fill"></i> Rank #{proj.overall_rank}
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
                                        
                                        {proj.technologies && (
                                            <div className="mb-3">
                                                {proj.technologies.split(',').slice(0, 4).map((t, idx) => (
                                                    <span key={idx} className="tech-tag">{t.trim()}</span>
                                                ))}
                                            </div>
                                        )}

                                        <div className="project-card-footer d-flex justify-content-between align-items-center">
                                            <small className="text-muted text-truncate" style={{ maxWidth: '140px' }}>
                                                <i className="bi bi-person-fill me-1"></i> {proj.student_name}
                                            </small>
                                            <Link to={`/project/${proj.id}`} className="btn btn-sm btn-outline-primary">
                                                View Showcase
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
