import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
    const { login } = useAuth();
    const navigate = useNavigate();

    const [role, setRole] = useState('student'); // 'student' or 'admin'
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const res = await login({ email, password, role });
            if (res.success) {
                if (res.user.role === 'admin') {
                    navigate('/admin');
                } else {
                    navigate('/student');
                }
            } else {
                setError(res.message || 'Invalid email or password.');
            }
        } catch (err) {
            setError('Login request failed. Ensure backend server is running.');
        } finally {
            setLoading(false);
        }
    };

    const fillDemoStudent = () => {
        setRole('student');
        setEmail('rahul@college.edu');
        setPassword('student123');
        setError('');
    };

    const fillDemoAdmin = () => {
        setRole('admin');
        setEmail('admin@projectsphere.edu');
        setPassword('admin123');
        setError('');
    };

    return (
        <div className="py-5" style={{ backgroundColor: '#f8fafc', minHeight: '80vh' }}>
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-md-7 col-lg-5">
                        <div className="content-card shadow-sm p-4 p-md-5">
                            {/* Header */}
                            <div className="text-center mb-4">
                                <div className="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex mb-2">
                                    <i className="bi bi-person-lock fs-3"></i>
                                </div>
                                <h3 className="fw-bold">Sign In to ProjectSphere</h3>
                                <p className="text-muted small">Enter your college credentials to access your portal</p>
                            </div>

                            {/* Demo Credentials Quick Switch Banner */}
                            <div className="p-3 bg-light rounded-3 border mb-4">
                                <small className="fw-bold d-block text-dark mb-2">
                                    <i className="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Demo Login:
                                </small>
                                <div className="d-flex gap-2">
                                    <button
                                        type="button"
                                        onClick={fillDemoStudent}
                                        className="btn btn-sm btn-outline-primary flex-grow-1"
                                    >
                                        Student Demo
                                    </button>
                                    <button
                                        type="button"
                                        onClick={fillDemoAdmin}
                                        className="btn btn-sm btn-outline-dark flex-grow-1"
                                    >
                                        Faculty Admin Demo
                                    </button>
                                </div>
                            </div>

                            {/* Role Tabs */}
                            <ul className="nav nav-pills nav-fill mb-4 bg-light p-1 rounded-3">
                                <li className="nav-item">
                                    <button
                                        type="button"
                                        className={`nav-link py-2 ${role === 'student' ? 'active shadow-sm' : ''}`}
                                        onClick={() => setRole('student')}
                                    >
                                        <i className="bi bi-mortarboard-fill me-1"></i> Student Portal
                                    </button>
                                </li>
                                <li className="nav-item">
                                    <button
                                        type="button"
                                        className={`nav-link py-2 ${role === 'admin' ? 'active shadow-sm' : ''}`}
                                        onClick={() => setRole('admin')}
                                    >
                                        <i className="bi bi-shield-lock-fill me-1"></i> Faculty / Admin
                                    </button>
                                </li>
                            </ul>

                            {error && (
                                <div className="alert alert-danger py-2 small" role="alert">
                                    <i className="bi bi-exclamation-triangle-fill me-1"></i> {error}
                                </div>
                            )}

                            <form onSubmit={handleSubmit}>
                                <div className="mb-3">
                                    <label className="form-label">Email Address</label>
                                    <div className="input-group">
                                        <span className="input-group-text bg-light text-muted">
                                            <i className="bi bi-envelope"></i>
                                        </span>
                                        <input
                                            type="email"
                                            className="form-control"
                                            placeholder={role === 'student' ? 'rahul@college.edu' : 'admin@projectsphere.edu'}
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <label className="form-label">Password</label>
                                    <div className="input-group">
                                        <span className="input-group-text bg-light text-muted">
                                            <i className="bi bi-key"></i>
                                        </span>
                                        <input
                                            type="password"
                                            className="form-control"
                                            placeholder="••••••••"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    className="btn btn-primary w-100 py-2 fw-semibold"
                                    disabled={loading}
                                >
                                    {loading ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Authenticating...
                                        </>
                                    ) : (
                                        <>Sign In <i className="bi bi-box-arrow-in-right ms-1"></i></>
                                    )}
                                </button>
                            </form>

                            <div className="text-center mt-4 pt-3 border-top">
                                <small className="text-muted">
                                    Don't have an account yet?{' '}
                                    <Link to="/register" className="fw-bold text-primary text-decoration-none">
                                        Register as Student
                                    </Link>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
