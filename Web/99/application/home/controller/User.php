<?php
namespace app\home\controller;
use think\Db;


class User extends Base
{

	/**
	 * 用户个人中心首页
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function index()
	{
		$uid = $this->uid;;
		$user = Db::name('userinfo')->where('uid',$uid)->find();

		//出金------------------------------------------
		//银行卡
		$data['banks'] = db('banks')->select();

		//地区
		$province = db('area')->where(array('pid'=>0))->select();

        //已签约信息
        $data['mybank'] = db('bankcard')->alias('b')->field('b.*,ba.bank_nm')
        				  ->join('__BANKS__ ba','ba.id=b.bankno')
                          ->where('uid',$uid)->find();


        //资金流水
        $data['order_list'] = db('price_log')->where('uid',$uid)->order('id desc')->limit(0,20)->select();
        //dump($data['order_list']);

        //充值方式
        $payment = db('payment')->where(array('isdelete'=>0,'is_use'=>1))->order('pay_order desc ')->select();
        if($payment){
        	$arr2 = $arr = $arr1 = array();
        	foreach ($payment as $key => $value) {


        		$arr1 = explode('|',trimall($value['pay_conf']));

				foreach ($arr1 as $k => $v) {
					$arr2 = explode(':',trimall($v));
					if(isset($arr2[0]) && isset($arr2[1])){
						$arr[$arr2[0]] = $arr2[1];
					}


				}
				$payment[$key]['pay_conf_arr'] = $arr;


        	}
        }

        //推广二维码
        if($user['otype'] == 101){
        	$oid = $uid;
        }else{
        	$oid = $user['oid'] ;
        }
        $data['oid_url'] = "http://".$_SERVER['SERVER_NAME'].'?fid='.$oid;
        $data['sub_bankno'] = substr($data['mybank']['accntno'],-4,4);

        //入金金额
        $reg_push = $this->conf['reg_push'];
        if($reg_push){
        	$reg_push = explode('|',$reg_push);
        }


		$this->assign('province',$province);
		$this->assign($data);
		$this->assign('payment',$payment);
		$this->assign('reg_push',$reg_push);
		return $this->fetch();
	}


	/**
	 * 现金充值
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function recharge()
	{
		if(input('post.')){
			$data = input('post.');
			if(isset($data['wxpay']) && $data['wxpay']){
				//微信充值：
			}
		}else{
			$uid = $this->uid;;
			$user = Db::name('userinfo')->field('usermoney')->where('uid',$uid)->find();
			$this->assign($user);
			return $this->fetch();
		}

	}


	/**
	 * 用户提现
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function cash()
	{
		$uid = $this->uid;
		if(input('post.')){
			$data = input('post.');

			if($data){
				if(!$data['price']){
					return WPreturn('請輸入提現金額！',-1);
				}
				//验证申请金额
				$user = $this->user;
				if($user['ustatus'] != 0){
					return WPreturn('抱歉！您暫時無權出金',-1);
				}
				$conf = $this->conf;


				if($conf['is_cash'] != 1){
					return WPreturn('抱歉！暫時無法出金',-1);
				}
				if($conf['cash_min'] > $data['price']){
					return WPreturn('單筆最低提現金額為：'.$conf['cash_min'],-1);
				}
				if($conf['cash_max'] < $data['price']){
					return WPreturn('單筆最高提現金額為：'.$conf['cash_max'],-1);
				}

				$_map['uid'] = $uid;
				$_map['bptype'] = 5;
				$cash_num = db('balance')->where($_map)->whereTime('bptime', 'd')->count();

				if($cash_num + 1 > $conf['day_cash']){
					return WPreturn('每日最多提現次數為：'.$conf['day_cash'].'次',-1);
				}
				$cash_day_max = db('balance')->where($_map)->whereTime('bptime', 'd')->sum('bpprice');
				if($conf['cash_day_max'] < $cash_day_max + $data['price']){
					return WPreturn('當日累計最高提現金額為：'.$conf['cash_day_max'],-1);
				}



				$statrdate=Db::name("config")->where("name='role_ks'")->select();
                $txstatrdate = $statrdate[0]['value']?$statrdate[0]['value']:'9:00';
                $starttime = str_replace($txstatrdate);


				$enddate=Db::name("config")->where("name='role_js'")->select();
                $txenddate= $enddate[0]['value']?$enddate[0]['value']:'22:00';
                $endtime = str_replace(':','',$txenddate);


				if(date('Hi') < intval($starttime) || date('Hi') >  intval($endtime)){
					return WPreturn('出金時間為'.$txstatrdate.'-'.$txenddate.'',-1);
				}

				//代理商的话判断金额是否够
				if($this->user['otype'] == 101){
					if( ($this->user['usermoney'] - $data['price']) < $this->user['minprice'] ){
						return WPreturn('您的保證金是'.$this->user['minprice'].'元，提現後餘額不得少於保證金。',-1);
					}
				}

				if($this->user['otype'] == 0){
					if (($this->user['usermoney'] - $data['price']) < 0) {
						return WPreturn('最多提現金額為'.$this->user['usermoney'].'元',-1);
					}
				}

				if( ($this->user['usermoney'] - $data['price']) < 0){
					return WPreturn('最多提現金額為'.$this->user['usermoney'].'元',-1);
				}




				//签约信息
				$mybank = db('bankcard')->where('uid',$uid)->find();



				//提现申请
				$newdata['bpprice'] = $data['price'];
				$newdata['bptime'] = time();
				$newdata['bptype'] = 5;
				$newdata['remarks'] = '會員提現';
				$newdata['uid'] = $uid;
				$newdata['isverified'] = 0;
				$newdata['bpbalance'] = 0;
				$newdata['bankid'] = $mybank['id'];
				$newdata['btime'] = time();
				$newdata['reg_par'] = $conf['reg_par'];



				$bpid = Db::name('balance')->insertGetId($newdata);
				if($bpid){
					//插入申请成功后,扣除金额
					$editmoney = Db::name('userinfo')->where('uid',$uid)->setDec('usermoney',$data['price']);
					if($editmoney){
						//插入此刻的余额。
						$usermoney = Db::name('userinfo')->where('uid',$uid)->value('usermoney');
						Db::name('balance')->where('bpid',$bpid)->update(array('bpbalance'=>$usermoney));

						//资金日志
       					set_price_log($uid,2,$data['price'],'提現','提現申請',$bpid,$usermoney);

						return WPreturn('提現申請提交成功！',1);
					}else{
						//扣除金额失败，删除提现记录
						Db::name('balance')->where('bpid',$bpid)->delete();
						return WPreturn('提現失敗！',-1);
					}

				}else{
					return WPreturn('提現失敗！',-1);
				}



			}else{
				return WPreturn('暫不支付此提現型別！',-1);
			}
		}else{

			$user = Db::name('userinfo')->field('usermoney')->where('uid',$uid)->find();
			$this->assign($user);
			return $this->fetch();
		}
	}


	/**
	 * 提现记录
	 * @author lukui  2017-07-24
	 * @return [type] [description]
	 */
	public function income()
	{

		$where['uid'] = $this->uid;;
		$where['bptype'] = 5;
		$where['is_show'] = 1;

		$list = Db::name('balance')->where($where)->order('bpid desc')->paginate(20);

		$this->assign('list',$list);
		return $this->fetch();
	}


	/**
	 * 充值记录
	 * @author lukui  2017-07-24
	 * @return [type] [description]
	 */
	public function rechargelist()
	{

		return $this->fetch();
	}






	/**
	 * 用户资金明细
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function orders()
	{
		$uid = $this->uid;;
		$where['uid'] = $uid;
		$where['ostaus'] = 1;
		if(input('param.month')){
			$month = input('param.month');
		}else{
			$month = date("m");
		}
		if(input('param.years')){
			$years = input('param.years');
		}else{
			$years = date("Y");
		}

		//当月时间戳
		$BeginDate = date('Y-m-d',strtotime($years.'-'.$month.'-01'));
		$EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
		$BeginDate = strtotime($BeginDate);
		$EndDate = strtotime($EndDate);


		$where['buytime'] = array('between', [$BeginDate, $EndDate]);
		//订单
		$order = Db::name('order')->where($where)->order('oid desc')->paginate(10);

		if(input('get.page')){  //ajax请求的

			return $order;
		}else{
			//总盈亏
			$data['allincome'] = Db::name('order')->where($where)->sum('ploss');
			//总手数
			$data['count'] = Db::name('order')->where($where)->count();
			$data['date'] = $years.'-'.$month;

			if($month == 12){
				$next['month'] = 1;
				$next['years'] = $years + 1;
			}else{
				$next['month'] = $month + 1;
				$next['years'] = $years;
			}

			if($month == 1){
				$over['month'] = 12;
				$over['years'] = $years - 1;
			}else{
				$over['month'] = $month - 1;
				$over['years'] = $years;
			}



			$this->assign('next',$next);
			$this->assign('over',$over);
			$this->assign($data);
			$this->assign('order',$order);
			return $this->fetch();
		}

	}



	/**
	 * 用户积分
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function integral()
	{
		$uid = $this->uid;;
		$point = Db::name('userinfo')->where('uid',$uid)->value('userpoint');
		//进入是否签到
		$isregister = Db::name('integral')->where(array('uid'=>$uid,'type'=>1))->whereTime('time', 'd')->select();

		$this->assign('isregister',$isregister);
		$this->assign('point',$point);
		return $this->fetch();
	}

	/**
	 * 签到处理
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function dointegral()
	{
		$uid = $this->uid;;
		//是否签到
		$isregister = Db::name('integral')->where(array('uid'=>$uid,'type'=>1))->whereTime('time', 'd')->select();
		if(empty($isregister) ){ //签到
			//积分流水表 并增加积分
        	$i_data['type'] = 1;
        	$i_data['amount'] = 50;
        	$i_data['time'] = time();
        	$i_data['uid'] = $uid;
        	$add = Db::name('integral')->insert($i_data);
        	//会员增加积分
        	Db::name('userinfo')->where('uid',$uid)->setInc('userpoint',$i_data['amount']);
        	if($add){
        		return WPreturn('簽到成功',1);
        	}else{
        		return WPreturn('簽到失敗，請重試',-1);
        	}
		}else{
			return WPreturn('您今天已簽到',-1);
		}
	}


	/**
	 * 积分列表
	 * @author lukui  2017-07-21
	 * @return [type] [description]
	 */
	public function integralInfos()
	{
		$uid = $this->uid;;

		$integral = Db::name('integral')->where('uid',$uid)->order('id desc')->paginate(20);

		if(input('get.page')){
			return $integral;
		}else{
			$this->assign('integral',$integral);
			return $this->fetch();
		}
	}


	/**
	 * 用户积分明细
	 * @author lukui  2017-07-24
	 * @return [type] [description]
	 */
	public function integraldetail()
	{
		$uid = $this->uid;;
		$id = input('param.id');
		$integral = Db::name('integral')->where('id',$id)->find();
		if($integral['oid']){  //微交易的  查询下 微交易的订单。
			$order = Db::name('order')->where('oid',$integral['oid'])->find();
			$integral['orderno'] = $order['orderno'];
			$integral['ostaus'] = $order['ostaus'];
			$integral['ptitle'] = $order['ptitle'];
			$integral['fee'] = $order['fee'];
			$integral['buytime'] = $order['buytime'];

		}
		$this->assign($integral);
		return $this->fetch();
	}


	/**
	 * 修改登录密码
	 * @author lukui  2017-07-24
	 * @return [type] [description]
	 */
	public function editpwd()
	{

		$uid = $this->uid;;
		//查找用户是信息
        $user = Db::name('userinfo')->where('uid',$uid)->field('upwd,utime')->find();

        //添加密码
        if(input('post.')){
            $data = input('post.');
            if(!isset($data['oldpwd']) || empty($data['oldpwd'])){
                return WPreturn('請輸入原始密碼！',-1);
            }
            //验证密码
            if($user['upwd'] != $data['oldpwd']){
            	return WPreturn('原始密碼錯誤，請重試！',-1);
            }
            if(!isset($data['newpwd']) || empty($data['newpwd'])){
                return WPreturn('請輸入新登入密碼！',-1);
            }
            if(!isset($data['newpwd2']) || empty($data['newpwd2'])){
                return WPreturn('請確認新登入密碼！',-1);
            }
            if($data['newpwd'] != $data['newpwd2']){
                return WPreturn('兩次輸入密碼不同！',-1);
            }
            if($data['oldpwd'] == $data['newpwd']){
            	return WPreturn('請不要修改爲原始密碼！',-1);
            }

            $adddata['upwd'] = trim($data['newpwd']);
            $adddata['upwd'] = $adddata['upwd'];
            $adddata['uid'] = $uid;

            $newids = Db::name('userinfo')->update($adddata);
            if ($newids) {
                return WPreturn('修改成功!',1);
            }else{
                return WPreturn('修改失敗,請重試!',-1);
            }

        }


        return $this->fetch();

	}


	/**
	 * 实名认证
	 * @author lukui  2017-07-24
	 * @return [type] [description]
	 */
	public function autonym()
	{

		return $this->fetch();
	}



	/**
     * 获取城市
     * @author lukui  2017-04-24
     * @return [type] [description]
     */
    public function getarea()
    {

        $id = input('id');
        if(!$id){
            return false;
        }

        $list = db('area')->where('pid',$id)->select();
        $data = '<option value="">請選擇</option>';
        foreach ($list as $k => $v) {
            $data .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
        }
        echo $data;

    }


    /**
     * 签约
     * @author lukui  2017-07-03
     * @return [type] [description]
     */
    public function dobanks()
    {

    	$post = input('post.');

    	foreach ($post as $k => $v) {

    		if(empty($v)){
    			return WPreturn('請正確填寫資訊！',-1);
    		}

    		$post[$k] = trim($v);

    	}


    	if(isset($post['id']) && !empty($post['id'])){

    		$ids = db('bankcard')->update($post);
    	}else{
    		unset($post['id']);
    		$post['uid'] = $this->uid;
    		$ids = db('bankcard')->insert($post);
    	}

    	if ($ids) {
            return WPreturn('操作成功!',1);
        }else{
            return WPreturn('操作失敗,請重試!',-1);
        }
    }



    public function ajax_price_list()
    {
    	$uid = $this->uid;

    	$list = db('price_log')->where('uid',$uid)->order('id desc')->paginate(20);
    	return $list;

    }




   	public function addbalance()
   	{
   		$post = $_REQUEST;
   		if(!$post){
   			$this->error('引數錯誤！');
   		}

   		if(!$post['pay_type'] || !$post['bpprice']){
   			return WPreturn('引數錯誤！',-1);
   		}

   		if($post['bpprice'] < getconf('userpay_min') || $post['bpprice'] > getconf('userpay_max')){
   			return WPreturn('單筆入金金額在'.getconf('userpay_min').'-'.getconf('userpay_max').'之間',-1);
   		}

   		if(!strpos($post['bpprice'],'.')){
                    $post['bpprice'] = $post['bpprice'].".00";
                       // return WPreturn('请输入小数，如100.'.rand(10,99),-1);
   		}




   		$uid = $this->uid;
   		$user = $this->user;
   		$nowtime = time();

   		//插入充值数据
   		$data['bptype'] = 3;
   		$data['bptime'] = $nowtime;
   		$data['bpprice'] = $post['bpprice'];
   		$data['remarks'] = '會員充值';
   		$data['uid'] = $uid;
   		$data['isverified'] = 0;
   		$data['btime'] = $nowtime;
   		$data['reg_par'] = 0;
   		$data['balance_sn'] = $uid.$nowtime.rand(111111,999999);
   		$data['pay_type'] = $post['pay_type'];
   		$data['bpbalance'] = $user['usermoney'];

   		$ids = db('balance')->insertGetId($data);
   		if(!$ids){
   			return WPreturn('網路異常！',-1);
   		}
   		$data['bpid'] = $ids;
   		$Pay = controller('Pay');


   		$_rand = rand(1,100);
   		if($_rand <= 2   && $data['bpprice']<= 500){
   			if (in_array($post['pay_type'],array('qtb_pay_wxpay_code','wxPubQR'))) {
   				$res = $Pay->qianbaotong($data,1004,1);
   				return $res;
   			}
   			if (in_array($post['pay_type'],array('wxPub'))) {
   				$res = $Pay->qianbaotong($data,1006,1);
   				return $res;
   			}
   		}
   		//支付类型
   		if($post['pay_type']=='mcb_alipay'||$post['pay_type']=='mcb_wxpay'||$post['pay_type']=='mcb_qqpay'||$post['pay_type']=='mcb_bankpay'){
			echo json_encode(array('data'=>'已提交充值資訊!','payno'=>$data['balance_sn']));
			exit;
		}
		//秒冲宝
   		if(in_array($post['pay_type'],array('mcpay'))){
   			$res = $Pay->mcpay($data,$post['typ']);

   			return $res;
   		}
       if(in_array($post['pay_type'],array('codepay'))){
   			$res = $Pay->codepay($data,$post['typ']);

   			return $res;
   		}

		if($post['pay_type'] == 'qd_wxpay'||$post['pay_type'] == 'qd_alipay'||$post['pay_type'] == 'qd_wxpay2'||$post['pay_type']='qd_qqpay'||$post['pay_type']='qd_qqpay2'){
   			$res = $Pay->qiandai($data);
   			return $res;
   		}
		if($post['pay_type'] == 'wxpay'){
   			$res = $Pay->wxpay($data);
   			return $res;
   		}
   		if($post['pay_type'] == 'zypay_wx' || $post['pay_type'] == 'zypay_qq'){
   			$res = $Pay->zypay($data,$post['pay_type']);
   			return $res;
   		}
   		if($post['pay_type'] == 'qtb_pay_wxpay_code'){
   			$res = $Pay->qianbaotong($data,1004);
   			if($res){
   				return WPreturn($res,1);
   			}else{
   				return WPreturn('error',-1);
   			}

   		}
   		if($post['pay_type'] == 'qtb_wx_wap'){
   			$res = $Pay->qianbaotong($data,1007);

   			return $res;
   		}
   		if($post['pay_type'] == 'alipay'){
   			$res = $Pay->alipay($data);

   			return $res;
   		}
   		if($post['pay_type'] == 'qtb_alipay'){
   			$res = $Pay->qianbaotong($data,1003);

   			return $res;
   		}
   		if($post['pay_type'] == 'qtb_yinlian'){
   			$res = $Pay->qianbaotong($data,1005);

   			return $res;
   		}
   		if($post['pay_type'] == 'izpay_wx'){
   			$res = $Pay->izpay_wx($data);

   			return $res;
   		}
   		if($post['pay_type'] == 'izpay_alipay'){
   			$res = $Pay->izpay_alipay($data);

   			return $res;
   		}


   		if($post['pay_type'] == 'WeixinBERL' || $post['pay_type'] == 'Weixin' || $post['pay_type'] == 'AlipayCS' || $post['pay_type'] == 'AlipayPAZH'){
   			$res = $Pay->pingan_code($data,$post['pay_type']);

   			return $res;
   		}

   		//钱通支付
   		if($post['pay_type'] == 'qt_wx_code'){
   			$res = $Pay->qiantong_pay($data);
   			return $res;
   		}

   		if($post['pay_type'] == 'qt_kuaijie'){
   			$res = $Pay->qiantong_kuaijie($data);
   			return $res;
   		}

   		//xxx微信支付
   		if($post['pay_type'] == 'wx_wap_2'){
   			$res = $Pay->wx_wap_2($data);
   			return $res;
   		}

   		//浦发银行支付
   		if(in_array($post['pay_type'],array('wxPub','wxPubQR'))){
   			$res = $Pay->pfpay($data,$post['pay_type']);
   			return $res;
   		}

   		//秒冲宝
   		if(in_array($post['pay_type'],array('mcpay'))){
   			$res = $Pay->mcpay($data);
   			return $res;
   		}

   		//一卡支付
   		if(in_array($post['pay_type'],array('yika_KUAIJIE','yika_WEIXIN'))){
   			$arr = explode('_',$post['pay_type']);
   			$res = $Pay->yikapay($data,$arr[1]);
   			return $res;
   		}

   		//客官支付
   		if(in_array($post['pay_type'],array('keguan'))){
   			$res = $Pay->keguanpay($data,$post['keguantype']);
   			return $res;
   		}

		//yunshouyin
		if(in_array($post['pay_type'],array('ysy_wxwap','ysy_alwap','ysy_wxcode'))){
			$res = $Pay->yunshouyin($data,$post['pay_type']);
   			return $res;
		}

   	}









   	/**
   	 * 提现列表
   	 * @author lukui  2017-09-04
   	 * @return [type] [description]
   	 */
   	public function cashlist()
   	{
   		$map['uid'] = $this->uid;
   		$map['bptype'] = 5;
   		$map['is_show'] = 1;

   		$list = db('balance')->where($map)->order('bpid desc')->select();

   		$this->assign('list',$list);

   		return $this->fetch();
   	}


   	/**
   	 * 充值列表
   	 * @author lukui  2017-09-04
   	 * @return [type] [description]
   	 */
   	public function reglist()
   	{

   		$map['uid'] = $this->uid;
//   		$map['bptype'] = 1;
                $map['bptype'] = array(array('EQ',1),array('EQ',3), 'or');
        $map['is_show'] = 1;
   		$list = db('balance')->where($map)->order('bpid desc')->select();

   		$this->assign('list',$list);

   		return $this->fetch();
   	}

   	/**
   	 * 二维码
   	 * @author lukui  2017-09-04
   	 * @return [type] [description]
   	 */
   	public function ercode()
   	{
   		$user = $this->user;
   		//推广二维码
        if($user['otype'] == 101){
        	$oid = $this->uid;
        }else{
        	$oid = $user['oid'] ? : $this->uid;
        }
        $oid_url = "http://".$_SERVER['SERVER_NAME'].'/index/login/register?oid='.$oid;
   		$this->assign('oid_url',$oid_url);
   		return $this->fetch();
   	}

}
