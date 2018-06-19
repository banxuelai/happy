<?php
/**
 * 配置相关
 * @author bxl@gmail.com
 * @date 2017-12-27
 *
 */
class OperationController extends AuthController
{
    //信息确认点
    public function confirm()
    {
        $confirm_model = new ConfirmModel();
        if ($this->req->method == 'POST') {
            $province = trim($this->req->post('province'));
            $city = trim($this->req->post('city'));
            $district = trim($this->req->post('district'));
            
            //校验
            if ($province == '省份') {
                throw new Exception("请选择省份~");
            }
           
            if ($city == '地级市') {
                throw new Exception("请选择地级市~");
            }
            
            if ($district == '区县') {
                throw new Exception("请选择区县~");
            }
            //判断重复
            $item = $confirm_model->getRow(array('status' => 1,'province' => $province,'city' => $city,'district' => $district));
            if ($item) {
                throw new Exception($province.$city.$district."已存在~");
            }
            $data = array(
                'province' => $province,
                'city' => $city,
                'district' => $district,
                'admin_name' => $this->getUserName(),
                'create_time' => time(),
            );
            
            $id = $confirm_model->insertOne($data);
        }
        $page = intval($this->req->get('page'));
        $page_size = max(intval($this->req->get('page_size')), 20);
        empty($page) && $page = 1;
        $offset = ($page - 1) * $page_size;
        
        $re = $confirm_model->getList(array('status' => 1), $offset, $page_size);
        $pageHtml = $this->createPageHtml($this->buildUrl("operation/confirm.html", $this->req->get()), $re['count'], $page, $page_size);
        
        $this->display('operation/confirm.html', array(
                'title' => '信息确认点',
                'pages' => $pageHtml,
                'nickname' => $this->getUserName(),
                'lists' => $re['rows'],
                'menu' => 'operation',
                'sub' => 'confirm',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //报考层次
    public function arrange()
    {
        $operation_model = new OperationModel();
        
        if ($this->req->method == 'POST') {
            $title = trim($this->req->post('title'));
            
            //校验
            if (!$title) {
                throw new Exception("名称不能为空~");
            }
            
            if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $title)) {
                throw new Exception("请输入三字汉语~");
            }
            //判断重复
            $item  = $operation_model->getRow(array('status' => 1,'title' => $title,'type' => 'arrange'));
            if ($item) {
                throw new Exception($title."已存在~");
            }
            $data = array(
                'title' => $title,
                'type' => 'arrange',
                'create_time' => time(),
                'admin_name' => $this->getUserName(),
            );
            
            $id = $operation_model->insertOne($data);
            $this->success();
        }
        
        //获取列表
        $re = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        $this->display('operation/arrange.html', array(
                'title' => '报考层次',
                'nickname' => $this->getUserName(),
                'lists' => $re['rows'],
                'menu' => 'operation',
                'sub' => 'arrange',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //专业类别
    public function professType()
    {
        $operation_model = new OperationModel();
        
        if ($this->req->method == 'POST') {
            $title = trim($this->req->post('title'));
            $fees = $this->req->post('fees');
            //校验
            if (!$title) {
                throw new Exception("名称不能为空~");
            }
        
/*             if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $title)) {
                throw new Exception("请输入三字汉语~");
            } */
            
            if (!$fees) {
                throw new Exception("金额不能为空~");
            }
            
            if (!preg_match('/^[1-9]\d*/', $fees)) {
                throw new Exception("金额必须为数字~");
            }
            //判断重复
            $item  = $operation_model->getRow(array('status' => 1,'title' => $title,'type' => 'professType'));
            if ($item) {
                throw new Exception($title."已存在~");
            }
            $data = array(
                    'title' => $title,
                    'fees' => $fees,
                    'type' => 'professType',
                    'create_time' => time(),
                    'admin_name' => $this->getUserName(),
            );
        
            $id = $operation_model->insertOne($data);
            $this->success();
        }
        
        //获取列表
        $re = $operation_model->getList(array('status' => 1,'type' => 'professType'), -1);
        $this->display('operation/professType.html', array(
                'title' => '专业类别',
                'nickname' => $this->getUserName(),
                'lists' => $re['rows'],
                'menu' => 'operation',
                'sub' => 'professType',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //学校配置
    public function school()
    {
        $operation_model = new OperationModel();
        
        $arrange = intval($this->req->get('arrange'));
        
        //获取层次列表
        $arrange_info = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        
        //获取学校列表
        $cond = array(
            'status' => 1,
            'type' => 'school',
        );
        
        if (!$arrange) {
            $arrange = $arrange_info['rows'][0]['id'];
        }
        $cond['parent_id'] = $arrange;
        
        $re = $operation_model->getList($cond, -1);
        

        $this->display('operation/school.html', array(
                'title' => '学校配置',
                'arrange' => $arrange,
                'lists' => $re['rows'],
                'arrange_lists' => $arrange_info['rows'],
                'nickname' => $this->getUserName(),
                'menu' => 'operation',
                'sub' => 'school',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //添加
    public function addschool()
    {
        $operation_model = new OperationModel();
        
        if ($this->req->method == 'POST') {
            $type = intval($this->req->post('type'));
            $title = trim($this->req->post('title'));
            
            if (!$type) {
                throw new Exception("请选择报考层次~");
            }
            
            if (!$title) {
                throw new Exception("学校名称不能为空~");
            }
            
            if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){4,15}$/', $title)) {
                throw new Exception("学校名称格式不正确~");
            }
            //判断重复
            $item  = $operation_model->getRow(array('status' => 1,'title' => $title,'type' => 'school','parent_id' => $type));
            if ($item) {
                throw new Exception("该层次下".$title."已存在~");
            }
            $data = array(
                'title' => $title,
                'parent_id' => $type,
                'type' => 'school',
                'create_time' => time(),
                'admin_name' => $this->getUserName(),
            );
            
            $id = $operation_model->insertOne($data);
            $this->success();
        }
        $re = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        $this->display('operation/addschool.html', array(
                'title' => '学校配置',
                'nickname' => $this->getUserName(),
                'lists' => $re['rows'],
                'menu' => 'operation',
                'sub' => 'school',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //专业配置
    public function profess()
    {
        $operation_model = new OperationModel();
        
        $arrange = intval($this->req->get('arrange'));
        $school = intval($this->req->get('school'));
        
        $cond = array(
            'status' => 1,
            'type' => 'profess',
        );
        
        if ($arrange) {
            $cond['arrange_id'] = $arrange;
        }
        
        if ($school) {
            $cond['parent_id'] = $school;
        }
        
        //专业列表
        $re = $operation_model->getList($cond, -1);
        
        if ($re['count']) {
            foreach ($re['rows'] as $key => $val) {
                //学校名称
                $schoolInfo = $operation_model->getRow(array('status' => 1,'id' => $val['parent_id'],'type' => 'school'));
                $re['rows'][$key]['schoolName'] = $schoolInfo['title'] ? $schoolInfo['title'] : 'undefined';
                //专业类别
                $professType = $operation_model->getRow(array('status' => 1,'id' => $val['fees'],'type' => 'professType'));
                $re['rows'][$key]['professType'] = $professType['title'] ? $professType['title'] : 'undefined';
            }
        }
        
        
        //层次和学校信息
        $arrangeInfo = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        $schoolInfo = $operation_model->getList(array('status' => 1,'type' => 'school','parent_id' => $arrange));
        $this->display('operation/profess.html', array(
                'title' => '专业配置',
                'lists' => $re['rows'],
                'arrangeInfo' => $arrangeInfo['rows'],
                'schoolInfo' => $schoolInfo['rows'],
                'arrange' => $arrange,
                'school' => $school,
                'nickname' => $this->getUserName(),
                'menu' => 'operation',
                'sub' => 'profess',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //添加
    public function addprofess()
    {
        $operation_model = new OperationModel();
        
        if ($this->req->method == 'POST') {
            //报考层次
            $arrange = intval($this->req->post('arrange'));
            //学校
            $school = intval($this->req->post('school'));
            //专业类别
            $professType = intval($this->req->post('professType'));
            //专业名称
            $title = trim($this->req->post('title'));
            
            //校验
            if (!$school) {
                throw new Exception("请选择所属学校~");
            }
            if (!$professType) {
                throw new Exception("请选择所属专业类别~");
            }
            if (!$title) {
                throw new Exception("请输入专业名称");
            }
            
            $item = $operation_model->getRow(array('parent_id' => $arrange,'status' => 1,'type' => 'school'));
            if (!$item) {
                throw new Exception("学校信息不存在~");
            }
            
            if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,15}$/', $title)) {
                throw new Exception("专业名称格式不正确~");
            }
            
            //判断重复
            $item  = $operation_model->getRow(array('status' => 1,'title' => $title,'type' => 'profess','parent_id' => $school,'arrange_id' => $arrange));
            if ($item) {
                throw new Exception("该层次该学校下  ".$title."已存在~");
            }
            
            $data = array(
                'title' => $title,
                'parent_id' => $school,
                'type' => 'profess',
                'fees' => $professType,
                'arrange_id' => $arrange,
                'create_time' => time(),
                'admin_name' => $this->getUserName(),
            );
            $id = $operation_model->insertOne($data);
            $this->success();
        }
        //报考层次
        $arrangeInfo = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        //学校列表
        $schoolInfo = $operation_model->getList(array('status' => 1,'type' => 'school','parent_id' => $arrangeInfo['rows'][0]['id']), -1);
        //专业类别
        $professTypeInfo = $operation_model->getList(array('status' => 1,'type' => 'professType'), -1);
        $this->display('operation/addprofess.html', array(
                'title' => '专业配置',
                'arrangeInfo' => $arrangeInfo['rows'],
                'schoolInfo' => $schoolInfo['rows'],
                'professTypeInfo' => $professTypeInfo['rows'],
                'nickname' => $this->getUserName(),
                'menu' => 'operation',
                'sub' => 'profess',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //根据报考层次获取学校名称
    public function schoolInfo()
    {
        $arrange = intval($this->req->post('arrange'));
        
        $operation_model = new OperationModel();
        $info = $operation_model->getList(array('parent_id' => $arrange,'status' => 1,'type' => 'school'), -1);
        $this->success($info['rows']);
    }
    
    //根据学校获取专业
    public function professInfo()
    {
        $school = intval($this->req->post('school'));
        
        $operation_model = new OperationModel();
        $info = $operation_model->getList(array('status' => 1,'parent_id' => $school,'type' => 'profess'), -1);
        $this->success($info['rows']);
    }
    
    //根据专业获取学费
    public function feesInfo()
    {
        $profess = intval($this->req->post('profess'));
        $operation_model = new OperationModel();
        
        //学费id
        $fees_info = $operation_model->getRow(array('id' => $profess,'status' => 1));
        $fees = $operation_model->getRow(array('id' => $fees_info['fees'],'status' => 1));
        $this->success($fees['fees']);
    }
    
    //删除operation
    public function del()
    {
        
        $id = intval($this->req->post('id'));
        $operation_model = new OperationModel();
        
        //权限验证
        if ($this->getTypebyUid() != 1) {
            throw new Exception("没有删除权限~");
        }

        $operation_model->updateOne(array('status' => -1,'update_time' => time(),'admin_name' => $this->getName()), array('id' => $id));
        
        $this->success();
    }
    
    //删除confirm
    public function delConfirm()
    {
    
        $id = intval($this->req->post('id'));
        $confirm_model = new ConfirmModel();
    
        //权限验证
        if ($this->getTypebyUid() != 1) {
            throw new Exception("没有删除权限~");
        }
   
        $confirm_model->updateOne(array('status' => -1,'update_time' => time(),'admin_name' => $this->getName()), array('id' => $id));
    
        $this->success();
    }
}
