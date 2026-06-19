import { useState, FormEvent } from 'react';
import { useAuth } from '../contexts/AuthContext';
import api from '../lib/axios';
import { Settings as SettingsIcon, User, Lock, Shield, AlertCircle, CheckCircle2 } from 'lucide-react';

/**
 * Settings Page
 *
 * User settings: profile info, password change, 2FA management.
 */
export default function Settings() {
  const { user, refreshUser } = useAuth();
  const [activeTab, setActiveTab] = useState<'profile' | 'password' | 'security'>('profile');

  return (
    <div className="settings-page">
      <div className="page-header">
        <SettingsIcon size={28} />
        <div>
          <h1>Settings</h1>
          <p>Manage your account preferences</p>
        </div>
      </div>

      {/* ─── Tab Navigation ──────────────────────────────────────── */}
      <div className="settings-tabs">
        <button
          className={`tab ${activeTab === 'profile' ? 'active' : ''}`}
          onClick={() => setActiveTab('profile')}
        >
          <User size={18} />
          Profile
        </button>
        <button
          className={`tab ${activeTab === 'password' ? 'active' : ''}`}
          onClick={() => setActiveTab('password')}
        >
          <Lock size={18} />
          Password
        </button>
        <button
          className={`tab ${activeTab === 'security' ? 'active' : ''}`}
          onClick={() => setActiveTab('security')}
        >
          <Shield size={18} />
          Security
        </button>
      </div>

      {/* ─── Tab Content ─────────────────────────────────────────── */}
      <div className="settings-content">
        {activeTab === 'profile' && <ProfileTab user={user} onUpdate={refreshUser} />}
        {activeTab === 'password' && <PasswordTab />}
        {activeTab === 'security' && <SecurityTab user={user} onUpdate={refreshUser} />}
      </div>
    </div>
  );
}

// ─── Profile Tab ──────────────────────────────────────────────────────
function ProfileTab({ user, onUpdate }: { user: ReturnType<typeof useAuth>['user']; onUpdate: () => Promise<void> }) {
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setMessage('');
    setError('');
    setIsSubmitting(true);

    try {
      const response = await api.put('/user/profile', { name, email });
      setMessage(response.data.message);
      await onUpdate();
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } };
      setError(axiosError.response?.data?.message || 'Failed to update profile.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="settings-card">
      <h2>Profile Information</h2>
      <p className="settings-description">Update your name and email address.</p>

      {message && <div className="alert alert-success"><CheckCircle2 size={18} /><span>{message}</span></div>}
      {error && <div className="alert alert-error"><AlertCircle size={18} /><span>{error}</span></div>}

      <form onSubmit={handleSubmit} className="settings-form">
        <div className="form-group">
          <label htmlFor="settings-name">Name</label>
          <input id="settings-name" type="text" value={name} onChange={(e) => setName(e.target.value)} required />
        </div>
        <div className="form-group">
          <label htmlFor="settings-email">Email</label>
          <input id="settings-email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
        </div>
        <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
          {isSubmitting ? 'Saving...' : 'Save Changes'}
        </button>
      </form>
    </div>
  );
}

// ─── Password Tab ─────────────────────────────────────────────────────
function PasswordTab() {
  const [currentPassword, setCurrentPassword] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setMessage('');
    setError('');
    setIsSubmitting(true);

    try {
      const response = await api.put('/user/password', {
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
      });
      setMessage(response.data.message);
      setCurrentPassword('');
      setPassword('');
      setPasswordConfirmation('');
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } };
      setError(axiosError.response?.data?.message || 'Failed to update password.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="settings-card">
      <h2>Change Password</h2>
      <p className="settings-description">Update your password to keep your account secure.</p>

      {message && <div className="alert alert-success"><CheckCircle2 size={18} /><span>{message}</span></div>}
      {error && <div className="alert alert-error"><AlertCircle size={18} /><span>{error}</span></div>}

      <form onSubmit={handleSubmit} className="settings-form">
        <div className="form-group">
          <label htmlFor="current-password">Current Password</label>
          <input id="current-password" type="password" value={currentPassword} onChange={(e) => setCurrentPassword(e.target.value)} required autoComplete="current-password" />
        </div>
        <div className="form-group">
          <label htmlFor="new-password">New Password</label>
          <input id="new-password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} autoComplete="new-password" />
        </div>
        <div className="form-group">
          <label htmlFor="confirm-new-password">Confirm New Password</label>
          <input id="confirm-new-password" type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} required autoComplete="new-password" />
        </div>
        <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
          {isSubmitting ? 'Updating...' : 'Update Password'}
        </button>
      </form>
    </div>
  );
}

// ─── Security Tab ─────────────────────────────────────────────────────
function SecurityTab({ user, onUpdate }: { user: ReturnType<typeof useAuth>['user']; onUpdate: () => Promise<void> }) {
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [qrCode, setQrCode] = useState('');
  const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);
  const [confirmCode, setConfirmCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const enable2FA = async () => {
    setIsLoading(true);
    setError('');
    try {
      const response = await api.post('/auth/two-factor/enable');
      setQrCode(response.data.qr_code);
      setRecoveryCodes(response.data.recovery_codes);
      setMessage('Scan the QR code with your authenticator app, then enter the code to confirm.');
    } catch {
      setError('Failed to enable 2FA.');
    } finally {
      setIsLoading(false);
    }
  };

  const confirm2FA = async () => {
    setIsLoading(true);
    setError('');
    try {
      await api.post('/auth/two-factor/confirm', { code: confirmCode });
      setMessage('Two-factor authentication is now active!');
      setQrCode('');
      setConfirmCode('');
      await onUpdate();
    } catch {
      setError('Invalid code. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const disable2FA = async () => {
    setIsLoading(true);
    setError('');
    try {
      await api.delete('/auth/two-factor/disable');
      setMessage('Two-factor authentication disabled.');
      setRecoveryCodes([]);
      await onUpdate();
    } catch {
      setError('Failed to disable 2FA.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="settings-card">
      <h2>Two-Factor Authentication</h2>
      <p className="settings-description">
        Add an extra layer of security with TOTP-based two-factor authentication.
      </p>

      {message && <div className="alert alert-success"><CheckCircle2 size={18} /><span>{message}</span></div>}
      {error && <div className="alert alert-error"><AlertCircle size={18} /><span>{error}</span></div>}

      {user?.two_factor_enabled ? (
        <div className="security-status">
          <div className="status-badge status-enabled">
            <Shield size={18} />
            2FA is enabled
          </div>
          <button onClick={disable2FA} className="btn btn-danger" disabled={isLoading}>
            {isLoading ? 'Disabling...' : 'Disable 2FA'}
          </button>
        </div>
      ) : (
        <div className="security-setup">
          {!qrCode ? (
            <button onClick={enable2FA} className="btn btn-primary" disabled={isLoading}>
              {isLoading ? 'Setting up...' : 'Enable 2FA'}
            </button>
          ) : (
            <div className="twofa-setup">
              <div className="qr-code" dangerouslySetInnerHTML={{ __html: qrCode }} />
              <div className="form-group">
                <label htmlFor="confirm-2fa-code">Enter code from authenticator</label>
                <input
                  id="confirm-2fa-code"
                  type="text"
                  value={confirmCode}
                  onChange={(e) => setConfirmCode(e.target.value)}
                  placeholder="000000"
                  maxLength={6}
                />
              </div>
              <button onClick={confirm2FA} className="btn btn-primary" disabled={isLoading}>
                {isLoading ? 'Confirming...' : 'Confirm'}
              </button>
            </div>
          )}
        </div>
      )}

      {recoveryCodes.length > 0 && (
        <div className="recovery-codes">
          <h3>Recovery Codes</h3>
          <p>Save these codes in a safe place. Each can only be used once.</p>
          <div className="codes-grid">
            {recoveryCodes.map((code, i) => (
              <code key={i}>{code}</code>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
