<?php

declare(strict_types=1);

/**
 * Боковое меню UI-набора.
 *
 * Единственный источник пунктов — `data/ui-kit.php`: компонент не перечисляет
 * страницы сам, иначе список разошёлся бы с каталогом.
 *
 * Своего оформления у меню нет: это `components::ui/sidebar-nav` в режиме
 * групп. Так навигация набора ведёт себя так же, как содержание статьи и любой
 * другой боковой список на сайте — одно поведение при наведении, фокусе и на
 * текущем пункте.
 *
 * @var array<int, array{title?: string, items?: array<int, array<string, mixed>>}> $groups
 * @var string|null $active slug текущей страницы — для aria-current и подсветки
 */

$groups ??= [];
$active ??= null;

if ($groups === []) {
	return;
}

$sections = [];

foreach ($groups as $group) {
	$items = [];

	foreach ((array) ($group['items'] ?? []) as $item) {
		$slug = (string) ($item['slug'] ?? '');

		if ($slug === '') {
			continue;
		}

		$items[] = [
			'label' => (string) ($item['title'] ?? $slug),
			'url' => '/ui-kit/' . $slug,
			'active' => $slug === $active,
		];
	}

	$sections[] = [
		'title' => (string) ($group['title'] ?? ''),
		'items' => $items,
	];
}

$this->insert('components::ui/sidebar-nav', [
	'groups' => $sections,
	'title' => 'Разделы UI-набора',
	'sticky' => false,
]);
