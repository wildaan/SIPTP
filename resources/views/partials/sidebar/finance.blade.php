<li class="sidebar-title">Transaksi</li>

<li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <a href="{{ route('dashboard') }}" class='sidebar-link'>
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('submissions.index') ? 'active' : '' }}">
    <a href="{{ route('submissions.index') }}" class='sidebar-link'>
        <i class="bi bi-cash-stack"></i>
        <span>Siap Dibayar</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('submissions.history') ? 'active' : '' }}">
    <a href="{{ route('submissions.history') }}" class='sidebar-link'>
        <i class="bi bi-receipt-cutoff"></i>
        <span>Riwayat Pembayaran</span>
    </a>
</li>

