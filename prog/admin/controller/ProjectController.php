<?php
/**
 * 项目配置
 * @author bxl@gmail.com
 * @date 2018-06-23
 *
 */
class ProjectController extends AuthController
{
	private $sourceConfig = array(
		'shixian', //实现网
		'codemart', //码市	
	);
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
				'menu' => 'project',
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
		$project_model = new ProjectModel();
			
		if ($this->req->method == 'POST') {
			$projectTitle = trim($this->req->post('project_title'));
			$projectFees = $this->req->post('project_fees');
			$projectTime = $this->req->post('project_time');
			$projectSummary = $this->req->post('project_summary');
			$projectSource = $this->req->post('project_source');
			$projectUrl = $this->req->post('project_url');
			
			$insertData = array(
				'project_title' => $projectTitle,
				'project_fees' => $projectFees,
				'project_time' => $projectTime,
				'project_summary' => $projectSummary,
				'project_source' => $projectSource,
				'project_url' => $projectUrl,
				'create_time' => time(),					
			);
			
			$project_model->insertOne($insertData);
			$this->success();
		}
		$this->display('project/add.html', array(
				'title' => '添加项目',
				'nickname' => $this->getUserName(),
				'menu' => 'project',
				'sub' => 'add',
				'type' => $this->getTypebyUid(),
				'source_config' => $this->sourceConfig,
		));
	}
	

	/**
	 * @desc 删除
	 * @author bxl@gmail.com
	 * @date 2018-06-23
	 */
	public function del()
	{
		$projectId = intval($this->req->post('project_id'));
		
		$project_model = new ProjectModel();
		
		$updateId = $project_model->updateOne(array('status' => -1,'update_time' => time()), array('project_id' => $projectId));
		
		if ($updateId) {
			$this->success();
		}
	}
}
