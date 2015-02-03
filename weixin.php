<?php


require('./src/Wechat.php');
require('bcs.class.php');
require('imgutil.php');
require_once('saeDao.php');


class MyWechat extends Wechat
{

    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe()
    {
        $this->responseText('欢迎访问红领巾！
我们是留学生代购联盟，目前在日本、香港、美国、德国、法国设有小分队，为大陆的小伙伴提供海外代购服务。说出你想买的东西，我们就能在当地为你采购，大多数商品从国外发货噢！[得意]
回复“买买买”，告诉我们你想买啥！
回复“呼叫小助手”，更全面地了解我们！');
    }

    /**
     * 上报地理位置时触发,回复收到的地理位置
     *
     * @return void
     */
    protected function onEventLocation()
    {
        $this->responseText('收到了位置推送：' . $this->getRequest('Latitude') . ',' . $this->getRequest('Longitude'));
    }

    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText()
    {
        // 好吧，强大的缓存开始了



        $keyword = $this->getRequest('content');
        $this->memContext($this->getRequest('FromUserName'),$keyword);
        if ($keyword == "买买买" || $keyword == "我要买" || $keyword == "下单" ) {
            $fromUsername = $this->getRequest('FromUserName');
            $contentStr = "代购商品请点击：
<a href='http://mytaotao123.sinaapp.com/reg.php?uwid=$fromUsername'>填写购买表单</a>";
            $this->responseText($contentStr);
        }
        if ($keyword == "我的订单" || $keyword == "查物流" || $keyword == "快递" || $keyword == "我的购买" || $keyword == "查快递" || $keyword == "我的代购" || $keyword == "查订单") {
            $fromUsername = $this->getRequest('FromUserName');
            $contentStr = "请点击：
<a href='http://mytaotao123.sinaapp.com/buyer/buyer_wait_request.php?uwid=$fromUsername'>查看我的订单</a>";
            $this->responseText($contentStr);
        }
        if ($keyword == "我要发布" || $keyword == "我要卖" || $keyword == "卖卖卖") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"seller");
            if($out) {
                if($out[0]["level"] == '3') {
                    $this->setMemControl($this->getRequest('FromUserName'),"pic_upload","yes");
                    $this->setMemControl($this->getRequest('FromUserName'),"pic_count",0);
                    $this->deletePicURLArray($this->getRequest('FromUserName'),null);
                    $contentStr = "请上传你要发布的商品图片，最多三张";
                    $this->responseText($contentStr);
                }
            }else {
                    $this->responseText("你还木有权限发布商品，如果已经获得邀请码，请输入“我有邀请码”");
            }
        }
        if ($keyword == "我发布的") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"seller");
            if($out) {
                if($out[0]["level"] == '3') {
                    $where = array("uwid"=>$this->getRequest('FromUserName'));
                    $out = get($where,"pub");
                    if($out) {
                        $items = array();
                        $num = count($out);
                        if(count($out) > 8) {
                            $num = 8;
                        }
                        $temp = new NewsResponseItem("下面是你近期发布的宝贝哦","O(∩_∩)O~","http://www.baobao88.com/bbfile/allimg/091019/1520240.jpg","");
                        array_push($items,$temp);
                        for($i=$num-1; $i >= 0 ; $i--) {
                            $title = $out[$i]['title'];
                            $des = $out[$i]['title'];
                            $blogURL = $out[$i]['blog_url'];
                            $temp = new NewsResponseItem($title,$des,"http://img3.redocn.com/20120324/Redocn_2012032408202885.jpg",$blogURL);
                            array_push($items,$temp);
                        }
                        $this->responseNews($items);
                    }else {
                        $this->responseText("你还木有发布过任何商品");
                    }



                }
            }else {
                $this->responseText("你还木有权限发布商品，如果已经获得邀请码，请输入“我有邀请码”");
            }
        }
        if ($keyword == "我有邀请码") {
            $this->responseText("请输入你获得的16位有效邀请码");
        }
        if ($this->getLastMemTextUserSaid($this->getRequest('FromUserName')) == "我有邀请码") {
            // 验证邀请码是否有效
            $where = array("code"=>$keyword);
            $out = get($where,"yaoqingma");
            if($out) {
                if($out[0]["valid"] == '1') {
                    $fromUsername = $this->getRequest('FromUserName');
                    $contentStr = "你输入的邀请码有效[得意]
<a href='http://mytaotao123.sinaapp.com/sellerReg.php?uwid=$fromUsername&keyword=$keyword'>点击填写该表单</a>
你将成为红领巾==！";
                    $this->responseText($contentStr);
                }else {
                    $this->responseText("你输入的邀请码已经被别人使用");
                }
            }else {
                $this->responseText("你输入的邀请码无效");
            }
        }
        if ($keyword == "啊") {
            if($this->getMemControl($this->getRequest('FromUserName'),"pic_upload")=="yes") {
                $this->setMemControl($this->getRequest('FromUserName'),"pic_upload","no");
                $contentStr = "请点击：<a href='http://mytaotao123.sinaapp.com/sellerUpload.php?uwid=".$this->getRequest('FromUserName')."'>我要发布商品</a>吧[得意]";
                $this->responseText($contentStr);
            }
        }
        if ($keyword == "生成邀请码") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"super");

            if($out[0]["level"] == '3') {
                $invcode = $this->invcode(16,1);
                for($i =0; $i< count($invcode); $i++) {
                    $datetime = date('Y-m-d H:i:s',time());
                    $para = array("code"=>$invcode[$i],"valid"=>'1',"gendate"=>$datetime);
                    insert($para,"yaoqingma");
                }
                $this->responseText("报告大大，我生成了一个最新的邀请码！");
            }else {
                $this->responseText("这个功能只有管理员才能使用哦。");
            }
        }
        if ($keyword == "管理员" || $keyword == "我是砖姐") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"super");
            if($out[0]["level"] == '3') {
                $fromUsername = $this->getRequest('FromUserName');
                $contentStr = "点击<a href='http://mytaotao123.sinaapp.com/admin/admin_wait_request.php?uwid=$fromUsername'>进入管理员页面</a>";
                $this->responseText($contentStr);
            }else {
                $this->responseText("这个功能只有管理员才能使用哦。");
            }
        }
        if ($keyword == "邀请码") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"super");

            if($out[0]["level"] == '3') {
                $where = array("valid"=>'1');
                $out = get($where,"yaoqingma",1);
                if($out) {
                    $this->responseText($out[0]['code']);
                }else {
                    $this->responseText("[流泪]没有请邀请码了，输入“生成邀请码”吧");
                }

            }else {
                $this->responseText("这个功能只有管理员才能使用哦。");
            }
        }
        if ($keyword == "刚才我说了啥") {
            $contentStr = $this->getLastMemTextUserSaid($this->getRequest('FromUserName'));
            $this->responseText($contentStr);
        }
        if ($keyword == "商品管理" || $keyword == "我要发货" || $keyword == "处理订单" || $keyword == "我的发布") {
            $where = array("wechat_id"=>$this->getRequest('FromUserName'));
            $out = get($where,"seller");

            if($out[0]["level"] == '3') {
                $fromUsername = $this->getRequest('FromUserName');
                $contentStr = "请点击：<a href='http://mytaotao123.sinaapp.com/seller_manager_off.php?uwid=$fromUsername'>管理我的商品</a>";
                $this->responseText($contentStr);
            }else {
                $this->responseText("这个功能只有成为红领巾才能使用哦。");
            }

        }
        if ($keyword == "呼叫小助手") {
            $this->responseText('HI！我是红领巾小助手。
回复“1”：货源哪里来？
回复“2”：价格贵不贵？
回复“3”：如何付款？
回复“买买买”：
想买啥，我们帮你买！
回复“我的订单”：
查看要买的、已买的商品，还能查已发货商品的物流跟踪信息噢！');
        }
        if ($keyword == "退款") {
            $this->responseText('代购商品具有特殊性，若非运输破损导致商品无法正常使用，不提供退换。感谢对红领巾的理解和支持噢！');
        }
        if ($keyword == "怎么还不到货" || $keyword == "没收到货" || $keyword == "什么时候到货") {
            $this->responseText('代购商品到货时间通常为付款后的2-4周，不排除物流公司原因造成的延迟到货情况。请耐心等待你的宝贝。');
        }
        if ($keyword == "付款" || $keyword == "支付" || $keyword == "付钱" || $keyword == "付不了钱" || $keyword == "付不了款" || $keyword == "不能付款") {
            $this->responseText('近期微信支付不稳定，建议亲爱的们使用信用卡/储蓄卡进行支付。');
        }
        if ($keyword == "1") {
            $this->responseText('【货源哪里来？】
①靠谱的代购买手
红领巾的代购买手都是就读/工作于世界各地的中国留学生，精通当地语言，非常靠谱哒！
②当地新鲜采购
收到代购需求后，红领巾们会在当地进行采购，必须原滋原味！
③直邮更放心
代购商品大多从海外直接发出，物流跟踪信息可以回复“我的订单”查看哦！');
        }
        if ($keyword == "2") {
            $this->responseText('【价格贵不贵？】
1、赚点小钱，绝不抢钱
红领巾的报价基于当地采购价格及实时汇率，跑腿费要赚，但请记住我们只想好好做生意、不抢钱~
2、邮费不需要操心
红领巾会选择当地靠谱的快递为你寄送商品。为减少你的烦恼，我们不单独罗列邮费——看了总价OK就买吧，妈妈再也不用担心我代购的邮费啦！');
        }
        if ($keyword == "3") {
            $this->responseText('【如何付款？】
支持信用卡、储蓄卡支付等多种支付方式。
近期微信支付不稳定，建议亲爱的们使用信用卡/储蓄卡进行支付。');
        }
        else {
            $url = "http://api.mrtimo.com/Simsimi.ashx?parm=";
            $keyword = $this->getRequest('content');
            $content = file_get_contents ( $url."".$keyword);
            if(strpos($content, "莫队长") !== false){
                $this->responseText('我竟然无言以对！');
            }else {
                $this->responseText('' . $content);
            }

        }

    }

    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage()
    {
        $baiduURL = $this->getRequest('FromUserName').microtime();
        uploadAnImage($this->getRequest('picurl'),$baiduURL);
        $acURL = "http://bcs.duapp.com/honglongjin-service/".$baiduURL.".jpg"; // 图片在百度云存储的真实地址
        if ($this->getMemControl($this->getRequest('FromUserName'),"pic_upload")=="yes"){        // 用户在上传图片状态
            $pic_al_count = $this->getMemControl($this->getRequest('FromUserName'),"pic_count"); // 取得当前已经上传的图片数量
            // 缓存图片地址
            $this->setPicURLArray($this->getRequest('FromUserName'),$acURL);
            // 我尼玛，微信好难测试
            $this->setMemControl($this->getRequest('FromUserName'),"pic_count",$pic_al_count+1); // 事件发生表示已经上传成功+1
            $contentStr = "你已经上传了".($pic_al_count + 1)."张图片[得意]，还可以继续上传".(3-($pic_al_count + 1))."张图片，或者输入“啊”结束上传。"; //啊创意好
            if($pic_al_count + 1 == 3) {                                                         // 达到三张及时改变状态
                $this->setMemControl($this->getRequest('FromUserName'),"pic_upload","no");
                $contentStr = "请点击：<a href='http://mytaotao123.sinaapp.com/sellerUpload.php?uwid=".$this->getRequest('FromUserName')."'>我要发布商品</a>吧[得意]";

            }
            $this->responseText($contentStr);
        }
        $this->responseText("你上传给红领巾小助手一个图片==！");

    }

    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation()
    {
        $this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
    }

    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink()
    {
        $this->responseText('收到了链接：' . $this->getRequest('url'));
    }

    /**
     * 收到语音消息时触发，回复语音识别结果(需要开通语音识别功能)
     *
     * @return void
     */
    protected function onVoice()
    {
        $keyword = $this->getRequest('Recognition');
        if ($keyword == "买买买" || $keyword == "我要买" || $keyword == "下单" ) {
            $fromUsername = $this->getRequest('FromUserName');
            $contentStr = "代购商品请点击：
<a href='http://mytaotao123.sinaapp.com/reg.php?uwid=$fromUsername'>填写购买表单</a>";
            $this->responseText($contentStr);
        }else {
            $url = "http://api.mrtimo.com/Simsimi.ashx?parm=";
            $content = file_get_contents ( $url."".$keyword);
            if(strpos($content, "莫队长") !== false){
                $this->responseText('我竟然无言以对！');
            }else {
                $this->responseText('' . $content);
            }
        }


    }

    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown()
    {
        $this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
    }
    private function memContext($fromUser, $keyword)
    {
        $mmc = memcache_init();
        if ($mmc == false)
            echo "mc init failed\n";
        $log = memcache_get($mmc, $fromUser);
        if ($log) {
            array_push($log, $keyword);
            if (count($log) == 10) {
                array_shift($log);
            }
        } else {
            $log = array($keyword);
        }
        memcache_set($mmc, $fromUser, $log);
    }

    private function getLastMemTextPic($fromUsername) {
        $mmc = memcache_init();
        $log = memcache_get($mmc, $fromUsername);
        $countLast = count($log) - 1;
        $lastWord = $log[$countLast];
        return $lastWord;
    }
    private function getLastMemTextUserSaid($fromUsername) {
        $mmc = memcache_init();
        $log = memcache_get($mmc, $fromUsername);
        if(count($log) == 1) {
            $lastWord = "你啥都没说==！我读书少，请不要骗我";
            return $lastWord;
        }else {
            $countLast = count($log) - 2;
            $lastWord = $log[$countLast];
            return $lastWord;
        }

    }

    private function getLastMemTextUserInput($fromUsername) {
        $mmc = memcache_init();
        $log = memcache_get($mmc, $fromUsername);
        $countLast = count($log) - 1;
        $lastWord = $log[$countLast];
        return $lastWord;
    }

    // 写入控制逻辑
    private function setMemControl($fromUsername,$type,$command) {
        $mmc = memcache_init();
        $log = null;
        $pure = memcache_get($mmc, $fromUsername."_control").'';
        $outcome = unserialize($pure);
        if(count($outcome) > 0) {
            $outcome[$type] = $command;
            $log = serialize($outcome);
        }else {
            $log = serialize(array($type => $command));
        }
        memcache_set($mmc, $fromUsername."_control", $log);

    }

    // 写入图片数组
    private function setPicURLArray($fromUsername,$picURL) {
        $mmc = memcache_init();
        $picArray = memcache_get($mmc, $fromUsername."_pic");
        if(!empty($picArray)) {
            array_push($picArray,$picURL);
        }else {
            $picArray = array($picURL);
        }
        memcache_set($mmc, $fromUsername."_pic", $picArray);
    }
    // 读取图片数组
    private function getPicURLArray($fromUsername) {
        $mmc = memcache_init();
        memcache_get($mmc, $fromUsername."_pic");
    }

    // 清除图片数组
    private function deletePicURLArray($fromUsername) {
        $mmc = memcache_init();
        memcache_set($mmc, $fromUsername."_pic",null);
    }

    // 读取控制逻辑
    private function getMemControl($fromUsername,$type) {
        $mmc = memcache_init();
        $pure = memcache_get($mmc, $fromUsername."_control").'';
        $outcome = unserialize($pure);
        return $outcome[$type];

    }

    private function getCounter() {
        try{
            $c = new SaeCounter();
        }catch(Exception $ex){
            die($ex->getMessage());
        }
        return $c;
    }
    private function invcode($length,$num=1) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $j = 0;
        $code_arr = array();
        while($j<$num){
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= $chars[ mt_rand(0, strlen($chars) - 1) ];
            }
            $code_arr[$j] = $random;
            $j++;
        }
        return $code_arr;
    }



}

$wechat = new MyWechat('FangXiang', TRUE);
$wechat->run();