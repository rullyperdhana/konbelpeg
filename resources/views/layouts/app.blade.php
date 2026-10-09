<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Realisasi Belanja Pegawai')</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Tom Select (For Searchable Dropdowns) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <!-- SweetAlert2 (Global Notification Dialogs) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            /* ===== LUNO ADMIN PALETTE (LIGHT MODE) ===== */
            --luno-primary: #4C35DE;
            --luno-primary-hover: #3b25cb;
            --luno-primary-light: rgba(76, 53, 222, 0.08);
            --luno-primary-border: rgba(76, 53, 222, 0.2);
            --luno-primary-text: #4C35DE;
            
            --bg-canvas: #f4f6fa;
            --bg-surface: #ffffff;
            --bg-surface-subtle: #f8fafc;
            --bg-surface-hover: #f1f5f9;
            --border-color: #e9edf4;
            --border-subtle: #f1f4f9;
            
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;
            
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --success-text: #059669;

            --warning: #f59e0b;
            --warning-light: rgba(245, 158, 11, 0.1);
            --warning-text: #d97706;

            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.1);
            --danger-text: #dc2626;

            --info: #06b6d4;
            --info-light: rgba(6, 182, 212, 0.1);
            --info-text: #0891b2;

            /* Sidebar (Light Mode - Clean Soft Palette) */
            --sidebar-bg: #ffffff;
            --sidebar-border: #e9edf4;
            --sidebar-brand-title: #1e293b;
            --sidebar-brand-sub: #64748b;
            --sidebar-text: #526177;
            --sidebar-text-hover: #1e293b;
            --sidebar-hover: rgba(76, 53, 222, 0.06);
            --sidebar-hover-icon: #4C35DE;
            --sidebar-active: #4C35DE;
            --sidebar-active-text: #ffffff;
            --sidebar-label: #94a3b8;
            --submenu-text: #64748b;
            --submenu-hover: rgba(76, 53, 222, 0.05);
            --submenu-active-bg: rgba(76, 53, 222, 0.09);
            --submenu-active-text: #4C35DE;
            
            --topbar-bg: rgba(255, 255, 255, 0.92);
            --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04), 0 0.25rem 0.5rem -0.25rem rgba(0, 0, 0, 0.02);
            --card-shadow-hover: 0 0.5rem 1rem rgba(0, 0, 0, 0.06);

            /* Compatibility */
            --primary: var(--luno-primary);
            --primary-hover: var(--luno-primary-hover);
            --primary-subtle: var(--luno-primary-light);
            --primary-bg: var(--bg-canvas);
            --card-bg: var(--bg-surface);
            --title-color: var(--text-main);
        }

        :root.dark-mode {
            /* ===== LUNO ADMIN PALETTE (DARK MODE) ===== */
            --luno-primary: #5c47ea;
            --luno-primary-hover: #715df2;
            --luno-primary-light: rgba(92, 71, 234, 0.18);
            --luno-primary-border: rgba(92, 71, 234, 0.35);
            --luno-primary-text: #a594fd;

            --bg-canvas: #0b111e;
            --bg-surface: #151d30;
            --bg-surface-subtle: #1a243a;
            --bg-surface-hover: #1f2b45;
            --border-color: #212d46;
            --border-subtle: #1a243a;
            
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.18);
            --success-text: #34d399;

            --warning: #f59e0b;
            --warning-light: rgba(245, 158, 11, 0.18);
            --warning-text: #fbbf24;

            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.18);
            --danger-text: #f87171;

            /* Sidebar (Dark Mode) */
            --sidebar-bg: #111726;
            --sidebar-border: #1e2a42;
            --sidebar-brand-title: #f1f5f9;
            --sidebar-brand-sub: #8c9ba5;
            --sidebar-text: #8c9ba5;
            --sidebar-text-hover: #ffffff;
            --sidebar-hover: rgba(255, 255, 255, 0.04);
            --sidebar-hover-icon: #a594fd;
            --sidebar-active: #5c47ea;
            --sidebar-active-text: #ffffff;
            --sidebar-label: #64748b;
            --submenu-text: #8c9ba5;
            --submenu-hover: rgba(255, 255, 255, 0.04);
            --submenu-active-bg: rgba(92, 71, 234, 0.2);
            --submenu-active-text: #a594fd;

            --topbar-bg: rgba(21, 29, 48, 0.92);
            --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.3);
            --card-shadow-hover: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.4);

            --primary: var(--luno-primary);
            --primary-hover: var(--luno-primary-hover);
            --primary-subtle: var(--luno-primary-light);
            --primary-bg: var(--bg-canvas);
            --card-bg: var(--bg-surface);
            --title-color: var(--text-main);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            font-size: 13.5px;
            line-height: 1.5;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease, border-color 0.2s ease;
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            height: 100vh;
            z-index: 50;
            left: 0;
            top: 0;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--sidebar-border);
            transition: border-color 0.2s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .luno-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4C35DE 0%, #7c3aed 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(76, 53, 222, 0.3);
            flex-shrink: 0;
        }

        .sidebar-brand-text h2 {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
            color: var(--sidebar-brand-title);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .sidebar-brand-text h2 span.brand-badge {
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(76, 53, 222, 0.12);
            color: var(--luno-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-brand-text p {
            font-size: 11px;
            color: var(--sidebar-brand-sub);
            font-weight: 500;
            margin: 0;
            transition: color 0.2s ease;
        }

        .sidebar-menu {
            padding: 14px 10px;
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(0,0,0,0.08) transparent;
        }

        .menu-label {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--sidebar-label);
            font-weight: 700;
            letter-spacing: 0.8px;
            margin: 18px 0 6px 12px;
            transition: color 0.2s ease;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 2px;
            transition: all 0.15s ease;
            font-weight: 500;
            font-size: 13px;
            gap: 10px;
        }

        .menu-item i {
            font-size: 18px;
            color: var(--sidebar-label);
            transition: color 0.15s ease;
        }

        .menu-item:hover {
            background: var(--sidebar-hover);
            color: var(--sidebar-text-hover);
        }

        .menu-item:hover i {
            color: var(--sidebar-hover-icon);
        }

        .menu-item.active {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(76, 53, 222, 0.25);
        }

        .menu-item.active i {
            color: var(--sidebar-active-text);
        }

        .submenu {
            padding-left: 16px;
            margin-top: 2px;
            margin-bottom: 4px;
            display: none;
        }
        
        .has-submenu.open + .submenu {
            display: block;
        }
        
        .submenu-item {
            display: flex;
            align-items: center;
            padding: 7px 12px;
            color: var(--submenu-text);
            text-decoration: none;
            font-size: 12.5px;
            border-radius: 6px;
            margin-bottom: 1px;
            transition: all 0.15s ease;
            gap: 8px;
        }

        .submenu-item:hover {
            color: var(--sidebar-text-hover);
            background: var(--submenu-hover);
        }

        .submenu-item.active, .submenu-item.text-white {
            color: var(--submenu-active-text) !important;
            background: var(--submenu-active-bg) !important;
            font-weight: 600;
        }

        .ph-caret-down {
            transition: transform 0.2s ease;
            color: var(--sidebar-label);
        }

        /* ===== SIDEBAR FOOTER (APP VERSION) ===== */
        .sidebar-footer {
            padding: 12px 14px 14px 14px;
            border-top: 1px solid var(--sidebar-border);
            background: var(--bg-surface-subtle);
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .version-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 8px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .version-card:hover {
            border-color: var(--luno-primary);
            box-shadow: 0 3px 10px rgba(76, 53, 222, 0.12);
            transform: translateY(-1px);
        }

        .version-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .version-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--sidebar-label);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .version-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .version-number {
            font-size: 12px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .version-codename {
            font-size: 10px;
            font-weight: 500;
            color: var(--text-muted);
            background: var(--bg-surface-subtle);
            padding: 1px 5px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .version-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 12px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .sidebar-copyright {
            font-size: 10.5px;
            color: var(--sidebar-label);
            text-align: center;
            margin-top: 1px;
        }

        /* ===== TIMELINE CHANGELOG ===== */
        .timeline-version {
            position: relative;
            padding-left: 18px;
            margin-top: 10px;
        }

        .timeline-version::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: var(--border-color);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 18px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-badge {
            position: relative;
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            background: var(--bg-surface-hover);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            margin-bottom: 6px;
        }

        .timeline-badge.current {
            background: var(--luno-primary);
            color: #ffffff;
            border-color: var(--luno-primary);
            box-shadow: 0 2px 6px rgba(76, 53, 222, 0.25);
        }

        .timeline-content {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 14px;
        }

        /* ===== MAIN LAYOUT ===== */
        .main-wrapper {
            flex-grow: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .main-wrapper.expanded {
            margin-left: 0;
        }

        /* ===== LUNO TOPBAR ===== */
        .topbar {
            height: 64px;
            background: var(--topbar-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 40;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* Luno Breadcrumb */
        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .topbar-breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s ease;
        }

        .topbar-breadcrumb a:hover {
            color: var(--luno-primary);
        }

        .topbar-breadcrumb .separator {
            color: var(--text-subtle);
            font-size: 11px;
        }

        .topbar-breadcrumb .active-crumb {
            color: var(--text-main);
            font-weight: 600;
        }

        .icon-btn {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            font-size: 18px;
            color: var(--text-muted);
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .icon-btn:hover {
            background: var(--luno-primary-light);
            color: var(--luno-primary);
            border-color: var(--luno-primary-border);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Luno Search Input Pill */
        .topbar-search {
            display: flex;
            align-items: center;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 999px;
            padding: 4px 14px;
            gap: 8px;
            width: 220px;
            transition: all 0.15s ease;
        }

        .topbar-search:focus-within {
            width: 260px;
            border-color: var(--luno-primary);
            box-shadow: 0 0 0 3px var(--luno-primary-light);
        }

        .topbar-search i {
            color: var(--text-subtle);
            font-size: 15px;
        }

        .topbar-search input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 12.5px;
            color: var(--text-main);
            width: 100%;
        }

        .theme-toggle-btn {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .theme-toggle-btn:hover {
            color: var(--luno-primary);
            border-color: var(--luno-primary-border);
            background: var(--luno-primary-light);
        }

        .user-dropdown-container {
            position: relative;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 4px 10px 4px 4px;
            border-radius: 999px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
        }

        .user-profile:hover {
            border-color: var(--luno-primary-border);
            box-shadow: 0 2px 8px rgba(76, 53, 222, 0.08);
        }

        .user-profile-info {
            text-align: right;
            line-height: 1.2;
            padding-right: 4px;
        }

        .user-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }

        .user-role {
            font-size: 10.5px;
            color: var(--text-muted);
        }

        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 220px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            padding: 6px;
            z-index: 100;
        }

        .user-dropdown-menu.show {
            display: flex;
        }

        .user-dropdown-header {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 4px;
        }

        .user-dropdown-header .dropdown-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-dropdown-header .dropdown-email {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dropdown-item-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 8px 10px;
            border: none;
            background: none;
            border-radius: 8px;
            font-size: 13px;
            color: var(--danger);
            cursor: pointer;
            text-align: left;
            transition: background-color 0.15s;
        }

        .dropdown-item-btn:hover {
            background-color: var(--danger-light);
        }

        /* ===== CONTENT AREA ===== */
        .content-area {
            padding: 24px;
            flex-grow: 1;
            max-width: 1920px;
            margin: 0 auto;
            width: 100%;
        }

        /* ===== LUNO PAGE HEADER ===== */
        .app-header, .aas-header {
            background: var(--bg-surface) !important;
            color: var(--text-main) !important;
            padding: 18px 22px !important;
            border-radius: 14px !important;
            margin-bottom: 22px !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: var(--card-shadow) !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 16px;
            flex-wrap: wrap;
        }

        .app-header h2, .aas-header h2 {
            font-size: 17px !important;
            font-weight: 700 !important;
            letter-spacing: -0.015em;
            color: var(--text-main) !important;
            margin-bottom: 3px !important;
        }

        .app-header p, .aas-header p {
            color: var(--text-muted) !important;
            font-size: 12.5px !important;
            margin: 0 !important;
        }

        .page-header {
            margin-bottom: 22px;
        }

        .page-title {
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }

        /* ===== LUNO CARDS ===== */
        .card {
            background: var(--bg-surface) !important;
            border-radius: 14px !important;
            padding: 22px;
            box-shadow: var(--card-shadow) !important;
            border: 1px solid var(--border-color) !important;
            margin-bottom: 22px;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        /* ===== LUNO STATS GRID & WIDGETS ===== */
        .stats-grid, .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }

        .luno-widget, .stat-card, .summary-card {
            background: var(--bg-surface) !important;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color) !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
            overflow: hidden;
        }

        .luno-widget:hover, .stat-card:hover, .summary-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        .luno-widget-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .luno-widget-title, .stat-info p, .summary-info h4 {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 0;
        }

        .luno-widget-icon, .stat-icon, .summary-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .icon-primary, .icon-blue, .summary-icon { 
            background: var(--luno-primary-light); 
            color: var(--luno-primary); 
        }
        .icon-success, .icon-green, .summary-icon.green { 
            background: var(--success-light); 
            color: var(--success); 
        }
        .icon-warning, .icon-orange { 
            background: var(--warning-light); 
            color: var(--warning); 
        }
        .icon-info, .icon-purple { 
            background: var(--info-light); 
            color: var(--info); 
        }

        .luno-widget-value, .stat-info h3, .summary-info .amount {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-main);
            font-variant-numeric: tabular-nums;
            margin: 4px 0 0 0;
        }

        .luno-widget-bottom {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: var(--text-muted);
            border-top: 1px dashed var(--border-subtle);
            padding-top: 10px;
        }

        .luno-trend-up {
            color: var(--success);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .luno-trend-neutral {
            color: var(--luno-primary);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* ===== LUNO BUTTONS ===== */
        .btn, .btn-primary, .btn-secondary, .btn-export, .btn-edit, .btn-delete, .btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12.5px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            line-height: 1.3;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--luno-primary) !important;
            color: #ffffff !important;
            border-color: var(--luno-primary) !important;
            box-shadow: 0 2px 6px rgba(76, 53, 222, 0.25);
        }

        .btn-primary:hover {
            background: var(--luno-primary-hover) !important;
            border-color: var(--luno-primary-hover) !important;
            box-shadow: 0 4px 12px rgba(76, 53, 222, 0.35);
        }

        .btn-export {
            background: var(--bg-surface) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
        }

        .btn-export:hover {
            background: var(--bg-surface-subtle) !important;
            border-color: #cbd5e1 !important;
        }

        .btn-edit {
            background: var(--luno-primary-light) !important;
            color: var(--luno-primary-text) !important;
            border: 1px solid transparent !important;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .btn-edit:hover {
            background: var(--luno-primary) !important;
            color: #ffffff !important;
        }

        .btn-delete, .btn-danger {
            background: var(--danger-light) !important;
            color: var(--danger-text) !important;
            border: 1px solid transparent !important;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
        }

        .btn-delete:hover, .btn-danger:hover {
            background: var(--danger) !important;
            color: #ffffff !important;
        }

        /* ===== LUNO TOOLBAR & FILTER ===== */
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            background: var(--bg-surface) !important;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid var(--border-color) !important;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: var(--card-shadow);
        }

        .search-box {
            display: flex;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            width: 320px;
            transition: all 0.15s ease;
        }

        .search-box:focus-within {
            border-color: var(--luno-primary);
            box-shadow: 0 0 0 3px var(--luno-primary-light);
        }

        .search-box input {
            border: none;
            padding: 8px 12px;
            flex-grow: 1;
            outline: none;
            font-size: 13px;
            background: transparent;
            color: var(--text-main);
        }

        .search-box button {
            background: var(--bg-surface-subtle);
            border: none;
            border-left: 1px solid var(--border-color);
            padding: 0 14px;
            cursor: pointer;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12.5px;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .search-box button:hover {
            background: var(--bg-surface-hover);
            color: var(--luno-primary);
        }

        /* ===== LUNO TABLE STYLES ===== */
        .table-container {
            background: var(--bg-surface) !important;
            border-radius: 14px !important;
            box-shadow: var(--card-shadow) !important;
            border: 1px solid var(--border-color) !important;
            overflow-x: auto;
            margin-bottom: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background: var(--bg-surface-subtle);
            color: var(--text-muted);
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid var(--border-color);
        }

        td {
            padding: 10px 12px;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            transition: background-color 0.15s ease;
        }

        tr:hover td {
            background: var(--bg-surface-hover);
        }

        /* Rekap Table - Specific Multi-column Grid */
        .table-rekap {
            min-width: 2150px;
        }

        .table-rekap th {
            text-align: center;
            vertical-align: middle;
            white-space: normal !important;
            word-break: normal;
            overflow-wrap: break-word;
            line-height: 1.3;
            padding: 8px 6px;
        }

        .table-rekap td {
            font-size: 12px;
            padding: 8px 10px;
        }

        .table-rekap .sticky-col-1 {
            position: sticky;
            left: 0;
            z-index: 10;
            background: var(--bg-surface);
        }

        .table-rekap .sticky-col-2 {
            position: sticky;
            left: 42px;
            z-index: 10;
            background: var(--bg-surface);
            box-shadow: 2px 0 6px rgba(0,0,0,0.06);
        }

        .table-rekap thead .sticky-col-1,
        .table-rekap thead .sticky-col-2 {
            z-index: 20;
            background: var(--bg-surface-subtle);
        }

        .money {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 12px;
            font-weight: 700;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .center {
            text-align: center;
        }

        .pagination-wrapper {
            padding: 14px 18px;
            background: var(--bg-surface);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Prevent giant SVG arrows in pagination */
        nav[role="navigation"] svg, .pagination svg {
            width: 15px !important;
            height: 15px !important;
            max-width: 15px !important;
            max-height: 15px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* ===== FORM INPUTS ===== */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label, label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        input[type="text"], input[type="date"], input[type="file"], select, .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: var(--bg-surface);
            color: var(--text-main);
            transition: all 0.15s ease;
        }

        input:focus, select:focus, .form-control:focus {
            border-color: var(--luno-primary);
            box-shadow: 0 0 0 3px var(--luno-primary-light);
        }

        /* ===== LUNO BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .badge-pns { background: var(--luno-primary-light); color: var(--luno-primary-text); }
        .badge-pppk { background: var(--success-light); color: var(--success-text); }
        .badge-paruh { background: var(--warning-light); color: var(--warning-text); }
        .badge-gaji { background: var(--luno-primary-light); color: var(--luno-primary-text); }
        .badge-tpp { background: var(--info-light); color: var(--info-text); }

        /* ===== MODAL DIALOGS ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(13, 19, 31, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            overflow-y: auto;
            padding: 24px 16px;
            box-sizing: border-box;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-surface) !important;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            padding: 24px;
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
            animation: modalIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.96) translateY(6px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-header h3 {
            font-size: 15.5px;
            font-weight: 700;
            color: var(--text-main);
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--text-muted);
            line-height: 1;
        }

        .btn-close:hover {
            color: var(--text-main);
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success-text);
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger-text);
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* TomSelect override */
        .ts-control {
            background: var(--bg-surface) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        .ts-dropdown {
            background: var(--bg-surface) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 10px !important;
            box-shadow: var(--card-shadow-hover) !important;
        }
        .ts-dropdown .option {
            color: var(--text-main) !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        .ts-dropdown .active {
            background: var(--luno-primary-light) !important;
            color: var(--luno-primary-text) !important;
        }
    </style>
</head>
<body>

    <!-- LUNO Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="sidebar-brand">
                <div class="luno-logo-icon">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <div class="sidebar-brand-text">
                    <h2>BELPEG <span class="brand-badge">PROV</span></h2>
                    <p>Realisasi Belanja Pegawai</p>
                </div>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            
            <a href="/dashboard" class="menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
                <i class="ph ph-squares-four"></i>
                Dashboard
            </a>

            <div class="menu-label">Master & Kepegawaian</div>
            
            <!-- Master Data -->
            <a href="#" class="menu-item has-submenu {{ request()->is('master*') ? 'open' : '' }}">
                <i class="ph ph-database"></i>
                Master Data
                <i class="ph ph-caret-down" style="margin-left: auto; font-size: 13px;"></i>
            </a>
            <div class="submenu" style="{{ request()->is('master*') ? 'display: block;' : 'display: none;' }}">
                <a href="/master/skpd" class="submenu-item {{ Request::is('master/skpd') ? 'active' : '' }}">
                    <i class="ph ph-buildings"></i> 1. SKPD & UPTD
                </a>
                <a href="/master/jabatan" class="submenu-item {{ Request::is('master/jabatan') ? 'active' : '' }}">
                    <i class="ph ph-identification-badge"></i> 2. Jabatan
                </a>
                <a href="/master/simgaji-dbf" class="submenu-item {{ Request::is('master/simgaji-dbf*') ? 'active' : '' }}">
                    <i class="ph ph-database"></i> 3. Database SIMGAJI (.DBF)
                </a>
                <a href="/master/pegawai-simpeg" class="submenu-item {{ Request::is('master/pegawai-simpeg*') ? 'active' : '' }}">
                    <i class="ph ph-file-arrow-up"></i> 4. Upload Pegawai SIMPEG
                </a>
            </div>

            <!-- Daftar Pegawai -->
            <a href="/pegawai" class="menu-item {{ Request::is('pegawai') ? 'active' : '' }}">
                <i class="ph ph-users"></i>
                Daftar Pegawai
            </a>

            <div class="menu-label">Keuangan & Realisasi</div>
            
            <a href="#" class="menu-item has-submenu {{ request()->is('laporan*') || request()->is('realisasi/tpp*') || request()->is('realisasi/gaji*') ? 'open' : '' }}">
                <i class="ph ph-chart-line-up"></i>
                Laporan & Realisasi
                <i class="ph ph-caret-down" style="margin-left: auto; font-size: 13px;"></i>
            </a>
            <div class="submenu" style="{{ request()->is('laporan*') || request()->is('realisasi/tpp*') || request()->is('realisasi/gaji*') ? 'display: block;' : 'display: none;' }}">
                <a href="/realisasi/gaji" class="submenu-item {{ Request::is('realisasi/gaji*') ? 'active' : '' }}">1. Realisasi Gaji</a>
                <a href="/realisasi/tpp" class="submenu-item {{ Request::is('realisasi/tpp*') ? 'active' : '' }}">2. Realisasi TPP</a>
                <a href="/laporan/gabungan" class="submenu-item {{ Request::is('laporan/gabungan*') ? 'active' : '' }}">3. Laporan Gabungan</a>
                <a href="/laporan/pegawai" class="submenu-item {{ Request::is('laporan/pegawai*') ? 'active' : '' }}">4. Pegawai per SKPD/UPT/Satker</a>
                <a href="/laporan/sikd-core" class="submenu-item {{ Request::is('laporan/sikd-core') ? 'active' : '' }}">5. SIKD Core</a>
                <a href="/laporan/unmatched-nip" class="submenu-item {{ Request::is('laporan/unmatched-nip*') ? 'active' : '' }}">6. Log Gagal Upload (NIP)</a>
                <a href="/laporan/sikd-core/rinci" class="submenu-item {{ Request::is('laporan/sikd-core/rinci') ? 'active' : '' }}">7. SIKD Core (Rinci)</a>
                <a href="/laporan/pppk-guru" class="submenu-item {{ Request::is('laporan/pppk-guru') ? 'active' : '' }}">8. Laporan PPPK Guru</a>
                <a href="/laporan/pppk-guru/rinci" class="submenu-item {{ Request::is('laporan/pppk-guru/rinci') ? 'active' : '' }}">9. PPPK Guru (Rinci)</a>
                <a href="/laporan/rekonsiliasi-simgaji" class="submenu-item {{ Request::is('laporan/rekonsiliasi-simgaji*') ? 'active' : '' }}">10. Rekonsiliasi SIMGAJI</a>
                <a href="/laporan/penyelarasan-unit-kerja" class="submenu-item {{ Request::is('laporan/penyelarasan-unit-kerja*') ? 'active' : '' }}">11. Penyelarasan SKPD & UPTD</a>
                <a href="/laporan/iwp-jamkes" class="submenu-item {{ Request::is('laporan/iwp-jamkes*') ? 'active' : '' }}">12. IWP & Jamkes BPJS</a>
                <a href="/laporan/trace-gaji" class="submenu-item {{ Request::is('*trace-gaji*') ? 'active' : '' }}" style="{{ Request::is('*trace-gaji*') ? 'font-weight: 700;' : '' }}">13. Trace Penggajian Per Orang</a>
                <a href="/laporan/audit-tunjangan-keluarga" class="submenu-item {{ Request::is('laporan/audit-tunjangan-keluarga*') ? 'active' : '' }}" style="{{ Request::is('laporan/audit-tunjangan-keluarga*') ? 'font-weight: 700;' : '' }}">14. Audit Tunjangan Keluarga</a>
                <a href="/laporan/perbaikan-simgaji-skpd" class="submenu-item {{ Request::is('laporan/perbaikan-simgaji-skpd*') ? 'active' : '' }}" style="{{ Request::is('laporan/perbaikan-simgaji-skpd*') ? 'font-weight: 700;' : '' }}">15. BNBA Perbaikan SKPD SIMGAJI</a>
                <a href="/laporan/tapera" class="submenu-item {{ Request::is('laporan/tapera*') ? 'active' : '' }}" style="{{ Request::is('laporan/tapera*') ? 'font-weight: 700;' : '' }}">16. Simulasi Tapera (ASN & Pemda)</a>
                <a href="/laporan/monitoring-status-pegawai" class="submenu-item {{ Request::is('laporan/monitoring-status-pegawai*') ? 'active' : '' }}" style="{{ Request::is('laporan/monitoring-status-pegawai*') ? 'font-weight: 700;' : '' }}">17. Monitoring Status & Pensiun</a>
            </div>
            
            <div class="menu-label">Sistem & Pengaturan</div>

            <!-- Setting -->
            <a href="#" class="menu-item has-submenu {{ request()->is('setting*') ? 'open' : '' }}">
                <i class="ph ph-gear"></i>
                Pengaturan
                <i class="ph ph-caret-down" style="margin-left: auto; font-size: 13px;"></i>
            </a>
            <div class="submenu" style="{{ request()->is('setting*') ? 'display: block;' : 'display: none;' }}">
                <a href="/setting/users" class="submenu-item {{ Request::is('setting/users') ? 'active' : '' }}">1. Kelola Pengguna</a>
                <a href="/setting/data" class="submenu-item {{ Request::is('setting/data*') ? 'active' : '' }}">2. Reset Data Laporan</a>
            </div>
        </nav>

        <!-- Sidebar Footer Version Card -->
        <div class="sidebar-footer">
            <div class="version-card" onclick="openAppVersionModal()" title="Klik untuk melihat catatan rilis & pembaruan versi (Changelog)">
                <div class="version-info">
                    <div class="version-label">
                        <span class="version-pulse"></span>
                        <span>Versi Sistem</span>
                    </div>
                    <div class="version-number">
                        {{ config('app.version', 'v2.4.0') }}
                        <span class="version-codename">{{ config('app.version_date', '04 Sep 2026') }}</span>
                    </div>
                </div>
                <div class="version-badge">
                    <i class="ph-bold ph-sparkle"></i>
                    <span>Terbaru</span>
                </div>
            </div>
            <div class="sidebar-copyright">
                &copy; {{ date('Y') }} BPKAD Prov. Kalsel
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- LUNO Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button id="sidebar-toggle" class="icon-btn" aria-label="Toggle Sidebar" title="Sembunyikan / Tampilkan Sidebar">
                    <i class="ph ph-list"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <a href="/dashboard"><i class="ph ph-house"></i> Home</a>
                    <span class="separator">/</span>
                    <span class="active-crumb">@yield('page_title', 'Dashboard')</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" placeholder="Cari data, NIP, SKPD...">
                </div>
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Dark Mode" title="Ganti Mode Gelap / Terang">
                    <i class="ph ph-moon"></i>
                </button>
                <div class="user-dropdown-container">
                    <div class="user-profile" id="userProfileBtn" title="Klik untuk menu akun">
                        <div class="avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
                        <div class="user-profile-info">
                            <div class="user-name">{{ Auth::user()->name ?? 'Admin Keuangan' }}</div>
                            <div class="user-role">{{ Auth::user()->email ?? 'Administrator' }}</div>
                        </div>
                        <i class="ph ph-caret-down" style="font-size: 12px; color: var(--text-muted);"></i>
                    </div>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <div class="user-dropdown-header">
                            <div class="dropdown-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="dropdown-email">{{ Auth::user()->email ?? 'admin@pemda.go.id' }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item-btn">
                                <i class="ph ph-sign-out" style="font-size: 16px;"></i>
                                <span>Keluar / Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown submenus
            const dropdowns = document.querySelectorAll('.has-submenu');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = this.nextElementSibling;
                    const caret = this.querySelector('.ph-caret-down');
                    
                    if (submenu.style.display === 'none' || submenu.style.display === '') {
                        submenu.style.display = 'block';
                        this.classList.add('open');
                        if (caret) caret.style.transform = 'rotate(180deg)';
                    } else {
                        submenu.style.display = 'none';
                        this.classList.remove('open');
                        if (caret) caret.style.transform = 'rotate(0deg)';
                    }
                });
            });

            // Dark Mode Toggle
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                const themeIcon = themeToggle.querySelector('i');
                const root = document.documentElement;
                
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    root.classList.add('dark-mode');
                    if (themeIcon) themeIcon.classList.replace('ph-moon', 'ph-sun');
                }

                themeToggle.addEventListener('click', () => {
                    root.classList.toggle('dark-mode');
                    const isDark = root.classList.contains('dark-mode');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    
                    if (isDark) {
                        if (themeIcon) themeIcon.classList.replace('ph-moon', 'ph-sun');
                    } else {
                        if (themeIcon) themeIcon.classList.replace('ph-sun', 'ph-moon');
                    }
                });
            }

            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainWrapper = document.querySelector('.main-wrapper');
            
            if (sidebarToggle && sidebar && mainWrapper) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('hidden');
                    mainWrapper.classList.toggle('expanded');
                });
            }
        });

        function openAppVersionModal() {
            const modal = document.getElementById('appVersionModal');
            if (modal) modal.classList.add('active');
        }

        function closeAppVersionModal() {
            const modal = document.getElementById('appVersionModal');
            if (modal) modal.classList.remove('active');
        }

        // User Dropdown Menu
        const userProfileBtn = document.getElementById('userProfileBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userProfileBtn && userDropdownMenu) {
            userProfileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });
            document.addEventListener('click', function(e) {
                if (!userProfileBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }
            });
        }
    </script>

    <!-- Modal Changelog & Versi Aplikasi -->
    <div class="modal-overlay" id="appVersionModal">
        <div class="modal-content" style="max-width: 580px;">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(76, 53, 222, 0.12); color: var(--luno-primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="ph-bold ph-git-branch"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Catatan Rilis & Versi Aplikasi</h3>
                        <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Informasi pembaruan terkini sistem KONBELPEG</p>
                    </div>
                </div>
                <button class="btn-close" onclick="closeAppVersionModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px 24px; max-height: 60vh; overflow-y: auto;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; margin-bottom: 20px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #059669; letter-spacing: 0.5px;">Versi Terpasang Saat Ini</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--text-main);">
                            {{ config('app.version', 'v2.4.0') }} &bull; <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">{{ config('app.version_title', 'Integrasi HIS_GPOK') }}</span>
                        </div>
                    </div>
                    <span style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: #10b981; color: white;">
                        Rilis {{ config('app.version_date', '04 Sep 2026') }}
                    </span>
                </div>

                <div class="timeline-version">
                    <!-- v2.13.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge current">v2.13.0 (Terbaru)</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Modul Simulasi &amp; Proyeksi Iuran Tapera ASN &amp; Pemda (PP 21/2024)</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Kalkulator simulasi interaktif perorangan dengan preset gaji pokok ASN 2024 (PP 5/2024 &amp; Perpres 11/2024).</li>
                                <li>Rekapitulasi proyeksi beban Pemda (0,5%) dan potongan ASN (2,5%) per SKPD berdasarkan data riil penggajian SIMGAJI.</li>
                                <li>Daftar nominatif perorangan (By Name By NIP) beserta ekspor resmi Microsoft Excel (.xlsx) dan PDF (A4 Landscape).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.9.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.9.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Modul Audit Tunjangan Keluarga SIMGAJI &amp; Penyelesaian Bukti STS</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Uji silang dobel tunjangan anak (klaim ganda ayah &amp; ibu ASN), pasangan saling menunjang (10%+10%), dan kelebihan batas kuota (&gt;2 anak).</li>
                                <li>Fitur tindak lanjut penyelesaian kasus dengan pencatatan bukti Surat Tanda Setoran (STS) ke Kas Daerah (No. STS, tanggal, nominal pengembalian, dan catatan tindak lanjut).</li>
                                <li>Filter status kasus (Semua, Pending/Belum Selesai, Sudah Selesai via STS) untuk fokus menyelesaikan kasus tersisa.</li>
                                <li>Ekspor laporan audit ke format Microsoft Excel (.xlsx) dan PDF resmi.</li>
                                <li>Komponen navigasi halaman (pagination) modern dan responsif.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.8.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.8.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Integrasi Riwayat Keluarga SIMGAJI (KEL), Profil Finansial &amp; Trace Gaji</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Dukungan berkas DBF Riwayat Keluarga (<code>KEL</code>, 70.000+ data) dengan sinkronisasi ke tabel <code>simgaji_keluargas</code>.</li>
                                <li>Penambahan atribut finansial pegawai: NIK, No. Rekening, Bank Penyalur, NPWP, dan No. Karpeg.</li>
                                <li>Modul Trace Riwayat Penggajian Pegawai (<code>/laporan/trace-gaji</code>) dengan profil perbankan dan tanggungan keluarga.</li>
                                <li>Peningkatan laporan Unmatched NIP dengan informasi status kepegawaian (PNS/PPPK/Non-ASN).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.7.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.7.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Modul Rekonsiliasi IWP &amp; BPJS Kesehatan (Jamkes)</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Rekonsiliasi IWP 2% Jamkes Gaji, IWP 8% Taspen, dan IWP 1% TPP di <code>/laporan/iwp-jamkes</code>.</li>
                                <li>Tab Rekapitulasi per SKPD dan Rincian per Pegawai beserta ekspor Excel &amp; PDF.</li>
                                <li>Sistem 2 Tab Master SKPD Induk (42) vs UPTD/Satker (1.638) di <code>/master/skpd</code>.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.6.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.6.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Sistem Autentikasi &amp; Deployment Server VPS</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Autentikasi sesi multi-user berbasis peran (Role-based access).</li>
                                <li>Konfigurasi Deployment Server VPS (PHP 8.4, Nginx, MySQL 8).</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.5.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.5.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Penyelarasan Unit Kerja SIMGAJI vs SIMPEG</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Modul penyesuaian penamaan SKPD &amp; UPTD antara data SIMGAJI dan master BKD.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.4.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.4.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Integrasi DBF Histori Gaji Pokok (HIS_GPOK) &amp; Manajemen DBF Ganda</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Integrasi database <code>HIS_GPOK</code> untuk membaca nomor SK, TMT gaji, dan gapok baru.</li>
                                <li>Identifikasi otomatis status SK: <strong>Terjadwal di SIMGAJI</strong> (351) vs <strong>Belum Diinput</strong> (124).</li>
                                <li>Manajemen berkas ganda di <code>/master/simgaji-dbf</code> dengan auto-detect kolom DBF.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.3.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.3.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Ekuivalensi Romawi PPPK & Filter Pensiunan</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Ekuivalensi cerdas pangkat PPPK (contoh: IX sama dengan 09/9), mengeliminasi 4.806 <em>false positives</em>.</li>
                                <li>Filter status kepegawaian (Semua, Pegawai Aktif, Pensiun BUP/Janda/Duda).</li>
                                <li>Perbaikan nama SKPD saat ekspor Excel pada tab Perbedaan Tanggal Lahir.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.2.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.2.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Zona Waktu WITA & Estetika Antarmuka</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Standarisasi zona waktu ke WITA / GMT+8 (Asia/Makassar).</li>
                                <li>Pembersihan sidebar dan penyesuaian light palette.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- v2.1.0 -->
                    <div class="timeline-item">
                        <div class="timeline-badge">v2.1.0</div>
                        <div class="timeline-content">
                            <h4 style="margin: 0 0 6px 0; font-size: 13.5px; font-weight: 700; color: var(--text-main);">Modul Rekonsiliasi SIMGAJI Cepat</h4>
                            <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                                <li>Parser DBF native dan 6 kategori pencocokan otomatis.</li>
                                <li>Sinkronisasi master data langsung & caching instan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-top: 1px solid var(--border-color); background: var(--bg-surface-subtle);">
                <a href="https://github.com/rullyperdhana/konbelpeg/blob/main/CHANGELOG.md" target="_blank" style="font-size: 12px; color: var(--luno-primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="ph-bold ph-arrow-square-out"></i> Buka Changelog Lengkap di GitHub
                </a>
                <button type="button" class="btn btn-primary" onclick="closeAppVersionModal()">Tutup</button>
            </div>
        </div>
    </div>
</body>
</html>
