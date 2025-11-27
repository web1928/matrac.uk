<?php

namespace Matrac\Framework;

/**
 * Base Middleware Class
 * All middleware extend this
 */
abstract class Middleware
{
    /**
     * Handle the request
     * Must be implemented by child classes
     * 
     * @return bool True to continue, false to stop
     */
    abstract public function handle();
}
