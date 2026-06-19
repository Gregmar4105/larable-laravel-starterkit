import { useAuth } from '../contexts/AuthContext';
import { LayoutDashboard, User, Shield, Key, Activity } from 'lucide-react';

/**
 * Dynamically resolve the backend API URL.
 */
const getBackendUrl = () => {
  if (typeof window !== 'undefined') {
    const { hostname, protocol } = window.location;
    if (hostname.endsWith('.test') && hostname.includes('-frontend')) {
      const backendHost = hostname.replace('-frontend', '');
      return `${protocol}//${backendHost}:8000`;
    }
  }
  return 'http://localhost:8000';
};

/**
 * Dashboard Page
 *
 * Authenticated user's main dashboard with account overview.
 */
export default function Dashboard() {
  const { user } = useAuth();

  const stats = [
    { icon: <User size={20} />, label: 'Account', value: user?.name || '—' },
    { icon: <Shield size={20} />, label: '2FA Status', value: user?.two_factor_enabled ? 'Enabled' : 'Disabled' },
    { icon: <Key size={20} />, label: 'Member Since', value: user?.created_at ? new Date(user.created_at).toLocaleDateString() : '—' },
    { icon: <Activity size={20} />, label: 'API Version', value: 'v1' },
  ];

  return (
    <div className="dashboard-page">
      <div className="page-header">
        <LayoutDashboard size={28} />
        <div>
          <h1>Dashboard</h1>
          <p>Welcome back, {user?.name}</p>
        </div>
      </div>

      {/* ─── Stats Grid ──────────────────────────────────────────── */}
      <div className="stats-grid">
        {stats.map((stat, i) => (
          <div key={i} className="stat-card">
            <div className="stat-icon">{stat.icon}</div>
            <div className="stat-info">
              <span className="stat-label">{stat.label}</span>
              <span className="stat-value">{stat.value}</span>
            </div>
          </div>
        ))}
      </div>

      {/* ─── Quick Links ─────────────────────────────────────────── */}
      <div className="dashboard-section">
        <h2>Quick Actions</h2>
        <div className="quick-links">
          <a href={`${getBackendUrl()}/larable`} target="_blank" rel="noopener noreferrer" className="quick-link-card">
            <Activity size={24} />
            <span>API Playground</span>
            <p>Test API endpoints in the backend GUI</p>
          </a>
          <a href="http://localhost:8025" target="_blank" rel="noopener noreferrer" className="quick-link-card">
            <LayoutDashboard size={24} />
            <span>Mailpit</span>
            <p>View sent emails in the test inbox</p>
          </a>
        </div>
      </div>
    </div>
  );
}
