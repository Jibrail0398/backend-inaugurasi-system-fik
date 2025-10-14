<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel API - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            box-sizing: border-box;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .api-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center">
  <div class="max-w-4xl w-full text-center p-4">
    <!-- Header -->
    <div>
      <div class="flex items-center justify-center mb-6">
        <svg class="w-16 h-16 text-white mr-4" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
          <path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" fill="none"/>
        </svg>
        <h1 class="text-5xl font-bold text-white">Laravel API</h1>
      </div>
      <p class="text-xl text-white opacity-90">Selamat datang di API Server Inaugurasi FIK UBP Karawang</p>
      <div class="mt-4 inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-full">
        <div class="w-3 h-3 bg-green-300 rounded-full pulse-animation mr-2"></div>
        <span class="font-semibold">Server Aktif</span>
      </div>
    </div>
  </div>


<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'98e5535bc38b87d7',t:'MTc2MDQyNjU0Ni4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
