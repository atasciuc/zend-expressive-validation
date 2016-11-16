<?php

namespace ExpressiveValidator\Validator;

/**
 * Helper for parsing all of the http input
 */
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Stratigility\Http\Request;

class RequestValidator extends Request
{

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $parsedBody = [];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var ValidationResultInterface
     */
    private $validationResult;

    /**
     * @param ServerRequestInterface $request
     * @param RouterInterface $router
     */
    public function __construct(ServerRequestInterface $request, RouterInterface $router)
    {
        parent::__construct($request);
        $this->parameters = $this->parseIncomingParams();
        $this->parameters = array_merge(
            $this->parameters,
            $this->getParsedBody(),
            $this->getParsedAttributes($request, $router),
            $this->getQueryParams(),
            $this->getUploadedFiles()
        );
        $this->parsedBody = array_merge($this->getParsedBody(), $this->parameters);
    }

    
    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * @return array
     */
    private function parseIncomingParams()
    {
        $parameters = [];

        $server = $this->getServerParams();

        $body = $this->getBody();

        $content_type = false;
        if (isset($server['CONTENT_TYPE'])) {
            $content_type = $server['CONTENT_TYPE'];
        }
        switch ($content_type) {
            case "application/json":
                $body_params = json_decode($body, true);
                if ($body_params) {
                    foreach ($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach ($postvars as $field => $value) {
                    $parameters[$field] = $value;

                }
                break;
        }
        return $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }


    /**
     * Gets the validation result
     * @return ValidationResultInterface
     */
    public function getValidationResult()
    {
        return $this->validationResult;
    }
    /**
     * Set the validations result
     * @param ValidationResultInterface $resultInterface
     */
    public function setValidationResult(ValidationResultInterface $resultInterface)
    {
        $this->validationResult = $resultInterface;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RouterInterface $router
     * @return array
     */
    private function getParsedAttributes(ServerRequestInterface $request, RouterInterface $router)
    {
        $routeResult = $router->match($request);
        return $routeResult->getMatchedParams();
    }
}
