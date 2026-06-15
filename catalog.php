<?php
$productsData = include __DIR__ . '/data/products.php';
$products = $productsData['products'];
$categories = array_values(array_unique(array_map(static function ($product) {
    return $product['category'];
}, $products)));

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function search_text($product)
{
    return $product['name'] . ' ' . $product['brand'] . ' ' . $product['category'];
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
    <title>Каталог товаров - ZooCare Market</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="./index.php#top" aria-label="ZooCare Market">
            <span class="brand-mark">Z</span>
            <span>ZooCare Market</span>
        </a>
        <nav class="nav" aria-label="Основная навигация">
            <a href="./index.php#catalog">Главная</a>
            <a href="./catalog.php">Каталог</a>
            <a href="./index.php#calculator">Калькулятор</a>
            <a href="./index.php#contacts">Контакты</a>
        </nav>
    </div>
</header>

<main>
    <section class="catalog-hero">
        <div class="container catalog-hero-inner">
            <p class="kicker">Каталог</p>
            <h1>Товары для кошек, собак, птиц и грызунов</h1>
            <p>Фильтрация работает на странице без перезагрузки. В карточках указаны цена, фасовка, категория и базовая норма расхода для кормов.</p>
        </div>
    </section>

    <section class="section catalog-page">
        <div class="container">
            <div class="catalog-tools" aria-label="Фильтры каталога">
                <label>Поиск
                    <input id="searchInput" type="search" placeholder="Название, бренд, категория">
                </label>
                <label>Категория
                    <select id="categoryFilter">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo e($category); ?>"><?php echo e($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Сортировка
                    <select id="sortSelect">
                        <option value="default">По умолчанию</option>
                        <option value="price-asc">Сначала дешевле</option>
                        <option value="price-desc">Сначала дороже</option>
                    </select>
                </label>
            </div>

            <div id="catalogCount" class="catalog-count"></div>

            <div id="catalogGrid" class="product-grid catalog-grid">
                <?php foreach ($products as $product): ?>
                    <article
                        class="product-card"
                        data-name="<?php echo e(search_text($product)); ?>"
                        data-category="<?php echo e($product['category']); ?>"
                        data-price="<?php echo e($product['price']); ?>"
                    >
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
                                <div><dt>Бренд</dt><dd><?php echo e($product['brand']); ?></dd></div>
                                <div><dt>Категория</dt><dd><?php echo e($product['category']); ?></dd></div>
                                <div><dt>Фасовка</dt><dd><?php echo e($product['weight_kg']); ?> кг</dd></div>
                                <?php if ((float) $product['rate_g_day'] > 0): ?>
                                    <div><dt>Норма</dt><dd><?php echo e($product['rate_g_day']); ?> г/день</dd></div>
                                <?php endif; ?>
                            </dl>
                            <strong class="price"><?php echo e(number_format((float) $product['price'], 0, ',', ' ')); ?> ₽</strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>ZooCare Market · каталог</span>
        <a href="./index.php#contacts">Связаться</a>
    </div>
</footer>

<script>
const grid = document.querySelector("#catalogGrid");
const cards = Array.from(document.querySelectorAll(".catalog-grid .product-card"));
const searchInput = document.querySelector("#searchInput");
const categoryFilter = document.querySelector("#categoryFilter");
const sortSelect = document.querySelector("#sortSelect");
const catalogCount = document.querySelector("#catalogCount");

function normalize(value) {
    return String(value || "").trim().toLowerCase();
}

function renderCatalog() {
    const query = normalize(searchInput.value);
    const category = categoryFilter.value;
    const sort = sortSelect.value;
    let visible = cards.filter((card) => {
        const matchSearch = !query || normalize(card.dataset.name).includes(query);
        const matchCategory = !category || card.dataset.category === category;
        return matchSearch && matchCategory;
    });

    visible.sort((a, b) => {
        if (sort === "price-asc") return Number(a.dataset.price) - Number(b.dataset.price);
        if (sort === "price-desc") return Number(b.dataset.price) - Number(a.dataset.price);
        return cards.indexOf(a) - cards.indexOf(b);
    });

    cards.forEach((card) => card.hidden = true);
    visible.forEach((card) => {
        card.hidden = false;
        grid.appendChild(card);
    });
    catalogCount.textContent = "Показано: " + visible.length + " из " + cards.length;
}

[searchInput, categoryFilter, sortSelect].forEach((control) => control.addEventListener("input", renderCatalog));
renderCatalog();
</script>
</body>
</html>
