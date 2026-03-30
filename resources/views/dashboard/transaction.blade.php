@extends('layouts.app', [
    'title' => 'Escrow.com | Детали сделки',
    'description' => 'Детали escrow-сделки, история, статусы и действия.',
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
    data-transaction-page
    data-transaction-id="{{ $transactionId }}"
    data-transaction-key="{{ $transactionKey ?? '' }}"
    data-transaction-payment-url="{{ route('transactions.payment', ['transaction' => $transactionKey ?? $transactionId]) }}"
    data-dashboard-url="{{ route('dashboard.page') }}"
  >
    <section class="section">
      <div class="section-container mvpTransactionLayout">
        <div class="mvpTransactionMain">
          <article class="mvpTransactionCard">
            <header class="mvpTransactionHeader">
              <div>
                <h1 class="mvpTransactionTitle" data-transaction-title>Сделка</h1>
                <p class="mvpTransactionRef" data-transaction-reference>Сделка #—</p>
                <p class="mvpTransactionLead" data-transaction-summary>Загрузка данных сделки...</p>
              </div>
              <span class="mvpTransactionBadge mvpAccountBadge mvpAccountBadge--yellow" data-transaction-status>Ожидает согласования</span>
            </header>

            <section class="mvpTransactionPartyCard">
              <h2 class="mvpTransactionPartyTitle">Участники сделки</h2>
              <div class="mvpTransactionPartyGrid">
                <div class="mvpTransactionPartyItem">
                  <span class="mvpTransactionPartyLabel">Покупатель</span>
                  <strong data-transaction-buyer>—</strong>
                </div>
                <div class="mvpTransactionPartyItem">
                  <span class="mvpTransactionPartyLabel">Продавец</span>
                  <strong data-transaction-seller>—</strong>
                </div>
              </div>
            </section>

            <div class="mvpTransactionProgress">
              <div class="mvpTransactionTimeline">
                <div class="mvpTransactionStep is-active" data-step="1">
                  <span>1</span>
                  <strong>Согласование</strong>
                </div>
                <div class="mvpTransactionStep" data-step="2">
                  <span>2</span>
                  <strong>Оплата</strong>
                </div>
                <div class="mvpTransactionStep" data-step="3">
                  <span>3</span>
                  <strong>Подтверждение оплаты</strong>
                </div>
                <div class="mvpTransactionStep" data-step="4">
                  <span>4</span>
                  <strong>Проверка</strong>
                </div>
                <div class="mvpTransactionStep" data-step="5">
                  <span>5</span>
                  <strong>Закрытие</strong>
                </div>
              </div>
            </div>

            <section class="mvpTransactionActionBox">
              <div>
                <h2 data-action-title>Проверьте условия сделки</h2>
                <p data-action-text>После согласования условий и оплаты сделка будет переведена на следующий этап.</p>
              </div>
              <div class="mvpTransactionActionButtons">
                <button type="button" class="mvpTransactionPrimaryAction" data-primary-action>Согласиться со сделкой</button>
                <button type="button" class="mvpTransactionSecondaryAction" data-open-modify-modal>Изменить офер</button>
              </div>
            </section>
          </article>

          <article class="mvpTransactionCard">
            <h2 class="mvpTransactionSectionTitle">Детали предмета сделки</h2>
            <div class="mvpTransactionDetailsGrid">
              <div class="mvpTransactionDetailsText" data-item-details>
                Название и описание сделки появятся после загрузки.
              </div>
              <div class="mvpTransactionTotals">
                <div><span>Сумма сделки</span><strong data-total-subtotal>$0.00</strong></div>
                <div data-total-fee-row><span>Комиссия escrow</span><strong data-total-fee>$50.00</strong></div>
                <div class="is-total"><span>Итого</span><strong data-total-grand>$0.00</strong></div>
              </div>
            </div>
          </article>

          <article class="mvpTransactionCard" data-payment-fees-card>
            <h2 class="mvpTransactionSectionTitle">Комиссии за обработку платежа</h2>
            <div class="mvpTransactionFeeList">
              <div><span>Международный банковский перевод</span><strong>+$25.00</strong></div>
              <div><span>Статус оплаты</span><strong data-payment-status>Не оплачено</strong></div>
            </div>
          </article>

          <button type="button" class="mvpTransactionCancel" data-cancel-transaction>Отменить сделку</button>
        </div>

        <aside class="mvpTransactionSidebar">
          <article class="mvpTransactionSideCard">
            <h2>История</h2>
            <div class="mvpTransactionHistory" data-transaction-history>
              <p>История загружается...</p>
            </div>
          </article>

          <article class="mvpTransactionSideCard">
            <h2>Частые вопросы</h2>
            <div class="mvpTransactionFaq">
              <details>
                <summary>Как работает онлайн-escrow?</summary>
                <p>Escrow удерживает деньги до тех пор, пока обе стороны не выполнят условия сделки.</p>
              </details>
              <details>
                <summary>Как сервис защищает меня?</summary>
                <p>Покупатель не переводит деньги напрямую продавцу, а продавец получает оплату только после подтверждения сделки.</p>
              </details>
              <details>
                <summary>Как изменить условия сделки?</summary>
                <p>Если этап допускает изменения, вы можете вернуться в кабинет и обновить параметры сделки до оплаты.</p>
              </details>
              <a href="{{ route('home') }}#licensing">Нужна дополнительная помощь?</a>
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

  <div class="mvpModal" data-transaction-modify-modal hidden>
    <div class="mvpModal-backdrop" data-close-modify-modal></div>
    <div class="mvpModal-dialog mvpTransactionModifyDialog" role="dialog" aria-modal="true" aria-labelledby="transaction-modify-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-modify-modal>&times;</button>
      <h2 class="mvpTransactionModifyTitle" id="transaction-modify-title">Изменить условия офера</h2>
      <p class="mvpTransactionModifyLead">После подтверждения мы сохраним обновлённые условия и покажем их второй стороне для повторного согласования.</p>

      <form class="mvpTransactionModifyForm" data-transaction-modify-form novalidate>
        <label class="mvpReviewField">
          <span>Период проверки (дней)</span>
          <input type="number" name="inspection_period_days" min="1" max="30" data-modify-inspection>
          <small data-modify-error-for="inspection_period_days"></small>
        </label>

        <label class="mvpReviewField">
          <span>Комиссию Escrow оплачивает</span>
          <select name="fee_paid_by" data-modify-fee-paid-by>
            <option value="buyer">Покупатель</option>
            <option value="seller">Продавец</option>
            <option value="split">Обе стороны</option>
          </select>
          <small data-modify-error-for="fee_paid_by"></small>
        </label>

        <label class="mvpReviewField">
          <span>Способ доставки</span>
          <select name="shipping_method" data-modify-shipping-method>
            <option value="cargo_shipping">Грузовая доставка</option>
            <option value="pickup">Самовывоз</option>
            <option value="courier">Курьерская доставка</option>
            <option value="digital_transfer">Цифровая передача</option>
          </select>
          <small data-modify-error-for="shipping_method"></small>
        </label>

        <label class="mvpReviewField">
          <span>Доставку оплачивает</span>
          <select name="shipping_paid_by" data-modify-shipping-paid-by>
            <option value="seller">Продавец</option>
            <option value="buyer">Покупатель</option>
            <option value="split">Обе стороны</option>
          </select>
          <small data-modify-error-for="shipping_paid_by"></small>
        </label>

        <label class="mvpReviewField">
          <span>Причина изменения</span>
          <textarea name="modification_reason" rows="3" placeholder="Кратко опишите, что именно меняется." data-modify-reason></textarea>
          <small data-modify-error-for="modification_reason"></small>
        </label>

        <p class="mvpStartMessage" data-transaction-modify-message hidden></p>

        <div class="mvpTransactionModifyActions">
          <button type="button" class="mvpTransactionSecondaryAction" data-close-modify-modal>Отмена</button>
          <button type="submit" class="mvpTransactionPrimaryAction" data-confirm-modify>Подтвердить изменения</button>
        </div>
      </form>
    </div>
  </div>

  <div class="mvpModal" data-payment-modal hidden>
    <div class="mvpModal-backdrop" data-close-payment-modal></div>
    <div class="mvpModal-dialog mvpTransactionModifyDialog" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-payment-modal>&times;</button>
      <h2 class="mvpTransactionModifyTitle" id="payment-modal-title">Выберите способ оплаты</h2>
      <p class="mvpTransactionModifyLead">Выберите способ оплаты и подтвердите перевод средств в Escrow. После этого сделка перейдёт к следующему этапу.</p>

      <form class="mvpTransactionModifyForm" data-payment-form novalidate>
        <label class="mvpReviewField">
          <span>Способ оплаты</span>
          <select name="provider">
            <option value="wire_transfer">Банковский перевод</option>
            <option value="card">Банковская карта</option>
            <option value="swift">SWIFT</option>
            <option value="crypto">Криптовалюта</option>
          </select>
        </label>

        <label class="mvpReviewField">
          <span>Сумма к оплате</span>
          <input type="text" name="amount_preview" data-payment-amount-preview readonly>
        </label>

        <label class="mvpReviewField">
          <span>Комментарий к платежу</span>
          <textarea name="external_reference" rows="3" placeholder="Например: счёт, банк, последние 4 цифры карты."></textarea>
        </label>

        <p class="mvpStartMessage" data-payment-message hidden></p>

        <div class="mvpTransactionModifyActions">
          <button type="button" class="mvpTransactionSecondaryAction" data-close-payment-modal>Отмена</button>
          <button type="submit" class="mvpTransactionPrimaryAction" data-payment-submit>Подтвердить оплату</button>
        </div>
      </form>
    </div>
  </div>

  <div class="mvpModal" data-disbursement-modal hidden>
    <div class="mvpModal-backdrop" data-close-disbursement-modal></div>
    <div class="mvpModal-dialog mvpTransactionModifyDialog" role="dialog" aria-modal="true" aria-labelledby="disbursement-modal-title">
      <button type="button" class="mvpModal-close" aria-label="Закрыть" data-close-disbursement-modal>&times;</button>
      <h2 class="mvpTransactionModifyTitle" id="disbursement-modal-title">Выберите способ получения выплаты</h2>
      <p class="mvpTransactionModifyLead">Укажите, как Escrow должен перевести вам средства после завершения сделки. Покупатель увидит, что способ выплаты уже сохранён.</p>

      <form class="mvpTransactionModifyForm" data-disbursement-form novalidate>
        <label class="mvpReviewField">
          <span>Способ выплаты</span>
          <select name="disbursement_method">
            <option value="bank_transfer">Банковский перевод</option>
            <option value="paypal">PayPal</option>
            <option value="wise">Wise</option>
            <option value="crypto_wallet">Криптокошелёк</option>
          </select>
        </label>

        <label class="mvpReviewField">
          <span>Реквизиты / аккаунт</span>
          <textarea name="disbursement_details" rows="3" placeholder="Укажите счёт, email PayPal, wallet address или другие реквизиты."></textarea>
        </label>

        <p class="mvpStartMessage" data-disbursement-message hidden></p>

        <div class="mvpTransactionModifyActions">
          <button type="button" class="mvpTransactionSecondaryAction" data-close-disbursement-modal>Отмена</button>
          <button type="submit" class="mvpTransactionPrimaryAction" data-disbursement-submit>Сохранить способ выплаты</button>
        </div>
      </form>
    </div>
  </div>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/transaction.js') }}?v={{ filemtime(public_path('js/transaction.js')) }}"></script>
@endpush
