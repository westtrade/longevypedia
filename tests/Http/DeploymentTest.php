<?php

declare(strict_types=1);

namespace Longevypedia\Tests\Http;

use Longevypedia\Tests\TestCase;

/**
 * Раскладка на хостинге: корень проекта совпадает с docroot, фронт-контроллер
 * лежит в `public/`, а код и корпус закрыты `.htaccess`.
 *
 * Проверять это локальным запросом нельзя — Apache в тестах не участвует,
 * поэтому проверяются сами правила: ошибка в них видна сразу, а не после
 * выкладки. Три правила, на которых схема ломается, — в
 * `docs/development/deployment.md`.
 */
final class DeploymentTest extends TestCase
{
	private function htaccess(): string
	{
		$path = $this->projectRoot() . '/.htaccess';

		self::assertFileExists($path, 'в корне проекта нет .htaccess: выкладка откроет config.php и vendor/');

		return (string) file_get_contents($path);
	}

	public function testHtaccessRoutesCleanUrlsThroughPublic(): void
	{
		$rules = $this->htaccess();

		self::assertMatchesRegularExpression(
			'/RewriteCond %\{DOCUMENT_ROOT\}\/public%\{REQUEST_URI\} -f\s*\n\s*RewriteRule \^\(\.\*\)\$ public\/\$1 \[L\]/',
			$rules,
			'статика из public/ должна отдаваться по чистому URL',
		);

		self::assertMatchesRegularExpression(
			'/RewriteCond %\{REQUEST_FILENAME\} !-f\s*\n\s*RewriteRule \^\(\.\*\)\$ public\/index\.php \[L\]/',
			$rules,
			'остальное должно уходить во фронт-контроллер',
		);

		// Редирект с `/public/…` на чистый URL ловит собственную подстановку
		// `public/index.php` — сайт отдаёт вечный 301, который браузер помнит.
		self::assertDoesNotMatchRegularExpression(
			'/RewriteRule \^public\/.*\[R[=,]?/i',
			$rules,
			'редиректа с /public/ быть не должно: он ловит сам себя',
		);
	}

	public function testHtaccessClosesTheProjectTree(): void
	{
		$rules = $this->htaccess();

		// Корень проекта — docroot, поэтому каждый каталог с кодом или данными
		// должен быть закрыт явно: Apache сам его не спрячет.
		foreach (['app', 'src', 'content', 'schemas', 'taxonomy', 'data', 'vendor', 'var'] as $dir) {
			self::assertMatchesRegularExpression(
				'/RedirectMatch 404 \^\/\([^)]*\)/',
				$rules,
				'закрывающее правило RedirectMatch 404 должно быть',
			);

			self::assertStringContainsString(
				$dir,
				$this->denyList($rules),
				"каталог {$dir}/ открыт по HTTP: его нет в списке RedirectMatch 404",
			);
		}

		// Имена файлов в правиле экранированы (`config\.php`), поэтому
		// сравниваем без обратных слэшей: экранирование — синтаксис, а не смысл.
		$plain = str_replace('\\', '', $rules);

		// `composer.(json|lock)` — группа альтернатив, поэтому в списке ищем
		// основу имени, а не имя файла целиком.
		$needles = [
			'config.php' => 'config.php',
			'composer.json' => 'composer.',
			'deploy.env' => 'deploy.env',
			'.env' => '.env',
		];

		foreach ($needles as $file => $needle) {
			self::assertStringContainsString(
				$needle,
				$plain,
				"файл {$file} открыт по HTTP",
			);
		}
	}

	/** Список каталогов из первого `RedirectMatch 404`, без регэкспного мусора. */
	private function denyList(string $rules): string
	{
		if (preg_match('/RedirectMatch 404 \^\/\(([^)]*)\)/', $rules, $matches) !== 1) {
			return '';
		}

		return $matches[1];
	}

	public function testDeployCredentialsAreNotCommitted(): void
	{
		$ignore = (string) file_get_contents($this->projectRoot() . '/.gitignore');

		self::assertMatchesRegularExpression('/^\/deploy\.env$/m', $ignore, 'deploy.env содержит пароль FTP — он должен быть в .gitignore');
		self::assertMatchesRegularExpression('/^!\/deploy\.env\.example$/m', $ignore, 'шаблон deploy.env.example должен остаться в репозитории');

		self::assertFileExists($this->projectRoot() . '/deploy.env.example', 'без шаблона новый разработчик не узнает про ключи выкладки');

		// В шаблоне не должно быть настоящих доступов.
		$example = (string) file_get_contents($this->projectRoot() . '/deploy.env.example');
		self::assertStringNotContainsString('62e66d042044', $example, 'в шаблоне не должно быть настоящего хоста');
		self::assertStringNotContainsString('Longevy-2026', $example, 'в шаблоне не должно быть настоящего пароля');
	}

	public function testDeployScriptBuildsAndDelegates(): void
	{
		$script = $this->projectRoot() . '/scripts/deploy';

		self::assertFileExists($script, 'без scripts/deploy выкладка требует помнить путь к бандлу');

		$body = (string) file_get_contents($script);
		self::assertStringContainsString('vite build', $body, 'скрипт должен собирать клиент перед выкладкой');
		self::assertStringContainsString('bolt-deploy/bin/deploy', $body, 'скрипт должен вызывать бандл выкладки');
		self::assertStringContainsString('--no-build', $body, 'сборка уже сделана скриптом — второй раз её запускать незачем');
	}
}
