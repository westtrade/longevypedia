<?php

declare(strict_types=1);

use Bolt\Core\Application;
use Bolt\Core\Runtime;
use Longevypedia\Content\ContentRepository;

/**
 * Хелперы контента, доступные шаблонам.
 *
 * Одна функция, одно имя с префиксом проекта: короткие глобальные имена
 * (`content`, `docs`) конфликтуют с хелперами Bolt и с функциями будущих
 * бандлов.
 */

/**
 * Индекс контента текущего приложения.
 *
 * Собирается один раз на процесс (`Application::make()` — ленивый синглтон),
 * поэтому несколько компонентов на странице не перечитывают корпус.
 */
function longevypedia_content(): ContentRepository
{
	return Runtime::app()->make(
		'longevypedia.content',
		static fn (Application $app): ContentRepository => ContentRepository::fromConfig($app->config()),
	);
}

/**
 * Подпись тематики для интерфейса: `aging-mechanisms` → `Aging mechanisms`.
 *
 * Слаг — значение фронтматтера, а не текст для читателя. В карточке каталога он
 * выглядел машинным кодом рядом с фильтром, где та же тематика подписана
 * человеческим именем. Неизвестная тематика возвращается как есть: таксономия
 * должна быть видна как недоработка, а не молча исчезать.
 */
function longevypedia_topic_label(string $slug, ?string $locale = null): string
{
	static $cache = [];

	$locale ??= longevypedia_content()->defaultLocale();

	if (!isset($cache[$locale])) {
		$cache[$locale] = array_map(
			static fn (array $topic): string => (string) $topic['label'],
			longevypedia_content()->topics($locale),
		);
	}

	return $cache[$locale][$slug] ?? $slug;
}

/**
 * Дата для интерфейса: `2026-11-20` → `20 Nov 2026`.
 *
 * Отдельная функция, потому что даты в шаблонах нужны в нескольких местах, а
 * `ru_date()` из Bolt форматирует по-русски. Неизвестный формат возвращается
 * как есть — лучше показать исходную строку, чем упасть на дате из фронтматтера.
 */
function longevypedia_date(?string $iso): string
{
	if ($iso === null || $iso === '') {
		return '';
	}

	$date = \DateTimeImmutable::createFromFormat('!Y-m-d', substr($iso, 0, 10));

	if ($date === false) {
		return $iso;
	}

	return $date->format('j M Y');
}
