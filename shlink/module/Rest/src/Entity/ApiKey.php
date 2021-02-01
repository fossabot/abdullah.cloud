<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Rest\Entity;

use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\Specification;
use Ramsey\Uuid\Uuid;
use Shlinkio\Shlink\Common\Entity\AbstractEntity;
use Shlinkio\Shlink\Rest\ApiKey\Model\RoleDefinition;
use Shlinkio\Shlink\Rest\ApiKey\Role;

class ApiKey extends AbstractEntity
{
    private string $key;
    private ?Chronos $expirationDate = null;
    private bool $enabled;
    /** @var Collection|ApiKeyRole[] */
    private Collection $roles;

    /**
     * @throws Exception
     */
    public function __construct(?Chronos $expirationDate = null)
    {
        $this->key = Uuid::uuid4()->toString();
        $this->expirationDate = $expirationDate;
        $this->enabled = true;
        $this->roles = new ArrayCollection();
    }

    public static function withRoles(RoleDefinition ...$roleDefinitions): self
    {
        $apiKey = new self();

        foreach ($roleDefinitions as $roleDefinition) {
            $apiKey->registerRole($roleDefinition);
        }

        return $apiKey;
    }

    public static function withKey(string $key, ?Chronos $expirationDate = null): self
    {
        $apiKey = new self($expirationDate);
        $apiKey->key = $key;

        return $apiKey;
    }

    public function getExpirationDate(): ?Chronos
    {
        return $this->expirationDate;
    }

    public function isExpired(): bool
    {
        return $this->expirationDate !== null && $this->expirationDate->lt(Chronos::now());
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Tells if this api key is enabled and not expired
     */
    public function isValid(): bool
    {
        return $this->isEnabled() && ! $this->isExpired();
    }

    public function __toString(): string
    {
        return $this->key;
    }

    public function toString(): string
    {
        return $this->key;
    }

    public function spec(bool $inlined = false): Specification
    {
        $specs = $this->roles->map(fn (ApiKeyRole $role) => Role::toSpec($role, $inlined))->getValues();
        return Spec::andX(...$specs);
    }

    public function isAdmin(): bool
    {
        return $this->roles->isEmpty();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->containsKey($roleName);
    }

    public function getRoleMeta(string $roleName): array
    {
        /** @var ApiKeyRole|null $role */
        $role = $this->roles->get($roleName);
        return $role === null ? [] : $role->meta();
    }

    public function mapRoles(callable $fun): array
    {
        return $this->roles->map(fn (ApiKeyRole $role) => $fun($role->name(), $role->meta()))->getValues();
    }

    public function registerRole(RoleDefinition $roleDefinition): void
    {
        $roleName = $roleDefinition->roleName();
        $meta = $roleDefinition->meta();

        if ($this->hasRole($roleName)) {
            /** @var ApiKeyRole $role */
            $role = $this->roles->get($roleName);
            $role->updateMeta($meta);
        } else {
            $role = new ApiKeyRole($roleDefinition->roleName(), $roleDefinition->meta(), $this);
            $this->roles[$roleName] = $role;
        }
    }

    public function removeRole(string $roleName): void
    {
        $this->roles->remove($roleName);
    }
}
