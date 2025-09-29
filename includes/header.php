<head>
    <link rel="stylesheet" href="/trade zone/assets/css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4338ca;
            --accent: #06b6d4;
            --accent-dark: #0e7490;
            --bg: #f4f6fb;
            --card-bg: #fff;
            --border: #e5e7eb;
            --text-main: #22223b;
            --text-light: #6b7280;
            --white: #fff;
        }
        .dark-mode {
            --primary: #6366f1;
            --primary-dark: #a5b4fc;
            --accent: #06b6d4;
            --accent-dark: #67e8f9;
            --bg: #181a20;
            --card-bg: #23263a;
            --border: #23263a;
            --text-main: #f3f4f6;
            --text-light: #a1a1aa;
            --white: #23263a;
        }
        body {
            background: var(--bg);
            color: var(--text-main);
            transition: background 0.3s, color 0.3s;
        }
    </style>
</head>
<header style="background: linear-gradient(90deg, #6366f1 0%, #06b6d4 100%); padding: 28px 0 18px 0; box-shadow: 0 2px 12px #e5e7eb; margin-bottom: 0; border-bottom: 4px solid #4338ca;">
    <div style="text-align: center; margin-bottom: 8px;">
    <img src="/trade zone/assets/images/C&amp;C logo.jpeg" alt="Click &amp; Carry Logo" style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 3px solid #06b6d4; box-shadow: 0 2px 8px #e5e7eb; background: #fff;">
    </div>
    <h1 style="color: #fff; text-align: center; font-size: 2.2em; letter-spacing: 2px; margin: 0 0 10px 0; font-family: 'Segoe UI', Arial, sans-serif;">Click &amp; Carry</h1>
    <nav style="text-align: center;">
    <!-- Home and Cart links only shown below if logged in -->
        <?php if(isset($_SESSION['name'])): ?>
            <a href="/trade zone/index.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Home</a>
            <a href="/trade zone/cart.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Cart</a>
            <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="/trade zone/admin/product_upload.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Product Upload</a>
                <a href="/trade zone/admin/view_products.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">View Products</a>
                <a href="/trade zone/admin/admin_register.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Add Admin</a>
            <?php endif; ?>
            <a href="/trade zone/logout.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Logout (<?php echo $_SESSION['name']; ?>)</a>
        <?php else: ?>
            <a href="/trade zone/login.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Login</a>
            <a href="/trade zone/register.php" style="color: #fff; font-weight: bold; margin: 0 18px; text-decoration: none; font-size: 1.1em; transition: color 0.2s;">Register</a>
        <?php endif; ?>
    </nav>
</header>
<?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['migration_notice']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <div style="max-width:1000px;margin:10px auto;padding:10px;border-radius:8px;background:#fff3cd;color:#856404;border:1px solid #ffeeba;text-align:center;">
        <?php echo htmlspecialchars($_SESSION['migration_notice']); ?>
    </div>
    <?php unset($_SESSION['migration_notice']); ?>
<?php endif; ?>
<button id="mode-toggle" title="Toggle light/dark mode" style="position: fixed; top: 18px; right: 28px; z-index: 1000; background: var(--card-bg); border: 2px solid var(--primary); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px var(--border);">
    <span id="mode-icon" style="font-size: 22px; color: var(--primary);">
        <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/></svg>
    </span>
</button>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeToggle = document.getElementById('mode-toggle');
    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');
    if (!modeToggle || !sunIcon || !moonIcon) return;

    const setMode = (dark) => {
        if (dark) {
            document.body.classList.add('dark-mode');
            sunIcon.style.display = 'none';
            moonIcon.style.display = '';
            modeToggle.title = 'Switch to light mode';
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.remove('dark-mode');
            sunIcon.style.display = '';
            moonIcon.style.display = 'none';
            modeToggle.title = 'Switch to dark mode';
            localStorage.setItem('theme', 'light');
        }
    };

    const savedTheme = localStorage.getItem('theme');
    // Default to light mode unless the user explicitly chose dark previously
    if (savedTheme === 'dark') {
        setMode(true);
    } else if (savedTheme === 'light') {
        setMode(false);
    } else {
        // No saved choice — respect system preference when available
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        setMode(!!prefersDark);
    }

    modeToggle.addEventListener('click', function() {
        const newDark = !document.body.classList.contains('dark-mode');
        setMode(newDark);
        // Persist server-side if user is logged in
        <?php if (isset($_SESSION['user_id'])): ?>
        fetch('/trade zone/save_theme.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'theme=' + (newDark ? 'dark' : 'light')
        }).catch(()=>{});
        <?php endif; ?>
    });
});
</script>
