import React, { useState, useEffect } from 'react';
import { apiGetStudents } from '../api';

export default function AdminStudents() {
    const [students, setStudents] = useState([]);
    const [search, setSearch] = useState('');
    const [loading, setLoading] = useState(true);

    const loadStudents = (query = '') => {
        setLoading(true);
        apiGetStudents(query)
            .then((res) => {
                if (res.success) setStudents(res.students || []);
            })
            .catch((err) => console.error("Error loading students:", err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadStudents(search);
    }, [search]);

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 className="fw-bold mb-1">Enrolled Student Directory</h2>
                        <p className="text-muted mb-0">List of students registered on ProjectSphere with their branch and submission count.</p>
                    </div>
                    <div style={{ minWidth: '280px' }}>
                        <div className="input-group">
                            <span className="input-group-text bg-white border-end-0 text-muted">
                                <i className="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                className="form-control border-start-0 ps-0"
                                placeholder="Search by name, roll no, email..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                </div>

                <div className="content-card shadow-sm p-0 overflow-hidden">
                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-primary" role="status"></div>
                            <p className="text-muted mt-2">Loading students...</p>
                        </div>
                    ) : students.length === 0 ? (
                        <div className="text-center py-5">
                            <i className="bi bi-people fs-1 text-muted"></i>
                            <h5 className="mt-3">No Students Found</h5>
                            <p className="text-muted">No student accounts matched your search.</p>
                        </div>
                    ) : (
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th>Student Details</th>
                                        <th>Roll / PRN</th>
                                        <th>Department / Branch</th>
                                        <th>Semester</th>
                                        <th className="text-center">Projects</th>
                                        <th>Registered Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {students.map((s) => (
                                        <tr key={s.id}>
                                            <td>
                                                <div className="d-flex align-items-center gap-2">
                                                    <div className="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style={{ width: 36, height: 36 }}>
                                                        {s.name ? s.name.charAt(0).toUpperCase() : 'S'}
                                                    </div>
                                                    <div>
                                                        <strong className="text-dark d-block">{s.name}</strong>
                                                        <small className="text-muted">{s.email}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span className="badge bg-light text-dark border">
                                                    {s.roll_number || 'N/A'}
                                                </span>
                                            </td>
                                            <td>{s.department || 'Computer Engineering'}</td>
                                            <td>{s.semester || '6th Semester'}</td>
                                            <td className="text-center">
                                                <span className="badge bg-primary text-white">
                                                    {s.project_count || 0}
                                                </span>
                                            </td>
                                            <td>
                                                <small className="text-muted">
                                                    {new Date(s.created_at || Date.now()).toLocaleDateString()}
                                                </small>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
