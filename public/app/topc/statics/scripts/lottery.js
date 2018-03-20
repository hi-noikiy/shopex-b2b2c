var turnplate = {
    restaraunts:[],
    //大转盘奖品名称
    colors:[],
    //大转盘奖品区块对应背景颜色
    outsideRadius:175,
    //大转盘外圆的半径
    textRadius:135,
    //大转盘奖品位置距离圆心的距离
    insideRadius:55,
    //大转盘内圆的半径
    startAngle:0,
    //开始角度
    bRotate:false
};

function readyTurnplate(restaraunts, colors, actionUrl, params, limitEle, regionData, regionUrl, saveurl) {
    //动态添加大转盘的奖品与奖品区域背景颜色
    turnplate.restaraunts = restaraunts;
    turnplate.colors = colors;
    var rotateTimeOut = function() {
        $("#wheelcanvas").rotate({
            angle:0,
            animateTo:2160,
            duration:8e3,
            callback:function() {
                Message.error("网络超时，请检查您的网络设置！");
            }
        });
    };
    //旋转转盘 item:奖品位置; txt：提示语;
    var rotateFn = function(item, txt, redirect, dataEl, responseData) {
        var angles = item * (360 / turnplate.restaraunts.length) - 360 / (turnplate.restaraunts.length * 2);
        if (angles < 270) {
            angles = 270 - angles;
        } else {
            angles = 360 - angles + 270;
        }
        $("#wheelcanvas").stopRotate();
        $("#wheelcanvas").rotate({
            angle:0,
            animateTo:angles + 1800,
            duration:8e3,
            callback:function() {
                //弹出抽到的奖品
                var lotteryType = txt.substring(txt.indexOf('-')+1, txt.indexOf('+'));
                txt = "恭喜你，获得" + txt.substr(txt.indexOf('+') + 1);
                if(lotteryType == 'none') {
                    Message.error("很遗憾，未中奖！");
                } else if(lotteryType == 'hongbao'){
                    if(responseData.hongbaotype != null && responseData.hongbaotype == "stochastic") {
                        txt = "恭喜你，获得随机红包" + responseData.hongbaomoney + "元";
                    } else {
                        txt = "恭喜你，获得红包" + responseData.hongbaomoney + "元";
                    }
                    Message.success(txt);
                } else {
                    Message.success(txt);
                }
                turnplate.bRotate = !turnplate.bRotate;
            }
        });
    };
    $(".pointer").click(function() {
        var lotteryLimit = Number($.trim($(".prize-result").find(".lotter-limit").text()));
        if (turnplate.bRotate) return;
        turnplate.bRotate = !turnplate.bRotate;
        params.lottery_joint_limit = lotteryLimit;
        $.post(actionUrl, params, function(rs) {
            if(rs.error) {
                Message.error(rs.message);
                turnplate.bRotate = !turnplate.bRotate;

                if(rs.redirect) {
                    setTimeout(function() {
                        window.location.href = rs.redirect;
                    }, 1500);
                }
            }
            if(rs.success) {
                var redirect = rs.redirect;
                var id = rs.message.id
                limitEle.text(rs.message.lottery_joint_limit);
               
                //获取中奖奖品的索引值
                var index = 0;
                for(var i = 0; i < turnplate.restaraunts.length; i++) {
                    if(turnplate.restaraunts[i].indexOf(id + '-') == 0) {
                        index = i;
                        break;
                    }
                } 
                var regionData = $(this).parent().find('label');
                rotateFn(index + 1 , turnplate.restaraunts[index], redirect, regionData, rs.message);
            }
        });
    });
}

function rnd(n, m) {
    var random = Math.floor(Math.random() * (m - n + 1) + n);
    return random;
}

function drawRouletteWheel() {
    var canvas = document.getElementById("wheelcanvas");
    if (canvas.getContext) {
        //根据奖品个数计算圆周角度
        var arc = Math.PI / (turnplate.restaraunts.length / 2);
        var ctx = canvas.getContext("2d");
        //在给定矩形内清空一个矩形
        ctx.clearRect(0, 0, 422, 422);
        //strokeStyle 属性设置或返回用于笔触的颜色、渐变或模式  
        ctx.strokeStyle = "#FFBE04";
        //font 属性设置或返回画布上文本内容的当前字体属性
        ctx.font = "16px Microsoft YaHei";
        for (var i = 0; i < turnplate.restaraunts.length; i++) {
            var angle = turnplate.startAngle + i * arc;
            ctx.fillStyle = turnplate.colors[i];
            ctx.beginPath();
            //arc(x,y,r,起始角,结束角,绘制方向) 方法创建弧/曲线（用于创建圆或部分圆）    
            ctx.arc(211, 211, turnplate.outsideRadius, angle, angle + arc, false);
            ctx.arc(211, 211, turnplate.insideRadius, angle + arc, angle, true);
            ctx.stroke();
            ctx.fill();
            //锁画布(为了保存之前的画布状态)
            ctx.save();
            //----绘制奖品开始----
            ctx.fillStyle = "#E5302F";
            var key = turnplate.restaraunts[i].substring(0, turnplate.restaraunts[i].indexOf('-'));
            var lotteryType = turnplate.restaraunts[i].substring(turnplate.restaraunts[i].indexOf('-')+1, turnplate.restaraunts[i].indexOf('+'));
            var text = turnplate.restaraunts[i].substr(turnplate.restaraunts[i].indexOf('+')+1);
            var line_height = 17;
            //translate方法重新映射画布上的 (0,0) 位置
            ctx.translate(211 + Math.cos(angle + arc / 2) * turnplate.textRadius, 211 + Math.sin(angle + arc / 2) * turnplate.textRadius);
            //rotate方法旋转当前的绘图
            ctx.rotate(angle + arc / 2 + Math.PI / 2);
            /** 下面代码根据奖品类型、奖品名称长度渲染不同效果，如字体、颜色、图片效果。(具体根据实际情况改变) **/
            if (text.indexOf("M") > 0) {
                //流量包
                var texts = text.split("M");
                for (var j = 0; j < texts.length; j++) {
                    ctx.font = j == 0 ? "bold 16px Microsoft YaHei" :"14px Microsoft YaHei";
                    if (j == 0) {
                        ctx.fillText(texts[j] + "M", -ctx.measureText(texts[j] + "M").width / 2, j * line_height);
                    } else {
                        ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
                    }
                }
            } else if (text.indexOf("M") == -1 && text.length > 6) {
                //奖品名称长度超过一定范围 
                text = text.substring(0, 6) + "||" + text.substring(6);
                var texts = text.split("||");
                for (var j = 0; j < texts.length; j++) {
                    ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
                }
            } else {
                //在画布上绘制填色的文本。文本的默认颜色是黑色
                //measureText()方法返回包含一个对象，该对象包含以像素计的指定字体宽度
                ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
            }
            //添加对应图标
            if (lotteryType == "point") {
                var img = document.getElementById("point_" + key);
                if(img != null) {
                    img.onload = function() {
                        ctx.drawImage(img, -20, 10, 40, 40);
                    };
                    ctx.drawImage(img, -20, 10, 40, 40);
                }
             } 
            else if (lotteryType == "none") {
                var img = document.getElementById("none_" + key);
                if(img != null) {
                    img.onload = function() {
                        ctx.drawImage(img, -20, 10, 40, 40);
                    };
                    ctx.drawImage(img, -20, 10, 40, 40);
                }
            } 
            else if (lotteryType == "hongbao") {
                var img = document.getElementById("hongbao_" + key);
                if(img != null) {
                    img.onload = function() {
                        ctx.drawImage(img, -20, 10, 40, 40);
                    };
                    ctx.drawImage(img, -20, 10, 40, 40);
                }
            } 
            else if(lotteryType == "custom"){
                var img = document.getElementById("custom_" + key);
                if(img != null) {
                    img.onload = function() {
                        ctx.drawImage(img, -20, 10, 40, 40);
                    };
                    ctx.drawImage(img, -20, 10, 40, 40);
                }
            }
            //把当前画布返回（调整）到上一个save()状态之前 
            ctx.restore();
        }
    }
}