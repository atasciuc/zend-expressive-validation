<?php
namespace ExpressiveValidator\Middleware;

interface ExtractorInterface
{
    /**
     * @param $object
     * @return mixed
     */
    public function extract($object);
}
