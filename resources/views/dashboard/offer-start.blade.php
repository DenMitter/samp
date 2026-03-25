@extends('layouts.app', [
    'title' => 'Escrow.com | Запуск сделки',
    'description' => 'Проверка условий офера перед запуском сделки.',
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
    class="mvpReviewMain"
    data-offer-start-page
    data-offer-id="{{ $offerId }}"
    data-dashboard-url="{{ route('dashboard.page') }}"
    data-transaction-base="{{ rtrim(route('transactions.show', ['transaction' => '__KEY__']), '__KEY__') }}"
  >
    <section class="section-container">
      <div class="mvpReviewCard">
        <header class="mvpReviewHeader">
          <h1>Запуск сделки</h1>
        </header>

        <form class="mvpReviewForm" data-offer-start-form novalidate>
          <label class="mvpReviewField is-full">
            <span>Название сделки</span>
            <input type="text" name="title" data-offer-title-input>
            <small data-error-for="title"></small>
          </label>

          <div class="mvpReviewGrid mvpReviewGrid--three">
            <label class="mvpReviewField">
              <span>Моя роль</span>
              <select name="role" data-offer-role-select>
                <option value="buyer">Покупатель</option>
                <option value="seller">Продавец</option>
                <option value="broker">Посредник</option>
              </select>
            </label>

            <label class="mvpReviewField">
              <span>Валюта</span>
              <select name="currency" data-offer-currency-select>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
                <option value="CAD">CAD</option>
              </select>
            </label>

            <label class="mvpReviewField">
              <span>Период проверки (дней)</span>
              <input type="number" name="inspection_period_days" min="1" max="30" data-offer-inspection-input>
            </label>
          </div>

          <section class="mvpReviewSection">
            <div class="mvpReviewSectionHeading">
              <h2>Детали сделки</h2>
            </div>

            <article class="mvpReviewAssetCard">
              <div class="mvpReviewAssetInfo">
                <strong data-offer-item-name>Товар</strong>
                <span data-offer-asset-type>Категория</span>
                <span data-offer-item-description>Описание отсутствует</span>
                <span data-offer-inspection-text>Период проверки: 1 день</span>
              </div>
              <div class="mvpReviewAssetPrice" data-offer-amount>$0.00</div>
            </article>
          </section>

          <section class="mvpReviewSection">
            <div class="mvpReviewSectionHeading">
              <h2>Сводка по сделке</h2>
              <button type="button" class="mvpReviewLink mvpReviewLinkButton" data-open-summary-modal>Как рассчитываются суммы?</button>
            </div>

            <div class="mvpReviewSummary">
              <div><span>Подытог:</span><strong data-summary-subtotal>$0.00</strong></div>
              <div class="mvpReviewSummaryInline">
                <span>Комиссию Escrow оплачивает:</span>
                <select name="fee_paid_by" data-fee-paid-by>
                  <option value="buyer">Покупатель</option>
                  <option value="seller">Продавец</option>
                  <option value="split">Обе стороны</option>
                </select>
              </div>
              <div><span>Комиссия Escrow:</span><strong data-summary-fee>$0.00</strong></div>
              <div class="is-separator"></div>
              <div><span>Итог для покупателя:</span><strong data-summary-buyer>$0.00</strong></div>
              <div><span>Сумма к получению продавцом:</span><strong data-summary-seller>$0.00</strong></div>
            </div>

            <p class="mvpReviewHint">Все суммы указаны в <span data-summary-currency>USD</span>. Налоги и банковские комиссии могут взиматься отдельно.</p>
          </section>

          <section class="mvpReviewSection">
            <div class="mvpReviewSectionHeading">
              <h2>Данные продавца</h2>
            </div>

            <div class="mvpReviewGrid mvpReviewGrid--two">
              <label class="mvpReviewField">
                <span>Email продавца</span>
                <input type="email" name="seller_email" placeholder="seller@example.com" data-seller-email>
                <small data-error-for="seller_email"></small>
              </label>

              <label class="mvpReviewField">
                <span>Телефон продавца</span>
                <input type="tel" name="seller_phone" placeholder="+7 (999) 123-45-67" inputmode="tel" data-seller-phone>
                <small data-error-for="seller_phone"></small>
              </label>
            </div>
          </section>

          <label class="mvpReviewAgreement">
            <input type="checkbox" name="terms_agreed" data-terms-checkbox>
            <span>Я прочитал(а) и принимаю <a href="{{ route('terms.page') }}" target="_blank" rel="noopener noreferrer">условия сделки</a> и <a href="{{ route('privacy.page') }}" target="_blank" rel="noopener noreferrer">политику конфиденциальности</a>.</span>
          </label>

          <p class="mvpStartMessage" data-offer-start-message hidden></p>

          <div class="mvpReviewActions">
            <button type="submit" class="mvpReviewSubmit" data-offer-start-submit disabled>Запустить сделку</button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <div class="mvpModal" data-summary-modal hidden>
    <div class="mvpModal-backdrop" data-close-summary-modal></div>
    <div class="mvpModal-dialog mvpReviewHelpDialog" role="dialog" aria-modal="true" aria-labelledby="summary-help-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-summary-modal>&times;</button>
      <h2 class="mvpReviewHelpTitle" id="summary-help-title">Как рассчитываются суммы?</h2>
      <div class="mvpReviewHelpContent">
        <div>
          <strong>Итог для покупателя:</strong>
          <p>Это ориентировочная сумма, которую покупатель оплачивает с учетом комиссии Escrow и выбранной схемы распределения комиссии.</p>
        </div>
        <div>
          <strong>Сумма к получению продавцом:</strong>
          <p>Это ориентировочная сумма, которую продавец получает после вычета комиссии, если она оплачивается полностью или частично продавцом.</p>
        </div>
      </div>
      <div class="mvpReviewHelpActions">
        <button type="button" class="mvpReviewHelpButton" data-close-summary-modal>Понятно</button>
      </div>
    </div>
  </div>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/offer-start.js') }}"></script>
@endpush
