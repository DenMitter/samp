@extends('layouts.app', [
    'title' => 'Escrow.com | Личный кабинет',
    'description' => 'Личный кабинет Escrow.com: сделки, оферы и платежи.',
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

  <main class="mvpAccountMain">
    <section class="section">
      <div class="section-container mvpAccountShell">
        <header class="mvpAccountPageHeader">
          <h1 class="mvpAccountTitle">Мои сделки</h1>
          <div class="mvpAccountTabs" role="tablist" aria-label="Фильтр сделок">
            <button type="button" class="mvpAccountTab" data-filter-tab="all">Все</button>
            <button type="button" class="mvpAccountTab is-active" data-filter-tab="action">Требуют действия</button>
            <button type="button" class="mvpAccountTab" data-filter-tab="open">Открытые</button>
            <button type="button" class="mvpAccountTab" data-filter-tab="closed">Закрытые</button>
          </div>
        </header>

        <section class="mvpAccountPanel" id="transactions">
          <div class="mvpAccountToolbar">
            <label class="mvpAccountSearch">
              <span class="mvpAccountSearchIcon">⌕</span>
              <input type="search" placeholder="Поиск по сделке" data-search-input>
            </label>
            <button type="button" class="mvpAccountFilterButton">Фильтр</button>
            <div class="mvpAccountCount" data-records-count>Вы просматриваете 0 сделок</div>
          </div>

          <div class="mvpAccountTableWrap">
            <table class="mvpAccountTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Название сделки</th>
                  <th>Создано</th>
                  <th>Сумма</th>
                  <th>Роль</th>
                  <th>Статус</th>
                </tr>
              </thead>
              <tbody data-transactions-table></tbody>
            </table>
          </div>
        </section>

        <section class="mvpAccountSummaryGrid" id="offers">
          <article class="mvpAccountSummaryCard">
            <span class="mvpAccountSummaryLabel">Оферы</span>
            <strong class="mvpAccountSummaryValue" data-stat="offers_total">0</strong>
          </article>
          <article class="mvpAccountSummaryCard">
            <span class="mvpAccountSummaryLabel">Транзакции</span>
            <strong class="mvpAccountSummaryValue" data-stat="transactions_total">0</strong>
          </article>
          <article class="mvpAccountSummaryCard" id="payments">
            <span class="mvpAccountSummaryLabel">Платежи</span>
            <strong class="mvpAccountSummaryValue" data-stat="payments_total">0</strong>
          </article>
        </section>
      </div>
    </section>
  </main>

  <footer class="mvpAccountFooter" id="support">
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
  <script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>
@endpush
