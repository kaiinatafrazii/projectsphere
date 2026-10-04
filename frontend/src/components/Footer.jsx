import React from 'react';
import { Link } from 'react-router-dom';
import { getAssetUrl } from '../api';

export default function Footer() {
    return (
        <footer className="footer-projectsphere mt-auto">
            <div className="container">
                <div className="row g-4">
                    <div className="col-lg-4 col-md-6">
                        <div className="d-flex align-items-center gap-2 mb-3">
                            <img 
                                src={getAssetUrl('assets/images/logo.svg')} 
                                alt="ProjectSphere Logo" 
                                style={{ height: '32px', filter: 'brightness(0) invert(1)' }} 
                            />
                        </div>
                        <p className="small text-secondary mb-3">
                            A centralized, secure college portal designed for Computer Engineering students to submit, showcase, and evaluate academic capstone projects.
                        </p>
                        <div className="badge bg-dark border border-secondary text-secondary p-2">
                            <i className="bi bi-shield-lock-fill me-1 text-primary"></i>Strict Code Protection Policy
                        </div>
                    </div>

                    <div className="col-lg-2 col-md-6 col-6">
                        <h5>Navigation</h5>
                        <ul className="list-unstyled small">
                            <li className="mb-2"><Link to="/">Home Portal</Link></li>
                            <li className="mb-2"><Link to="/browse">Browse Projects</Link></li>
                            <li className="mb-2"><Link to="/browse?sort=rank">Leaderboard</Link></li>
                            <li className="mb-2"><Link to="/student/submit-project">Submit Project</Link></li>
                        </ul>
                    </div>

                    <div className="col-lg-3 col-md-6 col-6">
                        <h5>Portals & Roles</h5>
                        <ul className="list-unstyled small">
                            <li className="mb-2"><Link to="/login"><i className="bi bi-person me-1"></i>Student Login</Link></li>
                            <li className="mb-2"><Link to="/register"><i className="bi bi-person-plus me-1"></i>Student Registration</Link></li>
                            <li className="mb-2"><Link to="/login?role=admin"><i className="bi bi-shield-check me-1"></i>Faculty Evaluator Portal</Link></li>
                            <li className="mb-2"><Link to="/admin/dashboard"><i className="bi bi-clipboard-data me-1"></i>Admin Dashboard</Link></li>
                        </ul>
                    </div>

                    <div className="col-lg-3 col-md-6">
                        <h5>Academic Details</h5>
                        <p className="small text-secondary mb-2">
                            <strong>Department:</strong> Computer Engineering<br />
                            <strong>Stack:</strong> React 19 Frontend &bull; PHP 8.2 MySQL REST Backend<br />
                            <strong>Evaluation Criteria:</strong> 100 Marks System (Innovation, Functionality, UI/UX, Tech & Docs)
                        </p>
                        <div className="text-secondary small mt-3">
                            <i className="bi bi-check2-circle text-success me-1"></i>Fullstack Diploma Project
                        </div>
                    </div>
                </div>

                <hr className="border-secondary my-4" />

                <div className="row align-items-center small">
                    <div className="col-md-6 text-center text-md-start text-secondary">
                        &copy; {new Date().getFullYear()} <strong>ProjectSphere</strong> &bull; React + PHP Capstone Platform.
                    </div>
                    <div className="col-md-6 text-center text-md-end text-secondary mt-2 mt-md-0">
                        Diploma Computer Science Final Year Project
                    </div>
                </div>
            </div>
        </footer>
    );
}
