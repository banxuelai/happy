<?php
/**
 * 登录
 * @author bxl@gmail.com
 * @date 2018-06-20
 *
 */
class AdminController extends Controller
{
    //登录
    public function login()
    {
        $back_url = $this->req->get('back_url');
        $user_model = new UserModel();
        
        if ($this->req->method == 'POST') {
            $nickname = trim($this->req->post('nickname'));
            $password = trim($this->req->post('password'));
            $remember = $this->req->post('remember');
            $back_url = $back_url ? $back_url : Config::site('base_url');
            
            $user_info = $this->checkLogin($nickname, $password);
            session_regenerate_id(true);
            Session::set('aducode', array(
            'login_time' => time(),
            'active_time' => ($remember == 'remember') ? (time() + 86400) : time(),
            'nickname' => $nickname,
            'name' => $user_info['name'],
            'user_type' => $user_info['user_type'],
            'user_id' => $user_info['user_id']));
            $data = array(
                'last_login_ip' => $this->req->client_ip,
                'last_login_time' => time(),
            );
            $user_model->updateOne($data, array('nickname' => $nickname));
            $this->success($back_url);
        }
        //清除session
        Session::set('happy', '');
        session_destroy();
        $this->display('admin/login.html', array(
                'back_url' => $back_url,
                'title' => '登陆',
        ));
    }
    
    //退出登录
    public function logout()
    {
        Session::set('happy', '');
        session_destroy();
        header("Location: /admin/login.html");
    }
    
    //校验用户名和密码
    private function checkLogin($nickname, $password)
    {
        if (!$nickname || !$password) {
            throw new Exception("请输入用户名和密码～");
        }
        $user_model = new UserModel();
        $user_info = $user_model->getRow(array('nickname' => $nickname,'status' => 1));
        if (!$user_info || !password_verify($password, $user_info['password'])) {
            throw new Exception("用户名或密码错误～");
        }
        return $user_info;
    }
}
