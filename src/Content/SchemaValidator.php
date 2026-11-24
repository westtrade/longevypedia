<?php

declare(strict_types=1);

namespace Longevypedia\Content;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;

/**
 * Проверка Frontmatter по JSON Schema.
 *
 * Схемы лежат в `schemas/` и адресуются по абсолютным `$id`, поэтому ссылки
 * вида `{"$ref": "document.schema.json"}` между файлами разрешаются без
 * дополнительной регистрации: базой служит `$id` самой схемы.
 *
 * Проверка не входит в рендеринг страницы: на каждый запрос разбирать схемы
 * незачем. Её запускают `scripts/content-check.php`, тесты и CI — а в отладочном
 * режиме ещё и репозиторий, чтобы ошибка в документе была видна сразу.
 */
final class SchemaValidator
{
    private const BASE_URI = 'https://longevypedia.localhost/schemas/';

    private Validator $validator;

    /**
     * @var array<string, string> тип документа => абсолютный `$id` схемы
     */
    private array $schemaIds = [];

    public function __construct(private readonly string $schemaDir)
    {
        $resolver = new SchemaResolver();
        $loader = new SchemaLoader(new SchemaParser(), $resolver, true);

        foreach (glob(rtrim($schemaDir, '/') . '/*.schema.json') ?: [] as $file) {
            $resolver->registerFile(self::BASE_URI . basename($file), $file);
        }

        foreach (DocumentTypes::TYPES as $type => $definition) {
            $this->schemaIds[$type] = self::BASE_URI . $definition['schema'];
        }

        // Все ошибки сразу: при правке документа полезно увидеть список целиком,
        // а не по одной за прогон.
        $this->validator = new Validator($loader, 50, false);
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return list<string> сообщения об ошибках; пустой список — документ валиден
     */
    public function validate(string $type, array $meta): array
    {
        $schemaId = $this->schemaIds[$type] ?? null;

        if ($schemaId === null) {
            return [sprintf('неизвестный тип документа "%s"', $type)];
        }

        $result = $this->validator->validate($this->toJsonValue($meta), $schemaId);

        if ($result->isValid()) {
            return [];
        }

        $error = $result->error();

        return $error === null ? ['документ не прошёл проверку по схеме'] : $this->flatten($error);
    }

    /**
     * Схема конкретного типа — для документации и отладки.
     */
    public function schemaId(string $type): ?string
    {
        return $this->schemaIds[$type] ?? null;
    }

    /**
     * @return list<string>
     */
    private function flatten(ValidationError $error): array
    {
        $subErrors = $error->subErrors();

        if ($subErrors === []) {
            return [$this->describe($error)];
        }

        $messages = [];

        foreach ($subErrors as $subError) {
            foreach ($this->flatten($subError) as $message) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    private function describe(ValidationError $error): string
    {
        $path = implode('.', $error->data()->fullPath());

        return $path === ''
            ? $error->message()
            : sprintf('%s: %s', $path, $error->message());
    }

    /**
     * YAML-разбор отдаёт массивы, а схемы описаны в терминах JSON-объектов:
     * пустой массив должен остаться списком, а не превратиться в `{}`.
     *
     * @param array<string, mixed> $meta
     */
    private function toJsonValue(array $meta): object
    {
        return json_decode(
            (string) json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            false,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
