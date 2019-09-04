<?php

namespace Commadore\GraphQL;

use Commadore\GraphQL\Interfaces\QueryInterface;

abstract class AbstractQuery implements QueryInterface
{
    public static $operationNamePlaceholder = '_operationNamePlaceholder_';

    abstract function getKeyword(): string;
    abstract function getPrefix(): string;

    /**
     * @var string
     */
    public $operationName;

    /**
     * @var array
     */
    public $variables = [];

    /**
     * @var bool
     */
    public $isRootQuery = true;

    /**
     * @var array
     */
    public $type = [];

    /**
     * @var array
     */
    public $args = [];

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    public $skipIf = [];

    /**
     * @var array
     */
    public $includeIf = [];

    /**
     * @var array
     */
    public $fragments = [];

    /**
     * @param null $type
     * @param array $args
     * @param array $fields
     */
    public function __construct($type = null, array $args = [], array $fields = [])
    {
        $this->type = $type;

        $this->arguments($args);

        $this->fields($fields);
    }

    /**
     * @param string $operationName
     *
     * @return QueryInterface
     */
    public function operationName(string $operationName): QueryInterface
    {
        $this->operationName = $operationName;

        return $this;
    }

    /**
     * @param $operationName
     * @param $variables
     *
     * @return string
     */
    private function printQuery($operationName, $variables): string
    {
        if (null === $operationName) {
            if (\count($variables)) {
                $operationName = static::$operationNamePlaceholder;
            } else {
                return '';
            }
        }

        return sprintf('%s %s %s', $this->getKeyword(), $operationName, $this->printVariables($variables));
    }

    /**
     * @param array $variables
     *
     * @return self
     */
    public function variables(array $variables = []): QueryInterface
    {
        foreach ($variables as $variableName => $variableType) {
            $this->variables[(string) $variableName] = (string) $variableType;
        }

        return $this;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    private function printVariables(array $value): string
    {
        if (!\count($value)) {
            return '';
        }

        $variables = [];

        foreach ($value as $variableName => $variableType) {
            $variables[] = sprintf('%s: %s', $variableName, $variableType);
        }

        return sprintf('(%s)', implode(', ', $variables));
    }

    /**
     * @param array $args
     *
     * @return self
     */
    public function arguments(array $args = []): QueryInterface
    {
        foreach ($args as $name => $value) {
            $this->args[$name] = $value;
        }

        ksort($this->args);

        return $this;
    }


    /**
     * @param array $value
     *
     * @return string
     */
    private function printArgs(array $value): string
    {
        if (!count($value)) {
            return '';
        }

        $args = [];
        foreach ($value as $argName => $argValue) {
            if (\is_string($argValue) && '$' !== $argValue[0]) {
                $argValue = sprintf('"%s"', $argValue);
            }

            if (\is_bool($argValue) || \is_float($argValue)) {
                $argValue = var_export($argValue, true);
            }

            $args[] = sprintf('%s: %s', $argName, $argValue);
        }

        return sprintf('(%s)', implode(', ', $args));
    }

    /**
     * @param array $fields
     *
     * @return self
     */
    public function fields(array $fields = []): QueryInterface
    {
        foreach ($fields as $fieldAlias => $field) {
            if (\is_string($field)) {
                if (\is_string($fieldAlias)) {
                    $this->fields[$fieldAlias] = $field;
                } else {
                    $this->fields[$field] = $field;
                }
            }

            if ($field instanceof self) {
                $field->isRootQuery = false;
                $this->fields[$fieldAlias] = $field;
            }
        }

        ksort($this->fields);

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return self
     */
    public function removeFields(array $fields = []): QueryInterface
    {
        foreach ($fields as $field) {
            unset($this->fields[$field]);
        }

        return $this;
    }

    /**
     * @param array $value
     * @param array $skipIf
     * @param array $includeIf
     *
     * @return string
     */
    private function printFields(array $value, array $skipIf = [], array $includeIf = []): string
    {
        $fields = [];

        foreach ($value as $fieldAlias => $field) {
            $directive = '';

            if (\is_string($field)) {
                if ($fieldAlias !== $field) {
                    if (array_key_exists($fieldAlias, $skipIf)) {
                        $directive = sprintf('@skip(if: %s)', $skipIf[$fieldAlias]);
                    } elseif (array_key_exists($fieldAlias, $includeIf)) {
                        $directive = sprintf('@include(if: %s)', $includeIf[$fieldAlias]);
                    }

                    $fields[] = sprintf('%s: %s %s', $fieldAlias, $field, $directive);
                } else {
                    if (array_key_exists($field, $skipIf)) {
                        $directive = sprintf('@skip(if: %s)', $skipIf[$field]);
                    } elseif (array_key_exists($field, $includeIf)) {
                        $directive = sprintf('@include(if: %s)', $includeIf[$field]);
                    }

                    $fields[] = sprintf('%s %s', $field, $directive);
                }
            }

            if ($field instanceof self) {
                $field->isRootQuery = false;

                if (array_key_exists($fieldAlias, $skipIf)) {
                    $directive = sprintf('@skip(if: %s)', $skipIf[$fieldAlias]);
                } elseif (array_key_exists($fieldAlias, $includeIf)) {
                    $directive = sprintf('@include(if: %s)', $includeIf[$fieldAlias]);
                }

                if (null !== $field->type) {
                    $fieldAlias = sprintf('%s: %s', $fieldAlias, $field->type);
                }

                $fields[] = sprintf('%s %s { %s }', $fieldAlias, $directive, static::printFields($field->fields));
            }
        }

        return implode(', ', $fields);
    }

    /**
     * @param array $values
     *
     * @return self
     */
    public function skipIf(array $values = []): QueryInterface
    {
        foreach ($values as $field => $argument) {
            $this->skipIf[$field] = $argument;
        }

        return $this;
    }

    /**
     * @param array $values
     *
     * @return self
     */
    public function includeIf(array $values = []): QueryInterface
    {
        foreach ($values as $field => $argument) {
            $this->includeIf[$field] = $argument;
        }

        return $this;
    }

    public function __toString()
    {
        if ($this->isRootQuery) {
            $query = sprintf('%s { %s %s { %s } } %s',
                $this->printQuery($this->operationName, $this->variables),
                $this->printType($this->type), $this->printArgs($this->args),
                $this->printFields($this->fields, $this->skipIf, $this->includeIf),
                $this->printFragments($this->fragments));
        } else {
            $query = sprintf('%s { %s }',
                $this->printType($this->type),
                $this->printFields($this->fields, $this->skipIf, $this->includeIf));
        }

        $query = \GraphQL\Language\Printer::doPrint(\GraphQL\Language\Parser::parse((string) $query));

        $query = str_replace(static::$operationNamePlaceholder, $this->getPrefix().sha1($query), $query);

        return $query;
    }

    /**
     * @param Fragment $fragment
     *
     * @return $this
     */
    public function addFragment(Fragment $fragment): QueryInterface
    {
        $this->fragments[$fragment->name] = $fragment;

        return $this;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function printFragments($value)
    {
        $fragments = '';
        foreach ($value as $fragment) {
            $fragments .= (string) $fragment;
        }

        return $fragments;
    }


    private function printType($value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (\is_array($value) && \count($value)) {
            foreach ($value as $alias => $type) {
                return sprintf('%s: %s', $alias, $type);
            }
        }
    }
}
