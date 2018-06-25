<?php
/**
 * 项目配置
 * @author bxl@gmail.com
 * @date 2018-06-23
 *
 */
class ProjectController extends AuthController
{
	/**
	 * @desc 项目列表
	 * @author bxl@gmail.com
	 * @date 2018-06-23
	 */
	public function lists() 
	{
		$project_model = new ProjectModel();

		$page = intval($this->req->get('page'));
		$page_size = max(intval($this->req->get('page_size')), 20);
		empty($page) && $page = 1;
		$offset = ($page - 1) * $page_size;
		
		# 构造查询条件
		$cond = array(
			'project_status' => 1,
		);
		
		$re = $project_model->getList($cond, $offset, $page_size);
		
		$pageHtml = $this->createPageHtml($this->buildUrl("student/lists.html", $this->req->get()), $re['count'], $page, $page_size);
		
		$this->display('project/lists.html', array(
				'title' => '项目列表',
				'pages' => $pageHtml,
				'lists' => $re['rows'],
				'user_type' => $this->getTypebyUid(),
				'menu' => 'student',
				'sub' => 'lists',
		));		
	}
	
	/**
	 * @desc 添加
	 * @author bxl@gmail.com
	 * @date 2018-06-23
	 */
	public function add() 
	{
		
	}
	

	/**
	 * @desc 删除
	 * @author bxl@gmail.com
	 * @date 2018-06-23
	 */
	public function del()
	{
		
	}
}
