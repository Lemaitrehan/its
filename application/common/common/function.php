<?php

use think\Db;
/**
 * 生成验证码
 */
function MBISVerify(){
	ob_clean();
	$Verify = new \verify\Verify();
    $Verify->length   = 4;
    $Verify->entry();
}
/**
 * 核对验证码
 */
function MBISVerifyCheck($code){
	$verify = new \verify\Verify();
	return $verify->check($code);
}
/**
 * 生成数据返回值
 */
function MBISReturn($msg,$status = -1,$data = []){
    if(empty($msg) && $status==1) $msg='success';
	$rs = ['status'=>$status,'msg'=>$msg];
	if(!empty($data))$rs['data'] = $data;
	return $rs;
}

/**
 * 接口返回数据处理
 * @example 
 {
   "status" : "1", 
   "data" : {
      "list":[
              ]
    }
   "msg":"success"
  }
*/
function MBISApiReturn($rs){
    
    if(isset($_POST['nojson']))
    {
        /*$nojson['status'] = $rs['status'];
        $nojson['data'] = $rs['data'];
        $nojson['msg'] = $rs['msg'];*/
        if(!isset($rs['data']))$rs['data'] = [];
        #echo '<pre>';var_export(json_decode(json_encode($rs),true));exit;
        //echo _format_json(json_encode($rs,1));
        echo '<pre>'; exit(jsonFormat($rs));
    }
    else
    {
        /*if(!empty($rs['data'])):
            foreach($rs['data'] as $k=>$v):
            dump( $v['data'] );
                $rs['data'][$k] = $v->data;
            endforeach;
        endif;*/
        //dump( $rs );
        header('content-type:application/json');
        if(!isset($rs['data']))$rs['data'] = [];
	    exit(json_encode($rs));
    }
}

/**
 * 检测字符串不否包含
 * @param $srcword 被检测的字符串
 * @param $filterWords 禁用使用的字符串列表
 * @return boolean true-检测到,false-未检测到
 */
function MBISCheckFilterWords($srcword,$filterWords){
	$flag = true;
	if($filterWords!=""){
		$filterWords = str_replace("，",",",$filterWords);
		$words = explode(",",$filterWords);
		for($i=0;$i<count($words);$i++){
			if(strpos($srcword,$words[$i]) !== false){
				$flag = false;
				break;
			}
		}
	}
	return $flag;
}

/**
 * 中国网建短信服务商
 * @param string $phoneNumer  手机号码
 * @param string $content     短信内容
 */
function MBISSendSMS($phoneNumer,$content){
	$url = 'http://utf8.sms.webchinese.cn/?Uid='.MBISConf("CONF.smsKey").'&Key='.MBISConf("CONF.smsPass").'&smsMob='.$phoneNumer.'&smsText='.$content;
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 ); //设置连接等待时间
	curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
	$data=curl_exec($ch);
	curl_close($ch);
	return $data;
}


/**
 * 获取指定的全局配置
 */
function MBISConf($key,$v = ''){
	if(is_null($v)){
		if(array_key_exists('MBISMARTCONF',$GLOBALS) && array_key_exists($key,$GLOBALS['MBISMARTCONF'])){
		    unset($GLOBALS['MBISMARTCONF'][$key]);
		}
	}else if($v === ''){
		if(array_key_exists('MBISMARTCONF',$GLOBALS)){
			$conf = $GLOBALS['MBISMARTCONF'];
			$ks = explode(".",$key);
			for($i=0,$k=count($ks);$i<$k;$i++){
				if(array_key_exists($ks[$i],$conf)){
					$conf = $conf[$ks[$i]];
				}else{
					return null;
				}
			}
			return $conf;
		}
	}else{
		return $GLOBALS['MBISMARTCONF'][$key] = $v;
	}
	return null;
}

//php获取中文字符拼音首字母
function MBISGetFirstCharter($str){
	if(empty($str)){
		return '';
	}
	$fchar=ord($str{0});
	if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
	$s1=iconv('UTF-8','gb2312',$str);
	$s2=iconv('gb2312','UTF-8',$s1);
	$s=$s2==$str?$s1:$str;
	if(empty($s{1})){
		return '';
	}
	$asc=ord($s{0})*256+ord($s{1})-65536;
	if($asc>=-20319 && $asc<=-20284) return 'A';
	if($asc>=-20283 && $asc<=-19776) return 'B';
	if($asc>=-19775 && $asc<=-19219) return 'C';
	if($asc>=-19218 && $asc<=-18711) return 'D';
	if($asc>=-18710 && $asc<=-18527) return 'E';
	if($asc>=-18526 && $asc<=-18240) return 'F';
	if($asc>=-18239 && $asc<=-17923) return 'G';
	if($asc>=-17922 && $asc<=-17418) return 'H';
	if($asc>=-17417 && $asc<=-16475) return 'J';
	if($asc>=-16474 && $asc<=-16213) return 'K';
	if($asc>=-16212 && $asc<=-15641) return 'L';
	if($asc>=-15640 && $asc<=-15166) return 'M';
	if($asc>=-15165 && $asc<=-14923) return 'N';
	if($asc>=-14922 && $asc<=-14915) return 'O';
	if($asc>=-14914 && $asc<=-14631) return 'P';
	if($asc>=-14630 && $asc<=-14150) return 'Q';
	if($asc>=-14149 && $asc<=-14091) return 'R';
	if($asc>=-14090 && $asc<=-13319) return 'S';
	if($asc>=-13318 && $asc<=-12839) return 'T';
	if($asc>=-12838 && $asc<=-12557) return 'W';
	if($asc>=-12556 && $asc<=-11848) return 'X';
	if($asc>=-11847 && $asc<=-11056) return 'Y';
	if($asc>=-11055 && $asc<=-10247) return 'Z';
	return null;
}

/**
 * 设置当前页面对象
 * @param int 0-用户  1-商家
 */
function MBISLoginTarget($target = 0){
	$MBIS_USER = session('MBIS_USER');
	$MBIS_USER['loginTarget'] = $target;
	session('MBIS_USER',$MBIS_USER);
}
/**
 * 邮件发送函数
 * @param string to      要发送的邮箱地址
 * @param string subject 邮件标题
 * @param string content 邮件内容
 * @return array
 */
function MBISSendMail($to, $subject, $content) {
    
	$mail = new \phpmailer\phpmailer();
    // 装配邮件服务器
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host      = 'smtp.163.com';#MBISConf("CONF.mailSmtp");
    $mail->SMTPAuth  = 'lijianhuaxx1234';#MBISConf("CONF.mailAuth");
    $mail->Username  = 'lijianhua_nihao@qq.com';#MBISConf("CONF.mailUserName");
    $mail->Password  = 'lijianhua';#MBISConf("CONF.mailPassword");
    $mail->CharSet   = 'utf-8';
    // 装配邮件头信息
    $mail->From      = '123123';#MBISConf("CONF.mailAddress");
    $mail->AddAddress($to);
    $mail->FromName  = 'xxxxxxxxxxxx';#MBISConf("CONF.mailSendTitle");
    $mail->IsHTML(true);
    // 装配邮件正文信息
    $mail->Subject = $subject;
    $mail->Body = $content;
    // 发送邮件
    $rs =array();
    if (!$mail->Send()) {
    	$rs['status'] = 0;
    	$rs['msg'] = $mail->ErrorInfo;
        return $rs;
    } else {
    	$rs['status'] = 1;
        return $rs;
    }
}

/**
 * 获取系统配置数据
 */
function MBISConfig(){
	$rs = cache('MBIS_CONF');
	if(!$rs){
		$rv = Db::name('sys_configs')->field('fieldCode,fieldValue')->select();
		$rs = [];
		foreach ($rv as $v){
			$rs[$v['fieldCode']] = $v['fieldValue'];
		}
		//获取风格
        $styles = Db::name('styles')->where(['styleSys'=>0,'isUse'=>1])->field('stylePath,id')->find();
        if(!empty($styles)){
            $rs['wstPcStyle'] = $styles['stylePath'];
            $rs['wstPcStyleId'] = $styles['id'];
        }else{
        	$rs['wstPcStyle'] = 'default';
            $rs['wstPcStyleId'] = 0;
        }
		//获取上传文件目录配置
		$data = Db::name('datas')->where('catId',3)->column('dataVal');
		foreach ($data as $key => $v){
			$data[$key] = str_replace('_','',$v);
		}
		$rs['wstUploads'] = $data;
		if(MBISConf('CONF.mallLicense')=='')$rs['mallSlogan'] = $rs['mallSlogan']."  ".base64_decode('UG93ZXJlZCBCeSBXU1RNYXJ0');
		cache('MBIS_CONF',$rs,31536000);
	}
	return $rs;
} 

/**
 * 判断手机号格式是否正确
 */
function MBISIsPhone($phoneNo){
	$reg = "/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/";
	$rs = \think\Validate::regex($phoneNo,$reg);
	return $rs;
}

/**
 * 检测登录账号是否可用
 * @param $key 要检测的内容
 */
function MBISCheckLoginKey($val,$userId = 0){
    if($val=='')return MBISReturn("登录账号不能为空");
    if(!MBISCheckFilterWords($val,MBISConf("CONF.registerLimitWords"))){
    	return MBISReturn("登录账号包含非法字符");
    }
    $dbo = Db::name('users')->where(["loginName|userEmail|userPhone"=>['=',$val],'dataFlag'=>1]);
    if($userId>0){
    	$dbo->where("userId", "<>", $userId);
    }
    $rs = $dbo->count();
    if($rs==0){
    	return MBISReturn("该登录账号可用",1);
    }
    return MBISReturn("对不起，登录账号已存在");
}

/**
 * 生成随机数账号
 */
function MBISRandomLoginName($loginName){
	$chars = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
    //简单的派字母
    foreach ($chars as $key =>$c){
    	$crs = MBISCheckLoginKey($loginName."_".$c);
    	if($crs['status']==1)return $loginName."_".$c;
    }
    //随机派三位数值
    for($i=0;$i<1000;$i++){
    	$crs = $this->MBISCheckLoginKey($loginName."_".$i);
    	if($crs['status']==1)return $loginName."_".$i;
    }
    return '';
}

/**
 * 删除一维数组里的多个key
 */
function MBISUnset(&$data,$keys){
    if($keys!='' && is_array($data)){
        $key = explode(',',$keys);
        foreach ($key as $v)unset($data[$v]);
    }
}
/**
 * 只允许一维数组里的某些key通过
 */
function MBISAllow(&$data,$keys){
    if($keys!='' && is_array($data)){
        $key = explode(',',$keys);
        foreach ($data as $vkeys =>$v)if(!in_array($vkeys,$key))unset($data[$vkeys]);
    }
}

/**
 * 字符串替换
 * @param string $str     要替换的字符串
 * @param string $repStr  即将被替换的字符串
 * @param int $start      要替换的起始位置,从0开始
 * @param string $splilt  遇到这个指定的字符串就停止替换
 */
function MBISStrReplace($str,$repStr,$start,$splilt = ''){
	$newStr = substr($str,0,$start);
	$breakNum = -1;
	for ($i=$start;$i<strlen($str);$i++){
		$char = substr($str,$i,1);
		if($char==$splilt){
			$breakNum = $i;
			break;
		}
		$newStr.=$repStr;
	}
	if($splilt!='' && $breakNum>-1){
		for ($i=$breakNum;$i<strlen($str);$i++){
			$char = substr($str,$i,1);
			$newStr.=$char;
		}
	}
	return $newStr;
}

/**
 * 获取指定商品分类的子分类列表
 */
function MBISGoodsCats($parentId = 0,$isFloor = -1){
	$dbo = Db::name('goods_cats')->where(['dataFlag'=>1, 'isShow' => 1,'parentId'=>$parentId]);
	if($isFloor!=-1)$db0>where('isFloor',$isFloor);
	return $dbo->field("catName,catId")->order('catSort asc')->select();
}


/**
 * 上传图片
 * 需要生成缩略图： isThumb=1
 * 需要加水印：isWatermark=1
 * pc版缩略图： width height
 * 手机版原图：mWidth mHeight
 * 缩略图：mTWidth mTHeight
 * 判断图片来源：fromType 0：商家/用户   1：平台管理员
 */
function MBISUploadPic($fromType=0){
	$fileKey = key($_FILES);
	$dir = Input('post.dir');
	if($dir=='')return json_encode(['msg'=>'没有指定文件目录！','status'=>-1]);
	$dirs = MBISConf("CONF.wstUploads");
    $dirs[] = 'coverimg';
    $dirs[] = 'userworks';
   	if(!in_array($dir, $dirs)){
   		return json_encode(['msg'=>'非法文件目录！','status'=>-1]);
   	}
   	// 上传文件
    $file = request()->file($fileKey);
    if($file===null){
    	return json_encode(['msg'=>'上传文件不存在或超过服务器限制','status'=>-1]);
    }
    $validate = new \think\Validate([
	    ['fileMime','fileMime:image/png,image/gif,image/jpeg,image/x-ms-bmp','只允许上传jpg,gif,png,bmp类型的文件'],
	    ['fileExt','fileExt:jpg,jpeg,gif,png,bmp','只允许上传后缀为jpg,gif,png,bmp的文件'],
	    ['fileSize','fileSize:2097152','文件大小超出限制'],//最大2M
	]);
	$data = ['fileMime'  => $file,
	    	 'fileSize' => $file,
	         'fileExt'=> $file
	        ];
	if (!$validate->check($data)) {
	    return json_encode(['msg'=>$validate->getError(),'status'=>-1]);
	}
    $info = $file->rule('uniqid')->move(ROOT_PATH.ENTRY_PATH.'/upload/'.$dir."/".date('Y-m'));
    if($info){
    	$filePath = $info->getPathname();
    	$filePath = str_replace(ROOT_PATH.ENTRY_PATH,'',$filePath);
    	$filePath = str_replace('\\','/',$filePath);
    	$name = $info->getFilename();
    	$filePath = str_replace($name,'',$filePath);
    	//原图路径
    	$imageSrc = trim($filePath.$name,'/');
    	//图片记录
    	MBISRecordImages($imageSrc, (int)$fromType);
    	//打开原图
    	$image = \image\Image::open($imageSrc);
    	//缩略图路径 手机版原图路径 手机版缩略图路径
    	$thumbSrc = $mSrc = $mThumb = null;
    	//手机版原图宽高
    	$mWidth = min($image->width(),(int)input('mWidth',700));
		$mHeight = min($image->height(),(int)input('mHeight',700));
		//手机版缩略图宽高
		$mTWidth = min($image->width(),(int)input('mTWidth',250));
		$mTHeight = min($image->height(),(int)input('mTHeight',250));

    	/****************************** 生成缩略图 *********************************/
    	$isThumb = (int)input('isThumb');
    	if($isThumb==1){
    		//缩略图路径
    		$thumbSrc = str_replace('.', '_thumb.', $imageSrc);
    		$image->thumb((int)input('width',min(300,$image->width())), (int)input('height',min(300,$image->height())),2)->save($thumbSrc,$image->type(),90);
    		//是否需要生成移动版的缩略图
    		$suffix = MBISConf("CONF.wstMobileImgSuffix");
    		if(!empty($suffix)){
    			$image = \image\Image::open($imageSrc);
    			$mSrc = str_replace('.',"$suffix.",$imageSrc);
    			$mThumb = str_replace('.', '_thumb.',$mSrc);
    			$image->thumb($mWidth, $mHeight)->save($mSrc,$image->type(),90);
    			$image->thumb($mTWidth, $mTHeight, 2)->save($mThumb,$image->type(),90);
    		}


    	}
    	/***************************** 添加水印 ***********************************/
    	$isWatermark=(int)input('isWatermark');
    	if($isWatermark==1 && (int)MBISConf('CONF.watermarkPosition')!==0){
	    	//取出水印配置
	    	$wmWord = MBISConf('CONF.watermarkWord');//文字
	    	$wmFile = trim(MBISConf('CONF.watermarkFile'),'/');//水印文件
	    	$wmPosition = (int)MBISConf('CONF.watermarkPosition');//水印位置
	    	$wmSize = ((int)MBISConf('CONF.watermarkSize')!=0)?MBISConf('CONF.watermarkSize'):'20';//大小
	    	$wmColor = (MBISConf('CONF.watermarkColor')!='')?MBISConf('CONF.watermarkColor'):'#000000';//颜色必须是16进制的
	    	$wmOpacity = ((int)MBISConf('CONF.watermarkOpacity')!=0)?MBISConf('CONF.watermarkOpacity'):'100';//水印透明度
	    	//是否有自定义字体文件
	    	$customTtf = $_SERVER['DOCUMENT_ROOT'].MBISConf('CONF.watermarkTtf');
	    	$ttf = is_file($customTtf)?$customTtf:EXTEND_PATH.'/verify/verify/ttfs/3.ttf';
	        $image = \image\Image::open($imageSrc);
	    	if(!empty($wmWord)){//当设置了文字水印 就一定会执行文字水印,不管是否设置了文件水印
		    	
	    		//执行文字水印
	    		$image->text($wmWord, $ttf, $wmSize, $wmColor, $wmPosition)->save($imageSrc);
	    		if($thumbSrc!==null){
	    			$image->thumb((int)input('width',min(300,$image->width())), (int)input('height',min(300,$image->height())),2)->save($thumbSrc,$image->type(),90);
	    		}
	    		//如果有生成手机版原图
	    		if(!empty($mSrc)){
	    			$image = \image\Image::open($imageSrc);
	    			$image->thumb($mWidth, $mHeight)->save($mSrc,$image->type(),90);
	    			$image->thumb($mTWidth, $mTHeight, 2)->save($mThumb,$image->type(),90);
	    		}
	    	}elseif(!empty($wmFile)){//设置了文件水印,并且没有设置文字水印
	    		//执行图片水印
	    		$image->water($wmFile, $wmPosition, $wmOpacity)->save($imageSrc);
	    		if($thumbSrc!==null){
	    			$image->thumb((int)input('width',min(300,$image->width())), (int)input('height',min(300,$image->height())),2)->save($thumbSrc,$image->type(),90);
	    		}
	    		//如果有生成手机版原图
	    		if($mSrc!==null){
	    			$image = \image\Image::open($imageSrc);
	    			$image->thumb($mWidth, $mHeight)->save($mSrc,$image->type(),90);
	    			$image->thumb($mTWidth, $mTHeight,2)->save($mThumb,$image->type(),90);
	    		}
	    	}
    	}
    	//判断是否有生成缩略图
    	$thumbSrc = ($thumbSrc==null)?$info->getFilename():str_replace('.','_thumb.', $info->getFilename());
		$filePath = ltrim($filePath,'/');
		// 用户头像上传宽高限制
		$isCut = (int)input('isCut');
		if($isCut){
			$imgSrc = $filePath.$info->getFilename();
			$image = \image\Image::open($imgSrc);
			$size = $image->size();//原图宽高
			$w = $size[0];
			$h = $size[1];
			$rate = $w/$h;
			if($w>$h && $w>500){
				$newH = 500/$rate;
				$image->thumb(500, $newH)->save($imgSrc,$image->type(),90);
			}elseif($h>$w && $h>500){
				$newW = 500*$rate;
				$image->thumb($newW, 500)->save($imgSrc,$image->type(),90);
			}
		}
        return json_encode(['status'=>1,'savePath'=>$filePath,'name'=>$info->getFilename(),'thumb'=>$thumbSrc]);
    }else{
        //上传失败获取错误信息
        return $file->getError();
    }    
}
/**
 * 上传文件
 */
function MBISUploadFile(){
	$fileKey = key($_FILES);
	$dir = Input('post.dir');
	if($dir=='')return json_encode(['msg'=>'没有指定文件目录！','status'=>-1]);
	$dirs = MBISConf("CONF.wstUploads");
   	if(!in_array($dir, $dirs)){
   		return json_encode(['msg'=>'非法文件目录！','status'=>-1]);
   	}
   	//上传文件
    $file = request()->file($fileKey);
    if($file===null){
    	return json_encode(['msg'=>'上传文件不存在或超过服务器限制','status'=>-1]);
    }
    $validate = new \think\Validate([
	    ['fileExt','fileExt:xls,xlsx,xlsm','只允许上传后缀为xls,xlsx,xlsm的文件']
	]);
	$data = ['fileExt'=> $file];
	if (!$validate->check($data)) {
	    return json_encode(['msg'=>$validate->getError(),'status'=>-1]);
	}
    $info = $file->rule('uniqid')->move(ROOT_PATH.ENTRY_PATH.'/upload/'.$dir."/".date('Y-m'));
    //保存路径
    $filePath = $info->getPathname();
	$filePath = str_replace(ROOT_PATH.ENTRY_PATH,'',$filePath);
	$filePath = str_replace('\\','/',$filePath);
	$name = $info->getFilename();
	$filePath = str_replace($name,'',$filePath);
	if($info){
		return json_encode(['status'=>1,'name'=>$info->getFilename(),'route'=>$filePath]);
	}else{
		//上传失败获取错误信息
		return $file->getError();
	}
}
/**
 * 生成默认商品编号/货号
 */
function MBISGoodsNo($pref = ''){
	return $pref.(round(microtime(true),4)*10000).mt_rand(0,9);
}
/**
 * 获取订单统一流水号
 */
function MBISOrderQnique(){
	return (round(microtime(true),4)*10000).mt_rand(1000,9999);
}


/**
* 图片管理
* @param $imgPath    图片路径
* @param $fromType   0：用户/商家 1：平台管理员
* 
*/
function MBISRecordImages($imgPath, $fromType){
	$data = [];
	$data['imgPath'] = $imgPath;
	if(file_exists($imgPath)){
		$data['imgSize'] = filesize($imgPath); //返回字节数 imgsize/1024 k  	imgsize/1024/1024 m
	}
	//获取表名
	$table = explode('/',$imgPath);
	$data['fromTable'] = $table[1];
	$data['fromType'] = (int)$fromType; 
	//根据类型判断所有者
	$data['ownId'] = ((int)$fromType==0)?(int)session('MBIS_USER.userId'):(int)session('MBIS_STAFF.staffId');
	$data['isUse'] = 0; //默认不使用
	$data['createTime'] = date('Y-m-d H:i:s');

	//保存记录
	Db::name('images')->insert($data);

}
/**
* 启用图片
* @param $fromType 0：  用户/商家 1：平台管理员
* @param $dataId        来源记录id
* @param $imgPath       图片路径,要处理多张图片时请传入一位数组,或用","连接图片路径
* @param $fromTable     该记录来自哪张表
* @param $imgFieldName  表中的图片字段名称
*/
function MBISUseImages($fromType, $dataId, $imgPath, $fromTable='', $imgFieldName=''){
	if(empty($imgPath))return;

	$image['fromType'] = (int)$fromType;
	//根据类型判断所有者
	$image['ownId'] = ((int)$fromType==0)?(int)session('MBIS_USER.userId'):(int)session('MBIS_STAFF.staffId');
	$image['dataId'] = (int)$dataId;

	$image['isUse'] = 1;//标记为启用
	if($fromTable!=''){
		$tmp = ['',''];
		if(strpos($fromTable,'-')!==false){
			$tmp = explode('-',$fromTable);
			$fromTable = str_replace('-'.$tmp[1],'',$fromTable);
		}
		$image['fromTable'] = str_replace('_','',$fromTable.$tmp[1]);
	}

	$imgPath = is_array($imgPath)?$imgPath:explode(',',$imgPath);//转数组


	//用于与旧图比较
	$newImage = $imgPath;

	// 不为空说明执行修改
	if($imgFieldName!=''){
		//要操作的表名  $fromTable;
		// 获取`$fromTable`表的主键
		$prefix = config('database.prefix');
		$tableName = $prefix.$fromTable;
		$pk = Db::getTableInfo("$tableName", 'pk');
		// 取出旧图
		$oldImgPath = model("$fromTable")->where("$pk",$dataId)->value("$imgFieldName"); 
		// 转数组
		$oldImgPath = explode(',', $oldImgPath);

		// 1.要设置为启用的文件
		$newImage = array_diff($imgPath, $oldImgPath);
		// 2.要标记为删除的文件
		$oldImgPath = array_diff($oldImgPath, $imgPath);
		//旧图数组跟新图数组相同则不需要继续执行
		if($newImage!=$oldImgPath)MBISUnuseImage($oldImgPath);
	}
	if(!empty($newImage)){
		Db::name('images')->where(['imgPath'=>['in',$newImage]])->update($image);
	}
}

/**
* 编辑器图片记录
* @param $fromType 0：  用户/商家 1：平台管理员
* @param $dataId        来源记录id
* @param $oldDesc       旧商品描述
* @param $newDesc       新商品描述
* @param $fromTable     该记录来自哪张表
*/
function MBISEditorImageRocord($fromTable, $dataId, $oldDesc, $newDesc){
		//编辑器里的图片
		$rule = '/src="\/(upload.*?)"/';
	    // 获取旧的src数组
	    preg_match_all($rule,$oldDesc,$images);
	    $oldImgPath = $images[1];

	    preg_match_all($rule,$newDesc,$images);  
	    // 获取新的src数组
	    $imgPath = $images[1];
		// 1.要设置为启用的文件
		$newImage = array_diff($imgPath, $oldImgPath);
		// 2.要标记为删除的文件
		$oldImgPath = array_diff($oldImgPath, $imgPath);
		//旧图数组跟新图数组相同则不需要继续执行
		if($newImage!=$oldImgPath){
			//标记新图启用
			MBISUseImages($fromTable, $dataId, $newImage);
			//标记旧图删除
			MBISUnuseImage($oldImgPath);
		}
}

/**
* 标记删除图片
*/
function MBISUnuseImage($fromTable, $field = '' , $dataId = 0){
	if($fromTable=='')return;
	$imgPath = $fromTable;
	if($field!=''){
		$prefix = config('database.prefix');
		$tableName = $prefix.$fromTable;
		$pk = Db::getTableInfo("$tableName", 'pk');
		// 取出旧图
		$imgPath = model("$fromTable")->where("$pk",$dataId)->value("$field");
	}
	if(!empty($imgPath)){
		$imgPath = is_array($imgPath)?$imgPath:explode(',',$imgPath);//转数组
		Db::name('images')->where(['imgPath'=>['in',$imgPath]])->setField('isUse',0);
	}
}
/**
 * 获取系统根目录
 */
function MBISRootPath(){
	return dirname(dirname(dirname(dirname(__File__))));
}
/**
 * 切换图片
 * @param $imgurl 图片路径
 * @param $imgType 图片类型    0:PC版大图   1:PC版缩略图       2:移动版大图    3:移动版缩略图
 * 图片规则  
 * PC版版大图 :201635459344.jpg
 * PC版版缩略图 :201635459344_thumb.jpg
 * 移动版大图 :201635459344_m.jpg
 * 移动版缩略图 :201635459344_m_thumb.jpg
 */
function MBISImg($imgurl,$imgType = 1){
	$m = MBISConf('CONF.wstMobileImgSuffix');
	$imgurl = str_replace($m.'.','.',$imgurl);
	$imgurl = str_replace($m.'_thumb.','.',$imgurl);
	$imgurl = str_replace('_thumb.','.',$imgurl);
	$img = '';
	switch ($imgType){
		case 0:$img =  $imgurl;break;
		case 1:$img =  str_replace('.','_thumb.',$imgurl);break;
		case 2:$img =  str_replace('.',$m.'.',$imgurl);break;
		case 3:$img =  str_replace('.',$m.'_thumb.',$imgurl);break;
	}
	return ((file_exists(MBISRootPath()."/".$img))?$img:$imgurl);
}

/**
 * 根据送货城市获取运费
 * @param $cityId 送货城市Id
 * @param @shopIds 店铺ID
 */
function MBISOrderFreight($shopId,$cityId){
	$goodsFreight = ['total'=>0,'shops'=>[]];
	$rs = Db::name('shops')->alias('s')->join('__SHOP_FREIGHTS__ sf','s.shopId=sf.shopId','left')
	     ->where('s.shopId',$shopId)->field('s.freight,sf.freightId,sf.freight freight2')->find();
    return ((int)$rs['freightId']>0)?$rs['freight2']:$rs['freight'];
}
/**
 * 生成订单号
 */
function MBISOrderNo(){
    $orderId = Db::name('orderids')->insertGetId(['rnd'=>time()]);
	return $orderId.(fmod($orderId,7));
}
/**
 * 高精度数字相加
 * @param $num
 * @param number $i 保留小数位
 */
function MBISBCMoney($num1,$num2,$i=2){
	$num = bcadd($num1, $num2, $i);
	return (float)$num;
}
/**
 * 获取支付方式
 */
function MBISLangPayType($v){
	return ($v==1)?"线上支付":"线下支付";
}
/**
 * 购买方式
 */
function MBISLangDeliverType($v){
	return ($v==1)?"代购":"直销";
}
/**
 * 订单状态
 */
function MBISLangOrderStatus($v){
	switch($v){
		case 0:return '待付款';
		case 1:return '已付款';
		case 2:return '已完成';
	}
}
/**
 * 积分来源
 */
function MBISLangScore($v){
    switch($v){
		case 1:return '商品订单';
		case 2:return '评价订单';
	}
}
/**
 * 资金来源
 */
function MBISLangMoneySrc($v){
    switch($v){
		case 1:return '商品订单';
		case 2:return '订单结算';
		case 3:return '提现申请';
	}
}
/**
 * 积分来源
 */
function MBISLangComplainStatus($v){
    switch($v){
		case 0:return '等待处理';
		case 1:return '等待应诉人应诉';
		case 2:return '应诉人已应诉';
		case 3:return '等待仲裁';
		case 4:return '已仲裁';
	}
}
/**
 * 支付来源
 */
function MBISLangPayFrom($v){
    switch($v){
		case 1:return '支付宝';
		case 2:return '微信';
	}
}
/**
 * 获取业务数据内容
 */
function MBISDatas($catId,$id = 0){
	$rs = Db::name('datas')->order('catId asc,dataSort asc,id asc')->cache(31536000)->select();
	$data = [];
	foreach ($rs as $key =>$v){
		$data[$v['catId']][$v['dataVal']] = $v;
	}
	if(isset($data[$catId])){
		if($id==0)return $data[$catId];
		return isset($data[$catId][$id])?$data[$catId][$id]:'';
	}
	return [];
}
/**
 * 截取字符串
 */
function MBISMSubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false){
	$newStr = '';
	if (function_exists ( "mb_substr" )) {
		if ($suffix){
			$newStr = mb_substr ( $str, $start, $length, $charset )."...";
		}else{
			$newStr = mb_substr ( $str, $start, $length, $charset );
		}
	} elseif (function_exists ( 'iconv_substr' )) {
		if ($suffix){
			$newStr = iconv_substr ( $str, $start, $length, $charset )."...";
		}else{
			$newStr = iconv_substr ( $str, $start, $length, $charset );
		}
	}
	if($newStr==''){
	$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all ( $re [$charset], $str, $match );
	$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	if ($suffix)
		$newStr = $slice;
	}
	return $newStr;
}
function MBISScore($score,$users,$type = 5,$len = 0,$total = 1){
	if((int)$score==0)return $type;
	switch($type){
		case 5:return round($score/$total/$users,0);
		case 10:return round($score/$total*2/$users,$len);
		case 100:return round($score/$total*2/$users,$len);
	}
}
function MBISShopEncrypt($shopId){
	return md5(base64_encode("application".date("Y-m-d").$shopId));
}
/**
 * 根据子分类循环获取其父级分类
 */
function MBISGoodsCatPath($catId, $data = []){
    if($catId==0)return $data;
    $data[] = $catId;
	$parentId = Db::name('goods_cats')->where('catId',$catId)->value('parentId');
	if($parentId==0){
		krsort($data);
		return $data;
	}else{
		return MBISGoodsCatPath($parentId, $data);
	}
}
/**
 * 提供原生分页处理
 */
function MBISPager($total,$rs,$page,$size = 0){
	$pageSize = ($size>0)?$size:config('paginate.list_rows');
	$totalPage = ($total%$pageSize==0)?($total/$pageSize):(intval($total/$pageSize)+1);
	return ['Total'=>$total,'PerPage'=>$pageSize,'CurrentPage'=>$page,'TotalPage'=>$totalPage,'Rows'=>$rs];
}


/**
* 编辑器上传图片
*/
function MBISEditUpload($fromType){
    //PHP上传失败
    if (!empty($_FILES['imgFile']['error'])) {
        switch($_FILES['imgFile']['error']){
            case '1':
                $error = '超过php.ini允许的大小。';
                break;
            case '2':
                $error = '超过表单允许的大小。';
                break;
            case '3':
                $error = '图片只有部分被上传。';
                break;
            case '4':
                $error = '请选择图片。';
                break;
            case '6':
                $error = '找不到临时目录。';
                break;
            case '7':
                $error = '写文件到硬盘出错。';
                break;
            case '8':
                $error = 'File upload stopped by extension。';
                break;
            case '999':
            default:
                $error = '未知错误。';
        }
        return MBISReturn(1,$error);
    }

    $fileKey = key($_FILES);
	$dir = 'image'; // 编辑器上传图片目录
	$dirs = MBISConf("CONF.wstUploads");
   	if(!in_array($dir, $dirs)){
   		return json_encode(['error'=>1,'message'=>'非法文件目录！']);
   	}
   	// 上传文件
    $file = request()->file($fileKey);
    if($file===null){
    	return json_encode(["error"=>1,"message"=>'上传文件不存在或超过服务器限制']);
    }
    $validate = new \think\Validate([
	    ['fileMime','fileMime:image/png,image/gif,image/jpeg,image/x-ms-bmp','只允许上传jpg,gif,png,bmp类型的文件'],
	    ['fileExt','fileExt:jpg,jpeg,gif,png,bmp','只允许上传后缀为jpg,gif,png,bmp的文件'],
	    ['fileSize','fileSize:2097152','文件大小超出限制'],//最大2M
	]);
	$data = ['fileMime'  => $file,
	    	 'fileSize' => $file,
	         'fileExt'=> $file
	        ];
	if (!$validate->check($data)) {
	    return json_encode(['message'=>$validate->getError(),'error'=>1]);
	}
    $info = $file->rule('uniqid')->move(ROOT_PATH.'/upload/'.$dir."/".date('Y-m'));
    if($info){
    	$filePath = $info->getPathname();
    	$filePath = str_replace(ROOT_PATH,'',$filePath);
    	$filePath = str_replace('\\','/',$filePath);
    	$name = $info->getFilename();
    	$imageSrc = trim($filePath,'/');
    	//图片记录
    	MBISRecordImages($imageSrc, (int)$fromType);
    	return json_encode(array('error' => 0, 'url' => request()->domain().$filePath));
	}
}
/**
 * 转义单引号
 */
function MBISHtmlspecialchars($v){
	return htmlspecialchars($v,ENT_QUOTES);
}

/**
* 发送商城消息
* @param int 	$to 接受者d
* @param string $content 内容
* @param array  $msgJson 存放json数据
*/
function MBISSendMsg($to,$content,$msgJson=[],$msgType = 1){
	$message = [];
	$message['msgType'] = $msgType;
	$message['sendUserId'] = 1;
	$message['createTime'] = date('Y-m-d H:i:s');
	$message['msgStatus'] = 0;
	$message['dataFlag'] = 1;

	$message['receiveUserId'] = $to;
	$message['msgContent'] = $content;
	$message['msgJson'] = json_encode($msgJson);
	model('admin/Messages')->save($message);

}

/**
 * 获取分类的佣金
 */
function MBISGoodsCommissionRate($goodsCatId){
	$cats = Db::name('goods_cats')->where('catId',$goodsCatId)->field('parentId,commissionRate')->find();
	if(empty($cats)){
		return 0;
	}else{
		if((float)$cats['commissionRate']>=0)return (float)$cats['commissionRate'];
		return MBISGoodsCommissionRate($cats['parentId']);
	}
}

function _format_json($json, $html = false) {
    $tabcount = 0;
    $result = '';
    $inquote = false;
    $ignorenext = false;
    if ($html) {
      $tab = "   ";
      $newline = "<br/>";
    } else {
      $tab = "\t";
      $newline = "\n";
    }
    for($i = 0; $i < strlen($json); $i++) {
      $char = $json[$i];
      if ($ignorenext) {
        $result .= $char;
        $ignorenext = false;
      } else {
        switch($char) {
          case '{':
            $tabcount++;
            $result .= $char . $newline . str_repeat($tab, $tabcount);
            break;
          case '}':
            $tabcount--;
            $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
            break;
          case ',':
            $result .= $char . $newline . str_repeat($tab, $tabcount);
            break;
          case '"':
            $inquote = !$inquote;
            $result .= $char;
            break;
          case '\\':
            if ($inquote) $ignorenext = true;
            $result .= $char;
            break;
          default:
            $result .= $char;
        }
      }
    }
    return $result;
  }
  
  /** Json数据格式化 
* @param  Mixed  $data   数据 
* @param  String $indent 缩进字符，默认4个空格 
* @return JSON 
*/  
function jsonFormat($data, $indent=null){  
  
    // 对数组中每个元素递归进行urlencode操作，保护中文字符  
    array_walk_recursive($data, 'jsonFormatProtect');  
  
    // json encode  
    $data = json_encode($data);  
  
    // 将urlencode的内容进行urldecode  
    $data = urldecode($data);  
  
    // 缩进处理  
    $ret = '';  
    $pos = 0;  
    $length = strlen($data);  
    $indent = isset($indent)? $indent : '    ';  
    $newline = "\n";  
    $prevchar = '';  
    $outofquotes = true;  
  
    for($i=0; $i<=$length; $i++){  
  
        $char = substr($data, $i, 1);  
  
        if($char=='"' && $prevchar!='\\'){  
            $outofquotes = !$outofquotes;  
        }elseif(($char=='}' || $char==']') && $outofquotes){  
            $ret .= $newline;  
            $pos --;  
            for($j=0; $j<$pos; $j++){  
                $ret .= $indent;  
            }  
        }  
  
        $ret .= $char;  
          
        //if(($char==',' || $char=='{' || $char=='[') && $outofquotes){  
        if(($char==',' || $char=='{' || $char=='[') && $outofquotes){  
            $ret .= $newline;  
            if($char=='{' || $char=='['){  
                $pos ++;  
            }  
  
            for($j=0; $j<$pos; $j++){  
                $ret .= $indent;  
            }  
        }  
  
        $prevchar = $char;  
    }  
  
    return $ret;  
}  
  
/** 将数组元素进行urlencode 
* @param String $val 
*/  
function jsonFormatProtect(&$val){  
    if($val!==true && $val!==false && $val!==null){  
        $val = urlencode($val);  
    }  
}  

/**
 * @do 获取的完整图片地址
 */
 function ITSPicUrl($picUrl='',$size='',$domain='')
 {
    $domainUrl = request()->domain();
    if($domain) $domainUrl = $domain;
    $retPicUrl = $domainUrl.'/'.$picUrl;
    if($picUrl=='') $retPicUrl = request()->domain().'/static/images/default'.$size.'.jpg';
    return $retPicUrl; 
 }
 /**
 * 专业下拉数据
 */
function ITSMajorSelData(){
    $sel_data = array(
        //层次：0为未知、1为高升本、2为高升专、3为专升本、4为专本套读
        array(
            'key' => 'level_type',
            'name' => '层次：',
            'lists' => array(
                //array('id'=>0,'name'=>'未知'),
                #array('id'=>1,'name'=>'专科(高升本)'),
                array('id'=>2,'name'=>'专科(高升专)'), #网教 专本套读 '' 套读  高升本
                array('id'=>3,'name'=>'本科(专升本)'),#自考 专本套读   本升本
                #array('id'=>4,'name'=>'专本套读'),
            )
        ),
        //考试类型：0为未知、1为自考、2为成考、3为网教
        array(
            'key' => 'exam_type',
            'name' => '考试类型：',
            'lists' => array(
                //array('id'=>0,'name'=>'未知'),
                array('id'=>1,'name'=>'自考'), #自考全日制  深大
                array('id'=>2,'name'=>'成考'), #
                array('id'=>3,'name'=>'网教'), #
                //array('id'=>4,'name'=>'自考（深大全日制学员）'), #
            )
        ),
        //学习时间：0为未知、1为2年、2为2.5年、3为3年、4为5年
        array(
            'key' => 'graduate_type',
            'name' => '学习时间：',
            'lists' => array(
                //array('id'=>0,'name'=>'未知'),
                array('id'=>1,'name'=>'2年'),
                array('id'=>2,'name'=>'2.5年'),
                array('id'=>3,'name'=>'3年'),
                array('id'=>4,'name'=>'5年'),
            )
        ),
        //学历类型：1=专科、2=本科
        array(
            'key' => 'edu_type',
            'name' => '学历类型：',
            'lists' => array(
                //array('id'=>0,'name'=>'未知'),
                array('id'=>1,'name'=>'专科'),
                array('id'=>2,'name'=>'本科'),
            )
        ),
    );
    return $sel_data;
}
 /**
 * 课程下拉数据
 */
function ITSCourseSelData(){
    $sel_data = array(
        array(
            'key' => 'is_shelves',
            'name' => '是否上架',
            'lists' => array(
                array('id'=>2,'name'=>'待上架'),
                array('id'=>1,'name'=>'已上架'),
                array('id'=>0,'name'=>'已下架'),
            )
        ),
        array(
            'key' => 'teaching_type',
            'name' => '上课方式',
            'lists' => array(
                array('id'=>1,'name'=>'线下面授'),
                array('id'=>2,'name'=>'线上直学'),
                array('id'=>3,'name'=>'混合【线下+线上】'),
            )
        ),
    );
    return $sel_data;
}
/**
 * 科目下拉数据
 */
function ITSSubjectSelData(){
    $sel_data = array(
        array(
            'key' => 'is_shelves',
            'name' => '是否上架',
            'lists' => array(
                array('id'=>2,'name'=>'待上架'),
                array('id'=>1,'name'=>'已上架'),
                array('id'=>0,'name'=>'已下架'),
            )
        ),
        array(
            'key' => 'teaching_type',
            'name' => '上课方式',
            'lists' => array(
                array('id'=>1,'name'=>'线下面授'),
                array('id'=>2,'name'=>'线上直学'),
                //array('id'=>3,'name'=>'混合【线下+线上】'),
            )
        ),
    );
    return $sel_data;
}
/**
 * 科目下拉数据
 */
function ITSUserSelData(){
    $sel_data = array(
        array(
            'key' => 'userSex',
            'name' => '性别',
            'lists' => array(
                array('id'=>2,'name'=>'男'),
                array('id'=>1,'name'=>'女'),
                array('id'=>0,'name'=>'保密'),
            )
        ),
        array(
            'key' => 'orderType',
            'name' => '交款方式',
            'lists' => array(
                array('id'=>1,'name'=>'一次性交全款'),
                array('id'=>2,'name'=>'预报+补费'),
            )
        ),
        array(
            'key' => 'uidType',
            'name' => '学员身份',
            'lists' => array(
                array('id'=>1,'name'=>'新生'),
                array('id'=>2,'name'=>'在校生'),
                array('id'=>3,'name'=>'会员'),
            )
        ),
        array(
            'key' => 'study_status',
            'name' => '学习状态',
            'lists' => array(
                array('id'=>1,'name'=>'在读'),
                array('id'=>2,'name'=>'毕业'),
                array('id'=>3,'name'=>'过期'),
                array('id'=>4,'name'=>'弃学'),
                array('id'=>5,'name'=>'休学'),
                array('id'=>6,'name'=>'退学'),
            )
        ),

    );
    return $sel_data;
}
/**
 * 订单下拉数据
 */
/*function ITSOrderSelData(){
    $sel_data = array(
        array(
            'key' => 'order_status',
            'name' => '订单状态',
            'lists' => array(
                array('id'=>-1,'name'=>'全部'),
                array('id'=>0,'name'=>'未付款'),
                array('id'=>1,'name'=>'全额付款'),
                array('id'=>2,'name'=>'定金付款'),
                array('id'=>99,'name'=>'已取消'),
            )
        ),
    );
    return $sel_data;
}*/
function ITSOrderSelData(){
    $sel_data = array(
        array(
            'key' => 'order_status',
            'name' => '订单状态',
            'lists' => array(
                array('id'=>'0_0_0','name'=>'全部'),
                array('id'=>'0_0_2','name'=>'未确认，未付款（定金）'),
                array('id'=>'0_0_1','name'=>'未确认，未付款（全款）'),
                array('id'=>'0_1_2','name'=>'未确认，已付款（定金）'),
                array('id'=>'0_1_1','name'=>'未确认，已付款（全款）'),
                array('id'=>'1_1_2','name'=>'已确认，已付款（定金）'),
                array('id'=>'1_1_1','name'=>'已确认，已付款（全款）'),
                array('id'=>'2_0_2','name'=>'已取消，未付款（定金）'),
                array('id'=>'2_0_1','name'=>'已取消，未付款（全款）'),
                array('id'=>'2_2_2','name'=>'已取消，已退款（定金）'),
                array('id'=>'2_2_1','name'=>'已取消，已退款（全款）'),
            )
        ),
    );
    return $sel_data;
}

/**
 * 缴费下拉数据
 */
function ITSFeeSelData(){
    $sel_data = array(
        array(
            'key' => 'bill_type',
            'name' => '缴费类型',
            'lists' => array(
                array('id'=>3,'name'=>'学杂费'),
                array('id'=>2,'name'=>'学费'),
                array('id'=>1,'name'=>'报考费'),
            )
        ),
        array(
            'key' => 'bill_way',
            'name' => '缴费方式',
            'lists' => array(
                array('id'=>1,'name'=>'一次性收费'),
                array('id'=>2,'name'=>'定金'),
                array('id'=>3,'name'=>'补费'),
            )
        ),
    );
    return $sel_data;
}

 /**
 * 公用下拉数据
 */
function ITSCommonSelData(){
    $sel_data = array(
        array(
            'key' => 'is_show',
            'name' => '是否前台显示',
            'lists' => array(
                array('id'=>1,'name'=>'是'),
                array('id'=>0,'name'=>'否'),
            )
        ),
        array(
            'key' => 'is_full_pay',
            'name' => '是否全款',
            'lists' => array(
                array('id'=>1,'name'=>'是'),
                array('id'=>0,'name'=>'否'),
            )
        ),
        array(
            'key' => 'platform',
            'name' => '平台列表',
            'lists' => array(
                array('id'=>1,'key'=>'pc','name'=>'pc'),
                array('id'=>2,'key'=>'ipad','name'=>'ipad'),
                array('id'=>3,'key'=>'iphone','name'=>'iphone'),
                array('id'=>4,'key'=>'android','name'=>'android'),
                array('id'=>5,'key'=>'weixin','name'=>'weixin'),
                array('id'=>6,'key'=>'wap','name'=>'wap'),
            )
        ),
    );
    return $sel_data;
}
function ITSGetSelData($type='',$key='')
{
    $funcName = 'ITS'.ucfirst($type).'SelData';
    $sel_data = $funcName(); 
    if(!empty($key))
    {
        foreach($sel_data as $k=>$v)
        {
            if($v['key'] == $key)
            {
               $sel_data = $v;
            }
        }   
    }
    return $sel_data;  
}
/**
 @do 获取类似以下模板数据
 @tmpl 1=>'自考', 
             2=>'成考', 
             3=>'网教'
*/
function ITSSelIdName($type='',$key='')
{
   $return_data = [];
   $sel_data = ITSGetSelData($type);
   foreach($sel_data as $k=>$v)
   {
        if($v['key'] == $key)
        {
           foreach($v['lists'] as $vv)
           {
               $return_data[$vv['id']] = $vv['name'];
           }
        }
    }
    return $return_data;
}
function ITSSelItemName($type='',$key='',$id=0)
{
    $item_name = '';
    $sel_data = ITSGetSelData($type);
    foreach($sel_data as $k=>$v)
    {
        if($v['key'] == $key)
        {
           foreach($v['lists'] as $vv)
           {
               if($vv['id'] == $id)
               {
                   $item_name = $vv['name'];    
               }
           }
        }
    }
    return $item_name;
}
/**
 [example]
 function ITSCommonSelData(){
    $sel_data = array(
        array(
            'key' => 'is_show',
            'name' => '是否前台显示',
            'lists' => array(
                array('id'=>1,'name'=>'是'),
                array('id'=>0,'name'=>'否'),
            )
        ),
        array(
            'key' => 'is_full_pay',
            'name' => '是否全款',
            'lists' => array(
                array('id'=>1,'name'=>'是'),
                array('id'=>0,'name'=>'否'),
            )
        ),
        array(
            'key' => 'platform',
            'name' => '平台列表',
            'lists' => array(
                array('id'=>1,'key'=>'pc','name'=>'pc'),
                array('id'=>2,'key'=>'ipad','name'=>'ipad'),
                array('id'=>3,'key'=>'iphone','name'=>'iphone'),
                array('id'=>4,'key'=>'android','name'=>'android'),
                array('id'=>5,'key'=>'weixin','name'=>'weixin'),
                array('id'=>6,'key'=>'wap','name'=>'wap'),
            )
        ),
    );
    return $sel_data;
}
 [/example]
 @do 统一获取数据项ID
 @type 统一获取数据项ID
*/
function ITSSelItemId($type='',$key='',$name='',$fname='name')
{
    $item_id = 0;
    $sel_data = ITSGetSelData($type);
    $fname!='name' && $name=strtolower($name);
    foreach($sel_data as $k=>$v)
    {
        if($v['key'] == $key)
        {
           foreach($v['lists'] as $vv)
           {
               if($vv[$fname] == $name)
               {
                   $item_id = $vv['id'];    
               }
           }
        }
    }
    return $item_id;
}
//时间戳日期转换
function ITSTime2Date($time=0,$format='Y-m-d')
{
   if(!$time) $time = time();
   return (string)date($format,$time); 
}
function ITSDate2Time($date='',$hour='begin')
{
   if($hour=='begin') $hour = '00:00:00';
   if($hour=='end') $hour = '23:59:00';
   return (int)strtotime($date.' '.$hour); 
}
//登录判断
function checkLogin()
{
    $request = request();
    $visit = strtolower($request->module()."/".$request->controller()."/".$request->action());
    $no_login = ['api/orders/make_statement_tmpl'];
    if(in_array($visit,$no_login)) return true;
    $user = new \application\common\model\Users();
    $rs = $user->check_accesstoken();
    if($rs['status'] == -1)
    {
        MBISApiReturn($rs);
    }
}
//支付方式
function getPaymentLists($params=[])
{
    //ICON 信息
    $payment_icon_url = \think\Request::instance()->domain().'/static/images/payment/';
    $payment_icon = [
        1 => ['icon'=>$payment_icon_url.'icon_pay_zhifubao_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_zhifubao_selected@2x.png'],
        2 => ['icon'=>$payment_icon_url.'icon_pay_wechat_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_wechat_selected@2x.png'],
        3 => ['icon'=>$payment_icon_url.'icon_pay_Union_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_Union_selected@2x.png'],
        4 => ['icon'=>$payment_icon_url.'icon_pay_cash_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_cash_selected@2x.png'],
        5 => ['icon'=>$payment_icon_url.'icon_pay_duigon_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_duigon_selected@2x.png'],
        6 => ['icon'=>$payment_icon_url.'icon_pay_check_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_check_selected@2x.png'],
        7 => ['icon'=>$payment_icon_url.'icon_pay_yue_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_yue_selected@2x.png'],
        8 => ['icon'=>$payment_icon_url.'icon_pay_wechat_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_wechat_selected@2x.png'],
        9 => ['icon'=>$payment_icon_url.'icon_pay_wechat_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_wechat_selected@2x.png'],
        10 => ['icon'=>$payment_icon_url.'icon_pay_zhifubao_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_zhifubao_selected@2x.png'],
        11 => ['icon'=>$payment_icon_url.'icon_pay_zhifubao_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_zhifubao_selected@2x.png'],
        //12 => ['icon'=>$payment_icon_url.'icon_pay_wechat_default@2x.png','icon_selected'=>$payment_icon_url.'icon_pay_wechat_selected@2x.png'],
    ];
    //配置信息
    $payment_config =[
        1 => ['config'=>[]],
        2 => ['config'=>[]],
        3 => ['config'=>[]],
        4 => ['config'=>[]],
        5 => ['config'=>[]],
        6 => ['config'=>[]],
        7 => ['config'=>[]],
        8 => ['config'=>[]],
        9 => ['config'=>[]],
        10 => ['config'=>[]],
        11 => ['config'=>[]],
        //12 => ['config'=>[]],
    ];
    $payment = [
        [
            'key'=>1,
            'name'=>'线上支付',
            'lists'=>[
                //['payment_id'=>1,'name'=>'支付宝支付'],
                ['payment_id'=>2,'name'=>'微信扫码支付','matchtext'=>'微信扫码支付'],
                //['payment_id'=>3,'name'=>'网银支付'],
                //['payment_id'=>12,'name'=>'微信APP支付'],
            ]
        ],
        [
            'key'=>2,
            'name'=>'线下支付',
            'lists'=>[
                ['payment_id'=>4,'name'=>'现金支付','matchtext'=>'现金,现金支付'],
                ['payment_id'=>5,'name'=>'POS机支付','matchtext'=>'POS机支付,POS机银联刷卡'],
                ['payment_id'=>6,'name'=>'支票','matchtext'=>'支票'],
                ['payment_id'=>7,'name'=>'对公账号转账','matchtext'=>'对公账号转账'],
                ['payment_id'=>8,'name'=>'微信刷卡','matchtext'=>'微信刷卡'],
                ['payment_id'=>9,'name'=>'微信转账','matchtext'=>'微信转账'],
                ['payment_id'=>10,'name'=>'支付宝刷卡','matchtext'=>'支付宝刷卡'],
                ['payment_id'=>11,'name'=>'支付宝转账','matchtext'=>'支付宝转账'],
                ['payment_id'=>12,'name'=>'现金+刷卡','matchtext'=>'现金+刷卡'],
                ['payment_id'=>13,'name'=>'现金+微信','matchtext'=>'现金+微信'],
                ['payment_id'=>14,'name'=>'现金+支付宝','matchtext'=>'现金+支付宝'],
            ]

        ],
    ];
    foreach($payment as &$v)
    {
        foreach($v['lists'] as &$vv)
        {
            isset($payment_icon[$vv['payment_id']]) && $vv = array_merge($vv,$payment_icon[$vv['payment_id']]);
            if(isset($params['get_config']))
            {
                isset($payment_config[$vv['payment_id']]) && $vv = array_merge($vv,$payment_config[$vv['payment_id']]);
            }
        }
    }
    return $payment;
}
function ITSGetPayTypeName($key='')
{
    $item_name = '';
    $sel_data = getPaymentLists();
    foreach($sel_data as $k=>$v)
    {
        if($v['key'] == $key)
        {
           $item_name = $v['name'];
        }
    }
    return $item_name;
}
function ITSGetPayFromName($key='')
{
    $item_name = '';
    $sel_data = getPaymentLists();
    foreach($sel_data as $k=>$v)
    {
       foreach($v['lists'] as $vv)
       {
           if($vv['payment_id'] == $key)
           {
               $item_name = $vv['name'];    
           }
       }
    }
    return $item_name;
}

function ITSGetPayFromId($name='')
{
    $item_name = '';
    $sel_data = getPaymentLists();
    foreach($sel_data as $k=>$v)
    {
       foreach($v['lists'] as $vv)
       {
           $matchtext = array();
           $matchtext[] = $vv['name'];
           if(!empty($vv['matchtext'])) $matchtext = explode(',',$vv['matchtext']);
           if(in_array($name,$matchtext) )
           {
               $item_name = $vv['payment_id'];    
           }
       }
    }
    return $item_name;
}

//对象转换数组
function obj2Array($object)
{
    if(!is_object($object)) return $object;
    return json_decode(json_encode($object),true);   
}

//高精度保留两位小数
function getNumFormat($num=0)
{
    return sprintf("%.2f",substr(sprintf("%.10f", $num), 0, -3));    
}

/**
 * 订单状态
 */
function ITSOrderStatus($v){
    /**
    `payType` '支付类型：1=线上支付，2=线下支付',
`orderType` '订单类型：1=全额订单，2=定金订单',
`confirmStatus` '确认状态：0=未确认，1=已确认，2=已取消',
`payStatus` '支付状态：0=未付款，1=已付款，2=已退款',

未确认，未付款（定金）；----线上付款
confirmStatus==0 && payStatus==0 && orderType==2 && payType==1
未确认，未付款（全款）；----线上付款
confirmStatus==0 && payStatus==0 && orderType==1 && payType==1

未确认，已付款（定金）；----线下付款
confirmStatus==0 && payStatus==1 && orderType==2 && payType==2
未确认，已付款（全款）；----线下付款
confirmStatus==0 && payStatus==1 && orderType==1 && payType==2

已确认，已付款（定金）；----线下线上付款（后台财务审核/支付网关回调触发）
confirmStatus==1 && payStatus==1 && orderType==2
已确认，已付款（全款）；----线下线上付款（后台财务审核/支付网关回调触发）
confirmStatus==1 && payStatus==1 && orderType==1

已取消，未付款（定金）；----线下线上付款（线上付款可由用户操作触发，线下付款需要后台人员操作触发）
confirmStatus==2 && payStatus==0 && orderType==2
已取消，未付款（全款）；----线下线上付款（线上付款可由用户操作触发，线下付款需要后台人员操作触发）
confirmStatus==2 && payStatus==0 && orderType==1

已取消，已退款（定金）；----线下线上付款
confirmStatus==2 && payStatus==2 && orderType==2
已取消，已退款（全款）；----线下线上付
confirmStatus==2 && payStatus==2 && orderType==1
    */
	switch($v){
		case '0_0_0':return '全部';
		case '0_0_2':return '未确认，未付款（定金）';
		case '0_0_1':return '未确认，未付款（全款）';
        case '0_1_2':return '未确认，已付款（定金）';
        case '0_1_1':return '未确认，已付款（全款）';
        case '1_1_2':return '已确认，已付款（定金）';
        case '1_1_1':return '已确认，已付款（全款）';
        case '2_0_2':return '已取消，未付款（定金）';
        case '2_0_1':return '已取消，未付款（全款）';
        case '2_2_2':return '已取消，已退款（定金）';
        case '2_2_1':return '已取消，已退款（全款）';
	}
}

function add_logs($type='common',$content='',$ipfrom=true,$e=null)
{
    $log_file = '../logs/'.$type.'/'.date('Ymd').'.txt';
    if(!is_dir(dirname($log_file)) )
    $com = '';
    if($ipfrom)
    {
        $com =date('Y-m-d H:i:s')." >> ";
    }
    !empty($e) && $exception_con = '[File] '.$e->getFile().' [Line] '.$e->getLine().' [Code] '.$e->getCode().' [Message] '.$e->getMessage();
    !empty($e) && $com .= $exception_con;
    @mkdir(dirname($log_file),0777,true);
    $tmpl = $com.$content.chr(10);
    @file_put_contents($log_file,$tmpl,FILE_APPEND);   
}

/* 分摊金额处理 */
function get_aver_num($arr,$total1,$total2,$field='price',$pkey='id')
{
    $last = array_pop($arr);
    $notlast_total = 0;
    $tmp_arr = [];
    foreach($arr as $k=>$v)
    {
        $price = getNumFormat($v[$field]/$total1*$total2);
        $notlast_total += $price;
        $tmp_arr[$v[$pkey]] = $price;
    }
    $tmp_arr[$last['id']] = $total2-$notlast_total;
    return $tmp_arr;
}

//获取支付方式
function get_payment_lists($payType=1,$payFrom=2)
{
    /*if(!in_array($payType,[1,2]) || !in_array($payFrom,[1,2,3,4,5,6,7]))
    {
        return MBISReturn("支付方式有误");      
    }*/
    $paymentLists = getPaymentLists();
    foreach($paymentLists as $k=>$v)
    {
       if($v['key']==$payType)
       {
           foreach($v['lists'] as $kk=>$vv)
           {
               if($vv['payment_id']==$payFrom)
               {
                   $paymentLists[$k]['lists'][$kk]['checked'] = 1;
               }
           }
       }
    }
    return $paymentLists;   
}
//支付单号规则生成
function make_payment_id()
{
    $i = rand(0,99999);
    do{
        if(99999==$i){
            $i=0;
        }
        $i++;
        $payment_id = date('YmdHi').str_pad($i,5,'0',STR_PAD_LEFT);
        $row = Db::name('payments')->where(['payment_id'=>$payment_id])->field('payment_id')->find();
    }while($row);
    return $payment_id;   
}
//通道配置
function get_channel_lists($params=[])
{
    $rs = [
        'key' => 'channelType',
        'name' => '通道列表',
        'lists' => [
            ['id'=>1,'name'=>'旧价格体系（3月1日前）','status'=>1],
            ['id'=>2,'name'=>'新价格体系（3月1日后）','status'=>1],
        ]
    ];
    return $rs;
}
//确认订单测试模拟参数
function make_params_by_order_create($params=[])
{
         if(isset($_POST['demo']))
     {
       #102	59	1	81	0	1	a:1:{s:11:"subject_ids";a:2:{i:0;s:2:"46";i:1;s:2:"45";}}	2
 
     $params = array (
  'accesstoken' => 'fd260d55b80646cb53375cf6f9e4c512',
  'cartData' => 
  array (
    102 => 
    array (
      'add_deposit_price' => '500',
      'course_id' => '81',
      'subject_id' => '0',
      'subjectList' => 
      array (
        46 => '0',
        45 => '0',
      ),
      'adItemList' => array(
            //4=>500,
            //3=>200,
        ),
    ),
  ),
  'channelType' => '2',
  'jump_type' => '2',
  'orderData' => 
  array (
    'orderInfo' => 
    array (
      'discountMoney' => '0',
      'idcard' => '429000190001010000',
      'mobile' => '13800138000',
      'name' => '用户名',
      //'addr' => '客户地址',
      //'taxType' => 2,
      //'taxCompany' => 'xxx公司',
      /*'teamLists' => 
      array (
        0 => 
        array (
          'idcard' => '429000190001010000',
          'mobile' => '13800138000',
          'name' => '用户名',
        ),
      ),*/
    ),
    'paymentInfo' => array(1=>2),
  ),
  'userId' => '59',
  'smsVcode' => '123456',
  'schoolDiscountPrice' => '50',
  'signPicUrl' => 'upload/base64/signimg/2017/03/24/58d4ff88d19e2.png',
  'nodelcart' => '50',
);
     }
     $_POST = $params; 
     return $params;   
}

function make_params_by_cart_lists($params=[])
{
    
    if(isset($_GET['skill']))
     {
$params = array (
  'accesstoken' => 'fd260d55b80646cb53375cf6f9e4c512',
  'cartData' => 
  array (
    333 => 
    array (
      'add_deposit_price' => '500',
      'course_id' => '91',
      'subject_id' => '0',
      'subjectList' => array(
            46=>0,
            47=>1,
        ),
      'adItemList' => array(
            //4=>500,
        ),
    ),
    334 => 
    array (
      'add_deposit_price' => '800',
      'course_id' => '0',
      'subject_id' => '45',
      'subjectList' => array(
            45=>1,
            //47=>1,
        ),
    ),
  ),
  'channelType' => '2',
  'jump_type' => '2',
  'orderData' => 
  array (
    'orderInfo' => 
    array (
      'discountMoney' => '0',
      'idcard' => '429000190001010000',
      'mobile' => '13800138000',
      'name' => '用户名',
      //'addr' => '客户地址',
      //'taxType' => 2,
      //'taxCompany' => 'xxx公司',
      'teamLists' => 
      array (
        array (
          'idcard' => '429000190001010000',
          'mobile' => '13800138000',
          'name' => '用户名1',
        ),
        array (
          'idcard' => '429000190001010000',
          'mobile' => '13800138000',
          'name' => '用户名2',
        ),
      ),
    ),
  ),
  'userId' => '59',
  'smsVcode' => '123456',
  'schoolDiscountPrice' => '50',
  'signPicUrl' => 'upload/base64/signimg/2017/03/24/58d4ff88d19e2.png',
);
     }
    
     if(isset($_GET['edu']))
     {
$params = array (
  'accesstoken' => 'fd260d55b80646cb53375cf6f9e4c512',
  'cartData' => 
  array (
    310 => 
    array (
      'add_deposit_price' => '500',
      'course_id' => '28',
      'subject_id' => '0',
      'is_full_pay' => '1',
      'adItemList' => array(
            4=>500,
            //3=>200,
        ),
    ),
    331 => 
    array (
      'add_deposit_price' => '500',
      'course_id' => '29',
      'subject_id' => '0',
      'is_full_pay' => '1',
      'adItemList' => array(
            4=>100,
            //3=>200,
        ),
    ),
  ),
  'channelType' => '2',
  'jump_type' => '1',
  'orderData' => 
  array (
    'orderInfo' => 
    array (
      'discountMoney' => '0',
      'idcard' => '429000190001010000',
      'mobile' => '13800138000',
      'name' => '用户名',
      //'addr' => '客户地址',
      //'taxType' => 2,
      //'taxCompany' => 'xxx公司',
      'teamLists' => 
      array (
        array (
          'idcard' => '429000190001010000',
          'mobile' => '13800138000',
          'name' => '用户名',
        ),
        array (
          'idcard' => '429000190001010000',
          'mobile' => '13800138000',
          'name' => '用户名',
        ),
      ),
    ),
  ),
  'userId' => '59',
  'smsVcode' => '123456',
  'schoolDiscountPrice' => '50',
  'signPicUrl' => 'upload/base64/signimg/2017/03/24/58d4ff88d19e2.png',
);
     }
     $_POST=$params;
     return $params;
}

//获取付款单号。
function getPaymentId(){
    $pid = date('YmdHis').rand(1000,9999);
    $data = Db::name('payments')->where(['payment_id'=>$pid])->field('payment_id')->find();
    if(!$data){
        return $pid;
    }
    return getPaymentId();
}

//校区优惠价配置
function getSchoolDiscountSetting($params=[])
{
    $setting = [
        //14 => ['name'=>'总经理','mobile'=>'','discount'=>95],
        /*21 => ['name'=>'宝安校区','mobile'=>'18128859049','discount'=>50],
        22 => ['name'=>'龙岗校区','mobile'=>'18128859050','discount'=>50],
        23 => ['name'=>'福田校区','mobile'=>'18128859083','discount'=>50],
        24 => ['name'=>'南山校区','mobile'=>'18128859067','discount'=>50],*/
        21 => ['name'=>'宝安校区','mobile'=>'15875914803','discount'=>50],
        22 => ['name'=>'龙岗校区','mobile'=>'15875914803','discount'=>50],
        23 => ['name'=>'福田校区','mobile'=>'15875914803','discount'=>50],
        24 => ['name'=>'南山校区','mobile'=>'15875914803','discount'=>50],
        0 => ['name'=>'','mobile'=>'','discount'=>100],
    ]; 
    return isset($setting[$params['schoolId']])?$setting[$params['schoolId']]:[];  
}
/**
 @do     按照指定key格式化数组
 @desc   输入二维数组
 @param  key field1|field2|field3
*/
function format_arr_by_key($key='',$key2='',$arr=[],&$msg)
{
    if(empty($key) || empty($arr)):
        $msg = MBISReturn("[key] OR [arr] 为空");
        return false;
    endif;
    //多个key处理
    $keys=[];
    strpos($key,'|')!==FALSE && $keys=explode('|',$key) && $keys=array_flip($keys);
    $tmp_arr = [];
    foreach($arr as $k=>$v):
        //多个key处理
        if(!empty($keys)):
            foreach($keys as $s_key):
               !empty($v[$s_key]) && $key=$s_key;
            endforeach;   
        endif;//END
        if(empty($v[$key])) continue;
        !empty($key2) && $tmp_arr[$v[$key]][$v[$key2]]=$v;
        empty($key2) && $tmp_arr[$v[$key]][]=$v;
    endforeach;
    return $tmp_arr;
}

function curlGet($url=''){
	#$url = 'http://utf8.sms.webchinese.cn/?Uid='.MBISConf("CONF.smsKey").'&Key='.MBISConf("CONF.smsPass").'&smsMob='.$phoneNumer.'&smsText='.$content;
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 ); //设置连接等待时间
	curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
	$data=curl_exec($ch);
	curl_close($ch);
	return $data;
}

//协议模板
function get_statement_tmpl($key='skill_notfull')
{
    /*$data = [
        #
        ['key'=>'skill_full','name'=>'课程培训合同（技能类）','content'=>'','path'=>'html/skill/tmpl-full','signcompany'=>request()->domain().'/static/images/stamp/skill/1.png'],
        ['key'=>'skill_notfull','name'=>'购买课程意向协议书（技能类）','content'=>'','path'=>'html/skill/tmpl-notfull','signcompany'=>request()->domain().'/static/images/stamp/skill/1.png'],
        ['key'=>'edu_full','name'=>'课程培训合同（学历类）','content'=>'','path'=>'html/edu/tmpl-full','signcompany'=>request()->domain().'/static/images/stamp/edu/1.png'],
        ['key'=>'edu_notfull','name'=>'购买课程意向协议书（学历类）','content'=>'','path'=>'html/edu/tmpl-notfull','signcompany'=>request()->domain().'/static/images/stamp/edu/1.png'],
    ];*/
    $data = Db::name('statement_tmpl')->select();
    
    $tmp_data = [];
    foreach($data as $k=>$v)
    {
        $v['signcompany'] = ITSPicUrl($v['signcompany']);
        $tmp_data[$v['key']] = $v;
    }
    return $tmp_data[$key];
}

//base64 KEY
function get_base64_key()
{
    return 'itsbase64key';   
}

function pre_request_data($params=[])
{
    $dataType = 'ARRAY';
    !empty($params['dataType']) && $dataType = strtolower($params['dataType']);
    if($dataType=='ARRAY') return $params;
    if($dataType=='JSON') return $params;
}
function make_json2arr($params=[])
{
    $arr_params = json_decode($params,true);
    return $params;
}

//去除字符串左右2边的字符XXX
if (!function_exists('substrstring')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function substrString($number)
    {
        $pattern       = '/(\【.*\】)/';
        preg_match($pattern, $number,$res);
        if( empty($res) ){
            return false;
        }
        $res = $res[0];
        $res =ltrim($res,'【');
        $res =rtrim($res,'】');
        return $res;

    }
}
if (!function_exists('getLastSql')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     * @return string
     */
    function getLastSql()
    {
        dump(db::getLastSql());
        exit;
    }
}


if (!function_exists('excelTime')) {
    /**
     * @param unknown $date  日期
     * @param string $time
     * @return string
     */
    function excelTime($date, $time = false) {
        if (function_exists('GregorianToJD')) {
            if (is_numeric($date)) {
                $jd = GregorianToJD(1, 1, 1970);
                $gregorian = JDToGregorian($jd + intval($date) - 25569);
                $date = explode('/', $gregorian);
                $date_str = str_pad($date[2], 4, '0', STR_PAD_LEFT) . "-" . str_pad($date[0], 2, '0', STR_PAD_LEFT) . "-" . str_pad($date[1], 2, '0', STR_PAD_LEFT) . ($time ? " 00:00:00" : '');
                return $date_str;
            }
        } else {
            //$date = $date > 25568 ? $date + 1 : 25569; /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
            $date = $date > 25568 ? $date: 25569;
            $ofs = (70 * 365 + 17 + 2) * 86400;
            $date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
        }
         
        return $date;
    }
}

if (!function_exists('importExcel')) {
     function importExcel($file,$SheetIndex="0"){
         set_time_limit(0);
         import('phpexcel.PHPExcel.IOFactory');
         $reader = \PHPExcel_IOFactory::createReader('Excel2007');
         $objReader = \PHPExcel_IOFactory::load($file);
         
         $objReader->setActiveSheetIndex($SheetIndex);
         $sheet  = $objReader->getActiveSheet();
         $rows   = $sheet->getHighestRow();//行
         $line   = $sheet->getHighestColumn();//列A B C D
         $allLine = \PHPExcel_Cell::columnIndexFromString($line);
         
         $allcolumn = 0;
         $arrContent = array();
         for ($r = 1; $r <= $rows; $r++){
             for($l = 0; $l < $allLine; $l++){
                 $arrContent[$r][]  = (string)$sheet->getCellByColumnAndRow($l,$r);
             }
         }
         return $arrContent;
     }

}

/**
 * 
 * @param unknown $filename 文件名称
 * @param unknown $data 导出数据
 */
function array_excel($filename, $data) {
    import('phpexcel.PHPExcel');
    $excel = new \PHPExcel();
    $sheet = $excel->getActiveSheet();
    // $sheet->setTitle('helen');
    $sheet->fromArray($data);
    $file = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    header("Content-type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Content-Disposition: attachment; filename={$filename}.xls");
    $file->save('php://output');
}

/**
 * 格式化二维数组，返回一维数组
 * @param $two_arr 二维数组
 * @param $key 返回数组key指定的key
 * @param $key2 返回数组val的指定key2
 */
function foramt_array_one($two_arr,$key='',$key2=''){
    if(empty($two_arr)) return array();
    $tmp_arr = array();
    foreach($two_arr as $v):
       $tmp_arr[$v[$key]] = $v[$key2];
    endforeach;
    return $tmp_arr;
}

/**
 * 课程类型列表
*/
function type_get_data(){
    return foramt_array_one(model('admin/subject')->get_subject_type_lists(),'type_id','name');   
}