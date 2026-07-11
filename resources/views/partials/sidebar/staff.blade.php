<li class="sidebar-title">Transaksi</li>

<li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class='sidebar-link'>
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('submissions.create') ? 'active' : '' }}">
    <a href="{{ route('submissions.create') }}" class='sidebar-link'>
        <i class="bi bi-file-earmark-plus-fill"></i>
        <span>Buat Pengajuan</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('submissions.index') ? 'active' : '' }}">
    <a href="{{ route('submissions.index') }}" class='sidebar-link'>
        <i class="bi bi-clock-history"></i>
        <span>Riwayat Pengajuan</span>
    </a>
</li>

