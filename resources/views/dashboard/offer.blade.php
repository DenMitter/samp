@extends('layouts.app', [
    'title' => 'Escrow.com | Детали офера',
    'description' => 'Детали офера, условия сделки и действия по согласованию.',
    'themeColor' => '#0d4b70',
    'bodyClass' => 'mvpAccountBody',
])

@section('body')
  <div class="mvpAccountNotice">
    <div class="section-container mvpAccountNotice-inner">
      <span class="mvpAccountNotice-icon">!</span>
      <div>
        <strong>Пожалуйста, подтвердите email адрес</strong>
        <p>Подтвердите email, чтобы закрепить аккаунт за собой. Если письмо не пришло, его можно отправить повторно позже.</p>
      </div>
    </div>
  </div>

  @include('partials.header', ['variant' => 'dark', 'showAccountActions' => true])

  <main
    class="mvpAccountMain"
    data-offer-page
    data-offer-id="{{ $offerId }}"
    data-dashboard-url="{{ route('dashboard.page') }}"
  >
    <section class="section">
      <div class="section-container mvpTransactionLayout">
        <div class="mvpTransactionMain">
          <article class="mvpTransactionCard">
            <header class="mvpTransactionHeader">
              <div>
                <h1 class="mvpTransactionTitle" data-offer-title>Офер</h1>
                <p class="mvpTransactionRef" data-offer-reference>Офер #—</p>
                <p class="mvpTransactionLead" data-offer-summary>Загрузка данных офера...</p>
              </div>
              <span class="mvpTransactionBadge mvpAccountBadge mvpAccountBadge--yellow" data-offer-status>Черновик</span>
            </header>

            <section class="mvpTransactionActionBox">
              <div>
                <h2 data-offer-action-title>Проверьте условия офера</h2>
                <p data-offer-action-text>Вы можете согласовать этот офер и создать транзакцию на его основе.</p>
              </div>
              <div class="mvpTransactionActionButtons">
                <button type="button" class="mvpTransactionPrimaryAction" data-offer-primary-action>Принять офер</button>
                <a href="{{ route('dashboard.page') }}" class="mvpTransactionSecondaryAction">Вернуться в кабинет</a>
              </div>
            </section>
          </article>

          <article class="mvpTransactionCard">
            <h2 class="mvpTransactionSectionTitle">Условия офера</h2>
            <div class="mvpTransactionDetailsGrid">
              <div class="mvpTransactionDetailsText" data-offer-details>
                Описание офера загружается...
              </div>
              <div class="mvpTransactionTotals">
                <div><span>Сумма офера</span><strong data-offer-subtotal>$0.00</strong></div>
                <div><span>Валюта</span><strong data-offer-currency>USD</strong></div>
                <div class="is-total"><span>Тип актива</span><strong data-offer-asset>general</strong></div>
              </div>
            </div>
          </article>
        </div>

        <aside class="mvpTransactionSidebar">
          <article class="mvpTransactionSideCard">
            <h2>История офера</h2>
            <div class="mvpTransactionHistory" data-offer-history>
              <p>История загружается...</p>
            </div>
          </article>

          <article class="mvpTransactionSideCard">
            <h2>Следующий шаг</h2>
            <div class="mvpTransactionFaq">
              <p>После принятия офера автоматически создастся транзакция, и вы перейдете на экран сделки.</p>
              <a href="{{ route('home') }}#offer">Как работают оферы?</a>
            </div>
          </article>
        </aside>
      </div>
    </section>
  </main>

  <footer class="mvpAccountFooter">
    <div class="section-container mvpAccountFooterGrid">
      <div>
        <h3>Язык</h3>
        <div class="mvpAccountLanguage">Русский</div>
      </div>
      <div>
        <h3>Escrow Services</h3>
        <a href="{{ route('home') }}#services">Vehicle Escrow</a>
        <a href="{{ route('home') }}#services">Domain and Website Escrow</a>
        <a href="{{ route('home') }}#services">General Merchandise Escrow</a>
      </div>
      <div>
        <h3>Support</h3>
        <a href="{{ route('home') }}#pay">Fee Calculator</a>
        <a href="{{ route('home') }}#pay">Payment Options</a>
        <a href="{{ route('home') }}#licensing">Security</a>
      </div>
      <div>
        <h3>Company</h3>
        <a href="{{ route('home') }}#top">About Us</a>
        <a href="{{ route('home') }}#offer">Get Started</a>
        <a href="mailto:hello@example.com">Contact Us</a>
      </div>
    </div>
  </footer>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/offer.js') }}"></script>
@endpush
