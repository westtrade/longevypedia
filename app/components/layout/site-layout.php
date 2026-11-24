<?php

declare(strict_types=1);

/**
 * Каркас страниц Longevypedia.
 *
 * Компонент, а не `layouts/`: секция `layouts/` не сканируется сборщиком
 * ассетов, у неё нет namespace, и `<style lang>` из неё в сборку не попадёт
 * (см. docs/architecture/bolt-analysis.md).
 *
 * Содержимое страницы передаётся секцией, а не параметром: страница вызывает
 * `$this->layout(...)` и печатает тело внутрь `start($section)`. Имя секции —
 * `page`, потому что `content` в Plates зарезервировано.
 *
 * @var string      $title       заголовок страницы без названия сайта
 * @var string      $description краткое описание для meta description
 * @var string      $canonical   путь страницы для canonical и og:url
 * @var string      $ogType      og:type
 * @var array|null  $schema      данные JSON-LD
 * @var string      $current     раздел навигации для подсветки
 * @var string      $section     имя секции с телом страницы
 * @var string      $bodyClass
 */

$title ??= 'Longevypedia';
$description ??= '';
$canonical ??= '';
$ogType ??= 'website';
$schema ??= null;
$current ??= '';
$section ??= 'page';
$bodyClass ??= '';
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php // Тема ставится до отрисовки: иначе тёмная система получит светлую
	      // вспышку на каждый переход. Скрипт крошечный и не блокирует разбор. ?>
	<script>
		try {
			const stored = localStorage.getItem('longevypedia.theme');
			if (stored === 'light' || stored === 'dark') {
				document.documentElement.dataset.theme = stored;
			}
		} catch (e) { /* приватный режим: остаёмся на системной теме */ }
	</script>

	<?= $this->insert('bolt://ui-kit/seo', [
		'title' => $title,
		'description' => $description,
		'url' => $canonical,
		'type' => $ogType,
		'schema' => $schema,
	]) ?>

	<link rel="icon" href="/favicon.svg" type="image/svg+xml">
	<meta name="color-scheme" content="light dark">

	<?= vite_assets(['app/client/main.ts'], '/build') ?>
</head>

<body class="<?= $this->e($bodyClass) ?>">
	<a class="skip-link" href="#main">Skip to content</a>

	<div class="page">
		<?= $this->insert('components::layout/site-header', ['current' => $current]) ?>

		<main class="page__main" id="main">
			<?= $this->section($section) ?>
		</main>

		<?= $this->insert('components::layout/site-footer') ?>
	</div>
</body>

</html>
