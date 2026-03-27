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
          <a href="{{ route('admin.transactions.page') }}" class="mvpHeader-link" data-admin-link hidden>Админка</a>
          <button type="button" class="mvpHeader-logout" data-logout>Выйти</button>
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
        <div class="mvpHeader-actions" data-auth-guest>
          <a href="{{ route('login.page') }}" class="mvpHeader-link">Вход</a>
          <a href="{{ route('signup.page') }}" class="mvpHeader-link mvpHeader-link--strong">Регистрация →</a>
        </div>

        <div class="mvpHeader-actions mvpHeader-actions--user" data-auth-user hidden>
          <a href="{{ route('admin.transactions.page') }}" class="mvpHeader-link" data-admin-link hidden>Админка</a>
          <span class="mvpHeader-user" data-user-name></span>
          <a href="{{ route('dashboard.page') }}" class="mvpAccountAvatar mvpAccountAvatar--header" aria-label="Личный кабинет">
            <span data-user-initial>U</span>
          </a>
          <button type="button" class="mvpHeader-logout" data-logout>Выйти</button>
        </div>
      @endif
    </div>
  </div>
</header>
