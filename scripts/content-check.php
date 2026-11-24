#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Проверка корпуса контента: схемы, идентификаторы, связи, Wikilinks.
 *
 * Запускается локально (`ddev composer content`), в CI и в pre-commit-хуке.
 * Код выхода 1 означает, что контент публиковать нельзя.
 *
 * Проверка идёт с принудительной валидацией по JSON Schema, независимо от
 * того, включена ли отладка: это гейт, а не подсказка.
 */

use Bolt\Core\Application;
use Longevypedia\Content\ContentRepository;
use Longevypedia\Content\DocumentTypes;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = Application::boot(dirname(__DIR__));
$config = $app->config();
$repository = ContentRepository::fromConfig($config, forceValidation: true);

$contentPath = $repository->contentPath();

if (!is_dir($contentPath)) {
	fwrite(STDERR, sprintf("Каталог контента не найден: %s\n", $contentPath));

	exit(1);
}

$defaultLocale = $repository->defaultLocale();
$problems = $repository->problems();
$unresolved = $repository->unresolvedLinks();
$stats = $repository->stats($defaultLocale);

// Картинки: битая ссылка на файл — это 404 в браузере и «пустая» статья,
// поэтому путь проверяется на диске. Внешние URL не проверяются: сеть в CI
// недоступна, а чужой хост может лежать независимо от нашего контента.
$imageProblems = [];

foreach (array_keys($repository->locales()) as $locale) {
	foreach ($repository->allIncludingDrafts($locale) as $document) {
		$sources = [];

		preg_match_all('/!\[[^\]]*\]\(\s*<?([^)\s>]+)/u', $document->body, $markdownImages);
		$sources = array_merge($sources, $markdownImages[1] ?? []);

		preg_match_all('/<img\b[^>]*\bsrc\s*=\s*["\']([^"\']+)/iu', $document->body, $htmlImages);
		$sources = array_merge($sources, $htmlImages[1] ?? []);

		$frontmatterImage = $document->metaValue('image');

		if (is_string($frontmatterImage) && trim($frontmatterImage) !== '') {
			$sources[] = $frontmatterImage;
		}

		foreach (array_unique($sources) as $source) {
			$source = trim($source);

			if ($source === '' || preg_match('~^(https?:)?//~i', $source) === 1 || str_starts_with($source, 'data:')) {
				continue;
			}

			$path = str_starts_with($source, '/')
				? dirname(__DIR__) . '/public' . $source
				: dirname($document->file) . '/' . $source;

			if (!is_file($path)) {
				$imageProblems[] = sprintf('%s: файл не найден — %s', $document->relativePath, $source);
			}
		}
	}
}

$lines = [];
$lines[] = sprintf('Контент: %s', $contentPath);
$lines[] = sprintf('Языки: %s', implode(', ', array_keys($repository->locales())));
$lines[] = '';

foreach (array_keys($repository->locales()) as $locale) {
	$documents = $repository->allIncludingDrafts($locale);

	if ($documents === []) {
		continue;
	}

	$lines[] = sprintf('%s: %d документов', $locale, count($documents));

	foreach (DocumentTypes::TYPES as $type => $definition) {
		$count = count($repository->ofType($type, $locale));

		if ($count > 0) {
			$lines[] = sprintf('  %-12s %3d', $definition['plural'], $count);
		}
	}

	$lines[] = '';
}

$lines[] = sprintf(
	'Опубликовано: %d, тематик: %d, исследований: %d, утверждений: %d',
	$stats['total'],
	$stats['topics'],
	$stats['studies'],
	$stats['claims'],
);

if ($problems !== []) {
	$lines[] = '';
	$lines[] = sprintf('Проблемы (%d):', count($problems));

	foreach ($problems as $problem) {
		$lines[] = '  - ' . $problem;
	}
}

if ($unresolved !== []) {
	$lines[] = '';
	$lines[] = sprintf('Незакрытые Wikilinks (%d):', count($unresolved));

	foreach ($unresolved as $key => $targets) {
		$lines[] = sprintf('  - %s: %s', $key, implode(', ', array_unique($targets)));
	}
}

if ($imageProblems !== []) {
	$lines[] = '';
	$lines[] = sprintf('Битые картинки (%d):', count($imageProblems));

	foreach ($imageProblems as $problem) {
		$lines[] = '  - ' . $problem;
	}
}

echo implode("\n", $lines) . "\n";

if ($problems !== [] || $imageProblems !== []) {
	fwrite(STDERR, sprintf(
		"\nПроверка не пройдена: проблем — %d, битых картинок — %d.\n",
		count($problems),
		count($imageProblems),
	));

	exit(1);
}

echo "\nКонтент в порядке.\n";
exit(0);
