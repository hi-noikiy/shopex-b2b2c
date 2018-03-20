<?php

/**
 * 计数器，用于活动时限购、限制参与活动等
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_counter {

   /**
     * 支持场景类型
     *
     * @var array
     */
    static private $supportScenes = ['syspromotion.scratchcard.limit','syspromotion.scratchcard.trade.limit'];

   /**
     * instances
     *
     * @var array
     */
    static private $instances = [];

   /**
     * scene
     *
     * @var misc
     */
    public $scene = null;

   /**
     * hourTtl 过期时间，小时
     *
     * @var misc
     */
    public $hourTtl = 0;

    /**
     * 获得对应场景的实例
     *
     * @param  string  $scene
     * @return sysuser_data_passwordLocker
     */
    static function instance($scene, $hourTtl = 0)
    {
        if (static::$instances[$scene]) return static::$instances[$scene];

        if (!in_array($scene, static::$supportScenes)) {
            throw new InvalidArgumentException(sprintf('sysuser_data_passwordLocker not support scene:%s', $scene));
        }
        return new syspromotion_counter($scene, $hourTtl);
    }

    /**
     * create a new scene instance
     *
     * @param  string  $scene
     * @return null
     */
    public function __construct($scene, $hourTtl = 0)
    {
        $this->scene = $scene;
        $this->hourTtl = $hourTtl;
    }

    /**
     * 获取小时ttl
     *
     * @return int
     */
    public function getHourTtl()
    {
        return $this->hourTtl;
    }

    /**
     * 获取分钟ttl
     *
     * @return int
     */
    public function getMinuteTtl()
    {
        return $this->getHourTtl() * 60;
    }

    /**
     * 生成对应的cache key
     *
     * @return string
     */
    protected function prepareKey($activityId, $rel)
    {
        return 'promotion-limit_' . $this->scene . '_' . $activityId . '_' . $rel;
    }

    /**
     * 检查是否已经消耗完
     *
     * @return null
     */
    public function checkLimit($activityId, $rel, $limit, $msg = null)
    {
        $message = $msg ? $msg : app::get('syspromotion')->_('机会已经用完了！');
        if ($this->readUserTimes($activityId, $rel, $limit) === 0) {
            throw new \LogicException($message);
        }
    }

    public function readUserTimes($activityId, $rel, $limit)
    {
        $times = cache::store('syspromotion')->get($this->prepareKey($activityId, $rel));

        return $times === 0 || $times ? $times : $limit ;
    }

    /**
     * 尝试消费
     *
     * @return null
     */
    public function tryVerify($activityId, $rel, $limit)
    {
        $this->checkLimit($activityId, $rel);

        $residualRetryTimes = cache::store('syspromotion')->decrement($this->prepareKey($activityId, $rel),
                                                                 1,
                                                                 $limit,
                                                                 $this->getMinuteTtl());

        return $residualRetryTimes;
    }

    //增加次数
    public function addVerify($activityId, $rel, $limit,  $times = 1)
    {
        $residualRetryTimes = cache::store('syspromotion')->increment($this->prepareKey($activityId, $rel),
                                                                 $times,
                                                                 $limit,
                                                                 $this->getMinuteTtl());

        return $residualRetryTimes;
    }

}
