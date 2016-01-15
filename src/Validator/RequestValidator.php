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
     * @var ServerRequestInterface
     */
    private $request;
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

        $this->request = $request;
        parent::__construct($this);
        $this->parameters = $this->parseIncomingParams();
        $this->parameters = array_merge(
            $this->parameters,
            $this->request->getParsedBody(),
            $this->getParsedAttributes($request, $router),
            $this->getQueryParams(),
            $this->request->getUploadedFiles()
        );
        $this->parsedBody = array_merge(parent::getParsedBody(), $this->parameters);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->request, $name], $arguments);
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->request->getMethod();
    }
    /**
     * @inheritdoc
     */
    public function withAttribute($name, $default = null)
    {
        $new = clone $this;
        $new->attributes[$name] = $default;
        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getUri()
    {
        return $this->request->getUri();
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

        $server = $this->request->getServerParams();

        $body = $this->request->getBody();

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
     * @return array
     */
    public function getQueryParams()
    {
        return $this->request->getQueryParams();
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
