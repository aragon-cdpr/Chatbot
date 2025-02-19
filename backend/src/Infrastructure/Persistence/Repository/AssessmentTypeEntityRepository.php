<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Assessment\Entities\AssessmentType;
use App\Domain\Assessment\Repositories\AssessmentTypeRepositoryInterface;
use App\Infrastructure\Persistence\Entities\AssessmentTypeEntity;
use App\Infrastructure\Persistence\Entities\PersistenceEntityInterface;
use App\Shared\Models\AggregateRoot;
use Doctrine\Persistence\ManagerRegistry;

class AssessmentTypeEntityRepository extends BaseEntityRepository implements AssessmentTypeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssessmentTypeEntity::class);
    }

    public function find(
        $id,
        $lockMode = null,
        $lockVersion = null,
        bool $raw = false,
    ): null|AssessmentType|AssessmentTypeEntity {
        $entity = parent::find(
            $id,
            $lockMode,
            $lockVersion
        );

        if ($entity instanceof AssessmentTypeEntity && $raw) {
            return $entity;
        }

        return $entity instanceof AssessmentTypeEntity ? $this->mapToDomainEntity(
            $entity
        ) : null;
    }

    public function findOneBy(
        array $criteria,
        array $orderBy = null,
        bool $raw = false,
    ): null|AssessmentType|AssessmentTypeEntity {
        $entity = parent::findOneBy(
            $criteria,
            $orderBy
        );

        if ($entity instanceof AssessmentTypeEntity && $raw) {
            return $entity;
        }

        return $entity instanceof AssessmentTypeEntity ? $this->mapToDomainEntity(
            $entity
        ) : null;
    }

    public function findAll(bool $raw = false): array
    {
        return $this->findBy([], raw: $raw);
    }

    /**
     * @return AssessmentType[]|AssessmentTypeEntity[]
     */
    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null,
        bool $raw = false,
    ): array {
        $entities = parent::findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        return !$raw ? array_map([$this, 'mapToDomainEntity'], $entities) : $entities;
    }

    public function save(AssessmentType|AggregateRoot $aggregateRoot): void
    {
        $this->getEntityManager()->persist(
            AssessmentTypeEntity::fromDomainEntity($aggregateRoot, $this->getEntityManager())
        );
        $this->getEntityManager()->flush();
    }

    public function delete(AssessmentType|AggregateRoot $aggregateRoot): void
    {
        $this->getEntityManager()->remove(
            AssessmentTypeEntity::fromDomainEntity($aggregateRoot, $this->getEntityManager())
        );
        $this->getEntityManager()->flush();
    }

    protected function mapToDomainEntity(
        AssessmentTypeEntity|PersistenceEntityInterface $entity
    ): AssessmentType {
        return new AssessmentType(
            $entity->getId(),
            $entity->getName()
        );
    }
}
