<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess\Expression;

use Ergnuor\DomainModel\DataAccess\ExpressionMapper\ExpressionMapperInterface;
use RuntimeException;

class CompositeExpression implements ExpressionInterface
{
    public const TYPE_AND = 'AND';
    public const TYPE_OR = 'OR';

    private string $type;

    /** @var ExpressionInterface[] */
    private array $expressions = [];

    /**
     * @param string $type
     * @param array $expressions
     *
     * @throws RuntimeException
     */
    public function __construct(string $type, array $expressions)
    {
        $this->setType($type);

        foreach ($expressions as $expr) {
            if ($expr instanceof Value) {
                throw new RuntimeException('Values are not supported expressions as children of and/or expressions.');
            }
            if (!($expr instanceof ExpressionInterface)) {
                throw new \RuntimeException(sprintf('Expression given to composite expression must implement "%s".',
                    ExpressionInterface::class));
            }

            $this->expressions[] = $expr;
        }
    }

    private function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_AND, self::TYPE_OR])) {
            throw new \RuntimeException("Unsupported composite expression type '{$type}'");
        }

        $this->type = $type;
    }

    /**
     * Returns the list of expressions nested in this composite.
     *
     * @return ExpressionInterface[]
     */
    public function getExpressionList(): array
    {
        return $this->expressions;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function visit(ExpressionMapperInterface $visitor): mixed
    {
        return $visitor->walkCompositeExpression($this);
    }
}
