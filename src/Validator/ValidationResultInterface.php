<?php

namespace ExpressiveValidator\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;

interface ValidationResultInterface
{
    /**
     * @param array $dataToValidate
     * @param ValidationRulesInterface $validationRulesInterface
     * @param EntityManagerInterface $entityManagerInterface
     * @param ServerRequestInterface $request
     */
    public function __construct(
        array $dataToValidate,
        ValidationRulesInterface $validationRulesInterface,
        EntityManagerInterface $entityManagerInterface,
        ServerRequestInterface $request
    );

    /**
     * Gets the validate data
     * @return mixed
     */
    public function getData();

    /**
     * Returns the error messages
     * @return mixed
     */
    public function getMessages();

    /**
     * Returns true if valid or false otherwise
     * @return mixed
     */
    public function valid();

    /**
     * Return true if not valid or false otherwise
     * @return mixed
     */
    public function notValid();

    /**
     * Get the validated request
     * @return mixed
     */
    public function getRequest();

    /**
     * @param $propertyItem
     * @param array $messages
     * @return mixed
     */
    public function addInvalidMessage($propertyItem, array $messages);
}
