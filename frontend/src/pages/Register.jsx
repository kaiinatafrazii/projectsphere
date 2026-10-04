import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Register() {
    const { register } = useAuth();
    const navigate = useNavigate();

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [rollNumber, setRollNumber] = useState('');
    const [department, setDepartment] = useState('Computer Engineering');
    const [semester, setSemester] = useState('6th Semester');
    const [phone, setPhone] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const res = await register({
                name,
                email,
                password,
                roll_number: rollNumber,
                department,
                semester,
                phone
            });

            if (res.success) {
                navigate('/student');
            } else {
                setError(res.message || 'Registration failed. Try again.');
            }
        } catch (err) {
            setError('Registration request failed. Server error.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="py-5" style={{ backgroundColor: '#f8fafc', minHeight: '80vh' }}>
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-md-8 col-lg-6">
                        <div className="content-card shadow-sm p-4 p-md-5">
                            {/* Header */}
                            <div className="text-center mb-4">
                                <div className="rounded-circle bg-success-subtle text-success p-3 d-inline-flex mb-2">
                                    <i className="bi bi-person-plus-fill fs-3"></i>
                                </div>
                                <h3 className="fw-bold">Student Registration</h3>
                                <p className="text-muted small">Create an account to submit academic projects and receive faculty reviews</p>
                            </div>

                            {error && (
                                <div className="alert alert-danger py-2 small" role="alert">
                                    <i className="bi bi-exclamation-triangle-fill me-1"></i> {error}
                                </div>
                            )}

                            <form onSubmit={handleSubmit}>
                                <div className="row g-3 mb-3">
                                    <div className="col-12">
                                        <label className="form-label">Full Name</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="e.g. Priya Sharma"
                                            value={name}
                                            onChange={(e) => setName(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Email Address</label>
                                        <input
                                            type="email"
                                            className="form-control"
                                            placeholder="priya@college.edu"
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Password</label>
                                        <input
                                            type="password"
                                            className="form-control"
                                            placeholder="Minimum 6 characters"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">College Roll / PRN Number</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="e.g. 23CO104"
                                            value={rollNumber}
                                            onChange={(e) => setRollNumber(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Phone Number (Optional)</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="e.g. 9876543210"
                                            value={phone}
                                            onChange={(e) => setPhone(e.target.value)}
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Department / Branch</label>
                                        <select
                                            className="form-select"
                                            value={department}
                                            onChange={(e) => setDepartment(e.target.value)}
                                        >
                                            <option value="Computer Engineering">Computer Engineering</option>
                                            <option value="Information Technology">Information Technology</option>
                                            <option value="AI & Data Science">AI & Data Science</option>
                                            <option value="Electronics & Telecommunication">Electronics & Telecom</option>
                                        </select>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Current Semester / Year</label>
                                        <select
                                            className="form-select"
                                            value={semester}
                                            onChange={(e) => setSemester(e.target.value)}
                                        >
                                            <option value="4th Semester (2nd Year)">4th Semester (2nd Year)</option>
                                            <option value="5th Semester (3rd Year)">5th Semester (3rd Year)</option>
                                            <option value="6th Semester (Final Year)">6th Semester (Final Year)</option>
                                            <option value="8th Semester (B.Tech Final)">8th Semester (B.Tech Final)</option>
                                        </select>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    className="btn btn-primary w-100 py-2 fw-semibold mt-2"
                                    disabled={loading}
                                >
                                    {loading ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Creating Account...
                                        </>
                                    ) : (
                                        <>Register Account <i className="bi bi-arrow-right ms-1"></i></>
                                    )}
                                </button>
                            </form>

                            <div className="text-center mt-4 pt-3 border-top">
                                <small className="text-muted">
                                    Already have an account?{' '}
                                    <Link to="/login" className="fw-bold text-primary text-decoration-none">
                                        Sign In
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
