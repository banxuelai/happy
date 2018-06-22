<?php
/**
 * 用户相关
 * @author bxl@gmail.com
 * @date 2017-12-12
 *
 */
class UserController extends AuthController
{    
    /**
     * @desc 修改密码
     * @author bxl@gmail.com
     * @throws Exception
     */
    public function modify()
    {
        $user_model = new UserModel();
        $userId = $this->getUidbySess();
        
        $item = $user_model->getRow(array('status' => 1,'user_id' => $userId));
        
        if ($this->req->method == 'POST') {
            $phone = $this->req->post('phone');
            $password = trim($this->req->post('password'));
            $new_password1 = trim($this->req->post('new_password1'));
            $new_password2 = trim($this->req->post('new_password2'));
            
            if (!$phone) {
                throw new Exception("手机号不能为空~");
            }
            if (!preg_match('/0?(13|14|15|17|18|19)[0-9]{9}/', $phone)) {
                throw new Exception("手机号格式不正确~");
            }
            
            $data = array(
                    'phone' => $phone,
            );
            $href = "undefined";
            if ($password || $new_password1 ||$new_password2) {
                //旧密码
                if (!$item || !password_verify($password, $item['password'])) {
                    throw new Exception("旧密码错误～");
                }
                //确认密码比较
                if ($new_password1 != $new_password2) {
                    throw new Exception("两次密码输入不一样～");
                }
                //新旧密码
                if ($new_password1 == $password) {
                    throw new Exception("新旧密码不能一样～");
                }
                if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]+$/', $new_password1)) {
                    throw new Exception("密码必须由字母和数字组成~");
                }
                if (strlen($new_password1) < 6) {
                    throw new Exception("密码长度不能少于6位~");
                }
                
                Log::file("nickname({$item['nickname']})--old_pwd({$password})--new_pwd({$new_password1})", 'updatePwd');
                
                $href = "password";
                $data['password'] = password_hash($new_password1, PASSWORD_DEFAULT);
            } else {
                if ($phone == $item['phone']) {
                    throw new Exception("无修改~");
                }
            }
            $data['update_time'] = time();
            //更新
            $id = $user_model->updateOne($data, array('user_id' => $userId,'status' => 1));
            
            $this->success($href);
        }

       
        $view = array(
                'title' => '用户列表',
                'item' => $item,
                'nickname' => $this->getUserName(),
                'menu' => 'me',
                'sub' => 'modify',
                'user_type' => $this->getTypebyUid(),
        );
        $this->display('user/modify.html', $view);
    }
    
  
}


