/**
 * ─── Larable Frontend Routes ─────────────────────────────────────────
 *
 * This file mirrors Laravel's routes/api.php pattern.
 * It serves as the single source of truth for all frontend routes.
 *
 * Structure:
 *   - Public routes: accessible without authentication
 *   - Protected routes: require authentication (wrapped in ProtectedRoute)
 *
 * Usage: Import `routes` in App.tsx and pass to useRoutes()
 */

import { RouteObject } from 'react-router-dom';
import Layout from './src/components/Layout';
import ProtectedRoute from './src/components/ProtectedRoute';
import Home from './src/pages/Home';
import Login from './src/pages/Login';
import Register from './src/pages/Register';
import ForgotPassword from './src/pages/ForgotPassword';
import ResetPassword from './src/pages/ResetPassword';
import Dashboard from './src/pages/Dashboard';
import Settings from './src/pages/Settings';

export const routes: RouteObject[] = [
  {
    path: '/',
    element: <Layout />,
    children: [
      // ─── Public Routes ──────────────────────────────────────────
      { index: true, element: <Home /> },
      { path: 'login', element: <Login /> },
      { path: 'register', element: <Register /> },
      { path: 'forgot-password', element: <ForgotPassword /> },
      { path: 'reset-password/:token', element: <ResetPassword /> },

      // ─── Protected Routes ───────────────────────────────────────
      {
        element: <ProtectedRoute />,
        children: [
          { path: 'dashboard', element: <Dashboard /> },
          { path: 'settings', element: <Settings /> },
        ],
      },
    ],
  },
];
