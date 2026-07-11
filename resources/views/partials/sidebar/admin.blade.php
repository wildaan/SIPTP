<li class="sidebar-title">Pengaturan</li>

<li class="sidebar-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
    <a href="{{ route('users.index') }}" class='sidebar-link'>
        <i class="bi bi-people-fill"></i>
        <span>Kelola Pengguna</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('audit-trail.*') ? 'active' : '' }}">
    <a href="{{ route('audit-trail.index') }}" class='sidebar-link'>
        <i class="bi bi-journal-text"></i>
        <span>Audit Trail</span>
    </a>
</li>
