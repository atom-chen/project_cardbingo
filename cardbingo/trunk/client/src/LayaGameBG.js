


(function()
{
    // 配置
    //var constServerAddr =  "http://10.0.65.92"; // 服务器
    var constServerAddr =  "http://42.159.240.231"; // 服务器
    var constPopOneTime = 400; // 弹出一个球的时间
    var constMaxSpeed = 3; // 最大速度
    // 可选的投注列表
    var constPutList = [
                1000,
                3000,
                6000,
                10000,
                15000,
                20000,
                ];

    // 字体列表，这个要与文件名一致
    var constFontList = [
        ["cardbing_number_award", -4], // 名称，间距
        ["cardbing_number_black", 2],
        ["cardbing_number_blue", 0],
        ["cardbing_number_green", -4],
        ["cardbing_number_white", 1],
    ];             
    var constMaxNum = 90; // 90个数
    var constOuterWidth = 720; // 外围宽度（预留适配用） 
    // 六张卡            
    var pngCardList = [
                    "res/pic/cardbing_img_cad_1.png",
                    "res/pic/cardbing_img_cad_2.png",
                    "res/pic/cardbing_img_cad_3.png",
                    "res/pic/cardbing_img_cad_4.png",
                    "res/pic/cardbing_img_cad_5.png",
                    "res/pic/cardbing_img_cad_6.png",
        ];

    // 引入模块
	var Sprite  = Laya.Sprite;
	var Stage   = Laya.Stage;
	var Texture = Laya.Texture;
	var Browser = Laya.Browser;
	var Handler = Laya.Handler;
	var WebGL   = Laya.WebGL;
    var Loader  = laya.net.Loader;
    var ComboBox = Laya.ComboBox;
 	var Text    = Laya.Text;
    var Image   = Laya.Image;
    var Button  = Laya.Button;
    var BitmapFont = Laya.BitmapFont;
    var ProgressBar  = Laya.ProgressBar;
	var Browser = Laya.Browser;
    var Skeleton  = laya.ani.bone.Skeleton;
	var Templet   = laya.ani.bone.Templet;
    var TimeLine = laya.utils.TimeLine;
	var Ease    = Laya.Ease;
	var Tween   = Laya.Tween;
	var Event       = laya.events.Event;
    var HttpRequest = laya.net.HttpRequest;
    var SoundManager = Laya.SoundManager;

    // 成员变量
    var m_speed = 1;    // 动画速度  
    var m_uiNumList = []; // 90个数字控件
    var m_uiNumBlackList = []; // 90个数字黑底
    var m_numList = []; // 90个数字, 分6组，每组15个
    var m_popList = []; // 弹出数字列表
    var m_winCard = 1;  // 赢得哪张卡
    var m_popIdx = 0;   // 当前弹出第几个
    var m_bonus_tplt = []; // xml表 {from=区间, to=区间, mul=赔率}
    var m_money = 0;    // 还有多少金额
    var m_bMusic = true; // 是否播放背景音乐
    var m_bSound = true; // 是否播放音效
    var m_bCanClick = true; // 是否可点击(所有按钮统一处理)
    var m_cacheAwardText = ""; // 辅助音乐播放用
    var m_awardIndex = 1; // 第几档奖金
    // 成员控件
    var m_rootNode;     // 根节点
    var m_progressBar;  // 进度条
    var m_progressBarPosX; // 进度条x坐标(反向)
    var m_uiProText;    // 当前显示第几个数字
    var m_btnSure;      // 确定按钮
    var m_btnSpeed;     // 速度按钮
    var m_btnRand;      // 换一组按钮
    var m_btnOpt;       // 设置按钮
    var m_btnSelect;    // 投注选择按钮
    var m_uiPopNum;     // 弹出数字
    var m_uiBall;       // 球
    var m_uiBallEffect; // 球光晕
    var m_uiCardList;   // 6张卡
    var m_uiCoin;       // 当前金额控件
    var m_uiPutMoney;   // 投注金额控件
    var m_uiAwardNum;   // 奖励金额控件
    var m_uiAwardNode;  // 奖励控件节点
    var m_uiTitle;      // 标题栏

    (function()
	{
        var wDivH = Browser.width/Browser.height;
        var showWidth = 720; // 屏幕宽度540~720之间
        if (wDivH <= 540/960)
        {
            showWidth = 540;
        }
        else if (wDivH >= constOuterWidth/960)
        {
            showWidth = constOuterWidth;
        }
        else
        {
            showWidth = 960 * wDivH;
        }

        // 屏幕适配        
		Laya.init(showWidth, 960, WebGL);

		Laya.stage.alignV = Stage.ALIGN_MIDDLE;
		Laya.stage.alignH = Stage.ALIGN_CENTER;

		Laya.stage.scaleMode = "showall";
		Laya.stage.bgColor = "#000000";

        // 根节点
        m_rootNode = new Sprite();
        Laya.stage.addChild(m_rootNode);
        m_rootNode.pos(-(constOuterWidth - showWidth)/2, 0);

        // 播放背景音乐
        playMusic("res/audio/bgm_cardbing.mp3");

        // 1.先加载字体，2.接着加载其他资源，3.最后开始游戏
        loadFonts();
	})();

    // 资源加载
    function loadAsserts() 
    {
        var assets = [
                    "res/pic/cardbing_btn_bet.png",
                    "res/pic/cardbing_btn_change.png",
                    "res/pic/cardbing_btn_speed_cofig.png",
                    "res/pic/cardbing_btn_speed_off.png",
                    "res/pic/cardbing_btn_speed_on.png",
                    "res/pic/cardbing_btn_star.png",
                    "res/pic/cardbing_img_back_01.png",
                    "res/pic/cardbing_img_back_02.png",
                    "res/pic/cardbing_img_back_04.png",
                    "res/pic/cardbing_img_ball_1.png",
                    "res/pic/cardbing_img_ball_2.png",
                    "res/pic/cardbing_img_ball_3.png",
                    "res/pic/cardbing_img_ball_4.png",
                    "res/pic/cardbing_img_ball_5.png",
                    "res/pic/cardbing_img_ball_6.png",
                    "res/pic/cardbing_img_cad_1.png",
                    "res/pic/cardbing_img_cad_2.png",
                    "res/pic/cardbing_img_cad_3.png",
                    "res/pic/cardbing_img_cad_4.png",
                    "res/pic/cardbing_img_cad_5.png",
                    "res/pic/cardbing_img_cad_6.png",
                    "res/pic/progressBar.png",
                    "res/pic/progressBar_1.png",
                    "res/pic/progressBar_2.png",
                    "res/pic/progressBar_bk.png",
                    "res/pic/cardbing_word_jj.png",
                    "res/pic/cardbing_word_title.png",
                    "res/pic/cardbing_word_yy.png",
                    "res/pic/cardbing_img_bg.png",
                    "res/pic/cardbing_btn_bet_confirm.png",
                    "res/pic/cardbing_img_bet_view.png",
                    "res/pic/cardbing_btn_bet_view.png",
                    "res/pic/cardbing_img_bet_view_back.png",
                    "res/pic/cardbing_img_bet_select.png",
                    "res/pic/cardbing_btn_bet_select_0.png",
                    "res/pic/cardbing_btn_bet_select_1.png",
                    "res/pic/cardbing_img_bet_set.png",
                    "res/pic/cardbing_btn_bet_close.png",
                    "res/pic/cardbing_btn_bet_bgm_0.png",
                    "res/pic/cardbing_btn_bet_bgm_1.png",
                    "res/pic/cardbing_btn_bet_sound_0.png",
                    "res/pic/cardbing_btn_bet_sound_1.png",
                    "res/pic/cardbing_img_bet_award_complet.png",
                    "res/pic/cardbing_word_sjwcjl.png",
                    "res/pic/cardbing_word_sd.png",
                    "res/pic/fk_guang.png",
                    "res/pic/cardbing_word_qs.png",
                    "res/pic/black_grid1.png",
                    "res/pic/black_grid2.png",
                    "res/pic/black_grid3.png",
                    "res/pic/black_grid4.png",
                    "res/pic/black_grid5.png",
                    "res/pic/black_grid6.png",
                    "res/pic/black_grid_light.png",
                    "res/pic/cardbing_cutline_01.png",
                    "res/pic/cardbing_cutline_02.png",
                    "res/pic/cardbing_cutline_03.png",                    
                    ];

        // 加载资源后开始游戏
		Laya.loader.load(assets, Handler.create(this, reqLoginData));
    }

    // 加载字体
	function loadFonts()
	{
        var idx = 0;
		var bitmapFont = new BitmapFont();
        var fnName = constFontList[idx][0];
		bitmapFont.loadFont("res/bitmapFont/" + fnName + ".fnt", 
                        new Handler(this, onFontLoaded, [bitmapFont, fnName, idx]));
	}

    // 字体完成加载
	function onFontLoaded(bitmapFont, fntName, idx)
	{
        bitmapFont.letterSpacing = constFontList[idx][1];
		Text.registerBitmapFont(fntName, bitmapFont);
        ++idx;    
        if (idx >= constFontList.length)
        {
            // 全部加载完, 加载其他资源
            loadAsserts(); 
            return; 
        }

        // 下一个字体
		bitmapFont = new BitmapFont();
        var fnName = constFontList[idx][0];
		bitmapFont.loadFont("res/bitmapFont/" + fnName + ".fnt", 
                        new Handler(this, onFontLoaded, [bitmapFont, fnName, idx]));
	}

    // 请求登陆数据，后显示开场动画
    function reqLoginData() 
    {
        reqTplt(startGame);
    }

    // 显示开场动画
    function showStartGameAni()
    {
        // 中间那个球要隐藏
        m_uiBall.alpha = 0;
        m_uiPopNum.visible = false;
        // 6张卡也要隐藏 
        for (var i = 0; i < m_uiCardList.length; ++i)
            m_uiCardList[i].visible = false;

        enableButtons(false);

        createAni("res/effect/faka.sk", m_rootNode,
                    constOuterWidth/2, Laya.stage.height/2,
                    function(ani) { // 动画播完要做的事
                        // 删除动画
                        ani.removeSelf();
                    });

        // 最后一段动画，回调太晚，只能用定时器衔接            
        Laya.timer.once(2500/m_speed, this, function(){
                // 隐藏所有数字    
                showAllNums(false);

                // 6张卡也要显示 
                for (var i = 0; i < m_uiCardList.length; ++i)
                {
                    var uiCard = m_uiCardList[i];
                    uiCard.visible = true;

                    // 翻牌动画 
                    skewOneCardAni(uiCard);                      
                }
        });
    }

    // 开始游戏
	function startGame()
	{
        // 背景图
        createBG();

        // 顶部信息
        createTopInfo();

        // 弹出数字信息
        createPopNumInfo();
        // 奖励信息
        createAwardInfo();

        // 进度信息
        createProgressInfo();

        // 6个卡片
        create6Card();
        
        // 各种按钮
        createButtons();

        // 所有数字随机
        randAllNums();

        // 显示开场动画
        showStartGameAni();                
	}

    function enableButtons(bVal)
    {
        m_bCanClick = bVal;
    }

    // 播放音效
    function playSound(res, fnFinish)
    {
        if (!fnFinish)
            fnFinish = function(){};
        if (m_bSound)
            SoundManager.playSound(res, 1, Handler.create(this, fnFinish));
    }

    // 播放背景音乐
    function playMusic(res)
    {
        if (m_bMusic)
            SoundManager.playMusic(res, 0);
    }

    // 点击速度
    function onClick_btnSpeed()
    {
       console.log("点击速度");         
       if (m_speed > 1)
       {
           m_speed = 1;
           m_btnSpeed.loadImage("res/pic/cardbing_btn_speed_on.png");
       }
       else
       {
           m_speed = constMaxSpeed;
           m_btnSpeed.loadImage("res/pic/cardbing_btn_speed_off.png");
       }   
    }

    // 点击随机
    function onClick_btnRand()
    {
        if (!m_bCanClick)
            return;

       console.log("点击随机");
       randAllNums();     
    }

    // 点击设置
    function onClick_btnOpt()
    {
       console.log("点击设置");
       showOptDialog();     
    }

    // 点击押注
    function onClick_btnSelect()
    {
        if (!m_bCanClick)
            return;

       console.log("点击押注");     
       showSelectDialog();     
    }

    // 点击确定
    function onClick_btnSure()
    {
        if (!m_bCanClick)
            return;
        console.log("点击确定");

        // 判断金钱不足
        if (m_money < parseFloat(m_uiPutMoney.text))
        {
            showError("金额不足!");
            return;
        }
        playSound("res/audio/se_cardbing_start.mp3");
        // 按钮禁止点击
        enableButtons(false);

        // 最后画出这些数字    
        for (var i = 0; i < constMaxNum; ++i)
        {
            m_uiNumBlackList[i].visible = true;
            m_uiNumList[i].visible = true;
        }    


        // 发送请求给服务器 10.0.65.92

        var obj = {};
        obj.card1 = m_numList.slice(0, 15);
        obj.card2 = m_numList.slice(15, 30);
        obj.card3 = m_numList.slice(30, 45);
        obj.card4 = m_numList.slice(45, 60);
        obj.card5 = m_numList.slice(60, 75);
        obj.card6 = m_numList.slice(75, 90);
        var jsonStr = JSON.stringify(obj);
        //console.log(jsonStr);
        httpGet("method=Account.start&card=" + jsonStr+ "&bet="+m_uiPutMoney.text, function(data) {
            m_popList = []
            console.log("收到数据: " + data);
            
            var resp = JSON.parse(data);

            if (resp.code == 0)
            {
                m_popList = resp.result;
                //m_popList.reverse();
                // 显示结算
                showResult();
            }
            else
            {
                // 错误处理
                showError("Account.start返回错误 " + resp.code);
            }
        });
    }

    // 请求xml数据
    function reqTplt(fnCallback) 
    {
        httpGet("method=Server.get_bonus_tplt", function(data) {
            console.log("收到reqTplt数据: " + data);

            var resp = JSON.parse(data);

            if (resp.code == 0)
            {
                // 赔率表
                // key： 档次id 
                // val： 生效范围 赔率
                m_bonus_tplt = [];    
                for (k in resp.result)
                {
                    var abc = resp.result[k];
                    var record = {from: parseInt(abc[0]), 
                                to: parseInt(abc[1]),
                                mul: parseFloat(abc[2])};
                    m_bonus_tplt.push(record);  
                }

                // 玩家金额(服务端没给，客户端自己模拟)
                m_money = 1000000;

                //console.log(JSON.stringify(m_bonus_tplt));    
                fnCallback();
            }
            else
            {
                // 错误处理
                showError("Server.get_bonus_tplt返回错误 " + resp.code);
            }
       });
    }

    // 发送请求
	function httpGet(req, fnCallback)
	{
        var url = constServerAddr + "/index.php?";
        url = url + req;
        console.log("httpSend " + url);
		hr = new HttpRequest();
		hr.once(Event.PROGRESS, this, onHttpRequestProgress);
		hr.once(Event.COMPLETE, this, function(){ fnCallback(hr.data) });
		hr.once(Event.ERROR, this, onHttpRequestError);
		hr.send(url, null, 'get', 'text') //, ["Access-Control-Allow-Origin", "http://10.0.65.92:80", "Access-Control-Allow-Methods", "POST, GET, OPTIONS"]);
        
	}

    function onHttpRequestError(e)
	{
        showError("httpGet请求错误");
		console.log(e);
	}

	function onHttpRequestProgress(e)
	{
		console.log(e);
	}

    // 奖励信息
    function createAwardInfo()
    {
        var uiParent = new Sprite();
        m_rootNode.addChild(uiParent);
        m_uiAwardNode = uiParent;        
        m_uiAwardNode.pos(m_progressBarPosX-10, 495);

        // 奖励文字
        var uiAwardText = new Sprite();
        uiAwardText.loadImage("res/pic/cardbing_word_jj.png");
        uiParent.addChild(uiAwardText);

        // 奖励金额
        var uiAwardNum = createAwardNum(getFirstAwardNum());
        m_uiAwardNum = uiAwardNum;
        uiAwardNum.pos(57, 15);
        uiParent.addChild(uiAwardNum);        
    }
    
    // 弹出数字信息
    function createPopNumInfo()
    {
        // 球
        var uiBall = new Sprite();
        m_uiBall = uiBall;
        uiBall.loadImage("res/pic/cardbing_img_ball_1.png")
        uiBall.pivot(uiBall.width/2, uiBall.height/2);
		uiBall.pos(constOuterWidth/2, 375);
        m_rootNode.addChild(uiBall);                    
        // 弹出数字
        var uiPopNum = new Text();
        m_uiPopNum = uiPopNum; 
        uiPopNum.text = "00";
		uiPopNum.font = "cardbing_number_black";
        uiPopNum.pivot(uiPopNum.width/2, uiPopNum.height/2);
		uiPopNum.pos(constOuterWidth/2, 375);
        m_rootNode.addChild(uiPopNum);

        // 光晕节点
        m_uiBallEffect = new Sprite();
		m_uiBallEffect.pos(constOuterWidth/2, 375);
        m_rootNode.addChild(m_uiBallEffect);                                                
    }

    // 进度信息
    function createProgressInfo()
    {
        var uiProgressInfo = new Sprite();
        uiProgressInfo.pos(90, 420)
        m_rootNode.addChild(uiProgressInfo);

        // 进度数字
        var uiProText = new Text();
        m_uiProText = uiProText; 
        uiProText.text = formatNumStr(0);
        uiProText.font = "cardbing_number_award";
        uiProText.pos(412, 50);
        uiProgressInfo.addChild(uiProText);
        
        // 进度条黑底
        var progressBarBK = new Sprite();
        progressBarBK.loadImage("res/pic/progressBar_bk.png");
        m_rootNode.addChild(progressBarBK);
        progressBarBK.pos(100, 470);

        // 进度条
        var progressBar = new Sprite();
        m_progressBar = progressBar;
		m_rootNode.addChild(progressBar);
        m_progressBarPosX = 100+2; // 反向进度条比较麻烦，需要多记一个值  
        progressBar.pos(m_progressBarPosX, 470+2);
        progressBar.loadImage("res/pic/progressBar.png");
        setProgress(0, m_progressBar);

        // 刻度
        //cardbing_cutline_03
        for (var i = 1; i < m_bonus_tplt.length; ++i)
        {
            var v = m_bonus_tplt[i];
            var uiLine = null;
            if (1 > i)
                uiLine = new Image("res/pic/cardbing_cutline_01.png");
            else if (1 <= i && i <= 3)
                uiLine = new Image("res/pic/cardbing_cutline_02.png");
            else    
                uiLine = new Image("res/pic/cardbing_cutline_03.png");
            uiLine.pivot(uiLine.width/2, uiLine.height);
            uiLine.pos(m_progressBarPosX + v.from/85*progressBar.width, 494)
            m_rootNode.addChild(uiLine);
            console.log(i, v.from/85*progressBar.width)
        }
    }

    // 设置进度条
    function setProgress(value, progressBar) 
    {
        var texture = null;

        if (1 <= m_awardIndex && m_awardIndex <= 3)
        {
        	texture = Laya.loader.getRes("res/pic/progressBar.png");
        }
        else if (4 <= m_awardIndex && m_awardIndex <= 5)
        {
        	texture = Laya.loader.getRes("res/pic/progressBar_1.png");
        }
        else
        { 
        	texture = Laya.loader.getRes("res/pic/progressBar_2.png");
        }
        
        var w1 = texture.width*value;
        var w2 = texture.width - w1; 
        var h = texture.height;
        progressBar.graphics.clear();
        var t = Laya.Texture.createFromTexture(texture, w1, 0, 
                                                        w2, h);
        progressBar.graphics.drawTexture(t, 0, 0, w2, h);
        progressBar.x = m_progressBarPosX + w1;
        // 奖励信息也跟着动  
        m_uiAwardNode.x = progressBar.x - 10 - 21;
        if (m_uiAwardNode.x < m_progressBarPosX - 10) // 为界面美观，做个保底值
            m_uiAwardNode.x = m_progressBarPosX - 10
    }

    // 顶部信息
    function createTopInfo()
    {
        var uiTitle = new Sprite();
        m_uiTitle = uiTitle;
        m_rootNode.addChild(uiTitle);
        // 文字
        var uiOwnTxt = new Sprite();        
        uiOwnTxt.loadImage("res/pic/cardbing_word_yy.png");
        uiOwnTxt.text = "拥有";
		uiOwnTxt.pos(110, 6);
        uiTitle.addChild(uiOwnTxt);
        
        // 玩家货币
        var uiCoin = new Text(); 
        m_uiCoin = uiCoin;
		uiCoin.text = m_money;
        uiCoin.font = "cardbing_number_white";
        uiCoin.scale(1.2, 1.2);     
        uiCoin.pivot(0, uiCoin.height/2);   
        uiCoin.pos(190, 21);
        uiTitle.addChild(uiCoin);
    } 

    // 各种按钮
    function createButtons() 
    {
        // 确定按钮
        var btnSure = createScaleButton("res/pic/cardbing_btn_star.png");
        m_btnSure = btnSure;
        btnSure.pos(constOuterWidth/2, 852);
        m_rootNode.addChild(btnSure);
        btnSure.on(Event.CLICK, this, onClick_btnSure);

        // 换一组按钮
        var btnRand = createScaleButton("res/pic/cardbing_btn_change.png");
        m_btnRand = btnRand;
        btnRand.pos(constOuterWidth/2+180, 823);
        m_rootNode.addChild(btnRand);
        btnRand.on(Event.CLICK, this, onClick_btnRand);

        // 加速按钮
        var btnSpeed = createScaleButton("res/pic/cardbing_btn_speed_on.png", true);
        m_btnSpeed = btnSpeed;
        btnSpeed.pos(constOuterWidth/2+195, 883);
        m_rootNode.addChild(btnSpeed);
        btnSpeed.on(Event.CLICK, this, onClick_btnSpeed);

        // 投注选择
        var btnSelect = createScaleButton("res/pic/cardbing_btn_bet.png");
        m_btnSelect = btnSelect;
        btnSelect.pos(constOuterWidth/2-185-7, 852);
        m_rootNode.addChild(btnSelect);
        btnSelect.on(Event.CLICK, this, onClick_btnSelect);
        // 投注金额
        var uiPutMoney = new Text();
        m_uiPutMoney = uiPutMoney;
        //uiPutMoney.scale(0.8, 0.8);
        uiPutMoney.font = "cardbing_number_green";
        uiPutMoney.text = constPutList[0]; 
        uiPutMoney.pivot(uiPutMoney.width, uiPutMoney.height/2);
        uiPutMoney.pos(constOuterWidth/2-170-7, 855);
        m_rootNode.addChild(uiPutMoney);

        // 设置按钮
        var btnOpt = createScaleButton("res/pic/cardbing_btn_speed_cofig.png", true);
        m_btnOpt = btnOpt;
        btnOpt.pos(constOuterWidth - 132, btnOpt.height/2);
        m_rootNode.addChild(btnOpt);
        btnOpt.on(Event.CLICK, this, onClick_btnOpt);
    }

    // 点下去会缩小的按钮
    function createScaleButton(skin, canClickAllTheTime) 
    {
        var btn = new Sprite();
        btn.loadImage(skin);
        btn.pivot(btn.width/2, btn.height/2);
        if (canClickAllTheTime)
        {
            btn.on(Event.MOUSE_DOWN, this, function() 
                                {
                                        btn.scale(0.95, 0.95); 
                                });
        }
        else
        {
            btn.on(Event.MOUSE_DOWN, this, function() 
                                {
                                    if (m_bCanClick)
                                        btn.scale(0.95, 0.95); 
                                });
        }
        btn.on(Event.MOUSE_UP, this, function() 
                            {
                               btn.scale(1, 1);                                 
                            });
        btn.on(Event.MOUSE_OUT, this, function() 
                            {
                               btn.scale(1, 1);                                 
                            });

        return btn;
    }

    // 背景图
	function createBG()
    {
        // 背景图
        var bg = new Sprite();
        //bg.loadImage("res/pic/bg.png");
        bg.loadImage("res/pic/cardbing_img_bg.png");
        m_rootNode.addChild(bg);

        // 游戏名
        var gameName = new Sprite();
        gameName.loadImage("res/pic/cardbing_word_title.png");
        m_rootNode.addChild(gameName);
        gameName.pos(186, 245)

        // 开奖箱子
        var box = new Sprite();
        box.loadImage("res/pic/cardbing_img_back_04.png");
        m_rootNode.addChild(box);
        box.pos(-20, 407);

        // 上面灰条背景
        var topBG = new Image("res/pic/cardbing_img_back_01.png");         
        topBG.size(constOuterWidth, 40);
        m_rootNode.addChild(topBG);        
        topBG.pos(0, 0);        
        
        // 中间灰条背景
        var midBG = new Image("res/pic/cardbing_img_back_02.png");         
        midBG.size(constOuterWidth, 103);
        m_rootNode.addChild(midBG);        
        midBG.pivot(midBG.width/2, midBG.height/2);
        midBG.pos(constOuterWidth/2, 477+10);        
        midBG.on("click", this, showDescDialog) // 点击显示说明窗 

        // 球数文字        
        var uiQiuShu = new Image("res/pic/cardbing_word_qs.png");         
        m_rootNode.addChild(uiQiuShu);        
        uiQiuShu.pos(constOuterWidth-223, 438);        
        
        // 说明按钮
        var btnDesc = createScaleButton("res/pic/cardbing_btn_bet_view.png");
        m_rootNode.addChild(btnDesc);
        btnDesc.pos(constOuterWidth-130, 463);        
        btnDesc.on("click", this, showDescDialog) // 点击显示说明窗 

    }

    // 90个数字随机
    function randAllNums()
    {
        // 先放入90个数字
        m_numList = [];
        for (var i = 1; i <= constMaxNum; ++i)
        {
            m_numList.push(i);            
        }

        console.log(JSON.stringify(m_numList.slice(10, 20)));

        // 接着随机交换数字
        for (var i = 0; i < constMaxNum; ++i)
        {
            var idx = randInt(constMaxNum);
            var tmp = m_numList[i];
            m_numList[i] = m_numList[idx];
            m_numList[idx] = tmp;
        }

        // 最后画出这些数字    
        for (var i = 0; i < constMaxNum; ++i)
        {
            m_uiNumBlackList[i].visible = true;
            m_uiNumList[i].visible = true;
            m_uiNumList[i].text = formatNumStr(m_numList[i]);            
        }    
    }

    // 6个卡片
    function create6Card() 
    {
        // 6个位置
        var cardPosList = [{x:98, y:50}, {x:286, y:50}, {x:474, y:50}, 
                            {x:98, y:547}, {x:286, y:547}, {x:474, y:547}]; 
        m_uiCardList = [];

        for (var i = 0; i < cardPosList.length; ++i) 
        {
            var uiCard = createOneCard(i);
            m_uiCardList.push(uiCard);
            m_rootNode.addChild(uiCard);
            var pt = cardPosList[i];
            uiCard.pos(pt.x+uiCard.width/2, pt.y+uiCard.height/2);
        }        
    }

    // 区域(包含多个数字)
    function createOneCard(i)
    {
        var pngGridList = [
                    "res/pic/black_grid1.png",
                    "res/pic/black_grid2.png",
                    "res/pic/black_grid3.png",
                    "res/pic/black_grid4.png",
                    "res/pic/black_grid5.png",
                    "res/pic/black_grid6.png",
        ];
        
        var n = i * 15;
        var parent = new Image(pngCardList[i]);
        parent.pivot(parent.width/2, parent.height/2);
        for (var y = 0; y < 5; ++y)
        {   
            for (var x = 0; x < 3; ++x)
            {
                ++n;

                var uiBack = new Sprite();
                uiBack.loadImage(pngGridList[i]);    
                
                parent.addChild(uiBack);
                uiBack.pos(x*43+8, y*43+4);  
                m_uiNumBlackList.push(uiBack);

                var uiNum = createNum(n);                      
                parent.addChild(uiNum);
                uiNum.pos(x*43+16, y*43+20);  
                m_uiNumList.push(uiNum);
            }
        }            
        return parent;
    }

    // 格式化数字
    function formatNumStr(iNum)
    {
        var s = "" + iNum;
        if (s.length < 2)
            s = "0" + s;    
        return s;
    }

    // 单个数字
    function createNum(iNum)
    {   
        var ui = new Text();
		ui.text = formatNumStr(iNum);
        ui.font = "cardbing_number_white";
        //ui.pivot(ui.width/2, ui.height/2);
		return ui;
    }

    // 模仿C语言的随机数
    function randInt(iMax)
    {
        return Math.floor(Math.random() * 10000000) % iMax;        
    }

    // 第一笔奖金
    function getFirstAwardNum()
    {
        var v = m_bonus_tplt[0];
        return v.mul * constPutList[0]/10;
    }

    // 显示结果, 用动画逐个弹出数字
    function showResult() 
    {
        // 扣钱表现
        m_money -= parseFloat(m_uiPutMoney.text);
        showMoneyChangeAni();// 播放金钱变化

        // 算出哪张卡赢了(服务端没推)        
        m_winCard = calcWinCard();
        console.log("获胜卡:" + m_winCard);
        m_winMoney = calcAwardMoney(m_popList.length);        
        console.log("奖励金额:" + m_winMoney);        

        m_popIdx = 0; // 当前弹出第几个

        // 奖金显示最大的那个
        m_uiAwardNum.text = calcAwardMoney(1);
        m_cacheAwardText = m_uiAwardNum.text; // 缓存播放音乐用

        // 播放球浮出动画, 然后才逐个数字显示
        showBallFlyOut(showOneNum);
    }

    function showBallFlyOut(fnCallback)
    {
        var top = m_popList[m_popIdx];
        var colorIdx = getBallColorIndex(top);
        // 球颜色变化
        m_uiBall.loadImage("res/pic/cardbing_img_ball_" + colorIdx + ".png");
        // 先放到下方，等会儿飞上来
        m_uiBall.pos(constOuterWidth/2, 375+200);
        m_uiBall.scale(0.3, 0.3);
        
        Tween.to(m_uiBall, {alpha:1, y:375, scaleX:1, scaleY:1}, 120/m_speed, null, Handler.create(this, function(){
            // 中间那个数字要显示
            m_uiPopNum.visible = true;
            fnCallback(); // 逐个显示数字
        }));
    }

    // 算出哪张卡赢了(服务端没推)
    function calcWinCard()
    {
        for (var iCard = 0; iCard < 6; ++iCard)
        {
            var n = 0;
            for (var i = 0; i < 15; ++i) // 每张卡15个数字
            {
                var v = m_numList[iCard*15 + i];
                if (m_popList.indexOf(v) >= 0)
                    ++n;        
            }
            if (n == 15)
                return iCard + 1;
        }
        return null;
    }

    // 算奖励第几档
    // 参数count: 球数
    function calcAwardIndex(count)
    {
        var base = parseFloat(m_uiPutMoney.text)
        var i = 1;
        for (k in m_bonus_tplt)
        {
            var v = m_bonus_tplt[k];
            if (v.from <= count && count <= v.to)
            {
                return i;
            }
            i = i + 1;        
        }
        // 默认第一笔
        return 1;
    }

    // 算奖励金额
    // 参数count: 球数
    function calcAwardMoney(count)
    {
        var base = parseFloat(m_uiPutMoney.text)
        for (k in m_bonus_tplt)
        {
            var v = m_bonus_tplt[k];
            if (v.from <= count && count <= v.to)
            {
                return v.mul * base/10;
            }        
        }
        // 默认第一笔
        var v = m_bonus_tplt[0];
        return v.mul * base/10;
    }

    function getBallColorIndex(num)
    {
        var idx = m_numList.indexOf(num);
        return Math.floor(idx / 15)+1;
    }

    // 逐个数字弹出显示
    function showOneNum() 
    {
        if (m_popList.length <= m_popIdx)
        {
            m_money += m_winMoney;
            // 最后一个数字播完要隐藏
            Laya.timer.once(1000/m_speed, this, function(){
                // 中间那个球要隐藏
                m_uiBall.alpha = 0;
                m_uiPopNum.visible = false;
            });
            // 停止音乐
            SoundManager.stopMusic();
            playSound("res/audio/se_cardbing_complete.mp3", function(){
                // 播放背景音乐
                playMusic("res/audio/bgm_cardbing.mp3");    
            });
            // 播放胜利动画
            showWinAni();
            return;
        }

        playSound("res/audio/se_cardbing_call.mp3");
        // 光晕特效
        createAni("res/effect/faguang.sk", m_uiBallEffect, 0, 0,                    
                    function(ani) { // 动画播完要做的事
                        // 删除动画
                        ani.removeSelf();
                    }
                    );

        var top = m_popList[m_popIdx];
        ++m_popIdx;
        var colorIdx = getBallColorIndex(top);
        // 球颜色变化
        m_uiBall.loadImage("res/pic/cardbing_img_ball_" + colorIdx + ".png");

        // 奖金随进度变化
        m_uiAwardNum.text = calcAwardMoney(m_popIdx);
        m_awardIndex = calcAwardIndex(m_popIdx); // 第几档
        if (m_cacheAwardText != m_uiAwardNum.text)
        {
            playSound("res/audio/se_cardbing_low.mp3");
            m_cacheAwardText = m_uiAwardNum.text; // 缓存播放音乐用
        }

        m_uiProText.text = formatNumStr(m_popIdx);

        var idx = m_numList.indexOf(top);
        // 进度条
        setProgress(m_popIdx/85, m_progressBar);

        // 开格子特效
        var gridLight = new Image("res/pic/black_grid_light.png");
        gridLight.alpha = 0;
        var uiCard = m_uiCardList[colorIdx-1];
        uiCard.addChild(gridLight);
        var uiBack = m_uiNumBlackList[idx];
        gridLight.pos(uiBack.x-12, uiBack.y-12); // 图片没切好, 先这样对齐 

        Tween.to(gridLight, {alpha:1}, 160/m_speed, null, Handler.create(this, function(){
            m_uiNumList[idx].visible = false;
            m_uiNumBlackList[idx].visible = false;
            Tween.to(gridLight, {alpha:0}, 120/m_speed, null, Handler.create(this, function(){
                                        gridLight.removeSelf();
                                    }), 0);
        }));
        
        m_uiPopNum.text = formatNumStr(top);
        // 数字变大又变小特效
        Tween.to(m_uiPopNum, {scaleX:1.79, scaleY:1.79}, 80/m_speed, null, Handler.create(this, function(){
            Tween.to(m_uiPopNum, {scaleX:1, scaleY:1}, 80/m_speed, null, Handler.create(this, function(){
                                    }));
        }));

        Laya.timer.once(constPopOneTime/m_speed, this, showOneNum);
    }

    // 创建对话框
    function createDialogBase()
    {
        // 背景
        var bg = new Image("res/pic/cardbing_img_back_02.png");
        bg.size(constOuterWidth, Laya.stage.height);
        m_rootNode.addChild(bg);
        bg.on("click", this, function(){ }) // 屏蔽点击事件

        return bg;
    }

    // 押注选择窗
    function showSelectDialog()
    {
        // 背景
        var bg = new Sprite();
        bg.size(constOuterWidth, Laya.stage.height);
        m_rootNode.addChild(bg);
        bg.on("click", this, function(){ bg.removeSelf() }); // 屏蔽点击事件

        // 框
        var frame = new Image("res/pic/cardbing_img_bet_select.png");
        frame.sizeGrid = "10,10,30,10,0";
        frame.height = constPutList.length * 73 + 45;
        frame.pivot(0, frame.height);
        frame.pos(95, 820);
        bg.addChild(frame);

        // 各种按钮
        for (var i = 0; i < constPutList.length; ++i)
        {
            var v = constPutList[constPutList.length-1-i];
            var uiText = new Text();
            uiText.text = v;
            uiText.pivot(uiText.width/2, uiText.height/2);
            uiText.pos(40, 23);
            var btn;
            if (parseInt(m_uiPutMoney.text) == v)
            { 
                btn = createScaleButton("res/pic/cardbing_btn_bet_select_1.png");
                uiText.font = "cardbing_number_award";
                uiText.scale(0.8, 0.8);
            }
            else
            {
                btn = createScaleButton("res/pic/cardbing_btn_bet_select_0.png");                
                uiText.font = "cardbing_number_blue";
                uiText.scale(0.8, 0.8);
            }
            
            btn.pos(frame.width/2, 52 + i*73);

            btn.on("click", this, function(iNum){ 
                                        m_uiPutMoney.text = iNum;
                                        m_uiAwardNum.text = calcAwardMoney(1);
                                    }, [v]);             
            frame.addChild(btn);

            btn.addChild(uiText);
        }
        return bg;
    }

    // 顶部金额改变动画
    function showMoneyChangeAni()
    {
        // 旧金额         
        var uiCoinOld = m_uiCoin; 

        // 新金额
        var uiCoin = new Text(); 
        m_uiCoin = uiCoin;
		uiCoin.text = m_money;
        uiCoin.font = "cardbing_number_white";
        uiCoin.pivot(0, uiCoin.height/2);   
        uiCoin.pos(uiCoinOld.x, uiCoinOld.y);
        m_uiTitle.addChild(uiCoin);
        uiCoin.alpha = 0;
        var timeLine = new TimeLine();
        timeLine.addLabel("money1",0).to(uiCoinOld,{alpha:0, scaleX:2, scaleY:2},120,null,0)
                .addLabel("money2",0).to(uiCoin,{alpha:1, scaleX:2, scaleY:2},120,null,-120)
                .addLabel("money3",0).to(uiCoin,{scaleX:1, scaleY:1},280,null,0)
        timeLine.scale = 1//m_speed; // 播放速度        
        timeLine.play(0, false);
    }

    // 播放胜利动画
    function showWinAni()
    {
        // 背景
        var bg = new Sprite()
        bg.size(constOuterWidth, Laya.stage.height);
        m_rootNode.addChild(bg);
        bg.on("click", this, function(){ }); // 屏蔽点击事件

        // 框
        var frame = new Image("res/pic/cardbing_img_back_01.png");
        frame.size(constOuterWidth, 286);
        frame.pivot(0, frame.height/2);
        frame.pos(0, Laya.stage.height/2);
        bg.addChild(frame);
        frame.alpha = 0;

        // 卡牌
        var card = new Image(pngCardList[m_winCard-1]);
        card.pivot(card.width, card.height);
        card.pos(0+card.width, 0+card.height);
        bg.addChild(card);
        
        // 获胜卡
        var winCard = m_uiCardList[m_winCard-1];
        card.pos(winCard.x+card.width/2, winCard.y+card.height/2);
        winCard.visible = false;

        // 矩形光
        var light = new Image("res/pic/fk_guang.png");
        light.size(card.width+5, card.height+10);
        light.pos(-2, -10);
        light.alpha = 0;
        card.addChild(light);

        var winNode = new Sprite();
        winNode.pos(constOuterWidth/2, Laya.stage.height/2);
        bg.addChild(winNode);
        // 平行线
        var uiComlet = new Image("res/pic/cardbing_img_bet_award_complet.png");
        uiComlet.pivot(uiComlet.width/2, uiComlet.height/2);
        winNode.addChild(uiComlet);
        // 收集完成奖励文字
        var uiTitle = new Image("res/pic/cardbing_word_sjwcjl.png");
        uiTitle.pivot(uiTitle.width/2, uiTitle.height/2);
        uiTitle.pos(0, -80);
        winNode.addChild(uiTitle);

        // 赛豆
        uiSaiDou = new Image("res/pic/cardbing_word_sd.png");
        uiSaiDou.pivot(uiSaiDou.width, uiSaiDou.height/2);
        winNode.addChild(uiSaiDou);

        // 规则说明
        var uiDesc = new Text();
        uiDesc.text = "收集完成时，球数越少，奖励越多！";
        uiDesc.color = "#FFFFFF";
        uiDesc.fontSize = 20;
        uiDesc.pivot(uiDesc.width/2, uiDesc.height/2);
        uiDesc.pos(0, 90);
        winNode.addChild(uiDesc);

        // 奖励数字
        var uiRewardNum = createAwardNum(m_uiAwardNum.text);
        uiRewardNum.pivot(0, uiRewardNum.height/2);
        var fScale = 1.8
        uiRewardNum.scale(fScale, fScale)
        winNode.addChild(uiRewardNum);
        // 赛豆和奖励数字两者共同居中
        var xOffset = -uiRewardNum.width * fScale/2 + uiSaiDou.width/2;
        uiSaiDou.pos(xOffset, +4);
        uiRewardNum.pos(xOffset, 0);
        winNode.alpha = 0;
        winNode.scale(0.5, 0.5);

        // 翻译flash动画 
        var timeLine = new TimeLine();
        timeLine.addLabel("light1",0).to(light,{alpha:1},240,null,0)
                .addLabel("light2",0).to(light,{alpha:0},240,null,0)
                .addLabel("card1",0).to(card,{x: constOuterWidth/2, 
                                            y: 300+card.height,
                                            scaleX: 1.28,
                                            scaleY: 1.28,
                                        },320,null, 0)
                .addLabel("frame",0).to(frame,{alpha:1},320,null,-320)                        
                .addLabel("card2",0).to(card,{x: constOuterWidth/2, 
                                            y: 310+card.height,
                                            scaleX: 1.30,
                                            scaleY: 1.30,
                                        },320,null,0)                                            
                .addLabel("card3",0).to(card,{rotation:-16},360,null,0)                                            
                .addLabel("winNode1",0).to(winNode,{scaleX:1, scaleY:1, alpha:1},160,null,-360)
                .addLabel("winNode2",0).to(winNode,{scaleX:1.05, scaleY:1.05},220,null, -200)
        timeLine.scale = m_speed; // 播放速度        
        timeLine.play(0, false);
        timeLine.on(Event.COMPLETE, this, function(){                                      
                        showMoneyChangeAni(); // 显示金额改变
                        bg.on("click", this, function(){
                            bg.removeSelf(); // 点击关闭结算界面
                            enableButtons(true);

                            winCard.visible = true;
                            m_awardIndex = 1;

                            // 重置进度信息    
                            setProgress(0, m_progressBar);     
                            m_uiProText.text = formatNumStr(0);  
                            m_uiAwardNum.text = calcAwardMoney(1);
                            showAllNums(true);
                        }); // 屏蔽点击事件

                    });
    }

    function getSoundPng()
    {
        if (m_bSound)
        {
            return "res/pic/cardbing_btn_bet_sound_1.png";
        }
        else
        {
            return "res/pic/cardbing_btn_bet_sound_0.png";
        }
    }

    function getMusicPng()
    {
        if (m_bMusic)
        {
            return "res/pic/cardbing_btn_bet_bgm_1.png";
        }
        else
        {
            return "res/pic/cardbing_btn_bet_bgm_0.png";
        }
    }

    // 显示选项窗口
    function showOptDialog()
    {
        var dia = createDialogBase()
        // 框
        var frame = new Sprite()
        frame.loadImage("res/pic/cardbing_img_bet_set.png");
        frame.pivot(frame.width/2, frame.width/2);
        frame.pos(dia.width/2, dia.height/2+100);
        dia.addChild(frame);

        var btnClose = createScaleButton("res/pic/cardbing_btn_bet_close.png");
        btnClose.pos(500, 35);
        btnClose.on("click", this, function(){dia.removeSelf()});
        frame.addChild(btnClose);

        var btnBGMusic = createScaleButton(getMusicPng());
        btnBGMusic.pos(frame.width/2-110, 180);
        btnBGMusic.on("click", this, function(){
            m_bMusic = !m_bMusic;
            if (!m_bMusic)
            {
                // 停止音乐
                SoundManager.stopMusic();
            }
            else
            {
                // 播放背景音乐
                playMusic("res/audio/bgm_cardbing.mp3");
            }    
            btnBGMusic.loadImage(getMusicPng())
        });
        frame.addChild(btnBGMusic);

        var btnSound = createScaleButton(getSoundPng());
        btnSound.pos(frame.width/2+110, 180);
        btnSound.on("click", this, function(){
            m_bSound = !m_bSound;
            btnSound.loadImage(getSoundPng())            
        });
        frame.addChild(btnSound);
    }

    // 显示说明窗口
    function showDescDialog()
    {
        if (!m_bCanClick)
            return;
        var dia = createDialogBase()
        // 框
        var frame = new Sprite()
        frame.loadImage("res/pic/cardbing_img_bet_view.png");
        frame.pos(dia.width/2, dia.height/2-30);
        frame.pivot(frame.width/2, frame.width/2);
        dia.addChild(frame)

        // 奖励明细
        for (var i = 0; i < m_bonus_tplt.length; ++i)
        {
            var v = m_bonus_tplt[i];
            // 黑条
            var bar = new Sprite();
            bar.loadImage("res/pic/cardbing_img_bet_view_back.png");
            bar.pivot(bar.width/2, bar.width/2);
            bar.pos(frame.width/2, 340 + (i-1)*42);
            frame.addChild(bar);

            // 区间
            var uiRange = new Text();
            uiRange.text = "" + v.from + "-" + v.to;
            uiRange.font = "cardbing_number_white";
            uiRange.pivot(0, uiRange.height/2);
            uiRange.scale(1.2, 1.2);
            uiRange.pos(50, 19);            
            bar.addChild(uiRange);            

            // 赔率
            var uiMul = new Text();
            uiMul.text = v.mul * parseInt(m_uiPutMoney.text)/10;
            uiMul.font = "cardbing_number_white";
            uiMul.pivot(0, uiMul.height/2);
            uiMul.scale(1.2, 1.2);
            uiMul.pos(250, 19);            
            bar.addChild(uiMul);            
        }

        // 确定按钮
        var btn = createScaleButton("res/pic/cardbing_btn_bet_confirm.png")
        btn.pos(frame.width/2, frame.height/2+190);
        frame.addChild(btn);
        btn.on("click", this, function(){ dia.removeSelf() });
    }

    // 创建奖励数字
    function createAwardNum(s)
    {
        var ui = new Text();        
        ui.font = "cardbing_number_award";
        ui.text = s;
        return ui;
    }

    // 创建特效
    function createAni(sk, uiParent, x, y, fnFinish)
	{
		var factory = new Templet();
		factory.on(Event.COMPLETE, this, function(){
            //创建模式为1，可以启用换装
            var armature = factory.buildArmature(1);
            armature.playbackRate(m_speed);            

            armature.pos(x, y);
            uiParent.addChild(armature);
            armature.on(Event.STOPPED, this, fnFinish, [armature]);            
            armature.play(0, false);
        });
		factory.loadAni(sk);
	}

    // 翻卡动画
    function skewOneCardAni(uiCard)
    {
        var oldY = uiCard.y
        Laya.timer.once(600/m_speed, this, function(){
            // 翻牌动画 
            Tween.to(uiCard, {y: oldY-20, skewY: 5, scaleX:0.0, scaleY:0.95}, 160/m_speed, null, 
                    Handler.create(this, function(){
                        // 显示所有数字    
                        showAllNums(true);
                        Tween.to(uiCard, {y: oldY, skewY: 0, scaleX:1, scaleY:1}, 160/m_speed, null, 
                                Handler.create(this, function(){
                                    // 按钮可点击
                                    enableButtons(true);
                                }));                         
                    }));
        });                                
    }

    // 显示数字
    function showAllNums(bVal)
    {
        for (var i = 0; i < constMaxNum; ++i)
        {
            m_uiNumBlackList[i].visible = bVal;
            m_uiNumList[i].visible = bVal;
        }         
    }    	

    // 显示错误
    function showError(msg)
    {
        console.log(msg);
        var uiText = new Text();
        uiText.fontSize = 30;
        uiText.text = msg;
        uiText.pivot(uiText.width/2, uiText.height/2);
        uiText.color = "#FFFF00";
        uiText.pos(constOuterWidth/2, Laya.stage.height/2);

        Tween.to(uiText, {y:200}, 2000, null, 
                Handler.create(this, function(){uiText.removeSelf()}));
        m_rootNode.addChild(uiText);
    }
})();