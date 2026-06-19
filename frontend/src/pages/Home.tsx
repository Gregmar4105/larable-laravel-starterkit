import { Link } from 'react-router-dom';
import { ArrowRight, Shield, Zap, Database, Code2, Lock, Boxes } from 'lucide-react';

/**
 * Home Page
 *
 * Landing page showcasing the Larable starterkit features.
 */
export default function Home() {
  const features = [
    {
      icon: <Zap size={24} />,
      title: 'Versioned API',
      description: 'Built-in API versioning with idempotency support for reliable mutations.',
    },
    {
      icon: <Shield size={24} />,
      title: 'Fortify + Sanctum',
      description: 'Full authentication: login, register, 2FA, passkeys, and password reset.',
    },
    {
      icon: <Database size={24} />,
      title: 'PostgreSQL',
      description: 'Production-grade PostgreSQL via Docker with one-command setup.',
    },
    {
      icon: <Code2 size={24} />,
      title: 'React + TypeScript',
      description: 'Decoupled frontend with Axios, React Router, and Lucide icons.',
    },
    {
      icon: <Lock size={24} />,
      title: 'Passkeys & 2FA',
      description: 'WebAuthn passkeys and TOTP two-factor for modern authentication.',
    },
    {
      icon: <Boxes size={24} />,
      title: 'Docker Ready',
      description: 'Complete Docker Compose for development and production deployment.',
    },
  ];

  return (
    <div className="home-page">
      {/* ─── Hero Section ──────────────────────────────────────── */}
      <section className="hero">
        <div className="hero-glow" />
        <h1 className="hero-title">
          Build faster with <span className="gradient-text">Larable</span>
        </h1>
        <p className="hero-subtitle">
          A production-ready Laravel starterkit with a decoupled React TypeScript frontend,
          versioned API, and enterprise-grade authentication.
        </p>
        <div className="hero-actions">
          <Link to="/register" className="btn btn-primary">
            Get Started
            <ArrowRight size={18} />
          </Link>
          <Link to="/login" className="btn btn-secondary">
            Sign In
          </Link>
        </div>
      </section>

      {/* ─── Features Grid ─────────────────────────────────────── */}
      <section className="features">
        <h2 className="section-title">Everything you need</h2>
        <div className="features-grid">
          {features.map((feature, i) => (
            <div key={i} className="feature-card">
              <div className="feature-icon">{feature.icon}</div>
              <h3>{feature.title}</h3>
              <p>{feature.description}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
