<nav class="sidebar">
  <div class="sidebar-header">
    <a href="#" class="sidebar-brand">
      {{ config('app.name') }}</span>
    </a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav" id="sidebarNav">
      <li class="nav-item {{ active_class(['/']) }}">
        <a href="{{ url('/') }}" class="nav-link">
          <i class="link-icon" data-lucide="home"></i>
          <span class="link-title">Nadzorna ploča</span>
        </a>
      </li>
      <li class="nav-item nav-category">Analitika</li>
      <li class="nav-item {{ active_class(['analytics/widgets']) }}">
        <a href="{{ url('/analytics/widgets') }}" class="nav-link">
          <i class="link-icon" data-lucide="trello"></i>
          <span class="link-title">Widgeti</span>
        </a>
      </li>
    </ul>
  </div>
</nav>