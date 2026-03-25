@extends('layouts.app', [
    'title' => 'Escrow.com | Регистрация',
    'description' => 'Регистрация нового аккаунта Escrow.com',
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
        <h1 class="mvpAuthCard-title">Создайте аккаунт Escrow.com</h1>

        <form class="mvpAuthStandaloneForm" data-signup-form novalidate>
          <label class="mvpAuthStandaloneField">
            <span>Введите ваше имя<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">◯</span>
              <input type="text" name="name" autocomplete="name" required>
            </div>
          </label>

          <label class="mvpAuthStandaloneField">
            <span>Введите ваш email<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">✉</span>
              <input type="email" name="email" autocomplete="email" required>
            </div>
          </label>

          <label class="mvpAuthStandaloneField">
            <span>Придумайте пароль<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">⌂</span>
              <input type="password" name="password" autocomplete="new-password" minlength="8" required>
            </div>
          </label>

          <label class="mvpAuthStandaloneField">
            <span>Повторите пароль<span class="mvpAuthRequired">*</span></span>
            <div class="mvpAuthStandaloneInput">
              <span class="mvpAuthStandaloneIcon">⌂</span>
              <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            </div>
          </label>

          <button type="button" class="mvpAuthPrimaryButton" data-signup-submit>Зарегистрировать аккаунт</button>

          <div class="mvpAuthDivider">- ИЛИ -</div>

          <a href="{{ route('login.page') }}" class="mvpAuthSecondaryButton">Войти в аккаунт</a>
        </form>

        <p class="mvpAuthInlineMessage" data-signup-message hidden></p>
      </div>
    </section>
  </main>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/signup.js') }}"></script>
@endpush
