<?php
declare(strict_types=1);

namespace Ergnuor\DomainModel\EntityManager\UnitOfWork;

class Events
{
    public const EVENT_BEFORE_COMMIT_FLUSH = 'ergnuor.domain_model.beforeCommitPersist';
    public const EVENT_AFTER_TRANSACTION_FLUSH = 'ergnuor.domain_model.afterTransactionFlush';
    public const EVENT_POST_FLUSH = 'ergnuor.domain_model.postFlush';
}