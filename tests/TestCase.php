<?php

declare(strict_types=1);

namespace Longevypedia\Tests;

use Bolt\Core\Application;
use Bolt\Core\Http\Headers;
use Bolt\Core\Http\Kernel;
use Bolt\Core\Http\Request;
use Bolt\Core\Runtime;
use Bolt\Testing\TestClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * База для тестов проекта: поднимает настоящее приложение из корня репозитория.
 *
 * Тесты не копируют проект во временный каталог: проверяется ровно то, что
 * лежит в `content/` и `schemas/`. Если документ сломан, тест падает — это и
 * нужно, потому что контент здесь такой же код, как PHP.
 *
 * Между тестами {@see Runtime::reset()} убирает приложение: иначе кэш
 * контента и `share()`-данные переезжали бы из теста в тест.
 */
abstract class TestCase extends BaseTestCase
{
	protected Application $app;

	protected function setUp(): void
	{
		parent::setUp();

		Runtime::reset();
		$this->app = Application::boot(dirname(__DIR__));
	}

	protected function tearDown(): void
	{
		Runtime::reset();

		parent::tearDown();
	}

	/**
	 * HTTP-клиент поверх настоящего `Kernel::handle()`.
	 */
	protected function client(): TestClient
	{
		return new TestClient($this->app);
	}

	/**
	 * Полный проход точки входа: `Kernel::run()` вместо `handle()`.
	 *
	 * Нужен там, где проверяется не разметка, а сам HTTP-ответ — заголовки
	 * ставит `run()` (в `handle()` их нет by design: он возвращает тело).
	 * Вывод перехватывается буфером, поэтому в консоль теста ничего не уходит.
	 *
	 * @return array{code: int, headers: array<string, string>, body: string}
	 */
	protected function runEntry(string $uri): array
	{
		$parts = parse_url($uri);
		$request = Request::make(
			(string) ($parts['path'] ?? '/'),
			'GET',
			[],
			[],
		);

		Headers::reset();
		ob_start();

		try {
			(new Kernel($this->app))->run($request);
		} finally {
			$body = (string) ob_get_clean();
		}

		return ['code' => Headers::code(), 'headers' => Headers::all(), 'body' => $body];
	}

	protected function projectRoot(): string
	{
		return dirname(__DIR__);
	}
}
