<?php
/**
 * 学员信息
 * @author bxl@gmail.com
 * @date 2017-12-30
 *
 */
class StudentController extends AuthController
{
    //缴费比例配置
    private $feesConfig = array(
            '1' => 0.4,
            '2' => 0.6,
    );
    
    //缴费状态
    private $feesStatusConfig = array(
            '1' => '未缴',
            '2' => '缴费1',
            '3' => '缴费2',
            '4' => '已缴',
    );
    
    //列表
    public function lists()
    {
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        $user_model = new UserModel();
        
        $name = trim($this->req->get('name'));
        $agent_uid = intval($this->req->get('uid'));
        $agent_id = intval($this->req->get('agent_id'));
        $school = trim($this->req->get('school'));
        $profess = trim($this->req->get('profess'));
        $arrange = trim($this->req->get('arrange'));
        $fees_status = $this->req->get('fees_status');
        //性别
        $gender = trim($this->req->get('gender'));
        //民族
        $ethnic = trim($this->req->get('ethnic'));
        //户籍点
        $local = $this->req->get('local');
        //确认点
        $confirm_id = intval($this->req->get('confirm_id'));
        //缴费金额
        $all_fees = $this->req->get('all_fees');
        if ($all_fees == '') {
            $all_fees = -1;
        }
        //时间点
        $create_time = $this->req->get('create_time');
        
        $uid = $this->getUidbySess();
        
        if (!isset($local) && empty($local)) {
            $province = '';
            $city = '';
            $district  = '';
        } else {
            list($province,$city,$district) = explode('-', $local);
        }
        
        
        $search = array();
        $search['name'] = $name;
        $search['uid'] = $agent_uid;
        $search['agent_id'] = $agent_id;
        $search['school'] = $school;
        $search['profess'] = $profess;
        $search['fees_status'] = $fees_status;
        $search['arrange'] = $arrange;
        $search['gender'] = $gender;
        $search['ethnic'] = $ethnic;
        $search['local'] = $local;
        $search['confirm_id'] = $confirm_id;
        $search['all_fees'] = $all_fees;
        $search['create_time'] = $create_time;
        $search['province'] = $province;
        $search['city'] = $city;
        $search['district'] = $district;
        
        
        $page = intval($this->req->get('page'));
        $page_size = max(intval($this->req->get('page_size')), 20);
        empty($page) && $page = 1;
        $offset = ($page - 1) * $page_size;
      
        $cond = array(
            'a.status' => 1,
        );
        
        if ($name) {
            $cond['a.name'] = array('like' => "%$name%");
        }
        //超级管理员特殊处理
        if ($this->getTypebyUid() == 1) {
            if ($agent_uid) {
                $cond['a.uid'] = $agent_uid;
            }
            $chUser = $student_model->studentUser();
            $uids = array_column($chUser, 'uid');
            if (!empty($uids)) {
                $userListWhere = array(
                        'status' => 1,
                        'id' => array('in' => $uids),
                );
                $uid_list = $user_model->getList($userListWhere, -1);
            }
        } else {
            $cond['a.uid'] = $uid;
        }
        
        if ($agent_id) {
            $cond['a.agent_id'] = $agent_id;
        }

        if ($school) {
            $cond['b.school'] = $school;
        }
        
        if ($profess) {
            $cond['b.profess'] = $profess;
        }
        
        if ($fees_status) {
            $cond['b.fees_status'] = $fees_status;
        }
        
        if ($arrange) {
            $cond['b.arrange'] = $arrange;
        }
        
        if ($gender) {
            $cond['a.gender'] = $gender;
        }
        if ($ethnic) {
            $cond['a.ethnic'] = $ethnic;
        }
        
        if ($confirm_id) {
            $cond['b.confirm_id'] = $confirm_id;
        }
          
        
        if ($province) {
            $cond['a.province'] = $province;
        }
        if ($city) {
            $cond['a.city'] = $city;
        }
        if ($district) {
            $cond['a.district'] = $district;
        }
        if ($all_fees >= 0) {
            $cond['b.all_fees'] = $all_fees;
        }
            
        if ($create_time) {
            $time = strtotime($create_time);
            $cond['a.create_time']['>='] = $time;
            $cond['a.create_time']['<'] = $time + 86400;
        }
        $re = $student_model->getList($cond, $offset, $page_size);
        
        foreach ($re['rows'] as $key => $val) {
            $re['rows'][$key] = $this->buildStudentItem($val);
        }
        $pageHtml = $this->createPageHtml($this->buildUrl("student/lists.html", $this->req->get()), $re['count'], $page, $page_size);
        
        //二级代理
        $agent_cond = array(
            'status' => 1,
        );
        if ($this->getTypebyUid() == 0) {
            $agent_cond['uid'] = $uid;
            $chAgent = $student_model->studentAgent($uid);
        }
        $chAgent = $student_model->studentAgent();
        //获取去重的agent_id
        $agent_ids = array_column($chAgent, 'agent_id');
        
        if (!empty($agent_ids)) {
            $agent_cond['id'] = array('in' => $agent_ids);
            
            $agent_info = $agent_model->getList($agent_cond, -1);
        }
        
        $info_uid = $this->getTypebyUid() ? 0 : $this->getUidbySess();
        //学校
        $school_info = $student_model->sdudentSchool($info_uid);
        //专业
        $profess_info = $student_model->sdudentProfess($info_uid);
        //层次
        $arrange_info = $student_model->sdudentArrange($info_uid);
        if ($this->getTypebyUid() == 1) {
            //信息确认点
            $confirm_info = $student_model->studentConfirm();
            //时间
            $time_lists = $student_model->studentTime();
            $tmp = array();
            foreach ($time_lists as $key => $val) {
                $tmp[] = date("Y-m-d", $val['create_time']);
            }
            $time_info = array_unique($tmp);
            //金额
            $fees_info = $student_model->studentFees();
            //户籍地
            $local_info = $student_model->studentLocal();
            //民族
            $ethnic_info = $student_model->studentEthnic();
        }
        $this->display('student/lists.html', array(
                'title' => '我的录入',
                'pages' => $pageHtml,
                'lists' => $re['rows'],
                'userInfo' => isset($uid_list['rows']) ? $uid_list['rows'] : array(),
                'agentInfo' => $agent_info['rows'],
                'schoolInfo' => isset($school_info) ? $school_info : array(),
                'professInfo' => isset($profess_info) ? $profess_info : array(),
                'arrangeInfo' => isset($arrange_info) ? $arrange_info : array(),
                'confirmInfo' => isset($confirm_info) ? $confirm_info : array(),
                'localInfo' => isset($local_info) ? $local_info : array(),
                'timeInfo' => isset($time_info) ? $time_info : array(),
                'ethnicInfo' => isset($ethnic_info) ? $ethnic_info : array(),
                'feesInfo' => isset($fees_info) ? $fees_info : array(),
                'nickname' => $this->getUserName(),
                'search' => $search,
                'type' => $this->getTypebyUid(),
                'menu' => 'student',
                'sub' => 'lists',
        ));
    }
    
    //添加
    public function add()
    {
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        
        $uid = $this->getUidbySess();
        
        
        if ($this->req->method == 'POST') {
            $name = trim($this->req->post('name'));
            $agent_id = intval($this->req->post('agent_id'));
            $gender = trim($this->req->post('gender'));
            $phone = $this->req->post('phone');
            $ethnic = trim($this->req->post('ethnic'));
            $ID_num = $this->req->post('ID_num');
            $province = trim($this->req->post('province'));
            $city = trim($this->req->post('city'));
            $district = trim($this->req->post('district'));
            
            $confirm_id = intval($this->req->post('confirm_id'));
            $arrange_id = intval($this->req->post('arrange'));
            $professType = intval($this->req->post('professType'));
            $school_id = intval($this->req->post('school'));
            $profess_id = intval($this->req->post('profess'));
            $entryFee = intval($this->req->post('entryFee'));
            $fees = intval($this->req->post('fees'));
            $extra = trim($this->req->post('extra'));
            
            $arrange_item = $operation_model->getRow(array('id' => $arrange_id,'status' => 1,'type' => 'arrange'));
            $school_item = $operation_model->getRow(array('id' => $school_id,'status' => 1,'type' => 'school'));
            $profess_item = $operation_model->getRow(array('id' => $profess_id,'status' => 1,'type' => 'profess'));
            
            
            //student 基础信息
            $data = array(
                'uid' => $uid,
                'agent_id' => $agent_id,
                'name' => $name,
                'gender' => $gender,
                'phone' => $phone,
                'ethnic' => $ethnic,
                'ID_num' => $ID_num,
                'province' => $province,
                'city' => $city,
                'district' => $district,
                'create_time' => time(),
            );
            //附加信息
            $extra_data = array(
                    'confirm_id' => $confirm_id,
                    'arrange_id' => $arrange_id,
                    'arrange' => $arrange_item['title'] ? $arrange_item['title'] : '',
                    'professType' => $professType,
                    'school_id' => $school_id,
                    'school' => $school_item['title'] ? $school_item['title'] : '',
                    'profess_id' => $profess_id,
                    'profess' => $profess_item['title'] ? $profess_item['title'] : '',
                    'entryFee' => $entryFee,
                    'fees' => $fees,
                    'extra' => $extra,
            );
            //校验
            $this->check($data, $extra_data);
            $id = $student_model->insertOne($data);
            if ($id) {
                $extra_data['student_id'] = $id;
            }
            $student_model->insertOne($extra_data, 'student_extra');
            $this->success();
        }
        
        //二级代理
        $agent_info = $agent_model->getList(array('uid' => $uid,'status' => 1), -1);
        //确认点
        $confirm_info = $confirm_model->getList(array('status' => 1), -1);
        foreach ($confirm_info['rows'] as $key => $val) {
            $confirm_info['rows'][$key]['confirm'] = $val['province'].$val['city'].$val['district'];
        }
        //报考层次
        $arrange_info = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        $this->display('student/add.html', array(
                'title' => '录入信息',
                'agentInfo' => $agent_info['rows'],
                'confirmInfo' => $confirm_info['rows'],
                'arrangeInfo' => $arrange_info['rows'],
                'nickname' => $this->getUserName(),
                'menu' => 'student',
                'sub' => 'add',
                'type' => $this->getTypebyUid(),
        ));
    }
    
    //校验信息
    private function check($data, $extra_data, $type = "add")
    {
        $student_model = new StudentModel();
        if (!$data['agent_id']) {
            throw new Exception("请选择二级代理~");
        }
        
        if (!$data['name']) {
            throw new Exception("学员姓名不能为空~");
        }
        
        if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $data['name'])) {
            throw new Exception("姓名格式不正确~");
        }
        
        if (!$data['phone']) {
            throw new Exception("手机号不能为空~");
        }
        
        if (!preg_match('/0?(13|14|15|17|18|19)[0-9]{9}/', $data['phone'])) {
            throw new Exception("手机号格式不正确~");
        }
        
        if (!$data['ethnic']) {
            throw new Exception("民族不能为空~");
        }
        
        if (!preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){1,9}$/', $data['ethnic'])) {
            throw new Exception("民族格式不正确~");
        }
        
        if (!$data['ID_num']) {
            throw new Exception("身份证号不能为空~");
        }
        
        if (!preg_match('/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', $data['ID_num'])) {
            throw new Exception("身份证号不正确~");
        }
        
        if ($data['province'] == '省份') {
            throw new Exception("请选择省份~");
        }
         
        if ($data['city'] == '地级市') {
            throw new Exception("请选择地级市~");
        }
        
        if ($data['district'] == '区县') {
            throw new Exception("请选择区县~");
        }
        
        if (!$extra_data['confirm_id']) {
            throw new Exception("请选择信息确认点~");
        }

        if (!$extra_data['arrange']) {
            throw new Exception("请选择报考层次~");
        }
        
        if (!$extra_data['school']) {
            throw new Exception("请选择学校~");
        }
        
        if (!$extra_data['profess']) {
            throw new Exception("请选择专业~");
        }
        
        if (!$extra_data['fees']) {
            throw new Exception("学费不能为空~");
        }
        
        if ($type == 'add') {
            //校验身份信息唯一性
            $studentItem = $student_model->getItemByCond(array('a.ID_num' => $data['ID_num'],'a.status' => 1));
            if ($data['ID_num'] == $studentItem['ID_num']) {
                throw new Exception("该学员身份信息已录入~");
            }
        }
    }
    
    //学员信息详情
    public function detail()
    {
        $student_id  = $this->req->get('student_id');
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        $user_model = new UserModel();
        
        $student_info = $student_model->getItem($student_id);
        
        if ($student_info) {
            $student_info = $this->buildStudentItem($student_info);
        }
        
        $this->display('student/detail.html', array(
                'title' => '详情信息',
                'nickname' => $this->getUserName(),
                'menu' => 'student',
                'sub' => 'lists',
                'type' => $this->getTypebyUid(),
                'studentInfo' => $student_info,
        ));
    }
    
    //修改学员信息
    public function modify()
    {
        $student_id  = $this->req->gpc('student_id');
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        $user_model = new UserModel();
        
        $student_info = $student_model->getItem($student_id);
        $item = $this->buildStudentItem($student_info);
                 
        if ($this->req->method == 'POST') {
            //权限判断
            if ($this->getTypebyUid() != 1) {
                throw new Exception("普通用户没有删除权限~");
            }
            
            $name = trim($this->req->post('name'));
            $agent_id = intval($this->req->post('agent_id'));
            $gender = trim($this->req->post('gender'));
            $phone = $this->req->post('phone');
            $ethnic = trim($this->req->post('ethnic'));
            $ID_num = $this->req->post('ID_num');
            $province = trim($this->req->post('province'));
            $city = trim($this->req->post('city'));
            $district = trim($this->req->post('district'));
            
            $confirm_id = intval($this->req->post('confirm_id'));
            $extra = trim($this->req->post('extra'));
            
            $arrange_id = intval($this->req->post('arrange'));
            $school_id = intval($this->req->post('school'));
            $profess_id = intval($this->req->post('profess'));
            $entryFee = intval($this->req->post('entryFee'));
            $fees = intval($this->req->post('fees'));
            
            
            $arrange_item = $operation_model->getRow(array('id' => $arrange_id,'status' => 1,'type' => 'arrange'));
            $school_item = $operation_model->getRow(array('id' => $school_id,'status' => 1,'type' => 'school'));
            $profess_item = $operation_model->getRow(array('id' => $profess_id,'status' => 1,'type' => 'profess'));
            
            //student 基础信息
            $data = array(
                    'agent_id' => $agent_id,
                    'name' => $name,
                    'gender' => $gender,
                    'phone' => $phone,
                    'ethnic' => $ethnic,
                    'ID_num' => $ID_num,
                    'province' => $province,
                    'city' => $city,
                    'district' => $district,
                    'update_time' => time(),
            );
            //附加信息
            $extra_data = array(
                    'confirm_id' => $confirm_id,
                    'arrange_id' => $arrange_id,
                    'arrange' => $arrange_item['title'] ? $arrange_item['title'] : '',
                    'school_id' => $school_id,
                    'school' => $school_item['title'] ? $school_item['title'] : '',
                    'profess_id' => $profess_id,
                    'profess' => $profess_item['title'] ? $profess_item['title'] : '',
                    'entryFee' => $entryFee,
                    'fees' => $fees,
                    'extra' => $extra,
                    
            );
            //校验
            $this->check($data, $extra_data, 'modify');
            Log::file("pre_info---id({$student_id})--agent_id({$student_info['agent_id']})--name({$student_info['name']})--gender({$student_info['gender']})--phone({$student_info['phone']})--ethnic({$student_info['ethnic']})--ID_num({$student_info['ID_num']}--province({$student_info['province']})--city({$student_info['city']})--district({$student_info['district']})--confirm_id({$student_info['confirm_id']})", 'modifyStudent');
            
            $fees1 = $this->req->post('fees1');
            $fees2 = $this->req->post('fees2');
            $f1 = $f2 = $all_f = 0;
            if ($fees1 && $fees2) {
                $f1 = $student_info['fees'] * $this->feesConfig[1];
                $f2 = $student_info['fees'] * $this->feesConfig[2];
                $all_f = $f1 + $f2;
                $fees_status = 4;
            } elseif ($fees1) {
                $f1 = $student_info['fees'] * $this->feesConfig[1];
                if ($student_info['fees_status'] == 3) {
                    $fees_status = 4;
                    $all_f = $f1 + $student_info['fees2'];
                }
                if ($student_info['fees_status'] == 1) {
                    $fees_status = 2;
                    $all_f = $f1;
                }
            } elseif ($fees2) {
                $f2 = $student_info['fees'] * $this->feesConfig[2];
                if ($student_info['fees_status'] == 2) {
                    $fees_status = 4;
                    $all_f = $f2 + $student_info['fees1'];
                }
                if ($student_info['fees_status'] == 1) {
                    $fees_status = 3;
                    $all_f = $f2;
                }
            }
            if ($f1) {
                $extra_data['fees1'] = $f1;
            }
            if ($f2) {
                $extra_data['fees2'] = $f2;
            }
            if ($all_f) {
                $extra_data['all_fees'] = $all_f;
            }
            if ($fees_status) {
                $extra_data['fees_status'] = $fees_status;
            }
            //更新
            $update_id = $student_model->updateOne($data, array('id' => $student_id), 'student');
            $update_extra_id = $student_model->updateOne($extra_data, array('student_id' => $student_id), 'student_extra');
            if ($update_id&&$update_extra_id) {
                Log::file("student_id({$student_id})--fees({$student_info['fees']})--fees_status({$student_info['fees_status']})--fees1({$student_info['fees1']})--fees2({$student_info['fees2']})--all_fees({$student_info['all_fees']})--new_status({$fees_status})--f1({$f1})--f2({($f2)})--all_f{($all_f)}--editor({$this->getUserName()})", 'editFees');
                 
                Log::file("new_info---id({$student_id})--agent_id({$agent_id})--name({$name})--gender({$gender})--phone({$phone})--ethnic({$ethnic})--ID_num({$ID_num}--province({$province})--city({$city})--district({$district})--confirm_id({$confirm_id})", 'modifyStudent');
            }
            $this->success();
        }
        //二级代理
        $agent_info = $agent_model->getList(array('uid' => $student_info['uid'],'status' => 1), -1);
        //确认点
        $confirm_info = $confirm_model->getList(array('status' => 1), -1);
        foreach ($confirm_info['rows'] as $key => $val) {
            $confirm_info['rows'][$key]['confirm'] = $val['province'].$val['city'].$val['district'];
        }
        //报考层次
        $arrange_info = $operation_model->getList(array('status' => 1,'type' => 'arrange'), -1);
        //学校
        $operation_model = new OperationModel();
        $school_info = $operation_model->getList(array('parent_id' => $item['arrange_id'],'status' => 1,'type' => 'school'), -1);
        //专业
        $profess_info = $operation_model->getList(array('status' => 1,'parent_id' => $item['school_id'],'type' => 'profess'), -1);
        
        $this->display('student/modify.html', array(
                'title' => '修改信息',
                'agentInfo' => $agent_info['rows'],
                'confirmInfo' => $confirm_info['rows'],
                'nickname' => $this->getUserName(),
                'menu' => 'student',
                'sub' => 'lists',
                'type' => $this->getTypebyUid(),
                'studentInfo' => $student_info,
                'arrangeInfo' => $arrange_info['rows'],
                'schoolInfo' => $school_info['rows'],
                'professInfo' => $profess_info['rows'],
        ));
    }
    
    //修改缴费信息
    public function edit()
    {
        $student_id  = $this->req->gpc('student_id');
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        $user_model = new UserModel();
        
        $student_info = $student_model->getItem($student_id);
        
        if ($this->req->method == 'POST') {
            $fees1 = $this->req->post('fees1');
            $fees2 = $this->req->post('fees2');
            if ($fees1 && $fees2) {
                $fees_status = 4;
            } elseif ($fees1) {
                if ($student_info['fees_status'] == 3) {
                    $fees_status = 4;
                }
                if ($student_info['fees_status'] == 1) {
                    $fees_status = 2;
                }
            } elseif ($fees2) {
                if ($student_info['fees_status'] == 2) {
                    $fees_status = 4;
                }
                if ($student_info['fees_status'] == 1) {
                    $fees_status = 3;
                }
            }
            Log::file("student_id({$student_id})--fees({$student_info['fees']})--fees_status({$student_info['fees_status']})--fees1({$fees1})--fees2({$fees2})--new_status({$fees_status})--editor({$this->getUserName()})", 'editFees');
            
            //更新
            $id = $student_model->updateOne(array('fees_status' => $fees_status,), array('student_id' => $student_id), 'student_extra');
            $this->success();
        }
            
        if ($student_info) {
            $student_info = $this->buildStudentItem($student_info);
        }
    
        $this->display('student/edit.html', array(
                'title' => '缴费信息',
                'nickname' => $this->getUserName(),
                'menu' => 'student',
                'sub' => 'lists',
                'type' => $this->getTypebyUid(),
                'studentInfo' => $student_info,
        ));
    }
    
    //删除学员信息
    public function del()
    {
        $student_id = intval($this->req->post('student_id'));
        $uid = $this->getUidbySess();
        if ($this->getTypebyUid() != 1) {
            throw new Exception("普通用户没有删除权限~");
        }
        
        $student_model = new StudentModel();
        
        $update_id = $student_model->updateOne(array('status' => -1,'update_time' => time()), array('id' => $student_id));
        
        if ($update_id) {
            Log::file("del_info---id({$student_id})--editor({$this->getName()})", 'delStudent');
            $this->success();
        }
    }
    
    //获取性别
    private function getGender($xingbie)
    {
        $gender = '你猜';
        switch ($xingbie) {
            case 'm':
                $gender = '男';
                break;
            case 'f':
                $gender = '女';
                break;
            default:
                $gender = '你猜';
        }
        return $gender;
    }
    
    //根据信息获取实际缴费
    private function getFees($student_info, $type = 'all_fees')
    {
        //缴费信息
        $fees1 = $fees2 = 0;
        if ($student_info['fees_status'] == 2) {
            $fees1 = $student_info['fees'] * $this->feesConfig[1];
        }
        
        if ($student_info['fees_status'] == 3) {
            $fees2 = $student_info['fees'] * $this->feesConfig[2];
        }
        
        if ($student_info['fees_status'] == 4) {
            $fees1 = $student_info['fees'] * $this->feesConfig[1];
            $fees2 = $student_info['fees'] * $this->feesConfig[2];
        }
        if ($type == "fees1") {
            return $fees1;
        } elseif ($type == "fees2") {
            return $fees2;
        } else {
            return $fees1 + $fees2;
        }
    }
    
    //构造学员详细信息
    private function buildStudentItem($student_info)
    {
        $student_model = new StudentModel();
        $agent_model = new AgentModel();
        $operation_model = new OperationModel();
        $confirm_model = new ConfirmModel();
        $user_model = new UserModel();
        
        //一级代理
        $user_info  = $user_model->getRow(array('id' => $student_info['uid']));
        $student_info['user_name'] = $user_info['name'] ? $user_info['name'] : '';
        
        //性别
        $student_info['gender'] = $this->getGender($student_info['gender']);
        //二级代理
        $agent_info = $agent_model->getRow(array('status' => 1,'id' => $student_info['agent_id']));
        $student_info['agent_name'] = $agent_info['name'];
        //确认点
        $confirm_info = $confirm_model->getRow(array('status' => 1,'id' => $student_info['confirm_id']));
        $student_info['confirm'] = $confirm_info['province'].$confirm_info['city'].$confirm_info['district'];
        //报考层次
/*         $arrange_info = $operation_model->getRow(array('status' => 1,'id' => $student_info['arrange'],'type' => 'arrange'));
        $student_info['arrange'] = $arrange_info['title'];
        //学校
        $school_info = $operation_model->getRow(array('status' => 1,'id' => $student_info['school'],'type' => 'school'));
        $student_info['school_name'] = $school_info['title'];
        //专业
        $profess_info = $operation_model->getRow(array('status' => 1,'id' => $student_info['profess'],'type' => 'profess'));
        $student_info['profess_name'] = $profess_info['title']; */
        //缴费信息
/*         $student_info['fees1'] = $this->getFees($student_info, 'fees1');
        $student_info['fees2'] = $this->getFees($student_info, 'fees2');
        $student_info['all_fees'] = $this->getFees($student_info, 'all_fees'); */
        
        //缴费状态
        $student_info['fees_status'] = $this->feesStatusConfig[$student_info['fees_status']];
        //时间
        $student_info['create_time'] = date("Y-m-d H:i:s", $student_info['create_time']);
         
        return $student_info;
    }
    
    
    //导出excel at 20180403
    public function export()
    {
        set_time_limit(0);
        ini_set("memory_limit", "512M");
        
        include 'PHPExcel/Classes/PHPExcel.php';
        include 'PHPExcel/Classes/PHPExcel/IOFactory.php';
        include 'PHPExcel/Classes/PHPExcel/Writer/Excel5.php';
        // 创建对象
        $excel = new PHPExcel();
        // Excel表格式,这里简略写了8列
        $letter = array(
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
        );
        
        //居中设置
        //$excel->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //左右居中
        //$excel->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); //上下居中
        
        //边框设置
        $styleThinBlackBorderOutline = array(
                'borders' => array (
                        'outline' => array (
                                'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                                //'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
                                'color' => array ('argb' => 'FF000000'),          //设置border颜色
                        ),
                ),
        );
        //$excel->getActiveSheet()->getStyle('A4:E10')->applyFromArray($styleThinBlackBorderOutline);
        
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(22);
        $excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        
        // 表头数组
        $tableheader = array(
                '一级代理',
                '二级代理',
                '学员姓名',
                '性别',
                '电话',
                '民族',
                '身份证号',
                '户籍地',
                '确认点',
                '层次',
                '学校',
                '专业',
                '缴费状态',
                '缴费金额',
                '录入时间',
        );
        // 填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        
        // 获取数据
        $student_model = new StudentModel();
        $user_model = new UserModel();
        
        $cond['a.status'] = 1;
        $re = $student_model->getList($cond, -1);
        
        // 填充数据
        $data = array();
        $i = 0;
        
        foreach ($re['rows'] as $key => $val) {
            //一级代理姓名
            $re['rows'][$key] = $this->buildStudentItem($val);
            $data[$i][0] = $re['rows'][$key]['user_name'];
            $data[$i][1] = $re['rows'][$key]['agent_name'];
            $data[$i][2] = $re['rows'][$key]['name'];
            $data[$i][3] = $re['rows'][$key]['gender'];
            $data[$i][4] = $re['rows'][$key]['phone'];
            $data[$i][5] = $re['rows'][$key]['ethnic'];
            $data[$i][6] = $re['rows'][$key]['ID_num'];
            $data[$i][7] = $re['rows'][$key]['province'].$re['rows'][$key]['city'].$re['rows'][$key]['district'];
            $data[$i][8] = $re['rows'][$key]['confirm'];
            $data[$i][9] = $re['rows'][$key]['arrange'];
            $data[$i][10] = $re['rows'][$key]['school'];
            $data[$i][11] = $re['rows'][$key]['profess'];
            $data[$i][12] = $re['rows'][$key]['fees_status'];
            $data[$i][13] = $re['rows'][$key]['all_fees'];
            $data[$i][14] = $re['rows'][$key]['create_time'];
            $i++;
        }
        
        // 填充表格信息
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                if ($j == 6) {
                    $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
                }
                $j++;
            }
        }
        
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header('Cache-Control: max-age=0');
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        
        $filename = date('Ymdhms', time()) . '_' . "学员信息.xls";
        $filename = iconv("utf-8", "gb2312", $filename);
        header("content-disposition:attachment;filename=" .$filename);
        header("Content-Transfer-Encoding:binary");
        
        $write->save('php://output');
        
        exit();
    }
    
    //同步压力测试数据 at 20180408
    public function syn()
    {
        $student_model = new StudentModel();
        $user_model = new UserModel();
        $operation_model = new OperationModel();
        
        $cond['a.status'] = 1;
        $re = $student_model->getList($cond, -1);
        
        for ($i = 1; $i <= 40; $i++) {
            foreach ($re['rows'] as $key => $val) {
                //student 基础信息
                $data = array(
                        'uid' => $val['uid'],
                        'agent_id' => $val['agent_id'],
                        'name' => $val['name'],
                        'gender' => $val['gender'],
                        'phone' => $val['phone'],
                        'ethnic' => $val['ethnic'],
                        'ID_num' => $val['ID_num'],
                        'province' => $val['province'],
                        'city' => $val['city'],
                        'district' => $val['district'],
                        'create_time' => time(),
                );
            
                $arrange_item = $operation_model->getRow(array('id' => $val['arrange_id'],'status' => 1,'type' => 'arrange'));
                $school_item = $operation_model->getRow(array('id' => $val['school_id'],'status' => 1,'type' => 'school'));
                $profess_item = $operation_model->getRow(array('id' => $val['profess_id'],'status' => 1,'type' => 'profess'));
            
                //附加信息
                $extra_data = array(
                        'confirm_id' => $val['confirm_id'],
                        'arrange_id' => $val['arrange_id'],
                        'arrange' => $arrange_item['title'] ? $arrange_item['title'] : '',
                        'professType' => $val['professType'],
                        'school_id' => $val['school_id'],
                        'school' => $school_item['title'] ? $school_item['title'] : '',
                        'profess_id' => $val['profess_id'],
                        'profess' => $profess_item['title'] ? $profess_item['title'] : '',
                        'entryFee' => $val['entryFee'],
                        'fees' => $val['fees'],
                        'extra' => $val['extra'],
                );
                //校验
                $id = $student_model->insertOne($data);
                if ($id) {
                    $extra_data['student_id'] = $id;
                }
                $student_model->insertOne($extra_data, 'student_extra');
            }
        }
    }
}
