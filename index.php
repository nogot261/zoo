<?php
$productsData = include __DIR__ . '/data/products.php';
$adoptions = include __DIR__ . '/data/adoptions.php';
$products = $productsData['products'];
$notice = '';
$noticeType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $contact = trim((string) ($_POST['contact'] ?? 'telegram'));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '' || $phone === '') {
        $notice = 'Заполните имя и телефон.';
        $noticeType = 'error';
    } else {
        $allowed = array('telegram', 'whatsapp', 'phone');
        if (!in_array($contact, $allowed, true)) {
            $contact = 'telegram';
        }

        $csv = __DIR__ . '/requests.csv';
        if (!is_file($csv)) {
            file_put_contents($csv, "created_at;name;phone;contact;message\n", LOCK_EX);
        }

        $line = sprintf(
            "%s;%s;%s;%s;%s\n",
            date('Y-m-d H:i:s'),
            str_replace(';', ',', $name),
            str_replace(';', ',', $phone),
            $contact,
            str_replace(array("\r", "\n", ';'), array(' ', ' ', ','), $message)
        );
        file_put_contents($csv, $line, FILE_APPEND | LOCK_EX);
        $notice = 'Заявка сохранена. Менеджер свяжется с вами в выбранном канале.';
        $noticeType = 'ok';
    }
}

$categories = array_values(array_unique(array_map(static function ($product) {
    return $product['category'];
}, $products)));

$featured = array_slice($products, 0, 6);
$feedProducts = array_values(array_filter($products, static function ($product) {
    return (float) $product['rate_g_day'] > 0;
}));

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function animal_mark($animal)
{
    if (strpos($animal, 'Кош') !== false) {
        return 'К';
    }
    if (strpos($animal, 'Соб') !== false) {
        return 'С';
    }
    if (strpos($animal, 'Пти') !== false) {
        return 'П';
    }
    if (strpos($animal, 'Грыз') !== false) {
        return 'Г';
    }
    return 'Z';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZooCare Market - зоомагазин и помощь питомцам</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="#top" aria-label="ZooCare Market">
            <span class="brand-mark">Z</span>
            <span>ZooCare Market</span>
        </a>
        <nav class="nav" aria-label="Основная навигация">
            <a href="#catalog">Каталог</a>
            <a href="#calculator">Калькулятор</a>
            <a href="#adoption">Свободные руки</a>
            <a href="#contacts">Контакты</a>
        </nav>
    </div>
</header>

<main id="top">
    <section class="hero">
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="container hero-inner">
            <p class="kicker">Зоомагазин с онлайн-подбором</p>
            <h1>Товары для питомцев, расчет корма и быстрый заказ через мессенджеры</h1>
            <p class="hero-text">
                Каталог объединяет корма, гигиену, игрушки и аксессуары. Клиент может рассчитать расход корма,
                отправить заявку в Telegram или WhatsApp и посмотреть животных, которым нужен новый дом.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="./catalog.php">Открыть каталог</a>
                <a class="btn btn-secondary" href="#contacts">Написать менеджеру</a>
            </div>
            <div class="hero-metrics" aria-label="Показатели магазина">
                <div><strong><?php echo e(count($products)); ?></strong><span>товаров в каталоге</span></div>
                <div><strong><?php echo e(count($categories)); ?></strong><span>разделов</span></div>
                <div><strong><?php echo e(count($adoptions)); ?></strong><span>анкет животных</span></div>
            </div>
        </div>
    </section>

    <section class="section intro">
        <div class="container split">
            <div>
                <p class="kicker">Как устроен сервис</p>
                <h2>ZooCare Market</h2>
                <p>
                    Это удобный сервис для заботливых владельцев. Мы сделали все, чтобы покупка товаров
                    для питомца занимала минимум вашего времени.
                </p>
                <p>
                    Выбирайте товары в каталоге, рассчитывайте запас корма и обращайтесь к менеджеру
                    удобным способом. Мы поможем подобрать подходящий рацион, уход и аксессуары.
                </p>
            </div>
            <div class="feature-list">
                <div><span>01</span><strong>Все необходимое в одном месте</strong><p>Корма, гигиена, игрушки и аксессуары с понятными характеристиками.</p></div>
                <div><span>02</span><strong>Расчет запаса корма</strong><p>Узнайте, на сколько дней хватит упаковки и сколько составит бюджет на месяц.</p></div>
                <div><span>03</span><strong>Помощь с выбором</strong><p>Оставьте заявку или напишите нам в Telegram и WhatsApp.</p></div>
            </div>
        </div>
    </section>

    <section id="catalog" class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="kicker">Каталог</p>
                    <h2>Популярные позиции</h2>
                </div>
                <a class="text-link" href="./catalog.php">Все товары</a>
            </div>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="product-card">
                        <div class="product-media">
                            <img
                                src="./images/products/<?php echo e($product['image']); ?>"
                                alt="<?php echo e($product['name']); ?>"
                                loading="lazy"
                            >
                        </div>
                        <div class="product-body">
                            <span class="pill"><?php echo e($product['badge']); ?></span>
                            <h3><?php echo e($product['name']); ?></h3>
                            <p><?php echo e($product['description']); ?></p>
                            <dl>
                                <div><dt>Категория</dt><dd><?php echo e($product['category']); ?></dd></div>
                                <div><dt>Фасовка</dt><dd><?php echo e($product['weight_kg']); ?> кг</dd></div>
                            </dl>
                            <strong class="price"><?php echo e(number_format((float) $product['price'], 0, ',', ' ')); ?> ₽</strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="calculator" class="section calculator-section">
        <div class="container split calculator-wrap">
            <div>
                <p class="kicker">Калькулятор корма</p>
                <h2>Расчет расхода и бюджета</h2>
                <p>
                    Клиент выбирает корм из каталога или вводит свои значения. Сайт считает, на сколько дней хватит
                    упаковки и сколько будет стоить питание в день и месяц.
                </p>
                <div class="calc-panel" aria-label="Калькулятор расхода корма">
                    <label>Товар из каталога
                        <select id="feedSelect">
                            <?php foreach ($feedProducts as $product): ?>
                                <option
                                    value="<?php echo e($product['id']); ?>"
                                    data-weight="<?php echo e($product['weight_kg']); ?>"
                                    data-price="<?php echo e($product['price']); ?>"
                                    data-rate="<?php echo e($product['rate_g_day']); ?>"
                                >
                                    <?php echo e($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="calc-inputs">
                        <label>Вес упаковки, кг<input id="feedWeight" type="number" min="0.1" step="0.1" value="1.8"></label>
                        <label>Цена, ₽<input id="feedPrice" type="number" min="1" step="1" value="920"></label>
                        <label>Норма, г/день<input id="feedRate" type="number" min="1" step="1" value="65"></label>
                    </div>
                    <div class="calc-result">
                        <div><span>Хватит на</span><strong id="daysResult">0 дней</strong></div>
                        <div><span>Стоимость дня</span><strong id="dayCostResult">0 ₽</strong></div>
                        <div><span>Стоимость месяца</span><strong id="monthCostResult">0 ₽</strong></div>
                    </div>
                </div>
            </div>
            <img class="section-photo" src="./images/feed-calculator.png" alt="Калькулятор корма и миска питомца">
        </div>
    </section>

    <section id="adoption" class="section adoption-section">
        <div class="container adoption-wrap">
            <div class="adoption-heading">
                <p class="kicker">Свободные руки</p>
                <h2>Питомцы ищут дом</h2>
                <p>
                    Познакомьтесь с животными, которые ждут заботливых хозяев. Все питомцы находятся
                    под присмотром кураторов, а мы поможем связаться и договориться о знакомстве.
                </p>
            </div>
            <div class="adoption-list">
                <?php foreach ($adoptions as $pet): ?>
                    <article class="adoption-card">
                        <img
                            src="./images/pets/<?php echo e($pet['image']); ?>"
                            alt="<?php echo e($pet['name']); ?>, <?php echo e($pet['type']); ?>"
                            loading="lazy"
                        >
                        <div class="adoption-card-body">
                            <h3><?php echo e($pet['name']); ?>, <?php echo e($pet['type']); ?></h3>
                            <p><?php echo e($pet['age']); ?> · <?php echo e($pet['status']); ?></p>
                            <span><?php echo e($pet['description']); ?></span>
                            <a href="#contacts">Узнать подробнее</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="contacts" class="section contacts-section">
        <div class="container contact-grid">
            <div>
                <p class="kicker">Связь с магазином</p>
                <h2>Заявка в Telegram или WhatsApp</h2>
                <p>
                    Выберите удобный канал связи, оставьте контакты и комментарий. Менеджер уточнит детали заказа
                    и поможет подобрать товары для питомца.
                </p>
                <div class="messenger-links">
                    <a href="https://t.me/zoocare_market_bot" target="_blank" rel="noopener noreferrer">Telegram</a>
                    <a href="https://wa.me/79990000000?text=Здравствуйте,%20нужна%20консультация%20зоомагазина" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                </div>
                <div class="work-info">
                    <strong>Адрес:</strong> г. Москва, ул. Лапина, 12<br>
                    <strong>График:</strong> ежедневно 10:00-21:00<br>
                    <strong>Телефон:</strong> +7 999 000-00-00
                </div>
            </div>
            <form class="contact-form" method="post">
                <h3>Оставить заявку</h3>
                <?php if ($notice !== ''): ?>
                    <div class="notice <?php echo e($noticeType); ?>"><?php echo e($notice); ?></div>
                <?php endif; ?>
                <label>Имя<input name="name" type="text" required></label>
                <label>Телефон<input name="phone" type="tel" required></label>
                <label>Канал связи
                    <select name="contact">
                        <option value="telegram">Telegram</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="phone">Звонок</option>
                    </select>
                </label>
                <label>Комментарий<textarea name="message" rows="4" placeholder="Например: подобрать корм для кошки 4 кг"></textarea></label>
                <button type="submit">Отправить</button>
            </form>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>ZooCare Market</span>
        <a href="./catalog.php">Каталог товаров</a>
    </div>
</footer>

<script>
const feedSelect = document.querySelector("#feedSelect");
const feedWeight = document.querySelector("#feedWeight");
const feedPrice = document.querySelector("#feedPrice");
const feedRate = document.querySelector("#feedRate");
const daysResult = document.querySelector("#daysResult");
const dayCostResult = document.querySelector("#dayCostResult");
const monthCostResult = document.querySelector("#monthCostResult");

function formatRub(value) {
    return new Intl.NumberFormat("ru-RU", { maximumFractionDigits: 0 }).format(value) + " ₽";
}

function calculateFeed() {
    const weight = Math.max(parseFloat(feedWeight.value || "0"), 0);
    const price = Math.max(parseFloat(feedPrice.value || "0"), 0);
    const rate = Math.max(parseFloat(feedRate.value || "0"), 1);
    const days = (weight * 1000) / rate;
    const dayCost = days > 0 ? price / days : 0;
    daysResult.textContent = Math.max(Math.floor(days), 0) + " дней";
    dayCostResult.textContent = formatRub(dayCost);
    monthCostResult.textContent = formatRub(dayCost * 30);
}

feedSelect?.addEventListener("change", () => {
    const option = feedSelect.selectedOptions[0];
    feedWeight.value = option.dataset.weight;
    feedPrice.value = option.dataset.price;
    feedRate.value = option.dataset.rate;
    calculateFeed();
});

[feedWeight, feedPrice, feedRate].forEach((input) => input?.addEventListener("input", calculateFeed));
calculateFeed();
</script>
</body>
</html>
