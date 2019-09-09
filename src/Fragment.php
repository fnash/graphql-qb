<?php

namespace Commadore\GraphQL;

final class Fragment
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @param string $name
     * @param string $type
     * @param array $fields
     */
    public function __construct(string $name, string $type, array $fields = [])
    {
        $this->name = $name;

        $this->type = $type;

        $this->fields($fields);
    }

    /**
     * @param array $fields
     *
     * @return Fragment
     */
    public function fields(array $fields = []): Fragment
    {
        foreach ($fields as $fieldAlias => $field) {
            if (\is_string($field)) {
                if (\is_string($fieldAlias)) {
                    $this->fields[$fieldAlias] = $field;
                } else {
                    $this->fields[$field] = $field;
                }
            }

            if ($field instanceof Query) {
                $this->fields[$fieldAlias] = $field;
            }
        }

        ksort($this->fields);

        return $this;
    }

    public function __toString()
    {
        $query = sprintf('fragment %s on %s { %s }', $this->name, $this->type, static::printFields($this->fields));

        $query = \GraphQL\Language\Printer::doPrint(\GraphQL\Language\Parser::parse((string) $query));

        return $query;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private static function printFields(array $value): string
    {
        $fields = [];

        foreach ($value as $fieldAlias => $field) {
            $directive = '';

            if (\is_string($field)) {
                if ($fieldAlias !== $field) {
                    $fields[] = sprintf('%s: %s %s', $fieldAlias, $field, $directive);
                } else {
                    $fields[] = sprintf('%s %s', $field, $directive);
                }
            }

            if ($field instanceof Query) {
                if (null !== $field->type) {
                    $fieldAlias = sprintf('%s: %s', $fieldAlias, $field->type);
                }

                $fields[] = sprintf('%s %s { %s }', $fieldAlias, $directive, static::printFields($field->fields));
            }
        }

        return implode(', ', $fields);
    }

}
