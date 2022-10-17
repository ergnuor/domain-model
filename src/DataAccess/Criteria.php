<?php

declare(strict_types=1);

namespace Ergnuor\DomainModel\DataAccess;

use Ergnuor\DomainModel\DataAccess\Expression\CompositeExpression;
use Ergnuor\DomainModel\DataAccess\Expression\ExpressionInterface;
use function array_map;
use function strtoupper;

/**
 * Criteria for filtering Selectable collections.
 */
class Criteria
{
    public const ASC = 'ASC';

    public const DESC = 'DESC';

    private ?ExpressionInterface $expression;

    /** @var string[] */
    private array $orderings = [];

    private ?int $firstResult = null;

    private ?int $maxResults = null;

    public static function create(): Criteria
    {
        return new static();
    }

    public function __construct(?ExpressionInterface $expression = null, ?array $orderings = null, ?int $firstResult = null, ?int $maxResults = null)
    {
        $this->expression = $expression;

        $this->setFirstResult($firstResult);
        $this->setMaxResults($maxResults);

        if ($orderings === null) {
            return;
        }

        $this->orderBy($orderings);
    }

    /**
     * Sets the where expression to evaluate when this Criteria is searched for.
     */
    public function where(ExpressionInterface $expression): Criteria
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Appends the where expression to evaluate when this Criteria is searched for
     * using an AND with previous expression.
     */
    public function andWhere(ExpressionInterface $expression): Criteria
    {
        if ($this->expression === null) {
            return $this->where($expression);
        }

        $this->expression = new CompositeExpression(
            CompositeExpression::TYPE_AND,
            [$this->expression, $expression]
        );

        return $this;
    }

    /**
     * Appends the where expression to evaluate when this Criteria is searched for
     * using an OR with previous expression.
     */
    public function orWhere(ExpressionInterface $expression): Criteria
    {
        if ($this->expression === null) {
            return $this->where($expression);
        }

        $this->expression = new CompositeExpression(
            CompositeExpression::TYPE_OR,
            [$this->expression, $expression]
        );

        return $this;
    }

    public function getWhereExpression(): ?ExpressionInterface
    {
        return $this->expression;
    }

    /**
     * Gets the current orderings of this Criteria.
     *
     * @return string[]
     */
    public function getOrderings(): array
    {
        return $this->orderings;
    }

    /**
     * Sets the ordering of the result of this Criteria.
     *
     * Keys are field and values are the order, being either ASC or DESC.
     *
     * @param string[] $orderings
     *
     * @return Criteria
     * @see Criteria::ASC
     * @see Criteria::DESC
     *
     */
    public function orderBy(array $orderings): Criteria
    {
        $this->orderings = array_map(
            static function (string $ordering): string {
                return strtoupper($ordering) === Criteria::ASC ? Criteria::ASC : Criteria::DESC;
            },
            $orderings
        );

        return $this;
    }

    /**
     * Gets the current first result option of this Criteria.
     */
    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    /**
     * Set the number of first result that this Criteria should return.
     */
    public function setFirstResult(?int $firstResult): Criteria
    {
        $this->firstResult = $firstResult === null ? null : $firstResult;

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function setMaxResults(?int $maxResults): Criteria
    {
        $this->maxResults = $maxResults === null ? null : $maxResults;

        return $this;
    }
}
