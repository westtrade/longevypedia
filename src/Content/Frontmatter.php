<?php

declare(strict_types=1);

namespace Longevypedia\Content;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Разбор YAML Frontmatter (блок между двумя строками `---` в начале файла).
 *
 * Даты разбираются с флагом `Yaml::PARSE_DATETIME`: без него Symfony YAML
 * превращает `2026-11-20` в Unix-время (int), и поле `updated` в шаблоне
 * показывало бы число вместо даты. Флаг возвращает DateTimeImmutable, который
 * затем приводится к строке `Y-m-d` (или к ISO 8601, если время задано явно).
 */
final class Frontmatter
{
    private const DELIMITER = '---';

    /**
     * @return array{meta: array<string, mixed>, body: string}
     *
     * @throws ContentException если блок не закрыт или YAML не разбирается
     */
    public static function split(string $raw): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        if (str_starts_with($raw, "\u{FEFF}")) {
            $raw = substr($raw, 3);
        }

        if (!str_starts_with($raw, self::DELIMITER . "\n")) {
            throw new ContentException('Документ должен начинаться с блока YAML Frontmatter (---).');
        }

        $rest = substr($raw, strlen(self::DELIMITER) + 1);
        $end = strpos($rest, "\n" . self::DELIMITER);

        if ($end === false) {
            throw new ContentException('Блок YAML Frontmatter не закрыт строкой ---.');
        }

        $yaml = substr($rest, 0, $end);
        $body = ltrim(substr($rest, $end + strlen(self::DELIMITER) + 1), "\n");

        return [
            'meta' => self::parse($yaml),
            'body' => $body,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function parse(string $yaml): array
    {
        if (trim($yaml) === '') {
            return [];
        }

        try {
            $data = Yaml::parse($yaml, Yaml::PARSE_DATETIME);
        } catch (ParseException $e) {
            throw new ContentException('YAML Frontmatter не разбирается: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($data)) {
            throw new ContentException('YAML Frontmatter должен быть отображением ключей и значений.');
        }

        /** @var array<string, mixed> $data */
        return self::normalize($data);
    }

    /**
     * Приводит даты к строкам, чтобы шаблоны и JSON Schema видели одинаковый тип.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private static function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::normalize($value);
            } elseif ($value instanceof \DateTimeInterface) {
                $data[$key] = self::formatDate($value);
            }
        }

        return $data;
    }

    private static function formatDate(\DateTimeInterface $date): string
    {
        $time = $date->format('H:i:s');

        return $time === '00:00:00' ? $date->format('Y-m-d') : $date->format('Y-m-d\TH:i:sP');
    }
}
