import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { apiGetRankings, apiGetCategories, getAssetUrl } from '../api';

export default function Rankings() {
    const [rankings, setRankings] = useState([]);
    const [categories, setCategories] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState(0);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        apiGetCategories().then((res) => {
            if (res.success) setCategories(res.categories || []);
        });
    }, []);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);

        apiGetRankings(selectedCategory)
            .then((res) => {
                if (isMounted && res.success) {
                    setRankings(res.rankings || []);
                }
            })
            .catch((err) => console.error("Error loading rankings:", err))
            .finally(() => {
                if (isMounted) setLoading(false);
            });

        return () => { isMounted = false; };
    }, [selectedCategory]);

    const topThree = rankings.slice(0, 3);

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                {/* Header */}
                <div className="text-center mb-5">
                    <span className="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold mb-2">
                        <i className="bi bi-award-fill me-1"></i> Academic Honors & Evaluation Scoreboard
                    </span>
                    <h1 className="fw-bold">Project Leaderboard & Rankings</h1>
                    <p className="text-muted mx-auto" style={{ maxWidth: '640px' }}>
                        Rankings are dynamically calculated based on faculty marks across Innovation (20), Functionality (30), UI/UX (20), Tech Stack (15), and Presentation/Docs (15).
                    </p>

                    {/* Category Filter Pills */}
                    <div className="d-flex justify-content-center gap-2 flex-wrap mt-3">
                        <button
                            className={`btn btn-sm ${selectedCategory === 0 ? 'btn-primary' : 'btn-outline-secondary'}`}
                            onClick={() => setSelectedCategory(0)}
                        >
                            All Categories (Overall)
                        </button>
                        {categories.map((c) => (
                            <button
                                key={c.id}
                                className={`btn btn-sm ${selectedCategory === c.id ? 'btn-primary' : 'btn-outline-secondary'}`}
                                onClick={() => setSelectedCategory(c.id)}
                            >
                                {c.name}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Top 3 Podium (Only shown if rankings >= 3) */}
                {topThree.length >= 3 && (
                    <div className="row g-4 mb-5 justify-content-center align-items-end">
                        {/* 2nd Place */}
                        <div className="col-md-4 order-2 order-md-1">
                            <div className="content-card text-center p-4 border-2 border-secondary shadow-sm position-relative">
                                <div className="badge-rank rank-2 position-static d-inline-flex mb-3">
                                    <i className="bi bi-award-fill me-1"></i> Rank #2 (Silver)
                                </div>
                                <h5 className="fw-bold mb-1">
                                    <Link to={`/project/${topThree[1].id}`} className="text-decoration-none text-dark">
                                        {topThree[1].title}
                                    </Link>
                                </h5>
                                <p className="text-muted small mb-2">{topThree[1].student_name} • {topThree[1].category_name}</p>
                                <div className="display-6 fw-bold text-secondary">
                                    {parseFloat(topThree[1].total_score || 0).toFixed(1)} <span className="fs-6 text-muted">/100</span>
                                </div>
                            </div>
                        </div>

                        {/* 1st Place (Gold Podium - Higher & Prominent) */}
                        <div className="col-md-4 order-1 order-md-2">
                            <div className="content-card text-center p-4 border-3 border-warning shadow position-relative" style={{ transform: 'scale(1.04)', backgroundColor: '#fffdf5' }}>
                                <div className="badge-rank rank-1 position-static d-inline-flex mb-3 fs-6">
                                    <i className="bi bi-trophy-fill me-1"></i> Rank #1 (Champion)
                                </div>
                                <h4 className="fw-bold mb-1">
                                    <Link to={`/project/${topThree[0].id}`} className="text-decoration-none text-dark">
                                        {topThree[0].title}
                                    </Link>
                                </h4>
                                <p className="text-muted small mb-2">{topThree[0].student_name} • {topThree[0].category_name}</p>
                                <div className="display-5 fw-bold text-warning-emphasis">
                                    {parseFloat(topThree[0].total_score || 0).toFixed(1)} <span className="fs-6 text-muted">/100</span>
                                </div>
                            </div>
                        </div>

                        {/* 3rd Place */}
                        <div className="col-md-4 order-3 order-md-3">
                            <div className="content-card text-center p-4 border-2 border-warning-subtle shadow-sm position-relative">
                                <div className="badge-rank rank-3 position-static d-inline-flex mb-3">
                                    <i className="bi bi-medal-fill me-1"></i> Rank #3 (Bronze)
                                </div>
                                <h5 className="fw-bold mb-1">
                                    <Link to={`/project/${topThree[2].id}`} className="text-decoration-none text-dark">
                                        {topThree[2].title}
                                    </Link>
                                </h5>
                                <p className="text-muted small mb-2">{topThree[2].student_name} • {topThree[2].category_name}</p>
                                <div className="display-6 fw-bold text-danger">
                                    {parseFloat(topThree[2].total_score || 0).toFixed(1)} <span className="fs-6 text-muted">/100</span>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Full Rankings Table */}
                <div className="content-card shadow-sm p-0 overflow-hidden">
                    <div className="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 className="fw-bold mb-0">Detailed Scorecard Table</h5>
                        <span className="badge bg-light text-dark border">
                            {rankings.length} Evaluated Submissions
                        </span>
                    </div>

                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-primary" role="status"></div>
                            <p className="text-muted mt-2">Computing ranks...</p>
                        </div>
                    ) : rankings.length === 0 ? (
                        <div className="text-center py-5">
                            <i className="bi bi-trophy fs-1 text-muted"></i>
                            <h5 className="mt-3">No Evaluated Projects in this Category</h5>
                            <p className="text-muted">Submissions are currently undergoing faculty review.</p>
                        </div>
                    ) : (
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th style={{ width: '80px' }} className="text-center">Rank</th>
                                        <th>Project Name</th>
                                        <th>Student & Department</th>
                                        <th>Category</th>
                                        <th className="text-center">Total Score</th>
                                        <th className="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rankings.map((r, index) => {
                                        const rankNumber = selectedCategory ? (r.category_rank || (index + 1)) : (r.overall_rank || (index + 1));
                                        return (
                                            <tr key={r.id}>
                                                <td className="text-center">
                                                    {rankNumber === 1 ? (
                                                        <span className="badge rounded-circle p-2 bg-warning text-dark fw-bold">#1</span>
                                                    ) : rankNumber === 2 ? (
                                                        <span className="badge rounded-circle p-2 bg-secondary text-white fw-bold">#2</span>
                                                    ) : rankNumber === 3 ? (
                                                        <span className="badge rounded-circle p-2 bg-danger text-white fw-bold">#3</span>
                                                    ) : (
                                                        <span className="fw-bold text-muted">#{rankNumber}</span>
                                                    )}
                                                </td>
                                                <td>
                                                    <Link to={`/project/${r.id}`} className="fw-bold text-dark text-decoration-none">
                                                        {r.title}
                                                    </Link>
                                                    <small className="text-muted d-block">{r.academic_year || '2025-2026'}</small>
                                                </td>
                                                <td>
                                                    <div className="fw-semibold text-dark">{r.student_name}</div>
                                                    <small className="text-muted">Roll: {r.roll_number || 'N/A'}</small>
                                                </td>
                                                <td>
                                                    <span className="badge bg-light text-primary border">
                                                        {r.category_name}
                                                    </span>
                                                </td>
                                                <td className="text-center">
                                                    <div className="fw-bold fs-5 text-primary">
                                                        {parseFloat(r.total_score || 0).toFixed(1)}
                                                        <span className="fs-6 text-muted fw-normal"> / 100</span>
                                                    </div>
                                                </td>
                                                <td className="text-end">
                                                    <Link to={`/project/${r.id}`} className="btn btn-sm btn-outline-primary">
                                                        View Rubric
                                                    </Link>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
