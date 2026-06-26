import { createContext, useContext, useState, useEffect } from 'react';
import { authApi } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem('user');
    return stored ? JSON.parse(stored) : null;
  });
  const [loading, setLoading] = useState(false);

  const login = async (identifier, password) => {
    setLoading(true);
    try {
      const { data } = await authApi.login(identifier, password);
      const { user: u, token } = data.data;
      if (u.role !== 'admin') throw new Error('Admin access required');
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(u));
      setUser(u);
      return u;
    } finally {
      setLoading(false);
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
  };

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, login, logout, loading, isAuthenticated: !!user }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
