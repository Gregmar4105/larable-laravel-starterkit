import { useState, useEffect } from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import {
  Home,
  LogIn,
  UserPlus,
  Settings,
  LogOut,
  LayoutDashboard,
  Boxes,
  Sun,
  Moon,
} from 'lucide-react';

/**
 * Layout
 *
 * Application shell with navigation bar and main content area.
 * Adapts navigation based on authentication state.
 */
export default function Layout() {
  const { isAuthenticated, user, logout } = useAuth();
  const navigate = useNavigate();

  const [theme, setTheme] = useState<'light' | 'dark'>(() => {
    return (localStorage.getItem('larable_theme') as 'light' | 'dark') || 'light';
  });

  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('larable_theme', theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === 'light' ? 'dark' : 'light'));
  };

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (

    <div className="app-layout">
      {/* ─── Navigation Bar ──────────────────────────────────────── */}
      <nav className="navbar">
        <div className="navbar-inner">
          <Link to="/" className="navbar-brand">
            <Boxes size={24} />
            <span>Larable</span>
          </Link>

          <div className="navbar-links">
            <button onClick={toggleTheme} className="nav-link theme-toggle-btn" aria-label="Toggle theme" style={{ padding: '0.5rem', marginRight: '0.25rem' }}>
              {theme === 'light' ? <Moon size={18} /> : <Sun size={18} />}
            </button>
            {isAuthenticated ? (
              <>
                <Link to="/dashboard" className="nav-link">
                  <LayoutDashboard size={18} />
                  <span>Dashboard</span>
                </Link>
                <Link to="/settings" className="nav-link">
                  <Settings size={18} />
                  <span>Settings</span>
                </Link>
                <div className="nav-user">
                  <span className="nav-user-name">{user?.name}</span>
                  <button onClick={handleLogout} className="nav-link nav-logout">
                    <LogOut size={18} />
                    <span>Logout</span>
                  </button>
                </div>
              </>
            ) : (
              <>
                <Link to="/" className="nav-link">
                  <Home size={18} />
                  <span>Home</span>
                </Link>
                <Link to="/login" className="nav-link">
                  <LogIn size={18} />
                  <span>Login</span>
                </Link>
                <Link to="/register" className="nav-link btn-primary-nav">
                  <UserPlus size={18} />
                  <span>Register</span>
                </Link>
              </>
            )}
          </div>
        </div>
      </nav>

      {/* ─── Main Content ────────────────────────────────────────── */}
      <main className="main-content">
        <Outlet />
      </main>
    </div>
  );
}
