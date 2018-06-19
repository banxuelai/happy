<?php
/**
 * 登录
 * @author bxl@gmail.com
 * @date 2017-12-12
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
            'type' => $user_info['type'],
            'id' => $user_info['id']));
            $data = array(
                'last_login_ip' => $this->req->client_ip,
                'last_login_time' => time(),
            );
            $user_model->updateOne($data, array('nickname' => $nickname));
            //$this->redirect("/?back_url=$back_url");
            $this->success($back_url);
        }
        //清除session
        Session::set('aducode', '');
        session_destroy();
        $this->display('admin/login.html', array(
                'back_url' => $back_url,
                'title' => '登陆',
        ));
    }
    
    //退出登录
    public function logout()
    {
        Session::set('aducode', '');
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
    
    
    public function test()
    {
        $nickname = "chenxiaolong";
        $user_model = new UserModel();
        $user_info = $user_model->getRow(array('nickname' => $nickname));
        print_r($user_info);
    }
    
    //同步数据
    public function syn()
    {
        $confirm_model = new ConfirmModel();
        foreach ($this->config as $key => $val) {
            $lists = $days_array = explode('、', $val);
            foreach ($lists as $k => $v) {
                $data  = array(
                    'province' => '山东省',
                    'city' => $key.'市',
                    'district' => $v,
                );
                Log::file("province(山东省)--city({$key})--district({$v})", 'syn');
                 
                $id = $confirm_model->insertOne($data);
            }
        }
    }
    
    //确认点
    private $config = array(
            '济南' => '历下区、市中区、槐荫区、天桥区、历城区、长清县、平阴县、济阳县、商河县、章丘市',
            '青岛' => '市南区、市北区、李沧区、开发区、崂山区、城阳区、胶州市、即墨市、平度市、黄岛区、莱西市',
            '烟台' => '开发区、芝罘区、福山区、牟平区、莱山区、长岛县、龙口市、莱阳市、莱州市、蓬莱市、招远市、栖霞市、海阳市',
            '潍坊' => '潍城区、寒亭区、坊子区、奎文区、高新区、临朐县、昌乐县、青州市、诸城市、寿光市、安丘市、高密市、昌邑市',
            '济宁' => '任城区、兖州区、微山县、鱼台县、金乡县、嘉祥县、汶上县、泗水县、梁山县、曲阜市、邹城市',
            '德州' => '德城区、陵县、宁津县、庆云县、临邑县、齐河县、平原县、夏津县、武城县、乐陵市、禹城市',
            '临沂' => '兰山区、罗庄区、河东区、沂南县、郯城县、沂水县、苍山县、费县、平邑县、莒南县、蒙阴县、临沭县',
            '菏泽' => '牡丹区、曹县、定陶县、成武县、单县、巨野县、郓城县、鄄城县、东明县',
            '淄博' => '淄川区、张店区、博山区、临淄区、周村区、桓台县、高青县、沂源县',
            '聊城' => '东昌府区、临清市、阳谷县、莘县、荏平县、东阿县、冠县、高唐县',
            '滨州' => '滨城区、惠民县、阳信县、无棣县、沾化县、博兴县、邹平县',
            '泰安' => '泰山区、岱岳区、宁阳县、东平县、新泰市、肥城市',
            '枣庄' => '市中区、薛城区、峄城区、台儿庄区、山亭区、滕州市',
            '东营' => '东营区、河口区、垦利县、利津县、广饶县',
            '威海' => '环翠区、文登区、荣成市、乳山市',
            '日照' => '东港区、岚山区、五莲县、莒县',
            '莱芜' => '莱城区、钢城区'
    );
}
