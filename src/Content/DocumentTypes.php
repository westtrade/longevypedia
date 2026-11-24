<?php

declare(strict_types=1);

namespace Longevypedia\Content;

/**
 * Реестр типов документов.
 *
 * Тип документа задаёт три вещи: каталог, в котором лежат файлы
 * (`content/<locale>/<dir>/`), JSON Schema для проверки Frontmatter и подпись
 * для интерфейса. Список типов — единственное место, которое нужно править,
 * чтобы добавить новый вид материала: маршруты, каталог и фильтры читают его
 * отсюда.
 */
final class DocumentTypes
{
    public const GROUP_KNOWLEDGE = 'knowledge';
    public const GROUP_EVIDENCE = 'evidence';
    public const GROUP_GUIDANCE = 'guidance';

    /**
     * @var array<string, array{dir: string, label: string, plural: string, group: string, schema: string, description: string}>
     */
    public const TYPES = [
        'concept' => [
            'dir' => 'concepts',
            'label' => 'Concept',
            'plural' => 'Concepts',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Key ideas of aging biology and longevity research.',
        ],
        'mechanism' => [
            'dir' => 'mechanisms',
            'label' => 'Mechanism',
            'plural' => 'Mechanisms',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Biological processes that drive or slow aging.',
        ],
        'pathway' => [
            'dir' => 'pathways',
            'label' => 'Pathway',
            'plural' => 'Pathways',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Signalling cascades that regulate metabolism, growth and repair.',
        ],
        'gene' => [
            'dir' => 'genes',
            'label' => 'Gene',
            'plural' => 'Genes',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Genes with a documented role in aging or healthspan.',
        ],
        'protein' => [
            'dir' => 'proteins',
            'label' => 'Protein',
            'plural' => 'Proteins',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Proteins and protein complexes relevant to aging.',
        ],
        'biomarker' => [
            'dir' => 'biomarkers',
            'label' => 'Biomarker',
            'plural' => 'Biomarkers',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Measurable indicators of biological age or risk.',
        ],
        'disease' => [
            'dir' => 'diseases',
            'label' => 'Disease',
            'plural' => 'Diseases',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Age-related diseases and their relationship to aging.',
        ],
        'compound' => [
            'dir' => 'compounds',
            'label' => 'Compound',
            'plural' => 'Compounds',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Molecules studied as geroprotectors or research tools.',
        ],
        'intervention' => [
            'dir' => 'interventions',
            'label' => 'Intervention',
            'plural' => 'Interventions',
            'group' => self::GROUP_KNOWLEDGE,
            'schema' => 'entity.schema.json',
            'description' => 'Pharmacological, dietary and lifestyle interventions.',
        ],
        'research' => [
            'dir' => 'research',
            'label' => 'Study',
            'plural' => 'Studies',
            'group' => self::GROUP_EVIDENCE,
            'schema' => 'research.schema.json',
            'description' => 'Primary studies and trials with verifiable identifiers.',
        ],
        'claim' => [
            'dir' => 'claims',
            'label' => 'Claim',
            'plural' => 'Claims',
            'group' => self::GROUP_EVIDENCE,
            'schema' => 'claim.schema.json',
            'description' => 'Atomic scientific statements with graded evidence.',
        ],
        'guideline' => [
            'dir' => 'guidelines',
            'label' => 'Guideline',
            'plural' => 'Guidelines',
            'group' => self::GROUP_GUIDANCE,
            'schema' => 'guideline.schema.json',
            'description' => 'Practical recommendations derived from graded evidence.',
        ],
        'article' => [
            'dir' => 'articles',
            'label' => 'Article',
            'plural' => 'Articles',
            'group' => self::GROUP_GUIDANCE,
            'schema' => 'article.schema.json',
            'description' => 'Long-form explanatory writing and reviews.',
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const GROUPS = [
        self::GROUP_KNOWLEDGE => 'Knowledge',
        self::GROUP_EVIDENCE => 'Evidence',
        self::GROUP_GUIDANCE => 'Guidance',
    ];

    public const STATUSES = ['draft', 'review', 'published', 'deprecated'];

    public static function exists(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /**
     * @return array<string, array{dir: string, label: string, plural: string, group: string, schema: string, description: string}>
     */
    public static function all(): array
    {
        return self::TYPES;
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::TYPES);
    }

    /**
     * @return list<string>
     */
    public static function dirs(): array
    {
        return array_values(array_map(static fn (array $type): string => $type['dir'], self::TYPES));
    }

    public static function dir(string $type): string
    {
        return self::type($type)['dir'];
    }

    public static function label(string $type): string
    {
        return self::type($type)['label'];
    }

    public static function plural(string $type): string
    {
        return self::type($type)['plural'];
    }

    public static function schema(string $type): string
    {
        return self::type($type)['schema'];
    }

    public static function group(string $type): string
    {
        return self::type($type)['group'];
    }

    public static function description(string $type): string
    {
        return self::type($type)['description'];
    }

    /**
     * Тип по имени каталога (`concepts` → `concept`).
     */
    public static function fromDir(string $dir): ?string
    {
        foreach (self::TYPES as $type => $definition) {
            if ($definition['dir'] === $dir) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Типы одной группы в порядке объявления.
     *
     * @return array<string, array{dir: string, label: string, plural: string, group: string, schema: string, description: string}>
     */
    public static function inGroup(string $group): array
    {
        return array_filter(self::TYPES, static fn (array $type): bool => $type['group'] === $group);
    }

    /**
     * @return array{dir: string, label: string, plural: string, group: string, schema: string, description: string}
     */
    private static function type(string $type): array
    {
        return self::TYPES[$type] ?? throw new ContentException(sprintf('Неизвестный тип документа "%s".', $type));
    }
}
