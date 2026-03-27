<!doctype html>
<html lang="{{ $lang ?? 'ru' }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $themeColor ?? '#01426a' }}">
    <meta name="description" content="{{ $description ?? 'Escrow.com frontend' }}">
    <title>{{ $title ?? 'Escrow.com' }}</title>
    <link rel="stylesheet" href="https://www.escrow.com/build/css/styles.c92d1ca52fd819967d36.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16">
  </head>
  <body
    class="{{ trim('mvp-body '.($bodyClass ?? '')) }}"
    data-locale="{{ $locale ?? 'ru' }}"
    data-theme="{{ $theme ?? 'light' }}"
    data-api-base="{{ url('/api') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-home-url="{{ route('home') }}"
    data-login-url="{{ route('login.page') }}"
    data-signup-url="{{ route('signup.page') }}"
    data-dashboard-url="{{ route('dashboard.page') }}"
    data-create-offer-url="{{ route('offers.create') }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-auth-user-id="{{ auth()->id() }}"
    data-auth-user-admin="{{ auth()->user()?->is_admin ? '1' : '0' }}"
    data-support-email="support@escrow.local"
    data-support-phone="+1-415-801-2270"
  >
    @yield('body')
    <div class="mvpSupportChat" data-support-chat hidden>
      <div class="mvpSupportChat-backdrop" data-support-chat-close></div>
      <div class="mvpSupportChat-dialog" role="dialog" aria-modal="true" aria-labelledby="support-chat-title">
        <div class="mvpSupportChat-header">
          <div>
            <strong id="support-chat-title">Техподдержка</strong>
            <span>Онлайн-чат по сделкам, оплате и кошельку</span>
          </div>
          <button type="button" class="mvpSupportChat-close" aria-label="Закрыть" data-support-chat-close>&times;</button>
        </div>
        <div class="mvpSupportChat-messages" data-support-chat-messages></div>
        <div class="mvpSupportChat-contacts">
          <a href="mailto:support@escrow.local" data-support-chat-email>support@escrow.local</a>
          <a href="tel:+14158012270" data-support-chat-phone>+1-415-801-2270</a>
        </div>
        <form class="mvpSupportChat-form" data-support-chat-form>
          <textarea rows="3" placeholder="Напишите ваш вопрос..." data-support-chat-input></textarea>
          <button type="submit">Отправить</button>
        </form>
      </div>
    </div>
    <script src="{{ asset('js/header-menu.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/support-chat.js') }}"></script>
  </body>
</html>
