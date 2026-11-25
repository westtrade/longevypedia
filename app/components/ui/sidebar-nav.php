<?php

declare(strict_types=1);

/**
 * Боковая навигация: содержание статьи, разделы страницы, список разделов.
 *
 * Плоский режим (`items`) — содержание статьи и якоря. Режим групп (`groups`) —
 * каталог разделов, где у каждой группы своя подпись: так устроено меню
 * UI-набора, и отдельного компонента для него не нужно.
 *
 * @var string|null $title заголовок блока в плоском режиме
 * @var array<int, array{label: string, url: string, active?: bool, level?: int}> $items
 * @var array<int, array{title?: string|null, items: array<int, array{label: string, url: string, active?: bool}>}> $groups
 * @var bool $sticky прилипает при прокрутке
 * @var bool $headings подсвечивать текущий раздел при прокрутке (нужны якоря этой страницы)
 */

$title ??= null;
$items ??= [];
$groups ??= [];
$sticky ??= false;
$headings ??= false;

if ($groups !== []) {
	$sections = array_values(array_filter(
		array_map(static function (array $group): array {
			return [
				'title' => $group['title'] ?? null,
				'items' => (array) ($group['items'] ?? []),
			];
		}, $groups),
		static fn (array $group): bool => $group['items'] !== [],
	));
	$label = $title;
} elseif ($items !== []) {
	$sections = [['title' => $title, 'items' => $items]];
	$label = $title;
} else {
	return;
}

if ($sections === []) {
	return;
}

$isGrouped = $groups !== [];

/* Подсветка по прокрутке имеет смысл только для оглавления самой страницы:
   курсор «где я сейчас» определён лишь тогда, когда все пункты — якоря на
   этой же странице. У меню набора ссылки ведут на другие страницы. */
$tracksHeadings = $headings && !$isGrouped;

if ($tracksHeadings) {
	foreach ($sections as $section) {
		foreach ($section['items'] as $item) {
			if (!str_starts_with((string) ($item['url'] ?? ''), '#')) {
				$tracksHeadings = false;
				break 2;
			}
		}
	}
}

$navAttrs = '';
$navAttrs .= $sticky ? ' data-sticky' : '';
$navAttrs .= $tracksHeadings ? ' data-heading-nav' : '';
?>
<nav class="ui-sidebar-nav<?= $sticky ? ' ui-sidebar-nav--sticky' : '' ?><?= $isGrouped ? ' ui-sidebar-nav--grouped' : '' ?><?= $tracksHeadings ? ' ui-sidebar-nav--headings' : '' ?>"<?= $label !== null ? ' aria-label="' . e($label) . '"' : '' ?><?= $navAttrs ?>>
	<?php foreach ($sections as $section): ?>
		<div class="ui-sidebar-nav__group">
			<?php if (($section['title'] ?? null) !== null): ?>
				<p class="ui-sidebar-nav__title"><?= e((string) $section['title']) ?></p>
			<?php endif ?>
			<ul class="ui-sidebar-nav__list">
				<?php foreach ($section['items'] as $item): ?>
					<?php
					$isActive = (bool) ($item['active'] ?? false);
					$level = max(0, (int) ($item['level'] ?? 0));
					?>
					<li class="ui-sidebar-nav__item">
						<a class="ui-sidebar-nav__link<?= $isActive ? ' ui-sidebar-nav__link--active' : '' ?>"
							href="<?= e((string) ($item['url'] ?? '#')) ?>"
							style="--ui-sidebar-nav-level: <?= e((string) $level) ?>"
							<?= $isActive ? 'aria-current="true"' : '' ?>><?= e((string) ($item['label'] ?? '')) ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
	<?php endforeach ?>
</nav>

<style lang="scss">
	/* Липнет сам компонент, но не его родитель. У `position: sticky` нет
	   запаса хода внутри собственного бокса: если элемент стоит в колонке
	   сетки равной его высоте, он просто уедет вверх вместе с колонкой.
	   Поэтому колонка страницы растягивается на высоту ряда, а липнет
	   ребёнок — здесь. */
	.ui-sidebar-nav--sticky {
		position: sticky;
		top: calc(var(--layout-header) + var(--space-4));
		align-self: start;
		max-height: calc(100vh - var(--layout-header) - var(--space-8));
		overflow-y: auto;
		overscroll-behavior: contain;
	}

	.ui-sidebar-nav__title {
		margin: 0 0 var(--space-2);
		color: var(--color-text-subtle);
		font-family: var(--font-ui);
		font-size: var(--text-xs);
		font-weight: var(--weight-semibold);
		letter-spacing: var(--tracking-wide);
		text-transform: uppercase;
	}

	/* Группы разделов: подпись + список, промежуток между группами больше,
	   чем между пунктами внутри. */
	.ui-sidebar-nav--grouped {
		display: flex;
		flex-direction: column;
		gap: var(--space-5);
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__list {
		border-left: 0;
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link {
		margin-left: 0;
		border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link--active {
		background: var(--color-brand-subtle);
	}

	.ui-sidebar-nav--grouped .ui-sidebar-nav__link:hover {
		background: var(--color-surface-alt);
		border-left-color: transparent;
	}

	.ui-sidebar-nav__list {
		display: flex;
		flex-direction: column;
		gap: 1px;
		margin: 0;
		padding: 0;
		list-style: none;
		border-left: 1px solid var(--color-border);
	}

	.ui-sidebar-nav__link {
		display: block;
		padding: var(--space-1) var(--space-3);
		padding-left: calc(var(--space-3) + var(--ui-sidebar-nav-level, 0) * var(--space-3));
		border-left: 2px solid transparent;
		margin-left: -1px;
		color: var(--color-text-muted);
		font-family: var(--font-ui);
		font-size: var(--text-sm);
		line-height: var(--leading-normal);
		text-decoration: none;
	}

	.ui-sidebar-nav__link:hover {
		border-left-color: var(--color-border-strong);
		color: var(--color-brand-text);
	}

	.ui-sidebar-nav__link--active {
		border-left-color: var(--color-brand);
		color: var(--color-brand-text);
		font-weight: var(--weight-medium);
	}

	.ui-sidebar-nav__link:focus-visible {
		outline: none;
		box-shadow: var(--focus-ring);
	}

	/* Оглавление страницы: подпись «Sections» над пунктами полезна, но при
	   липкой панели она съедает место; активный пункт читается и без неё. */
	.ui-sidebar-nav--headings .ui-sidebar-nav__list {
		border-left: 1px solid var(--color-border);
	}

	.ui-sidebar-nav--headings .ui-sidebar-nav__link {
		transition: border-color 120ms ease, color 120ms ease;
	}
</style>

<?php if ($tracksHeadings): ?>
	<?php /* `inline` обязателен: AssetExtractingEngine вырезает любой <script>
	   без него, как и <style>. */ ?>
	<script type="module" inline>
		/**
		 * Подсветка текущего раздела по прокрутке.
		 *
		 * Считаем позиции напрямую, а не через IntersectionObserver: у страницы
		 * включена плавная прокрутка, и наблюдатель во время неё отдаёт события
		 * пачками — активная строка успевает мигнуть на промежуточных разделах.
		 * Прямой расчёт даёт ровно один активный пункт в любой момент.
		 */
		const nav = document.querySelector('.ui-sidebar-nav--headings');

		if (nav) {
			const links = [...nav.querySelectorAll('.ui-sidebar-nav__link')];
			const targets = links
				.map((link) => {
					const id = (link.getAttribute('href') || '').slice(1);
					const section = id ? document.getElementById(id) : null;
					return section ? { link, section } : null;
				})
				.filter(Boolean);

			let current = null;

			const headerOffset = () => {
				const header = document.querySelector('.site-header');
				return header ? header.getBoundingClientRect().height : 0;
			};

			const update = () => {
				if (targets.length === 0) {
					return;
				}

				const limit = headerOffset() + 8;
				let active = targets[0];

				for (const target of targets) {
					if (target.section.getBoundingClientRect().top - limit <= 0) {
						active = target;
					}
				}

				/* Дно страницы: последний раздел читают до конца, хотя его
				   заголовок уже ушёл выше линии. Без этого активным оставался
				   бы предпоследний пункт. */
				if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
					active = targets[targets.length - 1];
				}

				if (active === current) {
					return;
				}

				current = active;

				for (const target of targets) {
					const isActive = target === active;
					target.link.classList.toggle('ui-sidebar-nav__link--active', isActive);
					if (isActive) {
						target.link.setAttribute('aria-current', 'true');
					} else {
						target.link.removeAttribute('aria-current');
					}
				}
			};

			let scheduled = false;
			const schedule = () => {
				if (scheduled) {
					return;
				}
				scheduled = true;
				requestAnimationFrame(() => {
					scheduled = false;
					update();
				});
			};

			/* Скроллится документ, а событие при прокрутке всплывает до
			   window: слушаем capture на document, чтобы поймать и вложенные
			   контейнеры, если они появятся. */
			window.addEventListener('scroll', schedule, { passive: true });
			document.addEventListener('scroll', schedule, { passive: true, capture: true });
			window.addEventListener('resize', schedule, { passive: true });
			update();
		}
	</script>
<?php endif ?>
