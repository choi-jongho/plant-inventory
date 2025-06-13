<?php
session_start();
  include 'db.php';
  include 'auth.php';
  checkLogin();

  $plantCountQuery = $conn->query("SELECT COUNT(*) as total FROM plants");
  $plantCount = $plantCountQuery->fetch_assoc()['total'];

  $categoryCountQuery = $conn->query("SELECT COUNT(*) as total FROM category");
  $categoryCount = $categoryCountQuery->fetch_assoc()['total'];

  $supplierCountQuery = $conn->query("SELECT COUNT(*) as total FROM suppliers");
  $supplierCount = $supplierCountQuery->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>🌱Plant Inventory System - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<style>
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background-color: #d1d1b8; 
    color: #324d2f;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  a {
    color: inherit;
    text-decoration: none;
  }
  button {
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
  }
  .app {
    display: flex;
    flex: 1;
    min-height: 0;
  }

  .sidebar {
    background-color: #4a6b35;
    color: #b8cdb0;
    display: flex;
    flex-direction: column;
    width: 280px;
    min-width: 280px;
    transition: transform 0.3s ease;
  }
  .sidebar-header {
    padding: 1rem 1.5rem;
    font-weight: 700;
    font-size: 1.2rem;
    border-bottom: 1px solid #6c8c44;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .sidebar-header span.material-icons {
    font-size: 1.8rem;
    color: #aed9a1;
  }
  .sidebar-subtitle {
    font-size: 0.875rem;
    color: #a2b084;
    margin-top: 0.25rem;
  }
  .nav {
    flex-grow: 1;
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  .nav a {
    color: #b8cdb0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    border-left: 4px solid transparent;
    transition: background 0.2s, border-color 0.3s;
    font-weight: 500;
    font-size: 1rem;
  }
  .nav a.active,
  .nav a:hover {
    background-color: #6c8c44;
    border-left-color: #a6ce70;
    color: #eaf4d7;
  }
  .nav a span.material-icons {
    font-size: 1.3rem;
    color: inherit;
  }
  .logout {
    margin-top: auto;
    border-top: 1px solid #6c8c44;
  }
  .user-info {
    padding: 1rem 1.5rem;
    font-size: 0.825rem;
    color: #a2b084;
    border-top: 1px solid #6c8c44;
  }

  /* Main content */
  .main-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }

  /* Header */
  header {
    background-color: #a4b785; /* muted green */
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: inset 0 -3px 8px #86996288;
  }
  .header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  .menu-toggle {
    display: none;
    font-size: 1.8rem;
    color: #324d2f;
    background: none;
    border: none;
  }
  .header-title {
    font-weight: 600;
    font-size: 1.3rem;
    color: #324d2f;
  }
  form.search-form {
    position: relative;
    width: 250px;
    max-width: 100%;
  }
  form.search-form input[type="search"] {
    width: 100%;
    padding: 0.4rem 2.5rem 0.4rem 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    outline-offset: 2px;
    outline-color: #4a6b35;
    background-color: #e6ebd8;
    color: #324d2f;
  }
  form.search-form button {
    position: absolute;
    top: 50%;
    right: 0.3rem;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #324d2f;
    font-size: 1.4rem;
  }
  form.search-form button:hover {
    color: #a6ce70;
  }

  /* Dashboard */
  .dashboard {
    padding: 1.5rem 2rem;
    flex-grow: 1;
    overflow-y: auto;
  }
  .dashboard h2 {
    margin-bottom: 1.5rem;
    font-weight: 700;
    color: #324d2f;
    font-size: 1.6rem;
  }
  .cards {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 1rem 1.5rem;
  }
  .card {
    background-color: #819b47;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    color: #d7e4b7;
    box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .card-title {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 0.35rem;
  }
  .card-subtitle {
    font-weight: 600;
    font-size: 0.9rem;
    color: #c8d3a7;
    margin-bottom: 1rem;
  }
  .card-button {
    margin-top: auto;
    align-self: flex-start;
    font-weight: 600;
    font-size: 0.875rem;
    color: #d7e4b7;
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0;
    cursor: pointer;
    transition: color 0.3s ease;
  }
  .card-button:hover {
    color: #a6ce70;
  }
  .card-button span.material-icons {
    font-size: 1.25rem;
  }

  /* Responsive breakpoints */
  @media (max-width: 767px) {
    .app {
      flex-direction: column;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      transform: translateX(-100%);
      z-index: 1000;
      width: 240px;
      min-width: 0;
      box-shadow: 3px 0 10px rgb(0 0 0 / 0.15);
    }
    .sidebar.show {
      transform: translateX(0);
      transition: transform 0.3s ease;
    }
    .menu-toggle {
      display: inline-flex;
    }
    header {
      padding: 0.5rem 1rem;
      position: sticky;
      top: 0;
      z-index: 900;
      box-shadow: 0 2px 4px rgb(0 0 0 / 0.1);
    }
    form.search-form {
      width: 100%;
      max-width: none;
    }
    .main-content {
      margin-top: 56px;
      padding: 0 1rem 1rem;
      overflow-y: auto;
      height: calc(100vh - 56px);
    }
  }

  @media (min-width: 768px) and (max-width: 1439px) {
    .app {
      flex-direction: row;
    }
    .sidebar {
      transform: translateX(0);
      position: relative;
      box-shadow: none;
      width: 280px;
      min-width: 280px;
    }
    .menu-toggle {
      display: none;
    }
    .main-content {
      overflow-y: auto;
      height: 100vh;
    }
  }

  @media (min-width: 1440px) {
    .app {
      max-width: 1400px;
      margin: 0 auto;
      width: 100%;
    }
  }
</style>
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar" aria-label="Sidebar navigation">
      <div class="sidebar-header" aria-label="System logo and title">
        <span class="material-icons" aria-hidden="true">spa</span>
        <div>
          <div><strong>Administrator</strong></div>
          <div class="sidebar-subtitle">Plant Inventory System</div>
        </div>
      </div>
      <nav class="nav" role="navigation" aria-label="Main navigation">
        <a href="#" class="active" aria-current="page">
          <span class="material-icons" aria-hidden="true">dashboard</span> Dashboard
        </a>
        <a href="logout.php" class="logout">
          <span class="material-icons" aria-hidden="true">logout</span> Log out
        </a>
      </nav>
      <div class="user-info" aria-label="Logged in user info">
        Logged in as : Admin
      </div>
    </aside>

    <main class="main-content" role="main" tabindex="-1">
      <header>
        <button class="menu-toggle" aria-label="Toggle sidebar menu" aria-expanded="false" aria-controls="sidebar">
          <span class="material-icons">menu</span>
        </button>
        <div class="header-left">
          <h1 class="header-title">Dashboard</h1>
        </div>
      </header>

      <section class="dashboard" aria-labelledby="dashboard-title">
        <h2 id="dashboard-title" class="visually-hidden">Dashboard</h2>
        <div class="cards">
            <article class="card" role="region" aria-labelledby="plants-title">
            <h3 id="plants-title" class="card-title">Plants (<?= $plantCount ?>)</h3>
            <p class="card-subtitle">&nbsp;</p>
            <a href="plants.php" class="card-button" aria-label="View details for Plants">
                View details <span class="material-icons" aria-hidden="true">arrow_forward</span>
            </a>
            </article>
            <article class="card" role="region" aria-labelledby="categories-title">
            <h3 id="categories-title" class="card-title">Categories (<?= $categoryCount ?>)</h3>
            <p class="card-subtitle">&nbsp;</p>
            <a href="manage_categories.php" class="card-button" aria-label="View details for Categories">
                View details <span class="material-icons" aria-hidden="true">arrow_forward</span>
            </a>
            </article>
            <article class="card" role="region" aria-labelledby="suppliers-title">
            <h3 id="suppliers-title" class="card-title">Suppliers (<?= $supplierCount ?>)</h3>
            <p class="card-subtitle">&nbsp;</p>
            <a href="manage_suppliers.php" class="card-button" aria-label="View details for Suppliers">
                View details <span class="material-icons" aria-hidden="true">arrow_forward</span>
            </a>
            </article>
        </div>
        </section>
    </main>
  </div>

  <script>

    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.getElementById('sidebar');
    menuToggle.addEventListener('click', () => {
      const expanded = menuToggle.getAttribute('aria-expanded') === 'true' || false;
      menuToggle.setAttribute('aria-expanded', !expanded);
      sidebar.classList.toggle('show');
    });


    document.addEventListener('click', (e) => {
      if(window.innerWidth > 767) return;
      if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('show');
        menuToggle.setAttribute('aria-expanded', false);
      }
    });
  </script>
</body>
</html>
