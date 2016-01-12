<?php

namespace ExpressiveValidator\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

interface DbValidatorInterface
{
    public function __construct(EntityManagerInterface $entityManagerInterface, array $options);

    /**
     * @return bool
     */
    public function isAutoloaded();
    /**
     * Get the found collection
     * @return ArrayCollection
     */
    public function getCollection();
}
