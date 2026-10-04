import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { apiGetCategories, getAssetUrl } from '../api';

export default function Navbar() {
    const { user, logout } = useAuth();
    const [categories, setCategories] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        apiGetCategories().then(res => {
            if (res.success && res.categories) {
                setCategories(res.categories);
            }
        });
    }, []);

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            navigate(`/browse?search=${encodeURIComponent(searchQuery.trim())}`);
        } else {
            navigate('/browse');
        }
    };

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    return (
        <nav className="navbar navbar-expand-lg navbar-projectsphere sticky-top bg-white border-bottom shadow-sm">
            <div className="container">
                <Link className="navbar-brand d-flex align-items-center gap-2" to="/">
                    <img src={getAssetUrl('assets/images/logo.svg')} alt="ProjectSphere Logo" style={{ height: '36px' }} />
                </Link>

                <button className="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarReact">
                    <span className="navbar-toggler-icon"></span>
                </button>

                <div className="collapse navbar-collapse" id="navbarReact">
                    <ul className="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname === '/' ? 'active' : ''}`} to="/">
                                <i className="bi bi-house-door me-1"></i>Home
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname === '/browse' ? 'active' : ''}`} to="/browse">
                                <i className="bi bi-compass me-1"></i>Explore Projects
                            </Link>
                        </li>
                        <li className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i className="bi bi-grid me-1"></i>Categories
                            </a>
                            <ul className="dropdown-menu shadow border-0 py-2">
                                {categories.map(cat => (
                                    <li key={cat.id}>
                                        <Link className="dropdown-item py-1.5" to={`/browse?category=${cat.id}`}>
                                            <i className={`bi ${cat.icon || 'bi-folder'} me-2 text-primary`}></i>
                                            {cat.name}
                                        </Link>
                                    </li>
                                ))}
                                <li><hr className="dropdown-divider" /></li>
                                <li>
                                    <Link className="dropdown-item text-primary fw-semibold" to="/browse">
                                        Browse All Categories &rarr;
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li className="nav-item">
                            <Link className="nav-link text-warning-emphasis fw-medium" to="/browse?sort=rank">
                                <i className="bi bi-trophy-fill me-1 text-warning"></i>Leaderboard
                            </Link>
                        </li>
                    </ul>

                    {/* Quick Search */}
                    <form className="d-flex me-3" onSubmit={handleSearch}>
                        <div className="input-group input-group-sm">
                            <input 
                                className="form-control" 
                                type="search" 
                                placeholder="Search projects..." 
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                style={{ width: '170px' }}
                            />
                            <button className="btn btn-outline-secondary" type="submit"><i className="bi bi-search"></i></button>
                        </div>
                    </form>

                    {/* User Auth Nav Actions */}
                    <div className="d-flex align-items-center gap-2">
                        {user ? (
                            user.role === 'admin' ? (
                                <div className="dropdown">
                                    <button className="btn btn-sm btn-dark dropdown-toggle fw-semibold d-flex align-items-center gap-1" data-bs-toggle="dropdown">
                                        <i className="bi bi-shield-lock-fill text-danger me-1"></i>
                                        {user.name}
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                        <li><h6 className="dropdown-header">Faculty Evaluator</h6></li>
                                        <li><Link className="dropdown-item" to="/admin/dashboard"><i className="bi bi-speedometer2 me-2"></i>Dashboard</Link></li>
                                        <li><Link className="dropdown-item" to="/admin/projects"><i className="bi bi-stack me-2"></i>Manage Projects</Link></li>
                                        <li><Link className="dropdown-item" to="/admin/rankings"><i className="bi bi-trophy me-2"></i>Rankings</Link></li>
                                        <li><Link className="dropdown-item" to="/admin/categories"><i className="bi bi-tags me-2"></i>Categories</Link></li>
                                        <li><Link className="dropdown-item" to="/admin/students"><i className="bi bi-people me-2"></i>Students</Link></li>
                                        <li><Link className="dropdown-item" to="/admin/feedback"><i className="bi bi-chat-dots me-2"></i>Feedback Log</Link></li>
                                        <li><hr className="dropdown-divider" /></li>
                                        <li><button className="dropdown-item text-danger" onClick={handleLogout}><i className="bi bi-box-arrow-right me-2"></i>Logout</button></li>
                                    </ul>
                                </div>
                            ) : (
                                <div className="d-flex align-items-center gap-2">
                                    <Link to="/student/submit-project" className="btn btn-sm btn-primary fw-semibold">
                                        <i className="bi bi-cloud-arrow-up-fill me-1"></i>Submit Project
                                    </Link>
                                    <div className="dropdown">
                                        <button className="btn btn-sm btn-light border dropdown-toggle fw-semibold" data-bs-toggle="dropdown">
                                            <i className="bi bi-person-circle text-primary me-1"></i>
                                            {user.name}
                                        </button>
                                        <ul className="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                            <li><h6 className="dropdown-header">Student Portal</h6></li>
                                            <li><Link className="dropdown-item" to="/student/dashboard"><i className="bi bi-speedometer2 me-2"></i>Dashboard</Link></li>
                                            <li><Link className="dropdown-item" to="/student/my-projects"><i className="bi bi-folder2-open me-2"></i>My Projects</Link></li>
                                            <li><Link className="dropdown-item" to="/student/my-evaluations"><i className="bi bi-award me-2"></i>My Evaluations</Link></li>
                                            <li><Link className="dropdown-item" to="/student/profile"><i className="bi bi-person-gear me-2"></i>Profile</Link></li>
                                            <li><hr className="dropdown-divider" /></li>
                                            <li><button className="dropdown-item text-danger" onClick={handleLogout}><i className="bi bi-box-arrow-right me-2"></i>Logout</button></li>
                                        </ul>
                                    </div>
                                </div>
                            )
                        ) : (
                            <div className="d-flex gap-2">
                                <Link to="/login" className="btn btn-sm btn-outline-primary fw-semibold">
                                    <i className="bi bi-box-arrow-in-right me-1"></i>Login
                                </Link>
                                <Link to="/register" className="btn btn-sm btn-primary fw-semibold">
                                    <i className="bi bi-person-plus-fill me-1"></i>Register
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </nav>
    );
}
