<?php
/**
 * 首页
 * @author bxl@gmail.com
 * @date 2017-12-12
 *
 */
class HomeController extends AuthController
{
	/**
	 * 首页
	 * @author bxl@gmail.com
	 * @date 2018/06/21
	 */
    public function index()
    {
        $this->display('home/index.html', array(
                'title' => '首页 ',
                'nickname' => $this->getUserName(),
                'user_type' => $this->getTypebyUid(),
                'user_name' => $this->getName(),
                'menu' => 'home',
        ));
    }
}
