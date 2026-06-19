import { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import api, { getCsrfCookie } from '../lib/axios';

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  two_factor_enabled: boolean;
  created_at: string;
  updated_at: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string, rememberMe?: boolean) => Promise<{ twoFactor?: boolean }>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  logout: () => Promise<void>;
  forgotPassword: (email: string) => Promise<string>;
  resetPassword: (token: string, email: string, password: string, passwordConfirmation: string) => Promise<string>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('larable_token'));
  const [isLoading, setIsLoading] = useState(true);

  const isAuthenticated = !!user && !!token;

  // ─── Refresh user from API ────────────────────────────────────
  const refreshUser = useCallback(async () => {
    if (!token) {
      setIsLoading(false);
      return;
    }
    try {
      const response = await api.get('/user');
      setUser(response.data.user);
    } catch {
      setToken(null);
      setUser(null);
      localStorage.removeItem('larable_token');
      localStorage.removeItem('larable_user');
    } finally {
      setIsLoading(false);
    }
  }, [token]);

  useEffect(() => {
    refreshUser();
  }, [refreshUser]);

  // ─── Login ────────────────────────────────────────────────────
  const login = async (email: string, password: string, rememberMe = false) => {
    await getCsrfCookie();
    const response = await api.post('/auth/login', {
      email,
      password,
      remember_me: rememberMe,
    });

    if (response.data.two_factor) {
      return { twoFactor: true };
    }

    const { user: userData, token: newToken } = response.data;
    setUser(userData);
    setToken(newToken);
    localStorage.setItem('larable_token', newToken);
    localStorage.setItem('larable_user', JSON.stringify(userData));

    return {};
  };

  // ─── Register ─────────────────────────────────────────────────
  const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
    await getCsrfCookie();
    const response = await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });

    const { user: userData, token: newToken } = response.data;
    setUser(userData);
    setToken(newToken);
    localStorage.setItem('larable_token', newToken);
    localStorage.setItem('larable_user', JSON.stringify(userData));
  };

  // ─── Logout ───────────────────────────────────────────────────
  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } finally {
      setUser(null);
      setToken(null);
      localStorage.removeItem('larable_token');
      localStorage.removeItem('larable_user');
    }
  };

  // ─── Forgot Password ─────────────────────────────────────────
  const forgotPassword = async (email: string) => {
    await getCsrfCookie();
    const response = await api.post('/auth/forgot-password', { email });
    return response.data.message;
  };

  // ─── Reset Password ──────────────────────────────────────────
  const resetPassword = async (
    resetToken: string,
    email: string,
    password: string,
    passwordConfirmation: string,
  ) => {
    await getCsrfCookie();
    const response = await api.post('/auth/reset-password', {
      token: resetToken,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
    return response.data.message;
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isAuthenticated,
        isLoading,
        login,
        register,
        logout,
        forgotPassword,
        resetPassword,
        refreshUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
