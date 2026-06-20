# Contributing to Larable

Thank you for considering contributing to Larable! To maintain code quality and consistency, please follow these guidelines.

## 🛠️ Local Development Setup

Refer to the **Quick Start** section in the [README.md](README.md) to set up your environment using Docker and Make:
```bash
# On Windows (PowerShell):
.\larable setup

# On Windows (CMD):
larable setup

# On macOS/Linux:
./larable setup
```

## 📜 Coding Standards

We follow Laravel's coding standards. We use **Laravel Pint** to format code automatically.

### Code Style Checks
Before committing any backend changes, run Pint to ensure code styling compliance:
```bash
# On Windows (PowerShell):
.\larable lint

# On Windows (CMD):
larable lint

# On macOS/Linux:
./larable lint
```
Alternatively, format files locally using:
```bash
./vendor/bin/pint
```

### TypeScript and React Checks
Ensure TypeScript compiles with no errors before submitting frontend changes:
```bash
cd frontend
npm run build
```

## 🧪 Testing

We use **Pest PHP** for testing the backend. All new features and bug fixes must include corresponding tests.

### Running Tests
To run the Pest test suite:
```bash
# On Windows (PowerShell):
.\larable test

# On Windows (CMD):
larable test

# On macOS/Linux:
./larable test
```
Or run Pest directly:
```bash
php artisan test
```

## 🔀 Branching and Git Strategy

- Work on separate feature branches (e.g. `feature/auth-improvements` or `bugfix/issue-123`).
- Rebase on top of the latest `main` branch before submitting a PR.
- Write clear, descriptive commit messages following conventional commits format.

## 🚀 Pull Request Process

1. Create a pull request to the `main` branch.
2. Ensure the GitHub Actions CI pipeline runs and passes completely.
3. Write a summary of changes in the PR description, referencing any related issues.
