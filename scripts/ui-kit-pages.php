<?php

declare(strict_types=1);

/**
 * Генератор страниц UI-набора.
 *
 * Запуск: `php scripts/ui-kit-pages.php`. Пишет `data/ui-kit.php` (каталог
 * пунктов) и `app/pages/ui-kit/<slug>/page.php` (по странице на компонент).
 * Страницы не правятся руками — правится спецификация здесь и генератор
 * перезапускается; так разметка образца и её исходник не расходятся.
 *
 * Страницы основ (`overview`, `tokens`, `icons`, `accessibility`) пишутся
 * вручную: у них своё содержимое, а не список вызовов компонентов.
 */

$root = dirname(__DIR__);

/** Группы каталога: порядок задаёт и меню, и индекс. */
$groups = [
	'Основы' => ['overview', 'tokens', 'icons', 'accessibility'],
	'Действия' => ['button', 'theme-toggle'],
	'Формы' => ['field', 'choice'],
	'Навигация' => ['breadcrumbs', 'tabs', 'pagination', 'menu', 'mobile-nav', 'sidebar-nav'],
	'Контент' => ['card', 'document-card', 'research-card', 'author-card', 'badge', 'tag', 'meta', 'callout', 'quote', 'alert', 'divider', 'accordion', 'tooltip', 'empty-state'],
	'Данные' => ['data-table', 'key-value', 'stat', 'progress', 'chart'],
	'Научные данные' => ['evidence', 'evidence-summary', 'claim-status', 'research-metadata', 'citation', 'source-list'],
	'Обратная связь' => ['toast', 'skeleton'],
	'Бренд' => ['wordmark'],
];

/**
 * Спецификация страниц.
 *
 * `specimens` — образцы по порядку: заголовок, пояснение, код вызова.
 * Код вставляется дважды: как исходник под примером и как сам живой пример,
 * поэтому копия в блоке кода всегда совпадает с тем, что нарисовано.
 */
$pages = [
	'button' => [
		'title' => 'Кнопка',
		'note' => 'Действия: вид, размер, состояние, иконка, ширина.',
		'specimens' => [
			['Основное', 'primary — главное действие на экране; одно на страницу', "<?= \$this->insert('components::ui/button', ['label' => 'Сохранить', 'variant' => 'primary']) ?>"],
			['Виды', 'вид отвечает за вес действия, а не за цвет бренда', "<?= \$this->insert('components::ui/button', ['label' => 'Основное', 'variant' => 'primary']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Вторичное', 'variant' => 'secondary']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Контур', 'variant' => 'outline']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Тихое', 'variant' => 'ghost']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Удалить', 'variant' => 'danger']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Ссылка', 'variant' => 'link'])?>"],
			['Размеры', 'sm — в таблице и тулбаре, md — обычный, lg — первый экран', "<?= \$this->insert('components::ui/button', ['label' => 'Маленькая', 'size' => 'sm']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Обычная', 'size' => 'md']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Крупная', 'size' => 'lg']) ?>"],
			['Состояния', 'загрузка блокирует повторное нажатие и объявляется через aria-busy', "<?= \$this->insert('components::ui/button', ['label' => 'Недоступно', 'disabled' => true]) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Сохраняем', 'loading' => true]) ?>"],
			['Иконки', 'у кнопки-иконки нет видимого текста — имя уходит в aria-label', "<?= \$this->insert('components::ui/button', ['label' => 'Найти', 'icon' => 'search']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Далее', 'iconRight' => 'arrow-right']) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Скачать', 'icon' => 'download', 'iconOnly' => true]) ?>"],
			['Ширина', 'block — на всю ширину формы или панели', "<?= \$this->insert('components::ui/button', ['label' => 'Войти', 'block' => true]) ?>"],
		],
	],
	'theme-toggle' => [
		'title' => 'Переключатель темы',
		'note' => 'Кнопка-значок открывает список из трёх состояний: как в системе, светлая, тёмная. Текущее состояние видно по значку и по галочке в списке.',
		'specimens' => [
			['Основное', 'значок показывает выбранное состояние: половина круга — «как в системе», солнце — светлая, луна — тёмная', "<?= \$this->insert('components::ui/theme-toggle') ?>"],
			['В ряду действий', 'высота и поля совпадают с соседними кнопками — отдельной вёрстки у переключателя нет', "<?= \$this->insert('components::ui/theme-toggle') ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Скачать', 'variant' => 'ghost', 'icon' => 'download', 'iconOnly' => true]) ?>\n<?= \$this->insert('components::ui/button', ['label' => 'Оформить подписку']) ?>"],
		],
	],
	'field' => [
		'title' => 'Поле ввода',
		'note' => 'Текст, поиск, выбор, дата, файл, диапазон: подпись, описание, ошибка.',
		'specimens' => [
			['Основное', 'подпись связана с полем, id строится из имени', "<?= \$this->insert('components::ui/field', ['name' => 'query', 'label' => 'Поиск по энциклопедии', 'type' => 'search', 'placeholder' => 'сенолитики, mTOR, саркопения']) ?>"],
			['Описание и ошибка', 'ошибка не заменяет описание: скринридер читает оба', "<?= \$this->insert('components::ui/field', ['name' => 'email', 'label' => 'Почта', 'type' => 'email', 'description' => 'Нужна для ответа на правку.', 'error' => 'Адрес не похож на почтовый.']) ?>\n<?= \$this->insert('components::ui/field', ['name' => 'doi', 'label' => 'DOI', 'description' => 'Только если идентификатор проверен.', 'required' => true]) ?>"],
			['Выбор и текст', 'select и textarea — те же состояния, что у input', "<?= \$this->insert('components::ui/field', ['name' => 'type', 'label' => 'Тип документа', 'type' => 'select', 'options' => ['concept' => 'Понятие', 'mechanism' => 'Механизм', 'research' => 'Исследование']]) ?>\n<?= \$this->insert('components::ui/field', ['name' => 'note', 'label' => 'Комментарий к правке', 'type' => 'textarea', 'rows' => 4]) ?>"],
			['Обязательное', 'required помечает подпись и ставит атрибут на поле', "<?= \$this->insert('components::ui/field', ['name' => 'title', 'label' => 'Название документа', 'required' => true, 'description' => 'Короткое и точное: по нему документ ищут.']) ?>"],
			['Дата, файл, диапазон', 'типы без отдельного компонента: разметку даёт то же поле', "<?= \$this->insert('components::ui/field', ['name' => 'reviewed', 'label' => 'Дата проверки', 'type' => 'date', 'value' => '2026-11-20']) ?>\n<?= \$this->insert('components::ui/field', ['name' => 'figure', 'label' => 'Рисунок', 'type' => 'file', 'description' => 'SVG или PNG до 2 МБ.']) ?>\n<?= \$this->insert('components::ui/field', ['name' => 'confidence', 'label' => 'Уверенность', 'type' => 'range', 'value' => '3']) ?>"],
			['Отключённое', 'состояние видно и объяснено, а не только серое', "<?= \$this->insert('components::ui/field', ['name' => 'slug', 'label' => 'Адрес', 'value' => 'mechanisms/autophagy', 'disabled' => true, 'description' => 'Адрес меняется только через перенос документа.']) ?>"],
		],
	],
	'choice' => [
		'title' => 'Выбор',
		'note' => 'Флажок, переключатель, тумблер: нативное поле под стилизованной меткой.',
		'specimens' => [
			['Флажок', 'для независимых параметров', "<?= \$this->insert('components::ui/choice', ['name' => 'published', 'type' => 'checkbox', 'label' => 'Опубликовано', 'description' => 'Видно в каталоге и поиске.']) ?>"],
			['Переключатель', 'для взаимоисключающего выбора', "<?= \$this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Черновик']) ?>\n<?= \$this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'На проверке']) ?>\n<?= \$this->insert('components::ui/choice', ['name' => 'status', 'type' => 'radio', 'label' => 'Опубликовано']) ?>"],
			['Тумблер', 'мгновенное переключение настройки', "<?= \$this->insert('components::ui/choice', ['name' => 'theme', 'type' => 'switch', 'label' => 'Тёмная тема', 'description' => 'Следует за системной, пока не выбран вручную.']) ?>"],
			['Отмеченные', 'знак по центру контрола: галочка, точка и кружок тумблера', "<?= \$this->insert('components::ui/choice', ['name' => 'published-checked', 'type' => 'checkbox', 'label' => 'Опубликовано', 'checked' => true]) ?>\n<?= \$this->insert('components::ui/choice', ['name' => 'status-checked', 'type' => 'radio', 'label' => 'Опубликовано', 'checked' => true]) ?>\n<?= \$this->insert('components::ui/choice', ['name' => 'theme-checked', 'type' => 'switch', 'label' => 'Тёмная тема', 'checked' => true]) ?>"],
			['Ошибка', 'ошибка относится к группе, а не к одной метке', "<?= \$this->insert('components::ui/choice', ['name' => 'license', 'type' => 'checkbox', 'label' => 'Согласен с лицензией CC BY-SA 4.0', 'error' => 'Без согласия правку нельзя опубликовать.']) ?>"],
		],
	],
	'breadcrumbs' => [
		'title' => 'Хлебные крошки',
		'note' => 'Путь от раздела к текущей странице; последний пункт — не ссылка.',
		'specimens' => [
			['Основное', 'разделитель рисует CSS, в разметке его нет', "<?= \$this->insert('components::ui/breadcrumbs', ['items' => [['label' => 'Знания', 'url' => '/catalog'], ['label' => 'Механизмы', 'url' => '/mechanisms'], ['label' => 'Аутофагия']]]) ?>"],
		],
	],
	'tabs' => [
		'title' => 'Вкладки',
		'note' => 'Разделы страницы ссылками: состояние в адресе, а не в скрипте.',
		'specimens' => [
			['Основное', 'счётчик показывает размер раздела', "<?= \$this->insert('components::ui/tabs', ['items' => [['label' => 'Все документы', 'url' => '/catalog', 'active' => true, 'count' => 10], ['label' => 'Исследования', 'url' => '/research', 'count' => 1], ['label' => 'Утверждения', 'url' => '/claims', 'count' => 1]]]) ?>"],
		],
	],
	'pagination' => [
		'title' => 'Постраничная навигация',
		'note' => 'Номера страниц ссылками; окно вокруг текущей и разрывы.',
		'specimens' => [
			['Основное', 'адрес страницы собирает вызывающий шаблон', "<?= \$this->insert('components::ui/pagination', ['current' => 3, 'total' => 12, 'href' => '/catalog?page=']) ?>"],
		],
	],
	'menu' => [
		'title' => 'Меню',
		'note' => 'Раскрывающаяся панель на details: работает без JavaScript.',
		'specimens' => [
			['Основное', 'для действий над документом и выбора раздела', "<?= \$this->insert('components::ui/menu', ['label' => 'Действия', 'items' => [['label' => 'Скачать Markdown', 'url' => '#', 'description' => 'Исходный файл документа'], ['label' => 'История изменений', 'url' => '#', 'description' => 'Коммиты в GitHub'], ['label' => 'Предложить правку', 'url' => '#']]]) ?>"],
		],
	],
	'sidebar-nav' => [
		'title' => 'Боковая навигация',
		'note' => 'Содержание статьи и разделы страницы; уровень задаётся отступом.',
		'specimens' => [
			['Содержание', 'уровень вложенности приходит параметром', "<?= \$this->insert('components::ui/sidebar-nav', ['title' => 'На этой странице', 'items' => [['label' => 'Определение', 'url' => '#definition', 'active' => true], ['label' => 'Механизм', 'url' => '#mechanism'], ['label' => 'Данные', 'url' => '#evidence', 'level' => 1], ['label' => 'Источники', 'url' => '#sources']]]) ?>"],
		],
	],
	'mobile-nav' => [
		'title' => 'Мобильная навигация',
		'note' => 'Меню для узкого экрана: раскрывается без JavaScript, закрывается повторным нажатием.',
		'specimens' => [
			['Закрытое', 'на узком экране стоит в шапке рядом с логотипом; здесь показано на широком через `preview`', "<?= \$this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav', 'label' => 'Основная навигация', 'current' => 'catalog', 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>", ['stack' => true]],
			['Открытое', 'панель раскрывается без JavaScript; текущий раздел помечен aria-current', "<?= \$this->insert('components::ui/mobile-nav', ['id' => 'kit-mobile-nav-open', 'label' => 'Основная навигация', 'current' => 'catalog', 'open' => true, 'preview' => true, 'items' => [['key' => 'catalog', 'label' => 'Knowledge', 'url' => '/catalog'], ['key' => 'research', 'label' => 'Research', 'url' => '/research'], ['key' => 'about', 'label' => 'About', 'url' => '/about']]]) ?>", ['stack' => true]],
		],
	],
	'card' => [
		'title' => 'Карточка',
		'note' => 'Контейнер материала: надзаголовок, заголовок, метаданные, действия.',
		'specimens' => [
			['Основное', 'карточка-ссылка: вся площадь кликабельна, фокус на ссылке', "<?= \$this->insert('components::ui/card', ['eyebrow' => 'Механизм', 'title' => 'Клеточное старение', 'meta' => 'Обновлено 20 ноября 2026', 'href' => '/mechanisms/cellular-senescence', 'children' => 'Клетки перестают делиться, но остаются в ткани и выделяют сигнальные молекулы.']) ?>"],
			['Виды', 'outlined — на белом фоне, flat — внутри другой карточки', "<?= \$this->insert('components::ui/card', ['title' => 'Определение', 'variant' => 'outlined', 'children' => 'Старение — постепенное снижение функции организма со временем.']) ?>\n<?= \$this->insert('components::ui/card', ['title' => 'Заметка', 'variant' => 'flat', 'children' => 'Вложенная карточка не добавляет вторую рамку.']) ?>"],
			['С действиями', 'кнопки в подвале — не ссылки на всю карточку', "<?= \$this->insert('components::ui/card', ['title' => 'Исследование', 'meta' => 'Harrison et al., 2009', 'children' => 'Рапамицин продлевает медианную и максимальную продолжительность жизни мышей.', 'footer' => 'Nature, 2009']) ?>"],
		],
	],
	'document-card' => [
		'title' => 'Карточка документа',
		'note' => 'Документ в списке: тип, дата, заголовок, краткое описание, тематики.',
		'specimens' => [
			['Основное', 'тип и дата — метаданные, заголовок — ссылка на документ', "<?php \$doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/document-card', ['document' => \$doc]) ?>"],
			['С тематиками', 'showTopics добавляет ссылки в каталог; summary можно убрать в плотном списке', "<?php \$doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/document-card', ['document' => \$doc, 'showTopics' => true, 'showSummary' => false]) ?>"],
			['Без типа', 'showType выключается там, где список уже отфильтрован по типу', "<?php \$doc = longevypedia_content()->recent(1, 'en')[0] ?? null; ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/document-card', ['document' => \$doc, 'showType' => false]) ?>"],
		],
	],
	'research-card' => [
		'title' => 'Карточка исследования',
		'note' => 'Публикация в списке: дизайн, авторы, журнал, год, число исходов.',
		'specimens' => [
			['Основное', 'дизайн и библиография берутся из фронтматтера, а не из текста', "<?php \$doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/research-card', ['document' => \$doc]) ?>"],
			['Уровень заголовка', 'headingLevel держит порядок заголовков страницы', "<?php \$doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/research-card', ['document' => \$doc, 'headingLevel' => 2, 'showSummary' => false]) ?>"],
		],
	],
	'author-card' => [
		'title' => 'Карточка автора',
		'note' => 'Автор или рецензент: имя, роль, организация, внешняя ссылка.',
		'specimens' => [
			['Основное', 'адрес профиля печатается текстом — заголовок уже ссылка', "<?= \$this->insert('components::ui/author-card', ['name' => 'Harrison D. E.', 'role' => 'Corresponding author', 'affiliation' => 'The Jackson Laboratory', 'href' => 'https://orcid.org/0000-0002-1825-0097']) ?>"],
			['Без ссылки', 'для рецензента без публичного профиля', "<?= \$this->insert('components::ui/author-card', ['name' => 'Рецензент 2', 'role' => 'Reviewer', 'affiliation' => 'Независимый рецензент']) ?>"],
		],
	],
	'badge' => [
		'title' => 'Бейдж',
		'note' => 'Короткий статус объекта: тип, состояние проверки, уверенность.',
		'specimens' => [
			['Виды', 'цвет не единственный носитель смысла — рядом всегда текст', "<?= \$this->insert('components::ui/badge', ['label' => 'Опубликовано', 'variant' => 'success']) ?>\n<?= \$this->insert('components::ui/badge', ['label' => 'На проверке', 'variant' => 'warning']) ?>\n<?= \$this->insert('components::ui/badge', ['label' => 'Черновик', 'variant' => 'neutral']) ?>\n<?= \$this->insert('components::ui/badge', ['label' => 'Оспаривается', 'variant' => 'danger']) ?>\n<?= \$this->insert('components::ui/badge', ['label' => 'Исследование', 'variant' => 'brand']) ?>\n<?= \$this->insert('components::ui/badge', ['label' => 'Пример структуры', 'variant' => 'outline']) ?>"],
		],
	],
	'tag' => [
		'title' => 'Тег',
		'note' => 'Тематика материала: всегда ссылка в каталог, не украшение.',
		'specimens' => [
			['Основное', 'счётчик — сколько документов в теме', "<?= \$this->insert('components::ui/tag', ['label' => 'Механизмы старения', 'href' => '/catalog?topic=aging-mechanisms', 'count' => 4]) ?>\n<?= \$this->insert('components::ui/tag', ['label' => 'Вмешательства', 'href' => '/catalog?topic=interventions', 'count' => 2, 'active' => true]) ?>"],
		],
	],
	'meta' => [
		'title' => 'Строка метаданных',
		'note' => 'Рубрика капителью и то, что о документе известно до чтения: дата, время чтения.',
		'specimens' => [
			['Основное', 'разделитель рисует компонент — точку в разметку не пишут', "<?= \$this->insert('components::ui/meta', ['eyebrow' => 'Соединение', 'items' => [['label' => 'Обновлено 20 ноября 2026', 'datetime' => '2026-11-20'], ['label' => '6 минут чтения']]]) ?>"],
			['Только рубрика', 'когда даты ещё нет — новый документ', "<?= \$this->insert('components::ui/meta', ['eyebrow' => 'Гипотеза']) ?>"],
		],
	],
	'callout' => [
		'title' => 'Врезка',
		'note' => 'Часть текста: определение, методика, предупреждение.',
		'specimens' => [
			['Определение', 'остаётся в статье всегда, в отличие от alert', "<?= \$this->insert('components::ui/callout', ['kind' => 'definition', 'children' => '<p>Сенолитики — препараты, избирательно удаляющие сенесцентные клетки.</p>']) ?>"],
			['Методика', 'объясняет, как получена оценка', "<?= \$this->insert('components::ui/callout', ['kind' => 'method', 'title' => 'Как мы оцениваем доказательства', 'children' => '<p>Качество данных присваивается по типу исследования, размеру выборки и воспроизведению, а не по величине эффекта.</p>']) ?>\n<?= \$this->insert('components::ui/callout', ['kind' => 'caution', 'children' => '<p>Данные на животных не переносятся на человека автоматически.</p>']) ?>"],
		],
	],
	'quote' => [
		'title' => 'Цитата',
		'note' => 'Цитата с атрибуцией: кто сказал и где опубликовано.',
		'specimens' => [
			['Основное', 'без подписи цитата читается как мнение редакции', "<?= \$this->insert('components::ui/quote', ['text' => 'Старение — не одна болезнь, а набор процессов, которые идут с разной скоростью.', 'cite' => 'Обзор геронтологии', 'source' => 'Longevypedia, раздел «Метод»']) ?>"],
		],
	],
	'alert' => [
		'title' => 'Сообщение',
		'note' => 'Реакция системы: результат действия, ошибка формы, предупреждение.',
		'specimens' => [
			['Виды', 'danger объявляется как alert, остальные — как status', "<?= \$this->insert('components::ui/alert', ['type' => 'success', 'title' => 'Правка сохранена', 'text' => 'Документ уйдёт на проверку после ревью.']) ?>\n<?= \$this->insert('components::ui/alert', ['type' => 'warning', 'title' => 'Источник не проверен', 'text' => 'DOI не найден в Crossref — ссылка не опубликована.']) ?>\n<?= \$this->insert('components::ui/alert', ['type' => 'danger', 'title' => 'Не удалось сохранить', 'text' => 'Проверьте frontmatter: поле updated обязательно.']) ?>\n<?= \$this->insert('components::ui/alert', ['type' => 'info', 'title' => 'Черновик виден только редакторам']) ?>"],
		],
	],
	'divider' => [
		'title' => 'Разделитель',
		'note' => 'Граница секций; с подписью — когда нужно назвать, что начинается ниже.',
		'specimens' => [
			['Основное', 'без подписи — просто линия', "<?= \$this->insert('components::ui/divider', []) ?>\n<?= \$this->insert('components::ui/divider', ['label' => 'Источники']) ?>"],
		],
	],
	'accordion' => [
		'title' => 'Аккордеон',
		'note' => 'Раскрывающиеся блоки на details: клавиатура и поиск работают сами.',
		'specimens' => [
			['Основное', 'для длинных пояснений, которые не нужны сразу', "<?= \$this->insert('components::ui/accordion', ['items' => [['title' => 'Что считается первичным источником', 'content' => '<p>Оригинальная публикация с описанием метода, а не пресс-релиз и не обзор.</p>', 'open' => true], ['title' => 'Как указать DOI', 'content' => '<p>Только проверенный идентификатор: doi.org должен открывать ту же публикацию.</p>']]]) ?>"],
		],
	],
	'tooltip' => [
		'title' => 'Подсказка',
		'note' => 'Пояснение термина или сокращения; читается и по фокусу.',
		'specimens' => [
			['Основное', 'текст подсказки — часть содержимого', "<?= \$this->insert('components::ui/tooltip', ['text' => 'Senescence-associated secretory phenotype — набор сигнальных молекул сенесцентной клетки', 'label' => 'SASP']) ?>"],
		],
	],
	'empty-state' => [
		'title' => 'Пустое состояние',
		'note' => 'Объясняет, почему пусто, и что делать дальше.',
		'specimens' => [
			['Основное', 'без объяснения пустой экран читается как ошибка', "<?= \$this->insert('components::ui/empty-state', ['icon' => 'search', 'title' => 'Ничего не найдено', 'text' => 'Попробуйте другое написание или более общий термин: «стар», «аутофаг», «mTOR».', 'action' => \$this->insert('components::ui/button', ['label' => 'Открыть каталог', 'variant' => 'secondary', 'href' => '/catalog'])]) ?>"],
		],
	],
	'data-table' => [
		'title' => 'Таблица данных',
		'note' => 'Сравнение по строкам: плотность, закреплённая шапка, сортировка, пустое состояние.',
		'specimens' => [
			['Основное', 'числа выровнены вправо и набраны табличными цифрами', "<?= \$this->insert('components::ui/data-table', ['caption' => 'Сравнение вмешательств', 'columns' => [['label' => 'Вмешательство', 'key' => 'name'], ['label' => 'Цель', 'key' => 'target'], ['label' => 'Статус', 'key' => 'status']], 'rows' => [['cells' => ['name' => 'Рапамицин', 'target' => 'mTOR', 'status' => 'Исследуется']], ['cells' => ['name' => 'Метформин', 'target' => 'AMPK', 'status' => 'Исследуется']]]]) ?>"],
			['Сортировка', 'состояние сортировки приходит из адреса', "<?= \$this->insert('components::ui/data-table', ['caption' => 'Плотная таблица', 'density' => 'compact', 'sort' => ['key' => 'year', 'direction' => 'desc'], 'sortUrl' => static fn (string \$key, string \$direction): string => '/research?sort=' . \$key . '&direction=' . \$direction, 'columns' => [['label' => 'Год', 'key' => 'year', 'numeric' => true, 'sortable' => true], ['label' => 'Исследование', 'key' => 'title', 'sortable' => true]], 'rows' => [['cells' => ['year' => '2009', 'title' => 'Рапамицин у мышей']], ['cells' => ['year' => '2015', 'title' => 'Сенолитики у мышей']]]]) ?>"],
			['Пустое состояние', 'пустая таблица объясняет причину, а не молчит', "<?= \$this->insert('components::ui/data-table', ['caption' => 'Результаты фильтра', 'columns' => [['label' => 'Документ', 'key' => 'title']], 'rows' => [], 'empty' => 'Под выбранные фильтры документов нет.']) ?>"],
			['Закреплённая шапка', 'sticky держит заголовки при прокрутке длинной таблицы; highlight помечает строку, к которой вернулись', "<?= \$this->insert('components::ui/data-table', ['caption' => 'Исходы исследования', 'sticky' => true, 'columns' => [['label' => 'Исход', 'key' => 'outcome'], ['label' => 'Направление', 'key' => 'direction'], ['label' => 'Показатель', 'key' => 'measure', 'numeric' => true]], 'rows' => [['cells' => ['outcome' => 'Медианная продолжительность жизни', 'direction' => 'Увеличение', 'measure' => '+9%']], ['cells' => ['outcome' => 'Максимальная продолжительность жизни', 'direction' => 'Увеличение', 'measure' => '+14%'], 'highlight' => true], ['cells' => ['outcome' => 'Масса тела', 'direction' => 'Без изменений', 'measure' => '—']]]]) ?>"],
		],
	],
	'key-value' => [
		'title' => 'Поля и значения',
		'note' => 'Паспорт объекта: идентификаторы, параметры исследования, свойства.',
		'specimens' => [
			['Основное', 'значение — готовая разметка, ссылки на внешние базы внутри', "<?= \$this->insert('components::ui/key-value', ['title' => 'Идентификаторы', 'items' => [['label' => 'HGNC', 'value' => 'HGNC:3942'], ['label' => 'UniProt', 'value' => 'P42345'], ['label' => 'MeSH', 'value' => 'D020123', 'hint' => 'Sirolimus']]]) ?>"],
		],
	],
	'stat' => [
		'title' => 'Показатель',
		'note' => 'Число с подписью; значение форматирует вызывающий шаблон.',
		'specimens' => [
			['Основное', 'ряд показателей на первом экране', "<?= \$this->insert('components::ui/stat', ['label' => 'Документов', 'value' => '10']) ?>\n<?= \$this->insert('components::ui/stat', ['label' => 'Тематик', 'value' => '7']) ?>\n<?= \$this->insert('components::ui/stat', ['label' => 'Исследований', 'value' => '1', 'hint' => 'с проверенными источниками']) ?>"],
		],
	],
	'progress' => [
		'title' => 'Шкала',
		'note' => 'Заполненность шкалы; смысл числа задаёт подпись.',
		'specimens' => [
			['Основное', 'тон не заменяет подпись', "<?= \$this->insert('components::ui/progress', ['label' => 'Готовность обзора', 'value' => 65, 'hint' => '65%']) ?>\n<?= \$this->insert('components::ui/progress', ['label' => 'Проверено источников', 'value' => 3, 'max' => 8, 'tone' => 'warning', 'hint' => '3 из 8']) ?>"],
		],
	],
	'chart' => [
		'title' => 'График',
		'note' => 'Рамка графика: данные рисует вызывающий шаблон, компонент их не выдумывает.',
		'specimens' => [
			['Пустое состояние', 'пока данных нет — так и написано', "<?= \$this->insert('components::ui/chart', ['title' => 'Продолжительность жизни', 'caption' => 'Медианная, дни', 'empty' => 'Данные появятся, когда будет размечен первый набор.']) ?>"],
		],
	],
	'evidence' => [
		'title' => 'Доказательность',
		'note' => 'Два независимых параметра: что говорят данные и насколько им можно верить.',
		'specimens' => [
			['Направление и качество', 'сильный эффект в малой выборке — это «подтверждает» + «низкое», а не «умеренно»', "<?= \$this->insert('components::ui/evidence', ['direction' => 'supports', 'quality' => 'high', 'basis' => 'Рандомизированное исследование, 190 участников, воспроизведено дважды.']) ?>\n<?= \$this->insert('components::ui/evidence', ['direction' => 'contradicts', 'quality' => 'low', 'basis' => 'Одна группа, 12 животных, эффект не воспроизведён.']) ?>\n<?= \$this->insert('components::ui/evidence', ['direction' => 'mixed', 'quality' => 'moderate']) ?>\n<?= \$this->insert('components::ui/evidence', ['direction' => 'insufficient', 'quality' => 'unassessed', 'basis' => 'Качество не оценивалось: нет разметки метода.']) ?>"],
		],
	],
	'evidence-summary' => [
		'title' => 'Сводка доказательств',
		'note' => 'Связанные утверждения по направлению: подтверждает, противоречит, смешанно, мало данных.',
		'specimens' => [
			['Основное', 'направление берётся из фронтматтера утверждения, а не из типа связи', "<?php\n\$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');\n?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/evidence-summary', [\n\t'groups' => longevypedia_content()->evidenceGroups(\$doc),\n\t'id' => 'claims-main',\n]) ?>"],
			['Свои подписи', 'перевод или другая терминология передаются словарём', "<?php\n\$doc = longevypedia_content()->find('compound', 'rapamycin', 'en');\n?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/evidence-summary', [\n\t'groups' => longevypedia_content()->evidenceGroups(\$doc),\n\t'id' => 'claims-ru',\n\t'title' => 'Связанные утверждения',\n\t'labels' => [\n\t\t'supports' => 'Подтверждающие данные',\n\t\t'contradicts' => 'Противоречащие данные',\n\t],\n]) ?>"],
		],
	],
	'claim-status' => [
		'title' => 'Статус утверждения',
		'note' => 'Уверенность сообщества и направление доказательств — разные шкалы.',
		'specimens' => [
			['Уровни', 'статус приходит из frontmatter утверждения', "<?= \$this->insert('components::ui/claim-status', ['confidence' => 'established', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>\n<?= \$this->insert('components::ui/claim-status', ['confidence' => 'likely', 'direction' => 'supports', 'asOf' => '2026-11-20']) ?>\n<?= \$this->insert('components::ui/claim-status', ['confidence' => 'contested', 'direction' => 'mixed']) ?>\n<?= \$this->insert('components::ui/claim-status', ['confidence' => 'speculative', 'direction' => 'insufficient']) ?>"],
		],
	],
	'research-metadata' => [
		'title' => 'Параметры исследования',
		'note' => 'Дизайн, выборка, вмешательство и исходы: поля и таблица из набора.',
		'specimens' => [
			['Основное', 'машинный код дизайна остаётся в data-code рядом с подписью', "<?php \$doc = longevypedia_content()->find('research', 'harrison-2009-rapamycin-mice', 'en'); ?>\n<?= \$doc === null ? '' : \$this->insert('components::ui/research-metadata', ['study' => (array) \$doc->metaValue('study', []), 'id' => 'kit-study']) ?>"],
			['Пустые поля', 'незаполненные поля не печатаются: прочерк выглядит как данные', "<?= \$this->insert('components::ui/research-metadata', ['study' => ['design' => 'meta_analysis', 'sample_size' => '12 исследований'], 'title' => 'Обзор', 'id' => 'kit-study-2']) ?>"],
		],
	],
	'citation' => [
		'title' => 'Цитата источника',
		'note' => 'Отсылка внутри текста; идентификаторы живут в списке источников.',
		'specimens' => [
			['Основное', 'номер и подпись, ссылка на источник', "<?= \$this->insert('components::ui/citation', ['number' => 1, 'text' => 'Harrison et al., 2009', 'href' => 'https://doi.org/10.1038/nature08221']) ?>\n<?= \$this->insert('components::ui/citation', ['number' => 2, 'text' => 'Saxton & Sabatini, 2017', 'href' => 'https://doi.org/10.1016/j.cell.2017.02.004', 'note' => 'обзор']) ?>"],
			['Без ссылки', 'источник упомянут, но адреса нет — cite без ссылки', "<?= \$this->insert('components::ui/citation', ['text' => 'личное сообщение, 2026']) ?>"],
		],
	],
	'source-list' => [
		'title' => 'Источники',
		'note' => 'Первоисточники с DOI и PMID; отсутствующий идентификатор не выдумывается.',
		'specimens' => [
			['Основное', 'ссылки ведут на doi.org и PubMed', "<?= \$this->insert('components::ui/source-list', ['title' => 'Источники', 'sources' => [['citation' => 'Harrison D. E. et al. Rapamycin fed late in life extends lifespan in genetically heterogeneous mice. Nature, 2009.', 'doi' => '10.1038/nature08221', 'pmid' => '19587680'], ['citation' => 'Hayflick L., Moorhead P. S. The serial cultivation of human diploid cell strains. Exp. Cell Res., 1961.', 'doi' => '10.1016/0014-4827(61)90192-6']]]) ?>"],
		],
	],
	'toast' => [
		'title' => 'Уведомление',
		'note' => 'Всплывающее сообщение в углу; не перехватывает фокус.',
		'specimens' => [
			['Виды', 'текст обязателен: уведомление без текста не прочитается', "<?= \$this->insert('components::ui/toast', ['type' => 'success', 'title' => 'Ссылка скопирована']) ?>\n<?= \$this->insert('components::ui/toast', ['type' => 'danger', 'title' => 'Ошибка сети', 'text' => 'Правка не отправлена — попробуйте ещё раз.', 'actionUrl' => '/about', 'actionLabel' => 'Что делать']) ?>"],
		],
	],
	'skeleton' => [
		'title' => 'Заглушка загрузки',
		'note' => 'Показывается, пока HTMX ждёт фрагмент; скрыта от скринридера.',
		'specimens' => [
			['Текст и карточка', 'высота повторяет будущий контент, чтобы не было скачка', "<?= \$this->insert('components::ui/skeleton', ['lines' => 4]) ?>\n<?= \$this->insert('components::ui/skeleton', ['variant' => 'card']) ?>"],
		],
	],
	'wordmark' => [
		'title' => 'Логотип',
		'note' => 'Знак и текстовый вордмарк; знак заменяется, разметка шапки — нет.',
		'specimens' => [
			['Основное', 'знак — зарезервированное место, а не эмблема', "<?= \$this->insert('components::ui/wordmark', ['href' => '/', 'label' => 'Longevypedia', 'tagline' => 'Энциклопедия долголетия']) ?>\n<?= \$this->insert('components::ui/wordmark', ['href' => null, 'label' => 'Longevypedia', 'size' => 'lg']) ?>"],
		],
	],
];

/**
 * Страницы основ пишутся вручную: у них прозаическое содержимое (описание
 * принципов, разбор токенов из `_tokens.scss`, список иконок, таблица
 * контраста), а не список вызовов компонентов. Здесь только заголовок и
 * подзаголовок для каталога и меню.
 */
$manual = [
	'overview' => ['title' => 'Обзор', 'note' => 'Из чего состоит набор и как его расширять.'],
	'tokens' => ['title' => 'Токены', 'note' => 'Цвета, типографика, отступы и раскладка — значения читаются из _tokens.scss.'],
	'icons' => ['title' => 'Иконки', 'note' => 'Один набор штриховых иконок: имена, размеры, правила использования.'],
	'accessibility' => ['title' => 'Доступность', 'note' => 'Контраст, фокус, клавиатура и семантика — с проверенными значениями.'],
];

$notes = [];

foreach ($pages as $slug => $page) {
	$notes[$slug] = (string) $page['note'];
}

/* ── Каталог ─────────────────────────────────────────────────────────── */

$catalogGroups = [];

foreach ($groups as $groupTitle => $slugs) {
	$items = [];

	foreach ($slugs as $slug) {
		if (isset($pages[$slug])) {
			$items[] = [
				'slug' => $slug,
				'title' => (string) $pages[$slug]['title'],
				'note' => (string) $pages[$slug]['note'],
			];

			continue;
		}

		// Страница основы: генератор её не пишет, но требует, чтобы она была.
		if (!isset($manual[$slug]) || !is_file($root . '/app/pages/ui-kit/' . $slug . '/page.php')) {
			fwrite(STDERR, "Нет страницы для пункта каталога: {$slug}\n");
			exit(1);
		}

		$items[] = [
			'slug' => $slug,
			'title' => (string) $manual[$slug]['title'],
			'note' => (string) $manual[$slug]['note'],
		];
	}

	$catalogGroups[] = ['title' => $groupTitle, 'items' => $items];
}

$catalog = "<?php\n\ndeclare(strict_types=1);\n\n"
	. "/**\n * Каталог UI-набора — единственный список его страниц.\n *\n"
	. " * Меню, индекс и проверка читают только этот файл. Сгенерирован\n"
	. " * `scripts/ui-kit-pages.php`: правится спецификация там, а не список здесь.\n */\n\n"
	. 'return ' . var_export(['title' => 'UI-набор', 'lead' => 'Компоненты Longevypedia: токены, состояния и разметка, из которой собираются страницы.', 'groups' => $catalogGroups], true) . ";\n";

file_put_contents($root . '/data/ui-kit.php', $catalog);

/* ── Страницы компонентов ────────────────────────────────────────────── */

$written = 0;

foreach ($pages as $slug => $page) {
	$title = (string) $page['title'];
	$note = (string) $page['note'];

	$out = "<?php\n\ndeclare(strict_types=1);\n\n";
	$out .= "/**\n * UI-набор: {$title}.\n *\n * {$note}\n *\n";
	$out .= " * Сгенерировано `scripts/ui-kit-pages.php` — правится спецификация там.\n */\n\n";
	$out .= "\$this->layout('components::layout/site-layout', [\n";
	$out .= "\t'title' => " . var_export($title . ' — UI-набор', true) . ",\n";
	$out .= "\t'description' => " . var_export($note, true) . ",\n";
	$out .= "\t'current' => 'ui-kit',\n";
	$out .= "]);\n?>\n";
	// Тело страницы обязано попасть в секцию, которую печатает layout.
	// Без start('page') Plates складывает вывод в секцию content, а
	// site-layout печатает section('page') — страница отдаёт пустой main.
	$out .= "<?php \$this->start('page'); ?>\n";
	$out .= "<?= \$this->insert('components::ui-kit/shell', [\n";
	$out .= "\t'active' => " . var_export($slug, true) . ",\n";
	$out .= "\t'title' => " . var_export($title, true) . ",\n";
	$out .= "\t'lead' => " . var_export($note, true) . ",\n";
	$out .= "\t'body' => function (): void { ?>\n";

	foreach ($page['specimens'] as $spec) {
		[$specTitle, $specNote, $code] = $spec;
		$specOptions = (array) ($spec[3] ?? []);

		$out .= "\t\t<?= \$this->insert('components::ui-kit/specimen', [\n";
		$out .= "\t\t\t'title' => " . var_export($specTitle, true) . ",\n";
		$out .= "\t\t\t'note' => " . var_export($specNote, true) . ",\n";

		foreach ($specOptions as $optionName => $optionValue) {
			$out .= "\t\t\t" . var_export($optionName, true) . ' => ' . var_export($optionValue, true) . ",\n";
		}

		$out .= "\t\t\t'code' => <<<'HTML'\n";

		foreach (explode("\n", (string) $code) as $line) {
			$out .= "\t\t\t\t" . $line . "\n";
		}

		$out .= "\t\t\t\tHTML,\n";
		$out .= "\t\t\t'body' => function (): void { ?>\n";

		foreach (explode("\n", (string) $code) as $line) {
			$out .= "\t\t\t\t" . $line . "\n";
		}

		$out .= "\t\t\t<?php },\n";
		$out .= "\t\t]) ?>\n";
	}

	$out .= "\t<?php },\n";
	$out .= "]) ?>\n";
	$out .= "<?php \$this->end(); ?>\n";

	$dir = $root . '/app/pages/ui-kit/' . $slug;
	@mkdir($dir, 0775, true);
	file_put_contents($dir . '/page.php', $out);
	$written++;
}

echo "Каталог: data/ui-kit.php\n";
echo "Страниц компонентов: {$written}\n";
