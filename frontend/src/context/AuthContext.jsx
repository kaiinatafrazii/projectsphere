import React, { createContext, useContext, useState, useEffect } from 'react';
import { apiGetMe, apiLogin, apiLogout, apiRegister } from '../api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const refreshUser = async () => {
        try {
            const res = await apiGetMe();
            if (res.authenticated && res.user) {
                setUser(res.user);
            } else {
                setUser(null);
                localStorage.removeItem('ps_token');
            }
        } catch (err) {
            setUser(null);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        refreshUser();
    }, []);

    const login = async (credentials) => {
        const res = await apiLogin(credentials);
        if (res.success && res.user) {
            if (res.token) localStorage.setItem('ps_token', res.token);
            setUser(res.user);
        }
        return res;
    };

    const register = async (userData) => {
        const res = await apiRegister(userData);
        if (res.success && res.user) {
            if (res.token) localStorage.setItem('ps_token', res.token);
            setUser(res.user);
        }
        return res;
    };

    const logout = async () => {
        await apiLogout();
        localStorage.removeItem('ps_token');
        setUser(null);
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout, refreshUser }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
