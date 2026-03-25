@extends('layouts.app', [
    'title' => 'Escrow.com | Вход',
    'description' => 'Вход в аккаунт Escrow.com',
    'themeColor' => '#ffffff',
    'bodyClass' => 'mvpAuthPage',
    'lang' => 'ru',
    'locale' => 'ru',
])

@section('body')
  @include('partials.header', ['variant' => 'light'])

  <main class="mvpAuthMain">
    <section class="section-container">
      <div class="mvpAuthCard">
        <h1 class="mvpAuthCard-title">Вход в Escrow.com</h1>

        <form class="mvpAuthStandaloneForm" data-login-form novalidate>
          <label class="mvpAuthStandaloneField">
            <span>Введите ваш email<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">✉</span>
              <input type="email" name="email" autocomplete="email" required>
            </div>
          </label>

          <label class="mvpAuthStandaloneField">
            <span>Введите ваш пароль<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">⌂</span>
              <input type="password" name="password" autocomplete="current-password" required>
            </div>
          </label>

          <button type="button" class="mvpAuthPrimaryButton" data-login-submit>Войти безопасно</button>

          <a href="#" class="mvpAuthRecover">Восстановить пароль</a>

          <div class="mvpAuthDivider">- ИЛИ -</div>

          <a href="{{ route('signup.page') }}" class="mvpAuthSecondaryButton">Зарегистрировать аккаунт</a>
        </form>

        <p class="mvpAuthInlineMessage" data-login-message hidden></p>
      </div>
    </section>
  </main>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/login.js') }}"></script>
@endpush
