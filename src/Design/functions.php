<?php

declare(strict_types=1);

/**
 * Хелперы дизайн-системы.
 *
 * Имена с префиксом `longevypedia_`, потому что короткие глобальные функции в
 * проекте запрещены: они сталкиваются с хелперами BOLT (`render`, `validate`).
 */

use Bolt\Core\Runtime;
use Longevypedia\Design\Icons;
use Longevypedia\Design\Tokens;

/**
 * Токены из `_tokens.scss` — один экземпляр на запрос.
 */
function longevypedia_tokens(): Tokens
{
	return Runtime::app()->make(
		'longevypedia.tokens',
		static fn ($app): Tokens => Tokens::fromProject($app->basePath()),
	);
}

/**
 * Реестр иконок спрайта.
 */
function longevypedia_icons(): Icons
{
	return Runtime::app()->make(
		'longevypedia.icons',
		static fn ($app): Icons => Icons::fromProject($app->basePath()),
	);
}
