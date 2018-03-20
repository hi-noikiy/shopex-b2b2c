<?php
class syscategory_events_listeners_updateVirtualCatList
{

	public function updateVirtualcatList($platform){
		return kernel::single('syscategory_data_virtualcat',$platform)->makeTree();
	}
}

