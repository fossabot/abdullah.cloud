<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Core\ShortUrl\Spec;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Specification\Specification;
use Shlinkio\Shlink\Rest\Entity\ApiKey;

class BelongsToApiKeyInlined implements Specification
{
    private ApiKey $apiKey;

    public function __construct(ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getFilter(QueryBuilder $qb, string $dqlAlias): string
    {
        // Parameters in this query need to be inlined, not bound, as we need to use it as sub-query later
        return (string) $qb->expr()->eq('s.authorApiKey', '\'' . $this->apiKey->getId() . '\'');
    }

    public function modify(QueryBuilder $qb, string $dqlAlias): void
    {
    }
}
