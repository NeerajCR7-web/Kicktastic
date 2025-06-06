<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  />
  <title>KickTastic</title>
  <style>
    /* ===== 1. RESET ===== */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    html {
      font-family: Arial, sans-serif;
      line-height: 1.5;
      color: #111;
    }
    body {
      background: #f9f9f9;
    }
    a {
      text-decoration: none;
      color: inherit;
    }

    /* ===== 2. TOP BAR ===== */
    .top-bar {
      background-color: #000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px;
      padding: 0 1rem;
    }
    .top-bar .logo {
      height: 100%;
      width: auto;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }
    .top-bar .title-container {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .top-bar .site-title {
      color: #fff;
      font-size: 2rem;
      font-weight: bold;
    }
    .top-bar .slogan {
      color: #fff;
      font-size: 1rem;
      margin-top: -1.75rem;
    }

     /* ===== 3. NAV MENU ===== */
    .menu-bar {
      background-color: #3498db;
      display: flex;
      justify-content: center;
      padding: 0.5rem 0;
    }
    .menu-bar a {
      color: #fff;
      font-size: 1rem;
      margin: 0 1rem;
      padding: 0.25rem 0.5rem;
      transition: background-color 0.2s;
    }
    .menu-bar a:hover {
      background-color: rgba(255,255,255,0.2);
      border-radius: 4px;
    }

    /* ===== 4. DROPDOWN ===== */
    .dropdown {
      position: relative;
      flex-shrink: 0;
    }
    .dropdown-button {
      padding: 0.5rem 1rem;
      font-size: 1rem;
      background-color: #3498db;
      color: #fff;
      border: none;
      cursor: pointer;
      border-radius: 4px;
      height: 40px;
      display: flex;
      align-items: center;
    }