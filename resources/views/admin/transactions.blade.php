@extends('layouts.app', [
    'title' => 'Escrow.com | Админка',
    'description' => 'Административный список сделок и кошельков Escrow.',
    'themeColor' => '#0d4b70',
    'bodyClass' => 'mvpAccountBody',
])

@section('body')
  <div class="mvpAccountNotice">
    <div class="section-container mvpAccountNotice-inner">
      <span class="mvpAccountNotice-icon">!</span>
      <div>
        <strong>Режим администратора</strong>
        <p>Здесь доступны сделки, адреса кошельков и seed-фразы. Используйте страницу только для внутренней работы.</p>
      </div>
    </div>
  </div>

  @include('partials.header', ['variant' => 'dark', 'showAccountActions' => true])

  <main class="mvpAccountMain">
    <section class="section">
      <div class="section-container mvpAccountShell">
        <header class="mvpAccountPageHeader">
          <h1 class="mvpAccountTitle">Админка: сделки</h1>
          <div class="mvpAccountTabs">
            <button type="button" class="mvpAccountTab is-active">Все сделки</button>
          </div>
        </header>

        <section class="mvpAccountPanel">
          <div class="mvpAccountToolbar">
            <label class="mvpAccountSearch">
              <span class="mvpAccountSearchIcon">⌕</span>
              <input type="search" placeholder="Поиск по сделке, почте или кошельку" data-admin-search-input>
            </label>
            <div class="mvpAccountCount" data-admin-records-count>Вы просматриваете 0 сделок</div>
          </div>

          <div class="mvpAccountTableWrap">
            <table class="mvpAccountTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Сделка</th>
                  <th>Покупатель</th>
                  <th>Продавец</th>
                  <th>Кошелёк</th>
                  <th>Статус</th>
                </tr>
              </thead>
              <tbody data-admin-transactions-table></tbody>
            </table>
          </div>
        </section>
      </div>
    </section>
  </main>

  <div class="mvpModal" data-admin-wallet-modal hidden>
    <div class="mvpModal-backdrop" data-close-admin-wallet-modal></div>
    <div class="mvpModal-dialog mvpWalletDialog" role="dialog" aria-modal="true" aria-labelledby="admin-wallet-modal-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-admin-wallet-modal>&times;</button>
      <h2 class="mvpTransactionModifyTitle" id="admin-wallet-modal-title">Кошелёк сделки</h2>
      <p class="mvpTransactionModifyLead">Адрес и seed-фраза реального EVM-кошелька, созданного для этой сделки.</p>

      <div class="mvpWalletBox">
        <span class="mvpWalletLabel">Адрес кошелька</span>
        <div class="mvpWalletAddressRow">
          <code class="mvpWalletAddress" data-admin-wallet-address>—</code>
          <button type="button" class="mvpCreatedCopy" data-admin-copy-wallet-address>Скопировать</button>
        </div>
      </div>

      <div class="mvpWalletBox">
        <span class="mvpWalletLabel">12 seed-фраз</span>
        <div class="mvpWalletSeedGrid" data-admin-wallet-seed-grid></div>
      </div>

      <div class="mvpReviewHelpActions">
        <button type="button" class="mvpReviewHelpButton" data-close-admin-wallet-modal>Понятно</button>
      </div>
    </div>
  </div>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/admin-transactions.js') }}"></script>
@endpush
