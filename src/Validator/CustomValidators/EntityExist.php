<?php
namespace ExpressiveValidator\Validator\CustomValidators;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use ExpressiveValidator\Validator\DbValidatorInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Db\RecordExists;

class EntityExist extends AbstractValidator implements DbValidatorInterface
{

    const NOT_FOUND = "notFound";
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var boolean
     */
    private $autoload;

    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * @var array Templates for errors
     */
    protected $messageTemplates = [
        self::NOT_FOUND => "'%value%' not found"
    ];

    /**
     * @param EntityManagerInterface $entityManagerInterface
     * @param array $options
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManagerInterface, array $options)
    {
        parent::__construct($options);
        $this->entityManager = $entityManagerInterface;
        $this->options = $options;
        if (!isset($options['entity'])) {
            throw new \Exception('Must provide the entity name');
        } elseif (!isset($options['field'])) {
            throw new \Exception('Must provide the field name');
        } elseif (!class_exists($this->options['entity'])) {
            throw new \Exception('The class ' . $this->options['entity'] . ' does not exits');
        }
        $this->autoload = isset($options['autoload']) ? boolval($options['autoload']) : false;
    }

    /**
     * @@inheritdoc
     */
    public function isAutoloaded()
    {
        return $this->autoload;
    }
    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false[, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws \Zend\Validator\Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $entityClass = $this->options['entity'];
        $this->collection = $this->entityManager
        ->getRepository($entityClass)
        ->findBy([
            $this->options['field'] => $value
        ]);
        if (empty($this->collection)) {
            $this->error(self::NOT_FOUND, $value);
            return false;
        } else {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCollection()
    {
        return (count($this->collection) == 1) ? $this->collection[0] : $this->collection;
    }
}
