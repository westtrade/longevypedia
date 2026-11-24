<?php

declare(strict_types=1);

namespace Longevypedia\Content;

/**
 * Словарь связей между документами.
 *
 * Связь хранится в Frontmatter документа-источника как
 * `relations: [{type: "part_of", target: "concept:biological-aging"}]`.
 * Обратная связь не дублируется в целевом документе: её вычисляет индекс
 * (`ContentRepository::backlinksOf()`), иначе два файла пришлось бы править
 * синхронно и они бы разъехались.
 */
final class RelationTypes
{
    public const RELATED_TO = 'related_to';
    public const PART_OF = 'part_of';
    public const HAS_PART = 'has_part';
    public const CITES = 'cites';
    public const CITED_BY = 'cited_by';
    public const SUPPORTED_BY = 'supported_by';
    public const SUPPORTS = 'supports';
    public const CONTRADICTED_BY = 'contradicted_by';
    public const CONTRADICTS = 'contradicts';
    public const STUDIES = 'studies';
    public const STUDIED_BY = 'studied_by';
    public const MEASURED_BY = 'measured_by';
    public const MEASURES = 'measures';
    public const TARGETS = 'targets';
    public const TARGETED_BY = 'targeted_by';
    public const REGULATES = 'regulates';
    public const REGULATED_BY = 'regulated_by';

    /**
     * Прямые связи, которые автор пишет в Frontmatter.
     *
     * @var array<string, array{label: string, inverse: string, inverse_label: string, symmetric: bool}>
     */
    public const DIRECT = [
        self::RELATED_TO => [
            'label' => 'Related to',
            'inverse' => self::RELATED_TO,
            'inverse_label' => 'Related to',
            'symmetric' => true,
        ],
        self::PART_OF => [
            'label' => 'Part of',
            'inverse' => self::HAS_PART,
            'inverse_label' => 'Includes',
            'symmetric' => false,
        ],
        self::HAS_PART => [
            'label' => 'Includes',
            'inverse' => self::PART_OF,
            'inverse_label' => 'Part of',
            'symmetric' => false,
        ],
        self::CITES => [
            'label' => 'Cites',
            'inverse' => self::CITED_BY,
            'inverse_label' => 'Cited by',
            'symmetric' => false,
        ],
        self::SUPPORTED_BY => [
            'label' => 'Supported by',
            'inverse' => self::SUPPORTS,
            'inverse_label' => 'Supports',
            'symmetric' => false,
        ],
        self::CONTRADICTED_BY => [
            'label' => 'Contradicted by',
            'inverse' => self::CONTRADICTS,
            'inverse_label' => 'Contradicts',
            'symmetric' => false,
        ],
        self::STUDIES => [
            'label' => 'Studies',
            'inverse' => self::STUDIED_BY,
            'inverse_label' => 'Studied by',
            'symmetric' => false,
        ],
        self::MEASURED_BY => [
            'label' => 'Measured by',
            'inverse' => self::MEASURES,
            'inverse_label' => 'Measures',
            'symmetric' => false,
        ],
        self::TARGETS => [
            'label' => 'Targets',
            'inverse' => self::TARGETED_BY,
            'inverse_label' => 'Targeted by',
            'symmetric' => false,
        ],
        self::REGULATES => [
            'label' => 'Regulates',
            'inverse' => self::REGULATED_BY,
            'inverse_label' => 'Regulated by',
            'symmetric' => false,
        ],
    ];

    /**
     * Полный словарь: прямые связи и их обратные имена.
     *
     * Автор вправе написать любую сторону пары: `A regulates B` в файле A и
     * `B regulated_by A` в файле B описывают одно и то же ребро. Поэтому
     * `exists()`/`label()` смотрят на объединение, а `DIRECT` остаётся
     * каноническим списком — по нему строится подпись обратной связи.
     *
     * @return array<string, array{label: string, inverse: string, inverse_label: string, symmetric: bool}>
     */
    public static function vocabulary(): array
    {
        static $vocabulary = null;

        if ($vocabulary !== null) {
            return $vocabulary;
        }

        $vocabulary = self::DIRECT;

        foreach (self::DIRECT as $type => $definition) {
            $inverse = $definition['inverse'];

            if ($inverse === $type || isset($vocabulary[$inverse])) {
                continue;
            }

            $vocabulary[$inverse] = [
                'label' => $definition['inverse_label'],
                'inverse' => $type,
                'inverse_label' => $definition['label'],
                'symmetric' => false,
            ];
        }

        return $vocabulary;
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::vocabulary());
    }

    public static function exists(string $type): bool
    {
        return isset(self::vocabulary()[$type]);
    }

    public static function label(string $type): string
    {
        return self::vocabulary()[$type]['label'] ?? $type;
    }

    /**
     * Как обратная связь выглядит на странице цели
     * (`related_to` симметрична и в обратном списке не повторяется).
     */
    public static function inverseLabel(string $type): string
    {
        return self::vocabulary()[$type]['inverse_label'] ?? self::label($type);
    }

    public static function inverse(string $type): ?string
    {
        return self::vocabulary()[$type]['inverse'] ?? null;
    }

    public static function isSymmetric(string $type): bool
    {
        return (bool) (self::vocabulary()[$type]['symmetric'] ?? false);
    }
}
