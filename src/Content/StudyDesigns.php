<?php

declare(strict_types=1);

namespace Longevypedia\Content;

/**
 * Словарь дизайнов исследования.
 *
 * Машинные значения — ровно те, что перечислены в `schemas/research.schema.json`
 * (`study.design`). Подписи для интерфейса живут здесь, а не в шаблонах, потому
 * что мест, где печатается дизайн, уже два (`research/page.php` и
 * `doc/study-fields.php`), и расходиться они не должны.
 *
 * Почему не выводить подпись из кода автоматически (`str_replace('_', ' ')`):
 * «randomized_controlled_trial» превратилось бы в «randomized controlled trial»
 * без заглавных букв, а «case_control» — в «case control» без тире. Словарь
 * дешевле, чем правила транслитерации, и его видно в ревью.
 *
 * Согласованность со схемой проверяется тестом
 * `tests/Content/StudyDesignsTest.php`: добавили значение в схему — тест
 * напомнит добавить подпись, и наоборот.
 */
final class StudyDesigns
{
    /**
     * @var array<string, string> машинное значение => подпись
     */
    public const LABELS = [
        'systematic_review' => 'Systematic review',
        'meta_analysis' => 'Meta-analysis',
        'randomized_controlled_trial' => 'Randomised controlled trial',
        'controlled_trial' => 'Controlled trial',
        'cohort' => 'Cohort study',
        'case_control' => 'Case–control study',
        'cross_sectional' => 'Cross-sectional study',
        'case_series' => 'Case series',
        'case_report' => 'Case report',
        'animal_experiment' => 'Animal experiment',
        'in_vitro' => 'In vitro study',
        'ecological' => 'Ecological study',
        'narrative_review' => 'Narrative review',
        'other' => 'Other design',
    ];

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::LABELS);
    }

    public static function exists(string $design): bool
    {
        return isset(self::LABELS[$design]);
    }

    /**
     * Подпись для известного значения; неизвестное печатается как есть, чтобы
     * опечатка в контенте была видна на странице, а не превращалась в пустоту.
     */
    public static function label(string $design): string
    {
        return self::LABELS[$design] ?? $design;
    }
}
