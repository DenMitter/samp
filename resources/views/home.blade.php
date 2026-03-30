@extends('layouts.app', [
    'title' => 'Escrow.com | Безопасные сделки онлайн',
    'description' => 'Безопасные онлайн-сделки с оплатой, оферами и личным кабинетом в стиле Escrow.com.',
    'themeColor' => '#01426a',
    'bodyClass' => '',
])

@section('body')
  <section class="content">
    <section class="sectionHero sectionHero--calculator" id="top">
      <div class="header--transparent">
        @include('partials.header', ['variant' => 'light'])
      </div>

      <div class="section-container mvpHeroWrap">
        <div class="sectionHero-inner">
          <div class="sectionHero-content">
            <h1 class="sectionHero-title">Никогда не покупайте и не продавайте онлайн без Escrow.com</h1>
            <h2 class="sectionHero-desc">С Escrow.com вы можете безопасно покупать и продавать онлайн без риска чарджбеков. Защита денег, оферов и поэтапных выплат в одном процессе.</h2>

            <div class="calculator">
              <form class="calculator-form defaultForm defaultForm--compact defaultForm--large defaultForm--light" novalidate>
                <div class="defaultForm-group">
                  <div class="field calculator-formUser field--minor">
                    <div class="field-input">
                      <div class="field-prefix">
                        <div class="field-prefix-wrapper">
                          <span class="field-prefix-label">Я хочу</span>
                        </div>
                      </div>
                      <div class="defaultSelect defaultSelect--form">
                        <select class="defaultSelect-select" name="role">
                          <option>Продать</option>
                          <option>Купить</option>
                          <option>Выступить посредником</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="field calculator-formService">
                    <div class="field-input">
                      <input type="text" class="defaultInput" name="service" placeholder="Домены, автомобили, товары..." value="">
                    </div>
                  </div>
                </div>

                <div class="defaultForm-group">
                  <div class="field calculator-price">
                    <div class="field-input">
                      <div class="field-prefix">
                        <div class="field-prefix-wrapper">
                          <span class="field-prefix-label">на сумму $</span>
                        </div>
                      </div>
                      <input type="number" class="defaultInput" id="field-price" value="800" name="price" step="10" min="0">
                    </div>
                  </div>

                  <div class="field calculator-currency field--minor">
                    <div class="field-input">
                      <div class="defaultSelect defaultSelect--form">
                        <select class="defaultSelect-select" name="currency">
                          <option>USD</option>
                          <option>EUR</option>
                          <option>GBP</option>
                          <option>AUD</option>
                          <option>CAD</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <button type="button" class="btn btn--secondary btn--large mvpHero-button" data-start-transaction>Начать сейчас</button>
              </form>
            </div>
          </div>

          <div class="sectionHero-carousel carousel" aria-label="Пример процесса">
            <div class="sectionHero-upsell carousel-item is-active">
              <span class="sectionHero-upsell-title">
                <span class="sectionHero-upsell-logo">
                  <span class="sectionHero-upsell-img"><svg width="192" height="192" viewBox="0 0 192 192" xmlns="http://www.w3.org/2000/svg"><title>icon-domains .com</title><g fill="none" fill-rule="evenodd"><circle fill="#FFE372" cx="96" cy="96" r="96"/><circle fill="#E6A725" cx="33.789" cy="103.579" r="7.579"/><path d="M64.15 106.538c1.692 0 3.073-.466 4.142-1.397 1.07-.93 1.614-2.168 1.635-3.713h8.635c-.042 3.64-1.434 6.635-4.175 8.984-2.74 2.35-6.1 3.524-10.08 3.524-5.31 0-9.285-1.677-11.92-5.03-2.634-3.356-3.952-7.467-3.952-12.334v-.89c0-4.867 1.318-8.978 3.953-12.333 2.634-3.354 6.597-5.03 11.888-5.03 4.233 0 7.662 1.205 10.286 3.618 2.624 2.412 3.958 5.682 4 9.81h-8.635c-.02-1.673-.524-3.096-1.508-4.27-.985-1.175-2.42-1.762-4.302-1.762-2.688 0-4.44 1.015-5.254 3.047-.815 2.032-1.223 4.34-1.223 6.92v.89c0 2.645.41 4.968 1.224 6.968.815 2 2.576 3 5.285 3zm16.325-10.064v-.666c0-4.995 1.418-9.16 4.254-12.492 2.835-3.334 6.835-5 12-5 5.226 0 9.248 1.666 12.062 5 2.815 3.333 4.222 7.497 4.222 12.492v.666c0 4.995-1.407 9.154-4.222 12.476-2.814 3.323-6.814 4.984-12 4.984-5.206 0-9.227-1.66-12.063-4.984-2.837-3.322-4.255-7.48-4.255-12.476zm9.206-.666v.666c0 2.794.536 5.17 1.605 7.127 1.068 1.96 2.904 2.938 5.507 2.938 2.56 0 4.376-.98 5.445-2.937 1.07-1.956 1.603-4.332 1.603-7.126v-.666c0-2.73-.534-5.095-1.603-7.095-1.07-2-2.905-3-5.508-3-2.562 0-4.377 1-5.445 3-1.07 2-1.604 4.365-1.604 7.095zm41.914-10.064c-2.54 0-4.412 1.08-5.62 3.238V113.3h-9.205V78.95h8.634l.317 3.778c2.393-2.94 5.673-4.412 9.842-4.412 2.053 0 3.868.412 5.445 1.238 1.577.825 2.8 2.16 3.666 4 1.1-1.63 2.514-2.91 4.24-3.84 1.723-.933 3.75-1.398 6.078-1.398 3.345 0 6.017.984 8.017 2.952 2 1.968 3 5.355 3 10.16V113.3H156.8V91.395c0-2.286-.46-3.8-1.38-4.54-.92-.74-2.175-1.11-3.762-1.11-2.71 0-4.604 1.28-5.683 3.84V113.3H136.8V91.46c0-2.266-.46-3.785-1.38-4.557-.92-.772-2.196-1.16-3.826-1.16z" fill="#E6A725"/></g></svg></span>
                  <span class="sectionHero-upsell-imgShadow"><svg width="231" height="192" viewBox="0 0 231 192" xmlns="http://www.w3.org/2000/svg"><g fill="#76CEF1" fill-rule="evenodd" opacity=".305"><circle cx="135" cy="96" r="96" opacity=".3"/><circle cx="115" cy="96" r="96" opacity=".3"/><circle cx="96" cy="96" r="96" opacity=".3"/></g></svg></span>
                </span>
                <span class="sectionHero-upsell-text">Безопасная покупка и продажа с прозрачным процессом</span>
              </span>
              <div class="sectionHero-steps">
                <span class="sectionHero-steps-decorator sectionHero-steps-decorator--above"></span>
                <ol class="sectionHero-upsell-list">
                  <li class="sectionHero-upsell-item is-disabled"><svg class="sectionHero-upsell-icon" width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M5.69 6.892l2.322 2.21L16.35.312c.38-.4 1.012-.418 1.413-.038.4.38.417 1.013.037 1.414l-9.027 9.517c-.38.402-1.014.418-1.415.037L4.31 8.34c-.4-.38-.415-1.014-.034-1.414.38-.4 1.014-.415 1.414-.034zM11.836.978c.484.265.662.873.397 1.357-.265.485-.873.663-1.357.398C10.002 2.253 9.02 2 8 2 4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6c0-.552.448-1 1-1s1 .448 1 1c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8c1.358 0 2.67.34 3.836.978z" fill-rule="nonzero"/></svg><span class="sectionHero-upsell-desc">Покупатель и продавец согласовывают условия</span></li>
                  <li class="sectionHero-upsell-item is-disabled"><svg class="sectionHero-upsell-icon" width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M5.69 6.892l2.322 2.21L16.35.312c.38-.4 1.012-.418 1.413-.038.4.38.417 1.013.037 1.414l-9.027 9.517c-.38.402-1.014.418-1.415.037L4.31 8.34c-.4-.38-.415-1.014-.034-1.414.38-.4 1.014-.415 1.414-.034zM11.836.978c.484.265.662.873.397 1.357-.265.485-.873.663-1.357.398C10.002 2.253 9.02 2 8 2 4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6c0-.552.448-1 1-1s1 .448 1 1c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8c1.358 0 2.67.34 3.836.978z" fill-rule="nonzero"/></svg><span class="sectionHero-upsell-desc">Покупатель отправляет деньги в Escrow.com</span></li>
                  <li class="sectionHero-upsell-item is-disabled"><svg class="sectionHero-upsell-icon" width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M5.69 6.892l2.322 2.21L16.35.312c.38-.4 1.012-.418 1.413-.038.4.38.417 1.013.037 1.414l-9.027 9.517c-.38.402-1.014.418-1.415.037L4.31 8.34c-.4-.38-.415-1.014-.034-1.414.38-.4 1.014-.415 1.414-.034zM11.836.978c.484.265.662.873.397 1.357-.265.485-.873.663-1.357.398C10.002 2.253 9.02 2 8 2 4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6c0-.552.448-1 1-1s1 .448 1 1c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8c1.358 0 2.67.34 3.836.978z" fill-rule="nonzero"/></svg><span class="sectionHero-upsell-desc">Продавец передает товар или актив</span></li>
                  <li class="sectionHero-upsell-item is-disabled"><svg class="sectionHero-upsell-icon" width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M5.69 6.892l2.322 2.21L16.35.312c.38-.4 1.012-.418 1.413-.038.4.38.417 1.013.037 1.414l-9.027 9.517c-.38.402-1.014.418-1.415.037L4.31 8.34c-.4-.38-.415-1.014-.034-1.414.38-.4 1.014-.415 1.414-.034zM11.836.978c.484.265.662.873.397 1.357-.265.485-.873.663-1.357.398C10.002 2.253 9.02 2 8 2 4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6c0-.552.448-1 1-1s1 .448 1 1c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8c1.358 0 2.67.34 3.836.978z" fill-rule="nonzero"/></svg><span class="sectionHero-upsell-desc">Покупатель подтверждает результат</span></li>
                  <li class="sectionHero-upsell-item is-disabled"><svg class="sectionHero-upsell-icon" width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M5.69 6.892l2.322 2.21L16.35.312c.38-.4 1.012-.418 1.413-.038.4.38.417 1.013.037 1.414l-9.027 9.517c-.38.402-1.014.418-1.415.037L4.31 8.34c-.4-.38-.415-1.014-.034-1.414.38-.4 1.014-.415 1.414-.034zM11.836.978c.484.265.662.873.397 1.357-.265.485-.873.663-1.357.398C10.002 2.253 9.02 2 8 2 4.686 2 2 4.686 2 8s2.686 6 6 6 6-2.686 6-6c0-.552.448-1 1-1s1 .448 1 1c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8c1.358 0 2.67.34 3.836.978z" fill-rule="nonzero"/></svg><span class="sectionHero-upsell-desc">Escrow.com переводит оплату продавцу</span></li>
                </ol>
                <span class="sectionHero-steps-decorator sectionHero-steps-decorator--below"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="reputation" id="trust">
      <div class="section-container">
        <ul class="reputation-inner">
          <li class="reputation-item reputation-item--stats"><div class="reputation-stats"><span class="reputation-stats-value">$7,500,000,000+</span><span class="reputation-stats-label">обработано безопасно</span></div></li>
          <li class="reputation-item reputation-item--stats"><div class="reputation-stats"><span class="reputation-stats-value">3,000,000+</span><span class="reputation-stats-label">клиентов доверяют сервису</span></div></li>
          <li class="reputation-item reputation-item--bbb"><span class="reputation-logo reputation-logo--bbb"><span class="is-accessibly-hidden">BBB Torch Awards</span></span><span class="reputation-logo-content"><span class="reputation-logo-title">Проверяемый процесс</span><span class="reputation-logo-desc">доверие к крупным онлайн-сделкам</span></span></li>
          <li class="reputation-item"><span class="reputation-logo reputation-logo--bbbAccredited"><span class="is-accessibly-hidden">BBB Accredited</span></span></li>
          <li class="reputation-item reputation-item--dbo"><span class="reputation-logo reputation-logo--dbo"><span class="is-accessibly-hidden">DBO</span></span></li>
          <li class="reputation-item reputation-item--usCommercial"><span class="reputation-logo reputation-logo--usCommercial"><span class="is-accessibly-hidden">US Commercial</span></span></li>
          <li class="reputation-item reputation-item--ebayMotors"><img src="https://www.escrow.com/build/images/partners/ebaymotors.png" alt="Partner" class="header-logo-img"></li>
        </ul>
      </div>
    </section>

    <main role="main">
      <section class="section howItWorks" id="how-it-works">
        <div class="section-container howItWorks-container">
          <header class="section-header">
            <div class="sectionHeading howItWorks-sectionHeading">
              <h2 class="sectionHeading-title" role="heading">Как работает escrow-сделка</h2>
              <div class="sectionHeading-subTitle">Escrow.com удерживает деньги до подтверждения сделки. Это снижает риск для обеих сторон и делает процесс прозрачным для покупателя, продавца и посредника.</div>
            </div>
          </header>
          <div class="steps">
            <ol class="steps-list formHero-steps mvpSimpleSteps">
              <li class="steps-step"><div class="mvpStepVisual">1</div><div class="steps-title">Покупатель и продавец согласовывают условия</div></li>
              <li class="steps-step"><div class="mvpStepVisual">2</div><div class="steps-title">Покупатель отправляет оплату в Escrow.com</div></li>
              <li class="steps-step"><div class="mvpStepVisual">3</div><div class="steps-title">Продавец передает товар, актив или результат работ</div></li>
              <li class="steps-step"><div class="mvpStepVisual">4</div><div class="steps-title">Покупатель проверяет и подтверждает результат</div></li>
              <li class="steps-step"><div class="mvpStepVisual">5</div><div class="steps-title">Escrow.com переводит деньги продавцу</div></li>
            </ol>
          </div>
          <footer class="section-footer">
            <div class="section-footer-actions">
              <a href="{{ route('signup.page') }}" class="btn btn--secondary section-footer-btn btn--large">Начать сейчас</a>
              <div><a href="#services" class="section-footer-link">Посмотреть типы сделок</a></div>
            </div>
          </footer>
        </div>
      </section>

      <section class="section services" id="services">
        <div class="section-container">
          <header class="section-header">
            <div class="sectionHeading services-title">
              <h2 class="sectionHeading-title" role="heading">Безопасно проводите сделки от $100 до крупных международных сумм</h2>
            </div>
          </header>
          <div class="grid grid--horizontalCenter">
            <div class="grid-col grid-col--desktopSmall-10">
              <ul class="siteFeatures siteFeatures--large grid grid--spaceAround">
                <li class="siteFeatures-item grid-col grid-col--tablet-5"><div class="siteFeatures-iconHolder"><div class="mvpServiceIcon">01</div></div><div><span class="siteFeatures-title">Домены и сайты</span><p class="siteFeatures-desc">Передача доменных имен, интернет-проектов и цифровых активов с удержанием средств до подтверждения результата.</p><a href="{{ route('signup.page') }}" class="siteFeatures-cta">Подробнее</a></div></li>
                <li class="siteFeatures-item grid-col grid-col--tablet-5"><div class="siteFeatures-iconHolder"><div class="mvpServiceIcon">02</div></div><div><span class="siteFeatures-title">Автомобили и техника</span><p class="siteFeatures-desc">Контроль перевода денег и передачи транспорта, оборудования, техники и других дорогих активов.</p><a href="{{ route('signup.page') }}" class="siteFeatures-cta">Подробнее</a></div></li>
                <li class="siteFeatures-item grid-col grid-col--tablet-5"><div class="siteFeatures-iconHolder"><div class="mvpServiceIcon">03</div></div><div><span class="siteFeatures-title">Товары и электроника</span><p class="siteFeatures-desc">Подходит для дорогих товаров, международных отправок, редких позиций и продаж с повышенным риском.</p><a href="{{ route('signup.page') }}" class="siteFeatures-cta">Подробнее</a></div></li>
                <li class="siteFeatures-item grid-col grid-col--tablet-5"><div class="siteFeatures-iconHolder"><div class="mvpServiceIcon">04</div></div><div><span class="siteFeatures-title">Поэтапные услуги</span><p class="siteFeatures-desc">Milestone-оплата для проектов, где деньги выпускаются частями после подтверждения этапов.</p><a href="{{ route('signup.page') }}" class="siteFeatures-cta">Подробнее</a></div></li>
              </ul>
            </div>
          </div>
          <div class="mvpSectionCta">
            <p>Нужна консультация по вашей сделке? Позвоните нам: <a href="tel:+14158012270">+1-415-801-2270</a></p>
            <div class="mvpSectionCta-actions">
              <a href="{{ route('signup.page') }}" class="btn btn--secondary btn--large">Начать сейчас</a>
              <a href="#pay" class="section-footer-link">Посмотреть оплату</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section apiIntroduction" id="pay">
        <div class="section-container">
          <div class="grid grid--verticalCenter">
            <div class="grid-col grid-col--desktopSmall-5">
              <figure class="apiIntroduction-figure media--hidden@tablet"><img src="https://www.escrow.com/build/images/escrow-pay/home-escrow-pay.png" class="apiIntroduction-img apiIntroduction-img--pay" alt="Escrow Pay"></figure>
            </div>
            <div class="grid-col grid-col--desktopSmall-6 grid-col--flushRight">
              <div class="sectionHeading sectionHeading--alignLeft"><h2 class="apiIntroduction-title sectionHeading-title">Escrow Pay: безопасные онлайн-платежи в одном процессе</h2></div>
              <p class="apiIntroduction-desc">Этот блок нужен, потому что в проекте будет логика оплаты. Здесь клиент видит, что деньги проходят через защищенный механизм escrow, а не переводятся продавцу напрямую.</p>
              <a href="{{ route('signup.page') }}" class="btn btn--secondary btn--hollow btn--large">Узнать подробнее</a>
            </div>
          </div>
        </div>
      </section>

      <section class="section apiIntroduction" id="offer">
        <div class="section-container">
          <div class="grid grid--verticalCenter">
            <div class="grid-col grid-col--desktopSmall-6">
              <div class="sectionHeading sectionHeading--alignLeft"><h2 class="apiIntroduction-title sectionHeading-title">Escrow Offer: согласование цены и условий сделки</h2></div>
              <p class="apiIntroduction-desc">Раз у тебя будет логика оферов, этот блок тоже обязателен. Он объясняет, что покупатель и продавец могут согласовать цену, условия и запуск сделки в одном защищенном потоке.</p>
              <a href="{{ route('signup.page') }}" class="btn btn--secondary btn--hollow btn--large">Узнать подробнее</a>
            </div>
            <div class="grid-col grid-col--desktopSmall-6 grid-col--flushRight"><figure class="apiIntroduction-figure media--hidden@tablet"><img src="https://www.escrow.com/build/images/offer/offer-introduction.png" class="apiIntroduction-img" alt="Escrow Offer"></figure></div>
          </div>
        </div>
      </section>

      <section class="section licensing" id="licensing">
        <div class="section-container licensing-container">
          <div class="grid grid--verticalCenter">
            <div class="grid-col grid-col--desktopSmall-6 media--hidden@tablet"><img src="https://www.escrow.com/build/images/sections/licensing/map.png" class="licensing-img" alt="Лицензирование и доверие"></div>
            <div class="grid-col grid-col--desktopSmall-6 grid-col--flushRight">
              <h2 class="sectionHeading-title" role="heading">Максимально важный для клиента блок: деньги не уходят продавцу раньше времени</h2>
              <div class="licensing-subtitle">Escrow.com удерживает средства до подтверждения сделки. Платформа ориентирована на прозрачный процесс, контроль этапов и доверие к крупным онлайн-расчетам.</div>
              <p class="sectionHeading-subTitle">Для MVP это один из ключевых блоков. Он закрывает главный вопрос клиента: почему системе можно доверить деньги, оферы и подтверждение результатов сделки.</p>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="defaultFooter defaultFooter--" id="contact">
      <div class="defaultFooter-container section-container mvpFooter-grid">
        <div class="defaultFooter-nav defaultFooter-item">
          <a class="defaultFooter-title defaultFooter-title--expand" href="#services">Сделки</a>
          <nav class="defaultFooter-links" aria-label="services navigation">
            <a class="defaultFooter-link" href="#services">Домены и сайты</a>
            <a class="defaultFooter-link" href="#services">Автомобили и техника</a>
            <a class="defaultFooter-link" href="#services">Товары и электроника</a>
            <a class="defaultFooter-link" href="#services">Поэтапные услуги</a>
          </nav>
        </div>
        <div class="defaultFooter-nav defaultFooter-item">
          <a class="defaultFooter-title defaultFooter-title--expand" href="#how-it-works">Процесс</a>
          <nav class="defaultFooter-links" aria-label="support navigation">
            <a class="defaultFooter-link" href="#how-it-works">Как это работает</a>
            <a class="defaultFooter-link" href="#pay">Безопасная оплата</a>
            <a class="defaultFooter-link" href="#offer">Оферы и переговоры</a>
            <a class="defaultFooter-link" href="#licensing">Почему это надежно</a>
          </nav>
        </div>
        <div class="defaultFooter-nav defaultFooter-item">
          <a class="defaultFooter-title defaultFooter-title--expand" href="#contact">Запуск</a>
          <nav class="defaultFooter-links" aria-label="contact navigation">
            <a class="defaultFooter-link" href="tel:+14158012270">+1-415-801-2270</a>
            <a class="defaultFooter-link" href="mailto:hello@example.com">hello@example.com</a>
            <a class="defaultFooter-link" href="#top">Вернуться наверх</a>
          </nav>
        </div>
      </div>
    </footer>
  </section>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@endpush
