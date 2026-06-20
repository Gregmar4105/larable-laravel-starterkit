# UI Design System (shadcn UI Style)

This document describes the design system and styling standards used in the Larable frontend, matching the **shadcn UI (Zinc theme)** aesthetic.

## Architecture & Principles

The styling system is implemented using **Vanilla CSS variables** (CSS custom properties) combined with clean utility-like classes. It decouples the design tokens from the framework, allowing both light and dark modes to run without Tailwind CSS or heavy component library runtimes.

- **Minimalist Aesthetic:** Focused on high-contrast colors, subtle borders, and clean spacing.
- **Flat Layouts:** Removed heavy glowing radial gradients, translations, or shadows in favor of subtle border-lines (`#e4e4e7` / `#27272a`) and flat solid backgrounds.
- **Micro-interactions:** Interactive components (cards, links, buttons) transition opacity or border colors quickly (`0.15s ease`) instead of rising/lifting vertically.

---

## Theme Tokens & Variables

All variables are declared in [index.css](file:///c:/Users/PC/Herd/larable-laravel-staterkit/frontend/src/styles/index.css).

### Light Theme (Default Zinc)
```css
:root {
  --bg-primary: #ffffff;
  --bg-secondary: #f4f4f5;         /* zinc-100 */
  --bg-tertiary: #f4f4f5;
  --bg-card: #ffffff;
  --bg-card-hover: #fafafa;         /* zinc-50 */
  --bg-glass: rgba(0, 0, 0, 0.02);
  --bg-glass-border: #e4e4e7;       /* zinc-200 */
  --bg-navbar: rgba(255, 255, 255, 0.8);

  --text-primary: #09090b;          /* zinc-950 */
  --text-secondary: #71717a;        /* zinc-500 */
  --text-muted: #a1a1aa;           /* zinc-400 */

  --accent-primary: #18181b;        /* zinc-900 / primary */
  --accent-primary-hover: #27272a;  /* zinc-800 */
  --accent-secondary: #18181b;
  --accent-gradient: #18181b;
  
  --border: #e4e4e7;               /* zinc-200 */
  --border-focus: #18181b;

  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;                /* shadcn standard (0.5rem) */
  --radius-xl: 12px;

  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
```

### Dark Theme (Zinc Dark)
Active when the `html[data-theme="dark"]` attribute is set:
```css
:root[data-theme="dark"] {
  --bg-primary: #09090b;            /* zinc-950 */
  --bg-secondary: #18181b;          /* zinc-900 */
  --bg-tertiary: #18181b;
  --bg-card: #09090b;
  --bg-card-hover: #18181b;
  --bg-glass: rgba(255, 255, 255, 0.02);
  --bg-glass-border: #27272a;       /* zinc-800 */
  --bg-navbar: rgba(9, 9, 11, 0.8);

  --text-primary: #fafafa;          /* zinc-50 */
  --text-secondary: #a1a1aa;        /* zinc-400 */
  --text-muted: #71717a;           /* zinc-500 */

  --accent-primary: #fafafa;        /* zinc-50 / primary */
  --accent-primary-hover: #e4e4e7;  /* zinc-200 */
  --accent-secondary: #fafafa;
  --accent-gradient: #fafafa;

  --border: #27272a;               /* zinc-800 */
  --border-focus: #fafafa;

  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
}
```

---

## Component Specifications

### 1. Buttons (`.btn`)
- **Primary (`.btn-primary`):** Solid `--accent-primary` background, contrasting text color, and thin focus border.
- **Secondary (`.btn-secondary`):** White/transparent background with a thin gray border (`--border`) and shadow. Focuses or hovers to a gray background.
- **Destructive (`.btn-danger`):** High-contrast red background with white text, fading slightly on hover.

### 2. Form Inputs (`.form-group`)
Inputs feature a transparent background, a thin gray border, and transition to a sharp border focus ring (`box-shadow: 0 0 0 1px var(--border-focus)`) on focus. Standard sizing uses `0.5rem 0.75rem` padding with `0.875rem` (text-sm) font.

### 3. Layout Cards
Containers (`.feature-card`, `.auth-card`, `.stat-card`, `.quick-link-card`, `.settings-card`) use:
- Solid background (`--bg-card`).
- Border (`1px solid var(--border)`).
- Standard border-radius (`--radius-lg` / `8px`).
- Hover shifts background to `--bg-card-hover` and border to `--border-focus`.

### 4. Tabs
`.settings-tabs` uses a bottom-border navigation layout where active tab `.tab.active` underlines the header with `--border-focus` indicating the active screen context.
