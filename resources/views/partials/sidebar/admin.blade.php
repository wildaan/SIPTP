<li class="sidebar-title">Budget & Kategori</li>

<li class="sidebar-item {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
    <a href="{{ route('budgets.index') }}" class='sidebar-link'>
        <i class="bi bi-cash-stack"></i>
        <span>Budget</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
    <a href="{{ route('categories.index') }}" class='sidebar-link'>
        <i class="bi bi-tags-fill"></i>
        <span>Kategori</span>
    </a>
</li>

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
