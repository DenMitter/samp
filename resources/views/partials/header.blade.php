@php
    $variant = $variant ?? 'light';
    $isDark = $variant === 'dark';
    $showAccountActions = $showAccountActions ?? false;
@endphp

<header class="mvpHeader mvpHeader--{{ $variant }}">
  <div class="section-container mvpHeader-inner">
    <a href="{{ route('home') }}#top" class="mvpHeader-brand">Escrow.com</a>

    <button
      type="button"
      class="mvpHeader-toggle"
      aria-label="Открыть меню"
      aria-expanded="false"
      aria-controls="site-navigation"
      data-header-toggle
    >
      <span>Меню</span>
    </button>

    <div class="mvpHeader-panel" id="site-navigation" data-header-panel>
      <nav class="mvpHeader-nav" aria-label="Основная навигация">
        <a href="{{ route('home') }}#how-it-works">Как это работает</a>
        <a href="{{ route('home') }}#services">Сделки</a>
        <a href="{{ route('home') }}#offer">Оферы</a>
        <a href="{{ route('home') }}#pay">Оплата</a>
        <a href="{{ route('home') }}#licensing">Доверие</a>
      </nav>

      @if ($isDark)
        <div class="mvpHeader-actions mvpHeader-actions--user">
          @if (auth()->user()?->is_admin)
            <a href="{{ route('admin.transactions.page') }}" class="mvpHeader-link" data-admin-link>Админка</a>
          @endif
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mvpHeader-logout">Выйти</button>
          </form>
        </div>

        @if ($showAccountActions)
          <div class="mvpAccountActions">
            <a href="{{ route('offers.create') }}" class="mvpAccountCta">Начать новую сделку</a>
            <a href="{{ route('dashboard.page') }}" class="mvpAccountAvatar" aria-label="Личный кабинет">
              <span data-user-initial>U</span>
            </a>
          </div>
        @endif
      @else
        @guest
          <div class="mvpHeader-actions">
            <a href="{{ route('login.page') }}" class="mvpHeader-link">Вход</a>
            <a href="{{ route('signup.page') }}" class="mvpHeader-link mvpHeader-link--strong">Регистрация →</a>
          </div>
        @endguest

        @auth
          <div class="mvpHeader-actions mvpHeader-actions--user">
            @if (auth()->user()?->is_admin)
              <a href="{{ route('admin.transactions.page') }}" class="mvpHeader-link" data-admin-link>Админка</a>
            @endif
            <span class="mvpHeader-user">{{ auth()->user()->name }}</span>
            <a href="{{ route('dashboard.page') }}" class="mvpAccountAvatar mvpAccountAvatar--header" aria-label="Личный кабинет">
              <span>{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="mvpHeader-logout">Выйти</button>
            </form>
          </div>
        @endauth
      @endif
    </div>
  </div>
</header>
