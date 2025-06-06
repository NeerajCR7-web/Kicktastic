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