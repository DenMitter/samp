@extends('layouts.app', [
    'title' => 'Escrow.com | Создание офера',
    'description' => 'Создание нового офера и запуск сделки.',
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
    class="mvpStartMain"
    data-offer-create-page
    data-offer-show-base="{{ rtrim(route('offers.show', ['offer' => '__ID__']), '__ID__') }}"
    data-offer-start-template="{{ route('offers.start', ['offer' => '__ID__']) }}"
  >
    <section class="section-container">
      <div class="mvpStartCard">
        <header class="mvpStartCardHeader">
          <h1>Создание сделки</h1>
        </header>

        <form class="mvpStartForm" data-offer-create-form novalidate>
          <label class="mvpStartField is-full">
            <span>Название сделки</span>
            <input type="text" name="title" placeholder="Название сделки" required>
            <small data-error-for="title"></small>
          </label>

          <div class="mvpStartGrid mvpStartGrid--three">
            <label class="mvpStartField">
              <span>Моя роль</span>
              <select name="role">
                <option value="buyer">Покупатель</option>
                <option value="seller">Продавец</option>
                <option value="broker">Посредник</option>
              </select>
            </label>

            <label class="mvpStartField">
              <span>Валюта</span>
              <select name="currency">
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="GBP">GBP</option>
                <option value="CAD">CAD</option>
              </select>
            </label>

            <label class="mvpStartField">
              <span>Период проверки (дней)</span>
              <input type="number" name="inspection_period_days" value="1" min="1" max="30">
            </label>
          </div>

          <section class="mvpStartSection">
            <h2>Детали сделки</h2>

            <div class="mvpStartGrid mvpStartGrid--stack">
              <label class="mvpStartField is-full">
                <select name="asset_type" required>
                  <option value="" selected disabled>Выберите категорию товара</option>
                  <optgroup label="Домены и интернет-активы">
                    <option value="domain">Домен</option>
                    <option value="website">Сайт</option>
                    <option value="online-business">Онлайн-бизнес</option>
                    <option value="mobile-app">Мобильное приложение</option>
                    <option value="hosting-account">Хостинг-аккаунт</option>
                    <option value="social-media-account">Аккаунт в соцсетях</option>
                    <option value="digital-assets">Цифровые активы</option>
                  </optgroup>
                  <optgroup label="Транспорт">
                    <option value="cars-trucks">Автомобили и грузовики</option>
                    <option value="boats">Лодки и катера</option>
                    <option value="motorcycles">Мотоциклы</option>
                    <option value="airplanes">Самолеты</option>
                    <option value="other-motor-vehicles">Другой мототранспорт</option>
                    <option value="special-equipment">Спецтехника</option>
                    <option value="trailers">Прицепы</option>
                  </optgroup>
                  <optgroup label="Товары и ценности">
                    <option value="antiques-collectibles">Антиквариат и коллекционные предметы</option>
                    <option value="appliances">Бытовая техника</option>
                    <option value="art">Искусство</option>
                    <option value="cameras">Камеры и фотооборудование</option>
                    <option value="electronics">Электроника</option>
                    <option value="jewelry">Ювелирные изделия</option>
                    <option value="luxury-goods">Предметы роскоши</option>
                    <option value="musical-instruments">Музыкальные инструменты</option>
                    <option value="sports-equipment">Спортивное оборудование</option>
                    <option value="watches">Часы</option>
                  </optgroup>
                  <optgroup label="Бизнес и услуги">
                    <option value="broker-services">Брокерские услуги</option>
                    <option value="freelance-services">Фриланс-услуги</option>
                    <option value="milestone-services">Услуги с поэтапной оплатой</option>
                    <option value="consulting">Консалтинг</option>
                    <option value="software-development">Разработка ПО</option>
                    <option value="marketing-services">Маркетинговые услуги</option>
                    <option value="design-services">Дизайн-услуги</option>
                  </optgroup>
                  <optgroup label="Прочее">
                    <option value="inventory">Товарные остатки</option>
                    <option value="industrial-equipment">Промышленное оборудование</option>
                    <option value="medical-equipment">Медицинское оборудование</option>
                    <option value="property-rent">Арендные сделки</option>
                    <option value="other">Другое</option>
                  </optgroup>
                </select>
                <small data-error-for="asset_type"></small>
              </label>

              <div class="mvpStartGrid mvpStartGrid--two">
                <label class="mvpStartField">
                  <span class="mvpStartFieldLabelGhost" aria-hidden="true">Цена (USD)</span>
                  <input type="text" name="item_name" placeholder="Название товара">
                </label>

                <label class="mvpStartField">
                  <span>Цена (USD)</span>
                  <input type="number" name="amount" placeholder="$0.00" min="1" step="0.01" required>
                  <small data-error-for="amount"></small>
                </label>
              </div>

              <label class="mvpStartField is-full">
                <textarea name="description" rows="3" placeholder="Описание товара"></textarea>
              </label>

              <div class="mvpStartDynamicFields" data-dynamic-fields hidden></div>
            </div>
          </section>

          <p class="mvpStartMessage" data-offer-create-message hidden></p>

          <div class="mvpStartActions">
            <button type="submit" class="mvpStartSubmit" data-offer-create-submit>Создать офер</button>
          </div>
        </form>
      </div>
    </section>
  </main>

  <div class="mvpToast" data-toast hidden></div>
@endsection

@push('scripts')
  <script src="{{ asset('js/offer-create.js') }}"></script>
@endpush
