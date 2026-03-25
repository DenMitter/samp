@extends('layouts.app', [
    'title' => 'Escrow.com | Помощь и вопросы',
    'description' => 'Ответы на частые вопросы по сделкам, оплате и работе Escrow.',
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

  <main class="mvpCreatedMain">
    <section class="section-container">
      <div class="mvpCreatedCard mvpHelpCard">
        <div class="mvpHelpIntro">
          <h1>Помощь и вопросы</h1>
          <p>Здесь собраны основные ответы по созданию сделки, оплате, подтверждению условий и безопасности расчётов.</p>
        </div>

        <div class="mvpHelpGrid">
          <article class="mvpHelpSection">
            <h2>По сделкам</h2>
            <div class="mvpHelpFaqList">
              <div class="mvpHelpFaqItem">
                <strong>Как работает Escrow-сделка?</strong>
                <p>Покупатель и продавец согласуют условия, покупатель отправляет оплату, продавец выполняет обязательства, после чего средства выпускаются по этапам подтверждения.</p>
              </div>
              <div class="mvpHelpFaqItem">
                <strong>Можно ли изменить условия после создания?</strong>
                <p>Да. Изменения отправляются второй стороне на повторное подтверждение. Пока она не согласится, сделка не перейдёт дальше.</p>
              </div>
              <div class="mvpHelpFaqItem">
                <strong>Кто может подтвердить сделку?</strong>
                <p>Подтверждать текущий этап может только та сторона, от которой ожидается действие. Автор сделки не может подтверждать шаг за другого участника.</p>
              </div>
            </div>
          </article>

          <article class="mvpHelpSection">
            <h2>По оплате</h2>
            <div class="mvpHelpFaqList">
              <div class="mvpHelpFaqItem">
                <strong>Как проверяется поступление оплаты?</strong>
                <p>Система проверяет фактическое поступление нужной суммы USDT на escrow-кошелёк. Минимум берётся из итоговой суммы на экране оплаты.</p>
              </div>
              <div class="mvpHelpFaqItem">
                <strong>Когда сделка переходит дальше?</strong>
                <p>После поступления средств сделка переходит на этап подтверждения оплаты второй стороной. Только после этого открывается следующий шаг.</p>
              </div>
              <div class="mvpHelpFaqItem">
                <strong>Что делать, если сумма не засчиталась?</strong>
                <p>Проверьте адрес кошелька, сеть и итоговую сумму. Если отправлено меньше показанного «Итого», система не подтвердит оплату.</p>
              </div>
            </div>
          </article>
        </div>

        <div class="mvpHelpContact">
          <h2>Нужна дополнительная помощь?</h2>
          <p>Свяжитесь с нами по email или телефону, если требуется ручная проверка сделки или оплаты.</p>
          <div class="mvpHelpContactLinks">
            <a href="mailto:hello@example.com">hello@example.com</a>
            <a href="tel:+14158012270">+1-415-801-2270</a>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
