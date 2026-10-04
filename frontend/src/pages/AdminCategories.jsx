import React, { useState, useEffect } from 'react';
import { apiGetCategories, apiCreateCategory, apiUpdateCategory, apiDeleteCategory } from '../api';

export default function AdminCategories() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [alert, setAlert] = useState(null);

    // Modal state
    const [modalOpen, setModalOpen] = useState(false);
    const [editingCategory, setEditingCategory] = useState(null);
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [processing, setProcessing] = useState(false);

    const loadCategories = () => {
        setLoading(true);
        apiGetCategories()
            .then((res) => {
                if (res.success) setCategories(res.categories || []);
            })
            .catch((err) => console.error("Error loading categories:", err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadCategories();
    }, []);

    const openCreateModal = () => {
        setEditingCategory(null);
        setName('');
        setDescription('');
        setModalOpen(true);
    };

    const openEditModal = (cat) => {
        setEditingCategory(cat);
        setName(cat.name);
        setDescription(cat.description || '');
        setModalOpen(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        if (!name.trim()) return;

        setProcessing(true);
        try {
            if (editingCategory) {
                const res = await apiUpdateCategory(editingCategory.id, { name: name.trim(), description: description.trim() });
                if (res.success) {
                    setAlert({ type: 'success', message: 'Category updated successfully!' });
                    setModalOpen(false);
                    loadCategories();
                } else {
                    setAlert({ type: 'danger', message: res.message || 'Error updating category.' });
                }
            } else {
                const res = await apiCreateCategory({ name: name.trim(), description: description.trim() });
                if (res.success) {
                    setAlert({ type: 'success', message: 'Category added successfully!' });
                    setModalOpen(false);
                    loadCategories();
                } else {
                    setAlert({ type: 'danger', message: res.message || 'Error creating category.' });
                }
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Network error saving category.' });
        } finally {
            setProcessing(false);
        }
    };

    const handleDelete = async (id, catName) => {
        if (!window.confirm(`Are you sure you want to delete category "${catName}"?`)) return;

        try {
            const res = await apiDeleteCategory(id);
            if (res.success) {
                setAlert({ type: 'success', message: 'Category deleted successfully.' });
                loadCategories();
            } else {
                setAlert({ type: 'danger', message: res.message || 'Error deleting category.' });
            }
        } catch (err) {
            setAlert({ type: 'danger', message: 'Network error deleting category.' });
        }
    };

    return (
        <div className="py-4" style={{ backgroundColor: '#f8fafc', minHeight: '85vh' }}>
            <div className="container">
                <div className="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h2 className="fw-bold mb-1">Academic Categories & Domains</h2>
                        <p className="text-muted mb-0">Organize student projects into relevant technical domains and specializations.</p>
                    </div>
                    <button type="button" className="btn btn-primary" onClick={openCreateModal}>
                        <i className="bi bi-plus-lg me-1"></i> Add New Category
                    </button>
                </div>

                {alert && (
                    <div className={`alert alert-${alert.type} alert-dismissible fade show`} role="alert">
                        {alert.message}
                        <button type="button" className="btn-close" onClick={() => setAlert(null)}></button>
                    </div>
                )}

                <div className="content-card shadow-sm p-0 overflow-hidden">
                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-primary" role="status"></div>
                            <p className="text-muted mt-2">Loading categories...</p>
                        </div>
                    ) : (
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="table-light">
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th className="text-center">Projects</th>
                                        <th className="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {categories.map((c) => (
                                        <tr key={c.id}>
                                            <td>
                                                <strong className="text-dark d-block">{c.name}</strong>
                                                <small className="text-muted">Slug: {c.slug}</small>
                                            </td>
                                            <td style={{ maxWidth: '400px' }}>
                                                <p className="text-muted small mb-0">{c.description || 'No description provided.'}</p>
                                            </td>
                                            <td className="text-center">
                                                <span className="badge bg-primary-subtle text-primary fs-6">
                                                    {c.project_count || 0}
                                                </span>
                                            </td>
                                            <td className="text-end">
                                                <button
                                                    type="button"
                                                    className="btn btn-sm btn-outline-secondary me-2"
                                                    onClick={() => openEditModal(c)}
                                                >
                                                    <i className="bi bi-pencil me-1"></i> Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    className="btn btn-sm btn-outline-danger"
                                                    onClick={() => handleDelete(c.id, c.name)}
                                                >
                                                    <i className="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Modal */}
                {modalOpen && (
                    <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                        <div className="modal-dialog modal-dialog-centered">
                            <div className="modal-content">
                                <form onSubmit={handleSave}>
                                    <div className="modal-header">
                                        <h5 className="modal-title fw-bold">
                                            {editingCategory ? 'Edit Domain Category' : 'Create New Domain Category'}
                                        </h5>
                                        <button type="button" className="btn-close" onClick={() => setModalOpen(false)}></button>
                                    </div>
                                    <div className="modal-body">
                                        <div className="mb-3">
                                            <label className="form-label">Category Name</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                placeholder="e.g. Blockchain & Cybersecurity"
                                                value={name}
                                                onChange={(e) => setName(e.target.value)}
                                                required
                                            />
                                        </div>
                                        <div className="mb-3">
                                            <label className="form-label">Description (Optional)</label>
                                            <textarea
                                                className="form-control"
                                                rows="3"
                                                placeholder="Brief overview of what projects belong to this domain..."
                                                value={description}
                                                onChange={(e) => setDescription(e.target.value)}
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-light border" onClick={() => setModalOpen(false)}>
                                            Cancel
                                        </button>
                                        <button type="submit" className="btn btn-primary" disabled={processing}>
                                            {processing ? 'Saving...' : 'Save Category'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
