@extends('layouts.app', [
    'title' => 'Escrow.com | Транзакция создана',
    'description' => 'Экран подтверждения после создания сделки.',
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

  <header class="mvpCreatedHeader">
    <div class="section-container mvpCreatedHeaderInner">
      <a href="{{ route('home') }}#top" class="mvpCreatedBrand">Escrow.com</a>
      <a href="{{ route('help.page') }}" class="mvpCreatedHelp" data-support-chat-open>Помощь и вопросы</a>
    </div>
  </header>

  <main
    class="mvpCreatedMain"
    data-transaction-created-page
    data-transaction-id="{{ $transactionId }}"
    data-transaction-key="{{ $transactionKey ?? '' }}"
    data-transaction-base="{{ rtrim(route('transactions.show', ['transaction' => '__KEY__']), '__KEY__') }}"
    data-dashboard-url="{{ route('dashboard.page') }}"
  >
    <section class="section-container">
      <div class="mvpCreatedCard">
        <h1 class="mvpCreatedTitle">Транзакция создана!</h1>
        <p class="mvpCreatedLead">Транзакция создана и ожидает подтверждения обеих сторон. Поделитесь ссылкой или QR-кодом, чтобы продавец смог открыть сделку и принять условия.</p>

        <div class="mvpCreatedQrWrap">
          <img src="" alt="QR-код для ссылки на транзакцию" class="mvpCreatedQr" data-created-qr>
        </div>

        <div class="mvpCreatedShare">
          <p class="mvpCreatedShareLabel">Поделиться</p>
          <div class="mvpCreatedShareGrid">
            <a href="#" class="mvpCreatedShareItem mvpCreatedShareItem--wa" data-share-whatsapp>
              <span class="mvpCreatedShareIcon">◔</span>
              <span>WhatsApp</span>
            </a>
            <a href="#" class="mvpCreatedShareItem mvpCreatedShareItem--email" data-share-email>
              <span class="mvpCreatedShareIcon">✉</span>
              <span>Email</span>
            </a>
            <a href="#" class="mvpCreatedShareItem mvpCreatedShareItem--messenger" data-share-messenger>
              <span class="mvpCreatedShareIcon">⌁</span>
              <span>Messenger</span>
            </a>
            <a href="#" class="mvpCreatedShareItem mvpCreatedShareItem--twitter" data-share-twitter>
              <span class="mvpCreatedShareIcon">t</span>
              <span>Twitter</span>
            </a>
          </div>
        </div>

        <div class="mvpCreatedUrlBlock">
          <p class="mvpCreatedUrlLabel">Ссылка</p>
          <div class="mvpCreatedUrlRow">
            <div class="mvpCreatedUrlValue" data-created-link-text>Загрузка...</div>
            <button type="button" class="mvpCreatedCopy" data-copy-link>Скопировать ссылку</button>
          </div>
        </div>

        <div class="mvpCreatedActions">
          <a href="#" class="mvpCreatedButton" data-view-transaction>Открыть транзакцию</a>
        </div>
      </div>
    </section>
  </main>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/transaction-created.js') }}?v={{ filemtime(public_path('js/transaction-created.js')) }}"></script>
@endpush
