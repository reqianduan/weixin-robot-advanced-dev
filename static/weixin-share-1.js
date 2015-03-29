/*function htmlEncode(e) {
    return e.replace(/&/g, "&amp;").replace(/ /g, "&nbsp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br />").replace(/"/g, "&quot;")
}

function htmlDecode(e) {
    return e.replace(/&#39;/g, "'").replace(/<br\s*(\/)?\s*>/g, "\n").replace(/&nbsp;/g, " ").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"').replace(/&amp;/g, "&")
}*/


//weixin_data.title	= htmlDecode(weixin_data.title),
//weixin_data.desc	= htmlDecode(weixin_data.desc),

weixin_data.desc	= weixin_data.desc || weixin_data.link;

function weixin_robot_credit_share(share_type){
	if(weixin_data.credit == 1 && weixin_data.weixin_openid != ''){
		jQuery.ajax({
			type: "post",
			url: weixin_data.ajax_url,
			data: { 
				action:			'weixin_share', 
				share_type:		share_type,
				post_id: 		weixin_data.post_id,
				weixin_openid: 	weixin_data.weixin_openid, 
				_ajax_nonce: 	weixin_data.nonce
			},
			success: function(html){
				if(weixin_data.notify){
					alert(html);
				}
			}
		});
	}
	jQuery(window).trigger('weixin_share',share_type);
}
	
(function(){
	var onBridgeReady=function(){

		//WeixinJSBridge.call("hideOptionMenu");

		//WeixinJSBridge.call('hideToolbar');
		/*
	    jQuery("#weixin-user").on('click', function(){
            WeixinJSBridge.invoke('profile',{
                'username':'gh_d0e8fa0609a2',
                'scene':'57'
            });
        });*/

		/*WeixinJSBridge.invoke('getNetworkType',{},
		function(e){
	    	alert(e.err_msg);
	    });*/

		// 发送给好友; 
		function writeObj(obj){
			var description = "";
			for(var i in obj){  
				var property=obj[i];  
				description+=i+" = "+property+"\n"; 
			}  
			alert(description);
		}

		WeixinJSBridge.on('menu:share:appmessage', function(argv){
			WeixinJSBridge.invoke('sendAppMessage',{
				"appid":		weixin_data.appid,
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height":	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){
				writeObj(res);
				if(res.err_msg == 'send_app_msg:cancel'){
					if(weixin_data.notify){
						alert('取消分享可是没有积分的哦。');
					}
				}else if(res.err_msg == 'send_app_msg:confirm'){
					weixin_robot_credit_share('SendAppMessage');
				}
			});
		});
		// 分享到朋友圈;
		WeixinJSBridge.on('menu:share:timeline', function(argv){
			WeixinJSBridge.invoke('shareTimeline',{
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height": 	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){
				weixin_robot_credit_share('ShareTimeline');
			});
		});
		// 分享到微博;
		WeixinJSBridge.on('menu:share:weibo', function(argv){
			WeixinJSBridge.invoke('shareWeibo',{
				"content":		weixin_data.title+' '+weixin_data.link,
				"url":			weixin_data.link
			}, function(res){
				weixin_robot_credit_share('ShareWeibo');
			});
		});
		// 分享到Facebook
		WeixinJSBridge.on('menu:share:facebook', function(argv){
			weixin_robot_credit_share('ShareFB');
			WeixinJSBridge.invoke('shareFB',{
				"img_url":		weixin_data.img,
				"img_width":	"120",
				"img_height":	"120",
				"link":			weixin_data.link,
				"desc":			weixin_data.desc,
				"title":		weixin_data.title
			}, function(res){});
		});
	};
	if(document.addEventListener){
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
	}else if(document.attachEvent){
		document.attachEvent('WeixinJSBridgeReady',		onBridgeReady);
		document.attachEvent('onWeixinJSBridgeReady',	onBridgeReady);
	}
})();