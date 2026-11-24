#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Проверка гигиены репозитория: запрещённые файлы, секреты, JSON Schema,
 * YAML и ссылки в документации.
 *
 * Дополняет `scripts/content-check.php`: тот отвечает за корпус контента,
 * этот — за то, что вокруг него. Запускается локально (`composer repo`) и в CI.
 * Код выхода 1 означает, что коммит публиковать нельзя.
 */

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

/**
 * Каталоги, которых в репозитории быть не должно: зависимости, генерации,
 * локальное состояние. Список совпадает с .gitignore — расхождение между ними
 * и есть та ошибка, которую ловит эта проверка.
 *
 * Проверяются не каталоги на диске (они есть у любого, кто собрал проект), а
 * список файлов, попадающих в коммит.
 */
$forbiddenPrefixes = [
	'vendor/',
	'node_modules/',
	'.bolt/',
	'public/build/',
	'var/',
	'tmp/',
];

$forbiddenFiles = [
	'.env',
	'.env.local',
	'.phpunit.result.cache',
	'bun.lockb',
];

$problems = [];
$checked = 0;

/**
 * Список файлов, которые попадут в коммит: `git ls-files`, если проект уже
 * под git, иначе обход дерева без служебных каталогов.
 *
 * @return list<string> пути относительно корня
 */
function collectFiles(string $root): array
{
	$git = trim((string) @shell_exec('cd ' . escapeshellarg($root) . ' && git rev-parse --is-inside-work-tree 2>/dev/null'));

	if ($git === 'true') {
		$output = @shell_exec('cd ' . escapeshellarg($root) . ' && git ls-files -z 2>/dev/null');

		if (is_string($output) && $output !== '') {
			$files = array_values(array_filter(explode("\0", $output), static fn (string $file): bool => $file !== ''));
			sort($files);

			return $files;
		}
	}

	$files = [];
	$skip = ['.git', 'vendor', 'node_modules', '.bolt', 'var', 'tmp', 'build'];
	$iterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
			static fn (SplFileInfo $file): bool => !$file->isDir() || !in_array($file->getFilename(), $skip, true),
		),
	);

	foreach ($iterator as $file) {
		if (!$file instanceof SplFileInfo || !$file->isFile()) {
			continue;
		}

		$files[] = ltrim(substr($file->getPathname(), strlen($root)), '/');
	}

	sort($files);

	return $files;
}

$files = collectFiles($root);

// 1. Запрещённые пути: ни один файл из коммита не должен лежать в них.
foreach ($files as $relative) {
	foreach ($forbiddenPrefixes as $prefix) {
		if (str_starts_with($relative, $prefix)) {
			$problems[] = sprintf('%s: каталог «%s» не публикуется (см. .gitignore).', $relative, rtrim($prefix, '/'));

			continue 2;
		}
	}

	if (in_array($relative, $forbiddenFiles, true)) {
		$problems[] = sprintf('%s: локальное состояние не публикуется (см. .gitignore).', $relative);
	}
}

// 2. Секреты. Шаблоны намеренно узкие: лучше пропустить, чем устроить
//    ложное срабатывание на каждом втором файле и приучить игнорировать вывод.
$secretPatterns = [
	'/gh[pousr]_[A-Za-z0-9]{20,}/' => 'токен GitHub',
	'/github_pat_[A-Za-z0-9_]{20,}/' => 'fine-grained токен GitHub',
	'/AKIA[0-9A-Z]{16}/' => 'ключ AWS',
	'/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/' => 'приватный ключ',
	// Значения вида "${ACQUIA_API_KEY}" и "$TOKEN" — подстановки из окружения,
	// а не секреты, поэтому они исключены отрицательным просмотром вперёд.
	'/(?:api[_-]?key|secret|password|passwd|token)\s*[:=]\s*[\'"](?!\$)[^\'"\s]{12,}[\'"]/i' => 'похоже на пароль или ключ',
];

$textExtensions = ['php', 'ts', 'js', 'scss', 'css', 'json', 'yaml', 'yml', 'md', 'svg', 'xml', 'txt', 'html', 'sh', 'ini', 'conf'];

foreach ($files as $relative) {
	$extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

	if (!in_array($extension, $textExtensions, true)) {
		continue;
	}

	$contents = @file_get_contents($root . '/' . $relative);

	if ($contents === false) {
		continue;
	}

	$checked++;

	foreach ($secretPatterns as $pattern => $label) {
		if (preg_match($pattern, $contents) === 1) {
			$problems[] = sprintf('%s: найдено «%s».', $relative, $label);
		}
	}
}

// 3. JSON Schema: файл должен быть валидным JSON с $schema и $id.
foreach (glob($root . '/schemas/*.json') ?: [] as $file) {
	$name = 'schemas/' . basename($file);
	$decoded = json_decode((string) file_get_contents($file), true);

	if (!is_array($decoded)) {
		$problems[] = sprintf('%s: невалидный JSON (%s).', $name, json_last_error_msg());

		continue;
	}

	foreach (['$schema', '$id', 'title'] as $key) {
		if (!isset($decoded[$key])) {
			$problems[] = sprintf('%s: нет ключа «%s».', $name, $key);
		}
	}
}

// 4. YAML: конфиг DDEV, workflow и прочие yaml/yml-файлы должны парситься.
foreach ($files as $relative) {
	if (!preg_match('/\.ya?ml$/', $relative)) {
		continue;
	}

	try {
		Yaml::parseFile($root . '/' . $relative);
	} catch (ParseException $exception) {
		$problems[] = sprintf('%s: YAML не парсится — %s', $relative, $exception->getMessage());
	}
}

// 5. Ссылки в документации: относительный путь должен существовать.
$brokenLinks = [];

foreach ($files as $relative) {
	if (!str_starts_with($relative, 'docs/') || !str_ends_with($relative, '.md')) {
		continue;
	}

	$contents = (string) file_get_contents($root . '/' . $relative);

	preg_match_all('/\[[^\]]*\]\(([^)\s]+)\)/u', $contents, $matches);

	foreach ($matches[1] as $target) {
		$target = trim($target);

		if ($target === '' || str_starts_with($target, '#') || preg_match('~^(https?:|mailto:|tel:)~i', $target) === 1) {
			continue;
		}

		// Ссылка не может содержать скобки и фигурные скобки — значит, это
		// фрагмент кода вроде `[A-Z][A-Z0-9]{3}`, а не Markdown-ссылка.
		if (preg_match('~[][{}]~', $target) === 1) {
			continue;
		}

		$path = str_starts_with($target, '/')
			? $root . $target
			: dirname($root . '/' . $relative) . '/' . $target;

		$path = preg_replace('/#.*$/', '', $path) ?? $path;

		if (!file_exists($path)) {
			$brokenLinks[] = sprintf('%s: ссылка «%s» никуда не ведёт.', $relative, $target);
		}
	}
}

$problems = array_merge($problems, $brokenLinks);

$lines = [];
$lines[] = sprintf('Файлов проверено: %d', $checked);
$lines[] = sprintf('Схем: %d', count(glob($root . '/schemas/*.json') ?: []));

if ($problems !== []) {
	$lines[] = '';
	$lines[] = sprintf('Проблемы (%d):', count($problems));

	foreach ($problems as $problem) {
		$lines[] = '  - ' . $problem;
	}
}

echo implode("\n", $lines) . "\n";

if ($problems !== []) {
	fwrite(STDERR, sprintf("\nПроверка не пройдена: %d проблем(ы).\n", count($problems)));

	exit(1);
}

echo "\nРепозиторий в порядке.\n";
exit(0);
