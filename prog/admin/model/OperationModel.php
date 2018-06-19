<?php
/**
 * 配置信息
 * @author bxl@gmail.com
 * @date 2017-12-28
 *
 */
class OperationModel extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->db = Db::mysql(Config::mysql('default'));
        $this->table = 'operation';
    }
    
    //获取列表
    public function getList($cond, $offset = 0, $limit = 20)
    {
        $offset = intval($offset);
        $limit = intval($limit);
        $where_str = $this->getWhereStr($cond);
        $from_str = " FROM `{$this->table}` $where_str ";
        $count_sql = "SELECT count(*) $from_str ";
        $sql = " SELECT id,parent_id,title,fees  $from_str order by id asc  ";
        if ($offset >= 0 && $limit > 0) {
            $sql .= " LIMIT $offset, $limit ";
        }
        $re = array(
                'count' => intval($this->queryFirst($count_sql)),
                'rows' => array(),
        );
        if ($re['count']) {
            $re['rows'] = $this->queryRows($sql);
        }
        return $re;
    }
}
