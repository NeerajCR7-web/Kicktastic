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
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      margin-top: 0.5rem;
      background-color: #98caeb;
      min-width: 260px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      border-radius: 4px;
      z-index: 1000;
      padding: 12px;
    }
    .dropdown-content.show {
      display: block;
    }
    .signin-container {
      display: flex;
      gap: 8px;
    }
    .signin-box {
      flex: 1;
      background-color: #f9f9f9;
      padding: 10px 12px;
      border-radius: 30px;
      text-align: center;
      color: #000;
      font-size: 0.875rem;
      transition: background-color 0.2s;
    }
    .signin-box:hover {
      background-color: #e0e0e0;
    }
    .register-link {
      display: block;
      margin-top: 12px;
      text-align: center;
      color: #555;
      font-size: 0.8125rem;
      transition: color 0.2s;
    }
    .register-link:hover {
      color: #3498db;
    }
    /* ===== 5. TEAM LOGO SLIDER ===== */
    .team-slider-container {
      overflow: hidden;
      background: #fff;
      padding: 1rem 0;
    }
    .team-slider-track {
      display: flex;
      gap: 2rem;
      animation: scroll-left 20s linear infinite;
    }
    .team-slider-track img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #ddd;
    }
    @keyframes scroll-left {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    @media (min-width: 640px) {
      .team-slider-track img {
        width: 80px;
        height: 80px;
      }
    }

    /* ===== 6. IMAGE SLIDER & CONTAINERS ===== */
    .content-section {
      display: flex;
      justify-content: space-between;
      gap: 2rem;
      padding: 2rem 1rem;
      background: #fefefe;
      flex-wrap: wrap;
    }
    .slider-container {
      position: relative;
      width: 55%;
      height: 300px;
      background: #000;
      overflow: hidden;
      border-radius: 8px;
    }
    .slider-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .slider-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      border: none;
      color: #fff;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .slider-button.prev {
      left: 8px;
    }
    .slider-button.next {
      right: 8px;
    }
    /* ===== 7. NEWS CONTAINER ===== */
    .news-container {
      width: 40%;
      height: 300px;
      overflow-y: auto;
      background: #fff;
      border-radius: 8px;
      padding: 0.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .news-item {
      display: flex;
      background: #f9f9f9;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      overflow: hidden;
      height: 90px;
    }
    .news-item img {
      width: 70px;
      height: 100%;
      object-fit: cover;
      flex-shrink: 0;
    }
    .news-text {
      padding: 0.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }