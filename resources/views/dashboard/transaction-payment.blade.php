@extends('layouts.app', [
    'title' => 'Escrow.com | Оплата сделки',
    'description' => 'Выбор способа оплаты для escrow-сделки.',
    'themeColor' => '#0d4b70',
    'bodyClass' => 'mvpAccountBody',
])

@section('body')
  <header class="mvpCreatedHeader">
    <div class="section-container mvpCreatedHeaderInner">
      <a href="{{ route('home') }}#top" class="mvpCreatedBrand">Escrow.com</a>
      <a href="{{ route('help.page') }}" class="mvpCreatedHelp" data-support-chat-open>Помощь и вопросы</a>
    </div>
  </header>

  <main
    class="mvpPayMain"
    data-transaction-payment-page
    data-transaction-id="{{ $transactionId }}"
    data-transaction-key="{{ $transactionKey }}"
    data-transaction-url="{{ route('transactions.show', ['transaction' => $transactionKey]) }}"
  >
    <section class="section-container">
      <div class="mvpPayCard">
        <div class="mvpPayTop">
          <div class="mvpPayAsset">
            <strong data-pay-item-name>Предмет сделки</strong>
            <span data-pay-item-description>Описание загружается...</span>
            <span data-pay-inspection>Период проверки: 1 день</span>
            <span data-pay-fee-payer>Комиссию Escrow оплачивает: покупатель</span>
            <span data-pay-vin hidden></span>
            <span data-pay-odometer hidden></span>
          </div>

          <div class="mvpPayTotals">
            <div class="mvpPayTotalLine mvpPayTotalLine--grand"><span>Итого</span><strong data-pay-total>$0.00 USD</strong></div>
            <div class="mvpPayTotalLine"><span>Сумма сделки</span><strong data-pay-subtotal>$0.00 USD</strong></div>
            <div class="mvpPayTotalLine"><span>Комиссия Escrow</span><strong data-pay-fee>$0.00 USD</strong></div>
            <div class="mvpPayTotalLine"><span>Комиссия обработки</span><strong data-pay-processing>$25.00 USD</strong></div>
          </div>
        </div>

        <div class="mvpPayProgress">
          <div class="mvpPayStep is-active">
            <span>1</span>
            <strong>Оплата</strong>
          </div>
          <div class="mvpPayStep">
            <span>2</span>
            <strong>Escrow</strong>
          </div>
          <div class="mvpPayStep">
            <span>3</span>
            <strong>Подтверждение</strong>
          </div>
        </div>

        <form class="mvpPayForm" data-payment-page-form novalidate>
          <h1>Как вы хотите оплатить?</h1>

          <label class="mvpPayOption">
            <input type="radio" name="provider" value="escrow_wallet" checked>
            <span class="mvpPayOptionIcon">👛</span>
            <span class="mvpPayOptionBody">
              <strong>Кошелёк Escrow</strong>
              <small>Создайте наш кошелёк, отправьте на него нужную сумму в USDT и нажмите «Продолжить оплату» для on-chain проверки поступления средств.</small>
            </span>
            <button type="button" class="mvpPayWalletButton" data-create-wallet>Создать кошелёк</button>
            <span class="mvpPayOptionCheck" aria-hidden="true"></span>
          </label>

          <p class="mvpStartMessage" data-payment-page-message hidden></p>

          <div class="mvpPayActions">
            <button type="submit" class="mvpCreatedButton" data-payment-page-submit>Продолжить оплату</button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <footer class="mvpPayFooter">
    <a href="{{ route('privacy.page') }}" target="_blank" rel="noopener noreferrer">Политика конфиденциальности</a>
    <a href="{{ route('terms.page') }}" target="_blank" rel="noopener noreferrer">Условия сделки</a>
    <a href="{{ route('home') }}#licensing">Лицензии и соответствие</a>
  </footer>

  <div class="mvpModal" data-wallet-modal hidden>
    <div class="mvpModal-backdrop" data-close-wallet-modal></div>
    <div class="mvpModal-dialog mvpWalletDialog" role="dialog" aria-modal="true" aria-labelledby="wallet-modal-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-wallet-modal>&times;</button>
      <h2 class="mvpTransactionModifyTitle" id="wallet-modal-title">Кошелёк Escrow</h2>
      <p class="mvpTransactionModifyLead">Ниже отображается адрес нашего кошелька и 12 seed-фраз. Сохраните их и используйте этот адрес для оплаты.</p>

      <div class="mvpWalletBox">
        <span class="mvpWalletLabel">Адрес кошелька</span>
        <div class="mvpWalletAddressRow">
          <code class="mvpWalletAddress" data-wallet-address>—</code>
          <button type="button" class="mvpCreatedCopy" data-copy-wallet-address>Скопировать</button>
        </div>
      </div>

      <div class="mvpWalletBox">
        <span class="mvpWalletLabel">12 seed-фраз</span>
        <div class="mvpWalletSeedGrid" data-wallet-seed-grid></div>
      </div>

      <div class="mvpReviewHelpActions">
        <button type="button" class="mvpReviewHelpButton" data-close-wallet-modal>Понятно</button>
      </div>
    </div>
  </div>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/transaction-payment.js') }}"></script>
@endpush
