<?php
/**
 * 首页
 * @author bxl@gmail.com
 * @date 2017-12-12
 *
 */
class HomeController extends AuthController
{
    public function index()
    {
        $this->display('home/index.html', array(
                'title' => '首页 ',
                'nickname' => $this->getUserName(),
                'type' => $this->getTypebyUid(),
                'name' => $this->getName(),
                'menu' => 'home',
        ));
    }
}
