import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { apiUpdateStudentProfile, apiUpdatePassword } from '../api';

export default function StudentProfile() {
    const { user, refreshUser } = useAuth();

    // Profile form state
    const [name, setName] = useState('');
    const [rollNumber, setRollNumber] = useState('');
    const [phone, setPhone] = useState('');
    const [department, setDepartment] = useState('');
    const [semester, setSemester] = useState('');
    const [profileAlert, setProfileAlert] = useState(null);
    const [profileLoading, setProfileLoading] = useState(false);

    // Password form state
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [passwordAlert, setPasswordAlert] = useState(null);
    const [passwordLoading, setPasswordLoading] = useState(false);

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setRollNumber(user.roll_number || '');
            setPhone(user.phone || '');
            setDepartment(user.department || 'Computer Engineering');
            setSemester(user.semester || '6th Semester');
        }
    }, [user]);

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setProfileAlert(null);
        setProfileLoading(true);

        try {
            const res = await apiUpdateStudentProfile({
                name,
                roll_number: rollNumber,
                phone,
                department,
                semester
            });
            if (res.success) {
                setProfileAlert({ type: 'success', message: 'Profile details updated successfully!' });
                refreshUser();
            } else {
                setProfileAlert({ type: 'danger', message: res.message || 'Failed to update profile.' });
            }
        } catch (err) {
            setProfileAlert({ type: 'danger', message: 'Network error updating profile.' });
        } finally {
            setProfileLoading(false);
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();
        setPasswordAlert(null);

        if (newPassword !== confirmPassword) {
            setPasswordAlert({ type: 'danger', message: 'New password and confirmation do not match.' });
            return;
        }

        if (newPassword.length < 6) {
            setPasswordAlert({ type: 'danger', message: 'New password must be at least 6 characters.' });
            return;
        }

        setPasswordLoading(true);
        try {
            const res = await apiUpdatePassword({
                current_password: currentPassword,
                new_password: newPassword
            });
            if (res.success) {
                setPasswordAlert({ type: 'success', message: 'Password updated successfully!' });
                setCurrentPassword('');
                setNewPassword('');
                setConfirmPassword('');
            } else {
                setPasswordAlert({ type: 'danger', message: res.message || 'Failed to change password.' });
            }
        } catch (err) {
            setPasswordAlert({ type: 'danger', message: 'Network error changing password.' });
        } finally {
            setPasswordLoading(false);
        }
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                <div className="mb-4">
                    <h2 className="fw-bold">My Account & Profile</h2>
                    <p className="text-muted">Manage your personal details, academic credentials, and security settings.</p>
                </div>

                <div className="row g-4">
                    {/* Left Column: Profile Information */}
                    <div className="col-lg-7">
                        <div className="content-card shadow-sm">
                            <h5 className="fw-bold mb-3 border-bottom pb-2">
                                <i className="bi bi-person-lines-fill text-primary me-2"></i> Student Details
                            </h5>

                            {profileAlert && (
                                <div className={`alert alert-${profileAlert.type} alert-dismissible fade show`} role="alert">
                                    {profileAlert.message}
                                    <button type="button" className="btn-close" onClick={() => setProfileAlert(null)}></button>
                                </div>
                            )}

                            <form onSubmit={handleProfileSubmit}>
                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label">Full Name</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={name}
                                            onChange={(e) => setName(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Email Address (Read-only)</label>
                                        <input
                                            type="email"
                                            className="form-control bg-light"
                                            value={user?.email || ''}
                                            disabled
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Roll / PRN Number</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={rollNumber}
                                            onChange={(e) => setRollNumber(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Phone Number</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={phone}
                                            onChange={(e) => setPhone(e.target.value)}
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Department / Branch</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={department}
                                            onChange={(e) => setDepartment(e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label">Semester / Year</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={semester}
                                            onChange={(e) => setSemester(e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="mt-4 text-end">
                                    <button type="submit" className="btn btn-primary" disabled={profileLoading}>
                                        {profileLoading ? 'Saving...' : 'Save Profile Changes'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {/* Right Column: Password Change */}
                    <div className="col-lg-5">
                        <div className="content-card shadow-sm">
                            <h5 className="fw-bold mb-3 border-bottom pb-2">
                                <i className="bi bi-shield-lock-fill text-primary me-2"></i> Change Password
                            </h5>

                            {passwordAlert && (
                                <div className={`alert alert-${passwordAlert.type} alert-dismissible fade show`} role="alert">
                                    {passwordAlert.message}
                                    <button type="button" className="btn-close" onClick={() => setPasswordAlert(null)}></button>
                                </div>
                            )}

                            <form onSubmit={handlePasswordSubmit}>
                                <div className="mb-3">
                                    <label className="form-label">Current Password</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        value={currentPassword}
                                        onChange={(e) => setCurrentPassword(e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="mb-3">
                                    <label className="form-label">New Password</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        value={newPassword}
                                        onChange={(e) => setNewPassword(e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="mb-4">
                                    <label className="form-label">Confirm New Password</label>
                                    <input
                                        type="password"
                                        className="form-control"
                                        value={confirmPassword}
                                        onChange={(e) => setConfirmPassword(e.target.value)}
                                        required
                                    />
                                </div>

                                <button type="submit" className="btn btn-outline-primary w-100" disabled={passwordLoading}>
                                    {passwordLoading ? 'Updating Password...' : 'Update Password'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
