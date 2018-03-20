<?php

class topdev_menu {

    public function getMenu()
    {
        $this->menu = $this->preMenu($this->menu);
        return $this->menu;
    }

    public function preMenu($menuRow)
    {
        foreach( $menuRow as $key=>$val)
        {
            if( is_array($val['group_name']) )
            {
                $data[$key]['name'] = $val['group_name'][0];
            }
            else
            {
                $data[$key]['name'] = $val['group_name'];
                $data[$key]['menu'][] = $val['menu'];
                continue;
            }

            if( is_array($val['icon']) )
            {
                $data[$key]['icon'] = $val['icon'][0];
            }

            if( $val['menu'] )
            {
                if( isset($val['menu']['name']) )
                {
                    $menu = array();
                    foreach( $val['menu']['name'] as $k=>$v )
                    {
                        $menu[$k]['name'] = $val['menu']['name'][$k];
                        $menu[$k]['href'] = $val['menu']['href'][$k];
                        $menu[$k]['tag'] = $val['menu']['tag'][$k];
                        $menu[$k]['icon'] = $val['menu']['icon'][$k];
                    }
                    $data[$key]['menu'] = $menu;
                }
                else
                {
                    $data[$key]['menu'] = $this->preMenu($val['menu']);
                }
            }
        }

        return $data;
    }

	public function group(array $attributes, Closure $callback)
	{
		$this->updateGroupStack($attributes);

		call_user_func($callback, $this);

		array_pop($this->groupStack);

        return $this;
	}

	protected function updateGroupStack(array $attributes)
	{
        if ( ! empty($this->groupStack))
        {
            $attributes = $this->mergeGroup($attributes, last($this->groupStack));
        }

        if( $this->groupStack )
        {
            $attributes['pid'] = count($this->groupStack)-1;
            $attributes['id'] = count($this->groupStack);
        }
        else
        {
            $attributes['id'] = 0;
        }
		$this->groupStack[] = $attributes;
	}

	public function mergeGroup($new, $old)
	{
        $oldGroupName = isset($old['group_name']) ? $old['group_name'] : null;

        if (isset($new['group_name']))
        {
            return $new;
        }

        return $oldGroupName;
	}

    public function createTree($arr,$pid=0, $menu)
    {
        $ret = array();
        foreach($arr as $k => $v)
        {
            if($v['pid'] == $pid)
            {
                $tmp = $arr[$k];
                unset($arr[$k]);
                unset($tmp['pid']);
                unset($tmp['id']);
                $tmp['menu'] = $this->createTree($arr,$v['id'], $menu);
                if( !$tmp['menu'] )
                {
                    $tmp['menu'] = $menu;
                }

                $ret[$tmp['group_name']] = $tmp;
            }
        }
        return $ret;
    }

    public function add($name, $action, $tag, $icon)
    {
        if ($this->hasGroupStack())
        {
            $menu['name'] = $name;
            if( is_array($action) )
            {
                $menu['href'] = url::action($action[0], $action[1]);
            }
            else
            {
                $menu['href'] = url::action($action);
            }
            $menu['tag'] = $tag;
            $menu['icon'] = $icon;
            $menuAll = current($this->createTree($this->groupStack, 0, $menu));
            if( $this->menu )
            {
                $data[$menuAll['group_name']] = $menuAll;
                $this->menu = array_merge_recursive($this->menu, $data);
            }
            else
            {
                $this->menu[$menuAll['group_name']] = $menuAll;
            }
        }
        return $this;
    }

    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }
}
