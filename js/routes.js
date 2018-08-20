var routes=[ // Index page
  {
      path: '/', url: './index.html', name: 'home',
  }
  ,
  {
      path: '/about/', url: './pages/about.html', name: 'about',
  }
  ,
  {
      path: '/login/', componentUrl: './pages/login.html', name: 'login',
  }
  ,
  {
      path: '/register/', componentUrl: './pages/register.html', name: 'login',
  }
  ,
  {
      path: '/home/', componentUrl: './pages/home.html', name: 'user-home',
  }

  ,
  {
      path: '/forgot-password/', componentUrl: './pages/fp.html', name: 'forgot-password',
  }
  ,
  {
      path: '/profile/', componentUrl: './pages/profile.html', name: 'profile',
  }
  ,
  {
      path: '/view-herb/', componentUrl: './pages/view-herb.html', name: 'profile',
  }
  ,
  {
      path: '/camera/', componentUrl: './pages/camera.html', name: 'camera',
  },
  ,
  {
      path: '/search/', componentUrl: './pages/search.html', name: 'search',
  }
  , // Default route (404 page). MUST BE THE LAST
  {
      path: '(.*)', url: './pages/404.html',
  }
  
  ];