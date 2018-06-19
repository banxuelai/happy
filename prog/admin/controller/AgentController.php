<?php
/**
 * 代理相关
 * @author bxl@gmail.com
 * @date 2017-12-27
 *
 */
class AgentController extends AuthController
{

    //添加代理
    public function add()
    {
        $agent_model = new AgentModel();
        if ($this->req->method == 'POST') {
            $name = trim($this->req->post('name'));
            $phone = trim($this->req->post('phone'));
            $uid = $this->getUidbySess();
            
            //check 重复
            $info = $agent_model->getRow(array('name' => $name,'phone' => $phone,'status' => 1));
            if ($info) {
                throw new Exception("该代理已存在");
            }
            $data = array(
                'name' => $name,
                'phone' => $phone,
                'uid' => intval($uid),
                'create_time' => time(),
            );
            
            //校验
            $this->check($data);
            $id = $agent_model->insertOne($data);
        }
        
        $this->display('agent/add.html', array(
                'title' => '添加代理',
                'nickname' => $this->getUserName(),
                'menu' => 'agent',
                'sub' => 'add',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //代理列表
    public function lists()
    {
        $agent_model = new AgentModel();
        $user_model = new UserModel();
        $agent_uid = intval($this->req->get('uid'));
        $cond = array(
            'status' => 1,
            'uid' => $this->getUidbySess(),
        );
        
        // 超级管理员
        if ($this->getTypebyUid() == 1) {
            //一级代理列表
            $userList = $user_model->getList(array('status' => 1,), -1);
             
            $cond = array(
                'status' => 1,
            );
            if ($agent_uid) {
                $cond['uid'] = $agent_uid;
            }
        }
        
        $re = $agent_model->getList($cond, -1);
        $view = array(
                'title' => '代理列表',
                'lists' => $re['rows'],
                'nickname' => $this->getUserName(),
                'menu' => 'agent',
                'sub' => 'lists',
                'type' => $this->getTypebyUid(),
                'uid' => $agent_uid,
                'user_lists' => $userList['rows'],
        );
        $this->display('agent/lists.html', $view);
    }
    
    //校验
    private function check($data)
    {
        
        if (!$data['name']) {
            throw new Exception("姓名不能为空~");
        }
        
        if (!$data['phone']) {
            throw new Exception("手机号不能为空~");
        }
        
        if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $data['name'])) {
            throw new Exception("姓名格式不正确~");
        }
        
        if (!preg_match('/0?(13|14|15|17|18|19)[0-9]{9}/', $data['phone'])) {
            throw new Exception("手机号格式不正确~");
        }
    }
    
    //删除
    public function del()
    {
        
        $id = intval($this->req->gpc('id'));
        $agent_model = new AgentModel();
        
        //权限验证
        $uid = $this->getUidbySess();
        $admin_agent = $agent_model->getRow(array('id' => $id));
        
        if (!isset($admin_agent) || ($admin_agent['uid'] != $uid && $this->getTypebyUid() != 1)) {
            throw new Exception("没有删除权限~");
        }
        
        $agent_model->updateOne(array('status' => -1,'update_time' => time()), array('id' => $id));
        
        $this->success();
    }
    
    // 修改代理信息
    public function modify()
    {
        $id = intval($this->req->gpc('id'));
        $agent_model = new AgentModel();
        
        if ($this->req->method == 'POST') {
            $name = trim($this->req->post('name'));
            $phone = trim($this->req->post('phone'));
            $uid = $this->getUidbySess();
            
            # 权限验证
            if ($this->getTypebyUid() != 1) {
                throw new Exception("对不起，您不具有修改代理的权限");
            }
            
            //check 重复
            $info = $agent_model->getRow(array('name' => $name,'phone' => $phone,'status' => 1));
            if ($info) {
                throw new Exception("该代理已存在,无需修改");
            }
            $data = array(
                    'name' => $name,
                    'phone' => $phone,
                    'update_time' => time(),
            );
    
            //校验
            $this->check($data);
            $agent_model->updateOne($data, array('id' => $id));
        }
                
        //权限验证
        $agentRow = $agent_model->getRow(array('id' => $id));
        
        $this->display('agent/modify.html', array(
                'title' => '修改代理',
                'nickname' => $this->getUserName(),
                'menu' => 'agent',
                'sub' => 'lists',
                'agent_row' => $agentRow,
                'type' => $this->getTypebyUid(),
        ));
    }
}
