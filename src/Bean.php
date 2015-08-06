<?php

namespace point\core;

/**
 * Class Bean
 * Bean configuration constant
 *
 * @author sj
 */
class Bean
{
    /**
     * Class name
     */
    const CLASS_NAME = 'class';

    /**
     * Auto invoke method on class instance
     */
    const INIT_METHOD = 'init-method';

    /**
     * Class instance arguments
     */
    const CONSTRUCTOR_ARG = 'constructor-arg';

    /**
     * Configuration of properties inject
     */
    const PROPERTY = 'property';

    /**
     * Setup bean auto loading on config
     */
    const AUTO_LOAD = 'auto-load';

    /**
     * Class file include path
     */
    const INCLUDE_PATH = 'include-path';

    /**
     * Bean ID
     */
    const ID = 'id';

    /**
     * Instance scope: singleton|prototype
     */
    const SCOPE = 'scope';
    const SCOPE_SINGLETON = 'singleton';
    const SCOPE_PROTOTYPE = 'prototype';
}
