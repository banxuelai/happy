<?php
/**
 * buaa
 * @author bxl@gmail.com
 * @date 2018-04-28
 *
 */
class TestController extends Controller
{
	public function syn(){
		$file = fopen("/buaa.txt", "r");
		$agent_model = new AgentModel();
		while(!feof($file)){
			$str = fgets($file);
			$arr = explode(' ',$str);
			$data = array(
					'student_ID' => isset($arr[0]) ? $arr[0] : '',
					'student_name' => isset($arr[1]) ? $arr[1] : '',
					'test_score' => isset($arr[2]) ? $arr[2] : '',
					'retest_score' => isset($arr[3]) ? $arr[3] : '',
					'total_score' => isset($arr[4]) ? $arr[4] : '',
					'faculty' => isset($arr[5]) ? $arr[5] : '',
					'profess' => isset($arr[6]) ? $arr[6] : '',
					'study_type' => isset($arr[7]) ? $arr[7] : '',
					'luqu' => isset($arr[8]) ? $arr[8] : '',
					'extra' => isset($arr[9]) ? $arr[9] : '',
			);
			$id = $agent_model->insertOne($data, 'buaa');
		}
		fclose($file);
	}
}
