<?php
/**
 * 验证
 * @author bxl@gmail.com
 * @date 2017-12-12
 *
 */
class AuthController extends Controller
{
    public function __construct($req, $res, $cfg)
    {
        parent::__construct($req, $res, $cfg);
        $now_time = time();
        $user = Session::get('aducode');
        if (!$user) {
            return $this->checkLogin();
        }
        if ($user['active_time'] + 3600 < $now_time) {
            return $this->checkLogin('请重新登录～');
        }
/*         $user['active_time'] = $now_time;
        Session::set('aducode', $user); */
    }

    protected function checkLogin($msg = '请登陆后操作～')
    {
        $back_url = urlencode($this->req->getCurrentUrl());
        header("Location: /admin/login.html?back_url={$back_url}");
    }
   
    //获取登录用户名
    protected function getUserName()
    {
        $aducode = Session::get('aducode');
        return is_array($aducode) && isset($aducode['nickname']) ? $aducode['nickname'] : null;
    }
    
    //获取实名
    protected function getName()
    {
        $aducode = Session::get('aducode');
        return is_array($aducode) && isset($aducode['name']) ? $aducode['name'] : null;
    }
    
    //获取uid
    protected function getUidbySess()
    {
        $aducode = Session::get('aducode');
        return is_array($aducode) && isset($aducode['id']) ? $aducode['id'] : null;
    }
    
    //获取type
    protected function getTypebyUid($uid = 0)
    {
        $aducode = Session::get('aducode');
        return is_array($aducode) && isset($aducode['type']) ? $aducode['type'] : null;
    }
    
    //获取用户信息
    protected function getUserInfo()
    {
            return Session::get('aducode');
    }
    
    protected function logAdminAction($log_str)
    {
        $admin_name = $this->getUserName();
        Log::file("$admin_name\t$log_str", 'admin_log');
    }
    
    /**
     * 分页函数
     * @param string $url 当前的URL
     * @param int $total 总记录数
     * @param int $currPage 当前页
     * @param int $perPage 每页显示多少条
     * @return string 生成的html字符串
     */
    protected function createPageHtml($url, $total, $currPage, $perPage = 20)
    {
        //处理参数
        $url .= strpos($url, '?') !== false ? '&page=' : '?page=';
        $perPage = $perPage > 0 ? (int)$perPage : 20;
        
        //分页
        $pageCount = ceil($total / $perPage);
        $currPage = min(max($currPage, 1), $pageCount);
        $firstPage = 1;
        $prevPage = $currPage > 1 ? $currPage - 1 : 1;
        $nextPage = $currPage < $pageCount ? $currPage + 1 : $pageCount;
        $lastPage = $pageCount;
        
        $str = '<div class="pagination-info">共'.$total.'条记录</div>';
        $str .= '<ul class="pagination">';
        //上一页
        if ($currPage <= 1) {
            $str .= '<li class="disabled"><span aria-hidden="true">&laquo;</span></li>';
        } else {
            $str .= '<li><a href="'.$url.$prevPage.'" aria-label="Next"><span aria-hidden="true">&laquo;</span></li>';
        }
        
        //中间页
        
        //左侧
        if ($currPage > 3) {
            $page = $currPage - 3;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        if ($currPage > 2) {
            $page = $currPage - 2;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        if ($currPage > 1) {
            $page = $currPage - 1;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        
        //当前页
        $str .= '<li class="active"><span>'.$currPage.'</span></li>';
        
        //右侧
        if ($lastPage - $currPage >= 1) {
            $page = $currPage + 1;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        if ($lastPage - $currPage >= 2) {
            $page = $currPage + 2;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        if ($lastPage - $currPage >= 3) {
            $page = $currPage + 3;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        if ($lastPage - $currPage >= 4) {
            $page = $currPage + 4;
            $str .= '<li><a href="'.$url.$page.'">'.$page.'</a></li>';
        }
        //下一页
        if ($currPage == $pageCount) {
            $str .= '<li class="disabled"><span aria-hidden="true">&raquo;</span></li>';
        } else {
            $str .= '<li><a href="'.$url.$nextPage.'" aria	-label="Next"><span aria-hidden="true">&raquo;</span></li>';
        }
        $str .= '</ul>';
        return $str;
    }
}
