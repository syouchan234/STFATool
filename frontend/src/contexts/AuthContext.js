import React, { createContext, useContext, useState, useEffect } from 'react';
import { API_BASE_URL } from '../apiConfig';

const AuthContext = createContext();

export const useAuth = () => useContext(AuthContext);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/postInfo/auth/me.php`, {
        credentials: 'include',
      });
      const payload = await res.json();
      if (res.ok && payload.success) {
        setUser(payload.data);
      } else {
        setUser(null);
      }
    } catch (err) {
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    const res = await fetch(`${API_BASE_URL}/postInfo/auth/login.php`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    const payload = await res.json();
    if (res.ok && payload.success) {
      setUser(payload.data.user);
      return { success: true };
    } else {
      return { success: false, error: payload.error };
    }
  };

  const logout = async () => {
    await fetch(`${API_BASE_URL}/postInfo/auth/logout.php`, {
      method: 'POST',
      credentials: 'include',
    });
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, checkAuth }}>
      {children}
    </AuthContext.Provider>
  );
};