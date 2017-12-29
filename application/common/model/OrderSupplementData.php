<?php
namespace application\common\model;
use think\Db;
/**
 * 补缴信息操作
 */
class OrderSupplementData extends Base{
    /**
	 * @do 保存补缴信息
     * @params 接受参数
     * @rs_course  课程科目等信息，存json
	 */
    public function saveData($params=[],$rs_course=[],$type=1)
    {
        $type_id = $params['type_id'];
        $userId = $params['userId'];
        $agent_uid = $params['agent_uid'];
        $orderId = $params['orderId'];
        $supplementNum = model('common/orders')->getSupplementNum($orderId);
        //$orderNo = $params['orderNo'];
        //需要保存数据
        $data = [
            'type_id' => $type_id,
            'userId' => $userId,
            'agent_uid' => $agent_uid,
            'orderId' => $orderId,
            //'orderNo' => $orderNo,
            'extend_data' => json_encode($rs_course),
            'isUse' => 0, //是否有效：1=是  0=否，付款完需要更新为1
            'lastmodify' => time(),
            'supplementNum' => $supplementNum,
        ];
        $id = $this->where(['type_id'=>$type_id,'userId'=>$userId,'agent_uid'=>$agent_uid,'orderId'=>$orderId,'supplementNum'=>$supplementNum])->value('id');
        //新增时处理
        empty($id) && $data['createtime']=time();
        //END
        //更新时处理
        !empty($id) && $data['id']=$id;
        //END
        if(empty($id)) return $this->insert($data);
        if(!empty($id)) return $this->update($data,['id'=>$id]);
    }
    
}
