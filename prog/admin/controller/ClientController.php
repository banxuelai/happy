<?php
/**
 * 前端展示
 * @author bxl@gmail.com
 * @date 2018-06-23
 *
 */
class ClientController extends Controller
{
	/**
	 * @desc 前端展示项目列表
	 */
	public function lists()
	{
		# day条件
		$day = intval($this->req->get('day'));
		
		$project_model = new ProjectModel();
		
		$cond = array(
			'project_status' => 1,
			'create_day' => ! empty($day) ? $day : date('Ymd',time()),	
		);
		
		# 获取列表
		$projectList = $project_model->getList($cond,-1);
		
		$this->display('client/lists.html', array(
			'project_list' => $projectList['rows'],
		));
	}
}
