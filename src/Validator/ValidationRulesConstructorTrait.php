<?php
namespace ExpressiveValidator\Validator;

use Psr\Http\Message\ServerRequestInterface;

trait ValidationRulesConstructorTrait
{
    /**
     * @var ServerRequestInterface
     */
    private $request;
    private $idRegex = '/^\S{8,8}(-\S{4,4}){3,3}-\S{12,12}$/';

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}
