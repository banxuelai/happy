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
		$project_model = new ProjectModel();
		
		$cond = array(
			'project_status' => 1,
		);
		
		# 获取列表
		$projectList = $project_model->getList($cond,-1);
		
		if(! empty($projectList['rows']))
		{
			foreach ($projectList['rows'] as $key => $val)
			{
				$projectList['rows'][$key]['project_summary'] = str_replace("\n","<br>",$val['project_summary']);
			}
		}
		
		$this->display('client/lists.html', array(
			'project_list' => $projectList['rows'],
		));
	}
}
