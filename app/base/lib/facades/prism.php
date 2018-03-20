<?php
class base_facades_prism extends base_facades_facade{

    /**
	 * The redis instance
	 *
	 * @var base_redis_database
	 */
    private static $__prism;

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        if (!static::$__prism)
        {
            static::$__prism = new base_oauth();
        }

        return static::$__prism;
    }

}
