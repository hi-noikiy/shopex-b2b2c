;(function($,doc){
	var namespace = 'decorate';
	var classNamePrefix = namespace + '-';
	var idNamePrefix = namespace + '_';
	var classSelectorPrefix = '.' + classNamePrefix;
	var idSelectorPrefix = '#' + idNamePrefix;
	var jsonData = [];
	$.className = function(className) {
		return classSelectorPrefix + className;
	};
	$.idName = function(idName) {
		return idSelectorPrefix + idName;
	};

	var active = 'active',
		removeBtn = 'action-remove',
		createBtn = 'action-create',
		saveBtn = $.idName('action_save');

	var _layoutWrapper = 'layout-view',
		_layoutItems = 'layout-items',
		_widgetsWrapper = 'widgets',
		_widgetsItems = 'widgets-items',
		_controlWrapper = 'control',
		_controlItems = 'control-items',
		_contentForm = 'form';
    
	var remove_btn = '<div class="'+ classNamePrefix+removeBtn +'"><i class="glyphicon glyphicon-trash"></i></div>'

	//通用操作组件
	var $EVENT = {
		create: function(name){
			if(jsonData.length >= 21){
				alert('你的挂件已是最大限额！');
				return;
			} 
			var actived = $.className(active);
			var activeLayout = $($.className(_layoutWrapper)).find(actived);
			var activeControl = $($.className(_controlWrapper)).find(actived);
			var timestamp = new Date().getTime();
			var widgetsID = name + '_' + timestamp;
			var baseData = JSON.parse(widgetsData[name]);
			$data.create(widgetsID,name,baseData);
			var templ = this.createTemp(widgetsID,name);
			
			var controlItem = '<li class="dd-item decorate-control-items" data-widgets="'+ name +'" data-id="'+ widgetsID +'"><div class="dd-handle"></div></li>';
			if($(activeLayout).length > 0){
				$(activeLayout).after(templ);
			}else{
				$($.className(_layoutWrapper)).append(templ);
			}
			if($(activeControl).length > 0){
				$(activeControl).after(controlItem);
			}else{
				$($.className(_controlWrapper)).find('ol').append(controlItem);
			}
			$control.bindClick($('[data-id="'+ widgetsID +'"]'));
		},
		edit: function(dom){
			if(dom){
				var name = $(dom).data('widgets');
				var content;
				$($.className(removeBtn)).remove();
				if($(dom).hasClass(classNamePrefix + _controlItems)){
					var layoutID = $(dom).data('id');
					content = this.createTemp(layoutID,name + '_' +_contentForm);
					$('#'+layoutID).append(remove_btn).addClass(classNamePrefix + active).siblings().removeClass(classNamePrefix + active);
				}
				if($(dom).hasClass(classNamePrefix + _layoutItems)){
					var layoutID = $(dom).attr('id');
					content = this.createTemp(layoutID,name + '_' +_contentForm);
					$(dom).append(remove_btn);
					$($('[data-id="'+layoutID+'"]')).addClass(classNamePrefix + active).siblings().removeClass(classNamePrefix + active);
				}
				$(dom).addClass(classNamePrefix + active).siblings().removeClass(classNamePrefix + active);
				$($.className(_contentForm)).html(content);
				$('.modal').off('hidden.bs.modal').off('shown.bs.modal');
				$widgetsScript[name].init();
				this.setSort();
				console.log(jsonData);
			}else{
				$($.className(removeBtn)).remove();
				$($.className(active)).removeClass(classNamePrefix + active);
				$($.className(_contentForm)).html('<div class="decorate-form-tip">请选择挂件</div>');
				$('.modal').off('hidden.bs.modal').off('shown.bs.modal');
			}
		},
		remove: function(dom){
			var widgetsID = $(dom).attr('id');

			var next = $(dom).next($.className(_layoutItems));
			if(next.length > 0){
				this.edit(next);
			}else{
				$($.className(_contentForm)).empty();
			}
			$($('[data-id="'+widgetsID+'"]')).remove();
			$(dom).remove();
			for (var i = 0; i < jsonData.length; i++) {
				if(jsonData[i].wid==widgetsID){
					jsonData.splice(i,1);
				};
			};
		},
		updateData: function(id,key,value){
			for (var i = 0; i < jsonData.length; i++) {
				if(jsonData[i].wid==id){
					jsonData[i][key] = value;
				}
			};
		},
		createTemp: function(id,name){
			for (var i = 0; i < jsonData.length; i++) {
				if(jsonData[i].wid==id){
					return template(name,jsonData[i]);
				}
			};
		},
		setSort: function(){
			$($.className(_layoutItems)).each(function(index, el) {
				for (var i = 0; i < jsonData.length; i++) {
					if(jsonData[i].wid == $(el).attr('id')){
						jsonData[i].sn = index;
					};
				};
			});
		}
	}

	//挂件元件
	var $widgets = {
		container: _widgetsWrapper,
		items: _widgetsItems,
		handle: function(){
			var wrapper = $($.className(this.container));
			if(wrapper.length <= 0){
				return
			}
			this.bindClick();
		},
		bindClick: function(){
			$($.className(this.container)).on('click',$.className(this.items),function(e){
				e.preventDefault();
				$EVENT.create($(this).data('widgetName'));
			});
		}
	}

	//显示层组件
	var $layout = {
		container: _layoutWrapper,
		items: _layoutItems,
		handle: function(){
			var wrapper = $($.className(this.container));
			if(wrapper.length <= 0){
				return
			}
			this.bindRemove();
			this.bindClick();
		},
		bindRemove: function(){
			var _this = this
			$($.className(this.container)).on('click',$.className(removeBtn),function(e){
				e.stopPropagation();
				$EVENT.remove($(this).parents($.className(_this.items)));
			});
		},
		bindClick: function(){
			$($.className(this.container)).on('click',$.className(this.items),function(e){
				e.preventDefault();
				$EVENT.edit($(this));
			});
		}
	}

	//控制层组件
	var $control = {
		container: _controlWrapper,
		items: _controlItems,
		handle: function(){
			var _this = this;
			var wrapper = $($.className(_this.container));
			if(wrapper.length <= 0){
				return
			}
			_this.drag($.className(_this.items));
			$($.idName(_this.container)).on('mousedown',$.className(_this.items),function(e){
				e.preventDefault();
				var thisParent = $(e.target).parent();
				_this.bindClick(thisParent);
			});
		},
		bindClick: function(dom){//控件点击事件
			if($(dom).data('id')){
				$($.className($layout.container)).find('#'+$(dom).data('id')).addClass(classNamePrefix + active).siblings().removeClass(classNamePrefix + active).scrollTop(0);
			}
			if(!$(dom).hasClass(classNamePrefix + active)){
				$EVENT.edit($(dom));
			}
		},
		drag: function(){//控件拖拽排序事件
			$($.idName(this.container)).nestable({
		        group: 1
		    });
		    $('.dd').on('mousedown', '.dd-item', function(e) {
		    	var that = $(this);
		    	var wid = that.data('id');
			    $('.dd').on('change',function(){
			    	var next = $(that).next();
			    	var nextId = $(next).data('id');
			    	if(nextId){
			    		$('#'+nextId).before($('#'+wid));
			    	}else{
			    		$($.className(_layoutWrapper)).append($('#'+wid));
			    	}
			    	$EVENT.setSort();
			    });
			});
		}
	}

	//数据处理组件
	var $data = {
		create : function(id,name,data){
			data.wid = id;
			data.widget_name = name;
			jsonData.push(data);
		},
		extend : function(data){
			if(data){

			}
		}
	}

	var $content = {
		container: _contentForm,
		insert: function(templ){
			$(_contentForm).html(templ);
		}
	}

	//挂件事件绑定
	var $widgetsScript = {
		title : {
			'init': function(){

			}
		},
		goods : {
			'wrapper': '.goods',
			'init': function(){
				this.dataBind();
			},
			'dataBind': function(){
				var obj = this;

				$(this.wrapper).on('change','input[name="title"]',function(){
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = $(wrapper).data('id');
					var widget = $(wrapper).attr('class');
					var keyname = $(this).attr('name');
					$EVENT.updateData(dataKey,keyname,$(this).val());
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
				});

				$('#goods_modal').on('hidden.bs.modal',function(){
					updateData()
				});

				$(this.wrapper).on('click','.item-del',function(){
					$(this).parents('tr').remove();
					updateData();
				});

				function updateData() {
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = $(wrapper).data('id');
					var widget = $(wrapper).attr('class');
					var goodslist = $('#selected_goods').find('tr');
					var goodsArr = [];
					for (var i = 0; i < goodslist.length; i++) {
						var thisData = {};
						thisData.item_id = $(goodslist[i]).data('itemid');
						thisData.goodslink = $(goodslist[i]).find('a').attr('href');
						thisData.imgurl = $(goodslist[i]).find('img').attr('src');
						thisData.goodstitle = $($(goodslist[i]).find('span')[0]).text();
						thisData.price = $($(goodslist[i]).find('span')[1]).text();
						thisData.soldnum = 5;
						goodsArr.push(thisData);
					};
        			$EVENT.updateData(dataKey,'list',goodsArr);
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
				}
			}
		},
		nav : {
			'wrapper': '.nav',
			'init': function(){
				this.addItem();
				this.removeItem();
				this.dataBind();
			},
			'addItem': function(){
				var _goodsitem = navGoodsItem,
					_linkitem = navLinkItem;
					_promotionitem = navPromotionItem;
				var wrapper = $(this.wrapper)
				var _form = wrapper.find('.nav-items-list');
				$(this.wrapper).on('click','.action-add',function(){
					if($(_form).find('.form-item').length < 5){
						if($(this).data('itemtype')=='link'){
							$(_form).append(_linkitem);
						}
						if($(this).data('itemtype')=='goods'){
							$(_form).append(_goodsitem);
						}
						if($(this).data('itemtype')=='promotion'){
							$(_form).append(_promotionitem);
						}
					}
				});
			},
			'removeItem': function(){
				$(this.wrapper).on('click','.action-remove',function(){
					$(this).parent().remove();
				});
			},
			'dataBind': function(){
				var obj = this;
				
				$(this.wrapper).on('change','input[name="navname"]',function(){
					updateData();
				});

				$(this.wrapper).on('change','input[name="navlink"]',function(){
					updateData();
				});

				$('#goods_modal').on('hidden.bs.modal',function(event) {
					updateData();
				});

				$('#promotions_modal').on('hidden.bs.modal',function(event){
					updateData();
				});

				$(this.wrapper).on('click','.action-remove',function(){
					updateData();
				});

				function updateData(button) {
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = $(wrapper).data('id');
					var widget = $(wrapper).attr('class');

					var list = $(wrapper).find('.form-item');
					var navArr = [];
					for(var i=0; i<list.length; i++){
						var thisData = {};
						var thisType = $(list[i]).data('itemtype');
						thisData.name = $(list[i]).find('input[name="navname"]').val();
						thisData.type = thisType;
						if(thisType == 'goods'){
							thisData.item_ids = $(list[i]).find('span').data('item_id');
						}
						if(thisType == 'link'){
							thisData.navlink = $(list[i]).find('input[name="navlink"]').val();
						}
						if(thisType == 'promotion'){
							thisData.promotion_id = $(list[i]).find('span').attr('data-promotion_id');
							thisData.promotion_type = $(list[i]).find('span').attr('data-promotion_type');
						}
						navArr.push(thisData);
					}
					$EVENT.updateData(dataKey,'list',navArr);
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
				};
			}
		},
		oneimg : {
			'wrapper': '.oneimg',
			'init': function(){
				this.dataBind();
			},
			'dataBind': function(){
				var obj = this;

				$(this.wrapper).on('change','input[name="imglink"]',function(){
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = wrapper.data('id');
					var widget = wrapper.attr('class');

					var keyname = $(this).attr('name');
					var link = $(this).val();
					$EVENT.updateData(dataKey,keyname,link);
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
				});

				$('#gallery_modal').on('shown.bs.modal',function(event){
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = wrapper.data('id');
					var widget = wrapper.attr('class');

					var button = $(event.relatedTarget);
					var img_input = button.siblings('input');
					var keyname = button.siblings('input').attr('name');
					$($('#gallery_modal').find('.action-save')).click(function(){
						var img = $('#gallery_modal').find('.checked');
			            var imgsrc = img.find('img').attr('src');
			            var url = img.find('.caption').attr('data-name');
	            		if(img.length>0){
	            			button.siblings('input').val(imgsrc);
	            			button.data('imgurl',url);
	            			$EVENT.updateData(dataKey,keyname,$(img_input).val());
	            			$EVENT.updateData(dataKey,'imgurl',button.data('imgurl'));
							$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
							$EVENT.edit($('#'+dataKey));
			            }else{
			                alert('请选择图片!');
			            }
						
					});
				});
			}
		},
		slider : {
			'wrapper': '.slider',
			'init': function(){
				shopex(".shopex-slider").slider({
			      interval:5000//自动轮播周期，若为0则不自动播放，默认为0；
			    });
			    this.addItem();
			    this.removeItem();
			    this.dataBind();
			},
			'addItem': function(){
				var _item = sliderFormItem;
				var wrapper = $(this.wrapper)
				var _form = wrapper.find('.slider-items-list');
				$(this.wrapper).on('click','.action-add',function(){
					if($(_form).find('.slider-item').length < 9){
						$(_form).append(_item);
					}
				});
			},
			'removeItem': function(){
				$(this.wrapper).on('click','.action-remove',function(){
					$(this).parent().remove();
				});
			},
			'dataBind': function(){
				var obj = this;				

				$(this.wrapper).on('change','input[name="imglink"]',function(){
					updateData()
				});

				$('#gallery_modal').on('shown.bs.modal',function(event){
					var button = $(event.relatedTarget);
					var img_input = button.siblings('input');
					var keyname = button.siblings('input').attr('name');
					$($('#gallery_modal').find('.action-save')).click(function(){
						var img = $('#gallery_modal').find('.checked');
			            var imgsrc = img.find('img').attr('src');
			            var url = img.find('.caption').data('name');
	            		if(img.length>0){
	            			button.siblings('input').val(imgsrc);
	            			button.data('imgurl',url);
			                updateData()
			            }else{
			                alert('请选择图片!');
			            }
						
					});
				});

				function updateData() {
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = $(wrapper).data('id');
					var widget = $(wrapper).attr('class');

					var list = wrapper.find('.form-item');
					var linkArr = [];
					for(var i=0; i<list.length; i++){
						var thisData = {};
						thisData.imglink = $(list[i]).find('input[name="imglink"]').val();
						thisData.imgsrc = $(list[i]).find('input[name="imgsrc"]').val();
						thisData.imgurl = $(list[i]).find('.select-image').data('imgurl');
						linkArr.push(thisData);
					}
					$EVENT.updateData(dataKey,'list',linkArr);
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
					if(linkArr.length > 1){
						shopex(".shopex-slider").slider({
					      interval:5000//自动轮播周期，若为0则不自动播放，默认为0；
					    });
					};
				};
			}
		},
		imgnav : {
			'init': function(){

			}
		},
		search : {
			'init': function(){

			}
		},
		showcase : {
			'init': function(){

			}
		},
		cutline : {
			'init': function(){

			}
		},
		cutgap : {
			'init': function(){

			}
		},
		storeinfo : {
			'init': function(){

			}
		},
		announcement : {
			'init': function(){

			}
		},
		coupon : {
			'wrapper': '.coupon',
			'init': function(){
				this.dataBind();
			},
			'dataBind':function(){
				var obj = this;

				$('#coupons_modal').on('hidden.bs.modal',function(){
					updateData()
				});

				$(this.wrapper).on('click','.coupon-del',function(){
					$(this).parents('li').remove();
					updateData()
				});

				function updateData(){
					var wrapper = $(doc).find(obj.wrapper);
					var dataKey = $(wrapper).data('id');
					var widget = $(wrapper).attr('class');
					var couponslist = $('#selected_coupons').find('li');
					var couponsArr = [];
					for (var i = 0; i < couponslist.length; i++) {
						var thisData = {};
						thisData.coupon_id = $(couponslist[i]).data('couponid');
						thisData.amount = $(couponslist[i]).find('.coupon-amount').text();
						thisData.rule = $(couponslist[i]).find('.coupon-rule').text();
						thisData.deadline = $(couponslist[i]).find('.coupon-deadline').text();
						couponsArr.push(thisData);
					};
					$EVENT.updateData(dataKey,'list',couponsArr);
					$('#'+dataKey).replaceWith($EVENT.createTemp(dataKey,widget));
					$EVENT.edit($('#'+dataKey));
				}
			}
		},
		activity : {
			'init': function(){

			}
		},
		goodsgroup : {
			'init': function(){

			}
		}
	}

	//挂件基础数据
	var widgetsData = {
		shopsign:       '{"wid": "","sn": "","widget_name": "","imgurl": ""}',
		title : 		'{"wid": "","sn": "","widget_name": "","title": ""}',
		goods : 		'{"wid": "","sn": "","widget_name": "","title": "","list": []}',
		nav : 			'{"wid": "","sn": "","widget_name": "","list": []}',
		oneimg : 		'{"wid": "","sn": "","widget_name": "","imgurl": "","imgsrc": "","imglink": ""}',
		slider : 		'{"wid": "","sn": "","widget_name": "","list": []}',
		imgnav : 		'{"wid": "","sn": "","widget_name": ""}',
		search : 		'{"wid": "","sn": "","widget_name": ""}',
		showcase : 		'{"wid": "","sn": "","widget_name": ""}',
		cutline : 		'{"wid": "","sn": "","widget_name": ""}',
		cutgap : 		'{"wid": "","sn": "","widget_name": ""}',
		storeinfo : 	'{"wid": "","sn": "","widget_name": ""}',
		announcement : 	'{"wid": "","sn": "","widget_name": ""}',
		coupon : 		'{"wid": "","sn": "","widget_name": "","morelink": "","list": []}',
		activity : 		'{"wid": "","sn": "","widget_name": ""}',
		goodsgroup : 	'{"wid": "","sn": "","widget_name": ""}'
	}

	//数据排序
	var sortby = function(name){
	    return function(o, p){
	        var a, b;
	        if (typeof o === "object" && typeof p === "object" && o && p) {
	            a = o[name];
	            b = p[name];
	            if (a === b) {
	                return 0;
	            }
	            if (typeof a === typeof b) {
	                return a < b ? -1 : 1;
	            }
	            return typeof a < typeof b ? -1 : 1;
	        }
	        else {
	            throw ("error");
	        }
	    }
	}

	//商品选择器
	var goodsChoose = function(options) {
	    options = $.extend({
	        url: '',
	        getProUrl: '',
	        getBrandUrl: '',
	        getGoodsUrl: '',
	        target: document.body,
	        handle: '.select-goods',
	        modalDom: '#goods_modal',
	        catBox: '.goods-category',
	        catlistItem: '.goods-category li',
	        catDefault: '分类',
	        brandBox: '.goods-brands',
	        brandList: '#brand_list',
	        brandListItem: '#brand_list li',
	        brandDefault: '品牌',
	        filterBtn: '#search_goods',
	        clearFilterBtn: '#clear_filter',
	        clearBrandBtn: '#clear_brand',
	        submitBtn: '#choose_goods',
	        checkAll: '#check_all',
	        uncheckAll: '#uncheck_all',
	        goodsList: '#goods_list',
	        goodsListItem: '#goods_list > ul > li',
	        goodsSearchKey: '.goods-search-key',
	        goodsSearchBn: '.goods-search-bn',
	        goodsSearchBrand: '.goods-search-brand',
	        insertWhere: null,
	        textcol: null,
	        view: null,
	        getPro: function(insertDom, getProUrl, catId, brandId, name, bn, brand, callback){
	            $.ajax({
	                url: getProUrl,
	                type: 'POST',
	                dataType: 'html',
	                data: {
	                    "searchname": name,
	                    "searchbn": bn,
	                    "searchbrand": brand,
	                    "catId": catId,
	                    "brandId": brandId
	                },
	                success: function(rs) {
	                    $(insertDom).html(rs);
	                    var list = $(insertDom).find('li');
	                    for (var i = 0; i < list.length; i++) {
	                        var itemId = $(list[i]).attr('data-id');
	                        for (var j = 0; j < selectId.length; j++) {
	                            if(itemId == selectId[j]){
	                                $(list[i]).addClass('checked');
	                            }
	                        };
	                    };
	                    if(callback) {
	                        return callback();
	                    }

	                }
	            });
	        }

	    }, options || {});

	    var selectId = [];
	    var getProUrl,catId,brandId,insertDom,data,editid,getGoodsUrl,insertWhere,limit,that,container;
	    
	    $(options.target).on('click', options.handle, function(){
	    	that = $(this);
        	container = $(that).parent().parent();
        	data = $(that).data();
	        for (var i in data) {
	            if(i === 'remote'){
	                delete(data[i]);
	            }
	            if(i === 'fetchgoods'){
	                delete(data[i]);
	            }
	            if(i === 'target'){
	                delete(data[i]);
	            }
	            if(i === 'limit'){
	                delete(data[i]);
	            }
	        };
	        editid = $(that).attr('data-item_id');
	        getGoodsUrl = $(that).attr('data-fetchgoods');
	        if(editid && editid.length > 0){
	            $.post(getGoodsUrl, data, function(rs) {
	                if(rs){
	                    insertWhere = container.find($(that).data('insertwhere') || options.insertWhere);
	                    $(insertWhere).html(rs);
	                };
	            });
	        }
            insertWhere = container.find($(that).data('insertwhere') || options.insertWhere);
            limit = $(that).data('limit');
            selectId = [];
            
            if(insertWhere){
            	var lastSelected = $(insertWhere).find('tr');
	            $(lastSelected).each(function(index, el) {
	                selectId.push($(el).data('itemid'));
	            });
	            console.log(selectId);
            }else{
            	var tplist = $(that).data('item_id');
            	for (var i = 0; i < tplist.length; i++) {
            		selectId.push(tplist[i]);
            	};
            }

            insertDom = options.goodsList;
            $(options.modalDom).on('shown.bs.modal',function(){
            	getProUrl = $(options.filterBtn).attr('data-remote') || options.getProUrl;
	            options.getPro(insertDom, getProUrl,'','','','','',function(){
	                var list = $(insertDom).find('li');
	                for (var i = 0; i < list.length; i++) {
	                    var itemId = $(list[i]).attr('data-id');
	                    for (var j = 0; j < selectId.length; j++) {
	                        if(itemId == selectId[j]){
	                            $(list[i]).addClass('checked');
	                        }
	                    };
	                };
	            });
            });
            
    	})
		.on('click', options.catlistItem, function(e) {
            e.stopPropagation();
            var isData = $(this).attr('data-val');
            if(isData){
                catId = $(this).attr('data-val');
                var catName = $(this).children('span').text();
                $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
            }
        })
        .on('click', options.brandListItem, function(){
            brandId = $(this).attr('data-val');
            var catName = $(this).text();
            $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
        })
        .on('click', options.filterBtn, function() {
            var name = $(this).parent().find('input[name="item_title"]').val();
            var bn = $(this).parent().find('input[name="item_bn"]').val();
            var brand = $(this).parent().find('input[name="item_brand"]').val();
            options.getPro(insertDom, getProUrl, catId, brandId, name, bn, brand, function(){
                var list = $(insertDom).find('li');
                for (var i = 0; i < list.length; i++) {
                    var itemId = $(list[i]).attr('data-id');
                    for (var j = 0; j < selectId.length; j++) {
                        if(itemId == selectId[j]){
                            $(list[i]).addClass('checked');
                        }
                    };
                };
            });
        })
        .on('click', options.goodsListItem, function(){
            $(this).toggleClass('checked');
            var dataId = $(this).data('id');

            if($(this).hasClass('checked')){
                if(limit) {
                    if(typeof(limit) === 'number' && parseInt(limit) > 0){
                        limit = parseInt(limit);
                    }else{
                        return
                    }
                    if($(this).parent().parent().find('.checked').length > limit){
                        alert('最多只能选择' + limit + '个商品')
                        $(this).removeClass('checked');
                        return
                    }
                }
                selectId.push(dataId);
            }else{
                for (var i = 0; i < selectId.length; i++) {
                    if(selectId[i] == dataId){
                        selectId.splice(i,1);
                    }
                };
            }
        })
        .on('click',options.checkAll,function(){
            var list = $(options.goodsListItem);
            var thisIds = []
            list.addClass('checked')
            for (var i = 0; i < list.length; i++) {
                var ids = $(list[i]).data('id');
                thisIds.push(ids);
            };
            selectId = $.unique($.merge(selectId, thisIds));
        })
        .on('click',options.uncheckAll,function(){
            var list = $(options.goodsListItem);
            var thisIds = [];
            list.removeClass('checked');
            var tempRemoveIds = selectId.concat();
            for (var i = 0; i < list.length; i++) {
                var ids = $(list[i]).data('id');
                thisIds.push(ids);
            };
            for (var i = 0; i < tempRemoveIds.length; i++) {
                for (var j = 0; j < thisIds.length; j++) {
                    if(tempRemoveIds[i] == thisIds[j]){
                        selectId.splice(0,1);
                        break;
                    }
                }
            };
        })
        .on('mouseover mouseout', options.catBox, function(event){
            var el = $(this).children('.filters-list');

            if(event.type == "mouseover"){
              el.show();
            }else if(event.type == "mouseout"){
              el.hide();
            }
        })
        .on('mouseover mouseout', options.brandBox, function(event){
            var el = $(this).children('.filters-list');

            if(event.type == "mouseover"){
              el.show();
            }else if(event.type == "mouseout"){
              el.hide();
            }
        })
        .on('mouseover mouseout', options.catlistItem, function(event){
            var el = $(this).children('.child-list');
            
            if(!!$(el)){
                if(event.type == "mouseover"){
                  el.show();
                }else if(event.type == "mouseout"){
                  el.hide();
                }
            }
        })
        .on('click', options.clearFilterBtn, function(e){
            e.preventDefault();
            catId = null;
            brandId = null;
            $(options.catBox).find('.filter-name').text(options.catDefault);
            $(options.brandBox).find('.filter-name').text(options.brandDefault);
            $(options.brandList).empty();
            $(options.goodsSearchKey).val('');
            $(options.goodsSearchBn).val('');
            $(options.goodsSearchBrand).val('');
            getProUrl = $(options.filterBtn).attr('data-remote') || options.getProUrl;
            options.getPro(insertDom, getProUrl,'','','','',function(){});
        })
        .on('click', options.clearBrandBtn, function(e){
            e.preventDefault();
            brandId = null;
            $(options.brandBox).find('.filter-name').text(options.brandDefault);
        })
        .on('click', options.submitBtn, function(){
            if (selectId.length == 0) {
                alert('请选择商品');
                return;
            }
            var cid = {
                'item_id' : selectId
            };
            data = $(that).data();
            data = $.extend(data, cid);
            $(that).attr('data-item_id',JSON.stringify(selectId));
            $.post(getGoodsUrl, data, function(rs) {
                if (rs) {
                    $(container.find(insertWhere)).html(rs);
                    $($(that).attr('data-modal') || options.modalDom).modal('hide');
                } else {
                    alert('还未添加商品');
                }
            });
        })
        .on('click', '.pagination a', function(e){
            e.preventDefault();
            if(!$(this).parent().hasClass('disabled')){
                $.post($(this).attr('href'),function(rs){
                    $(insertDom).html(rs);
                    var list = $(insertDom).find('li');
                    for (var i = 0; i < list.length; i++) {
                        var itemId = $(list[i]).attr('data-id');
                        for (var j = 0; j < selectId.length; j++) {
                            if(itemId == selectId[j]){
                                $(list[i]).addClass('checked');
                            }
                        };
                    };
                });
            };
        });
	};

	var couponsChoose = function(options){
		options = $.extend({
	        handle: '.select-coupons',
	        modalDom: '#coupons_modal',
	        insertWhere: '#selected_coupons',
	        submitBtn: '#choose_coupons',
	        items: '.shop-coupon-list li'
		}, options || {});
		
		var selectId = [],
			insertWhere;

		$(doc).on('click', options.handle, function(){
			selectId = [];
			insertWhere = $(doc).find(options.insertWhere);
            var lastSelected = $(insertWhere).find('li');
            $(lastSelected).each(function(index, el) {
                selectId.push($(el).data('couponid'));
            });

            $(options.modalDom).on('shown.bs.modal',function(){
            	var coupons = $(options.modalDom).find(options.items);
            	for (var i = 0; i < selectId.length; i++) {
            		for (var j = 0; j < coupons.length; j++) {
            			if(selectId[i] == $(coupons[j]).data('couponid')){
            				$(coupons[j]).addClass('active');
            			}
            		};
            	};
            });

            $(options.modalDom).on('hidden.bs.modal',function(){
            	$(this).removeData();
            });
		});

		$(options.modalDom).on('click', options.items, function(){
			var list = $(options.modalDom).find('.active');
			if(list.length > 2 && !$(this).hasClass('active')) return;
			$(this).toggleClass('active');
		})
		.on('click', options.submitBtn, function(){
			var list = $(options.modalDom).find('.active');
			if(list.length != 0){
				var selectedDom = '<ul class="shop-coupon-list form-list">';
				for (var i = 0; i < list.length; i++) {
					selectedDom += '<li data-couponid="'+ $(list[i]).data('couponid') +'"><div class="coupon-amount">'+ $(list[i]).find('.coupon-amount').text() +'</div><div class="coupon-rule">'+ $(list[i]).find('.coupon-rule').text() +'</div><div class="coupon-deadline">'+ $(list[i]).find('.coupon-deadline').text() +'</div><div class="coupon-del"><i class="fa fa-times"></i></div></li>';
				};
				selectedDom += '</ul>';
				$(insertWhere).html(selectedDom);
                $('#coupons_modal').modal('hide');
            }else{
                alert('请选择优惠券!');
            }
		})
	};

	var promotionChoose = function(options){
		options = $.extend({
	        handle: '.select-promotion',
	        modalDom: '#promotions_modal',
	        submitBtn: '#choose_promotion',
	        items: '.shop-promotion-list li'
		}, options || {});

		var that;

		$(doc).on('click', options.handle, function(){
            var selectedId = $(this).data('promotion_id');
            that = $(this);
            if(selectedId){
	            $(options.modalDom).on('shown.bs.modal',function(event){
	            	var list = $(options.modalDom).find(options.items);
	            	for (var i = 0; i < list.length; i++) {
	            		if(selectedId == $(list[i]).data('promotionid')){
	            			$(list[i]).addClass('active');
	            		}
	            	};
	            });
            }

            $(options.modalDom).on('hidden.bs.modal',function(){
            	$(this).removeData();
            });
		})
		.on('click', options.items, function(){
			$(this).toggleClass('active').siblings().removeClass('active');
		})
		.on('click', options.submitBtn, function(){
			var selected = $(options.modalDom).find('.active');
			if($(selected).length == 0){
                alert('请选择活动!');
                return;
            }
            $(that).attr('data-promotion_id', $(selected).data('promotionid'));
            $(that).attr('data-promotion_type', $(selected).data('promotiontype'));
            $(options.modalDom).modal('hide');
		})
	};

	//图片选择器
	var imagesChoose = function(options) {
	    options = $.extend({
	        url: '',
	        target: document.body,
	        fileName: 'upload_files',
	        inputName: 'images[]',
	        size: 500 * 1024,
	        width: 50,
	        height: 50,
	        multiple: true,
	        isModal: false,
	        limit: 0,
	        handle: '.action-upload',
	        file_input: '.action-file-input',
	        insertWhere: null,
	        callback: null
	    }, options || {});


	    var button, limit, name, uploadItem, hasImgNum;
	    var selectId = [];

	    $(options.target).on('click', options.handle, function(e) {
	        var handle = $(this);
	        var container = handle.parent();
	        var file_input = container.find(options.file_input);
	        file_input.click();
	    })
	    .on('change', options.file_input, function(e) {
	        var file_input = $(this);
	        var container = file_input.parent();
	        var handle = container.find(options.handle);

	        var fileName = this.getAttribute('data-filename') || options.fileName;
	        var size = this.getAttribute('data-size');
	        size = Number(size) || options.size;


	        var insertWhere = container.find(this.getAttribute('data-insertwhere') || options.insertWhere || '.image-box');

	        var multiple = this.multiple;
	        var isModal = this.getAttribute('data-ismodal');
	        var url = this.getAttribute('data-remote') || options.url;
	        var limit = this.getAttribute('data-max') || options.limit;
	        var inputName = this.getAttribute('name') || options.inputName;
	        var callback = this.getAttribute('data-callback') || file_input.data('callback') || options.callback;

	        var data = new FormData();
	        var files = this.files;

	        if (limit) {
	            var length = container.find('.img-thumbnail:not(.action-upload)').length,
	                filelen;
	            if(multiple && files) {
	                filelen = files.length;
	            }
	            else {
	                filelen = 1;
	            }
	            if(length + filelen > limit) {
	                alert('超出限制，最多上传' + limit + '张。');
	                return false;
	            }
	        }

	        if(!files || !Array.prototype.slice.call(files).every(function (file, i) {
	            if(file.size > size) {
	                alert('抱歉，上传图片 "' + file.name + '" 须小于' + size / 1024 + 'kB!');
	                file_input.val('');
	                return false;
	            }
	            if(multiple) {
	                data.append(fileName + '[]', file);
	            }
	            else {
	                data.append(fileName, file);
	            }
	            return true;
	        })) return false;

	        $.ajax({
	            url: url,
	            type: 'POST',
	            data: data,
	            cache: false,
	            contentType: false,
	            processData: false,
	            success: function (rs) {
	                try {
	                    rs = JSON.parse(rs);
	                }
	                catch (e) {}
	                if(rs.success && rs.data) {
	                    if(callback) {
	                        return callback(rs);
	                    }
	                    var html = '';
	                    if(multiple) {
                            var modal = handle.parents('.modal');

                            $('#areaselect_modal').modal('hide');

                            var type = modal.find('.nav-tabs .active').attr('data-type');
                            if(modal.find('.has-searched')){
                                var name = $('.has-searched').val();
                            }else{
                                var name = '';
                            }
                            if(modal.find('.gallery-condition .active').hasClass('time')){
                                var orderBy = modal.find('.gallery-condition .active').attr('data-order') + ' ' + modal.find('.gallery-condition .active').attr('data-sort');
                            }else{
                                var orderBy = modal.find('.gallery-condition .active').attr('data-order');
                            }
                            $.each(rs.data, function (k, data) {
                                if(data.url) {
                                    var item = {}
                                    item.img_id = data.image_id;
                                    item.img_src = data.t_url;
                                    // selectId.push(item);
                                    if(file_input.parents('.note-image-dialog').length <= 0) {
                                        if(isMultiple && isMultiple == 'true') {
                                            html += '<div class="col-sm-3"><div class="thumbnail checked"><div class="img-show"><a href='+ data.url +'"><img src="' + data.t_url +'"></a></div><div class="caption" data-name="'+ data.url +'" data-url="'+ data.url +'"><p class="image-name">'+ data.image_name +'</p></div></div></div>';
                                        }else{
                                            html += '<div class="col-sm-3"><div class="thumbnail"><div class="img-show"><a href='+ data.url +'"><img src="' + data.t_url +'"></a></div><div class="caption" data-name="'+ data.url +'" data-url="'+ data.url +'"><p class="image-name">'+ data.image_name +'</p></div></div></div>';
                                        } 
                                    } else {
                                        html += '<div class="col-sm-3"><div class="thumbnail"><div class="img-show"><a href='+ data.url +'"><img src="' + data.t_url +'"></a></div><div class="caption" data-name="'+ data.url +'" data-url="'+ data.url +'"><p class="image-name">'+ data.image_name +'</p></div></div></div>';
                                    }
                                }
                            });

                            $(html).insertBefore($('.gallery > div:first-child'));
	                    }else{
	                        var data = rs.data[fileName];
	                        if(data.url) {
	                            html = '<img src="' + data.url +'"><input type="hidden" name="' + inputName + '" value="' + data.image_id +'">';
	                        }
	                        $(insertWhere).html(html);
	                    }
	                }else if(rs.message) {
	                    alert(rs.message);
	                    file_input.val('');
	                }
	            },
	            error: function () {
	                alert('上传出错，请重试');
	            }
	        });
	    });


	    $('#gallery_modal').on('shown.bs.modal', function(event) {
	        selectId = [];
	        button = $(event.relatedTarget);
	        limit = button.attr('data-limit');
	        isMultiple = button.attr('data-isMultiple');
	        name = button.attr('data-name');
	        uploadItem = button.parents('.multiple-upload').find('.multiple-item');
	        hasImgNum = button.parent().find('.multiple-item');
	        $(this).attr('data-ids',selectId);
	        if (hasImgNum) {
	            $(hasImgNum).each(function(index, el) {
	                var item = {}
	                item.img_id = $(el).find('input[name^="list"]').val();
	                item.img_src = $(el).find('img').attr('src');
	                selectId.push(item);
	            })
	            isChecked();
	        }
	    })
	    .on('click','.gallery-modal-tabs li a', function(e) {
	        e.preventDefault();
	        var $this = $(this);
	        $(this).parent().addClass('active').siblings('.active').removeClass('active');
	        var urlData = $(this).attr('href');
	        $.post(urlData, function(data) {
	            $this.parents('.nav-tabs-custom').find('.gallery-modal-content').empty().append(data);
	            isChecked();
	        });
	    })
	    .on('click','.action-save',function(){
            var img = $('#gallery_modal').find('.checked');
            if(img.length>0){
                $('#gallery_modal').modal('hide');
            }else{
                alert('请选择图片!');
            }
	    })
	    .on('hide.bs.modal', function (e) {
	        $(this).removeData('bs.modal');
	    })
	    .on('click','.thumbnail',function(e){
	        e.preventDefault();
	        var item = {};
	        item.img_id = $(this).find('.caption').attr('data-name')
	        item.img_src = $(this).find('.caption').attr('data-url')
	        $(this).addClass('checked').parent().siblings().find('.thumbnail').removeClass('checked');
	    }).on('click', '.pagination a', function(e){
	        e.preventDefault();
	        if(!$(this).parent().hasClass('disabled')){
	            var confirmselectId = selectId;
	            $('#gallery_modal').find('.thumbnail.checked').each(function(){
	                var isexist = false;
	                var item = {};
	                item.img_id = $(this).find('.caption').attr('data-name');
	                item.img_src = $(this).find('.caption').attr('data-url');
	                for (var i = 0; i < confirmselectId.length; i++) {
	                    if(confirmselectId[i].img_id.indexOf(item.img_id) >= 0){
	                        isexist = true;
	                    }
	                }
	                if(!isexist) {
	                   selectId.push(item); 
	                }
	            });
	            $.post($(this).attr('href'),function(rs){
	                setTimeout(function(){
	                    var list = $('.img-show a');
	                    if(hasImgNum){
	                        for (var j = 0; j < list.length; j++) {
	                            for (var i = 0; i < selectId.length; i++) {
	                                if(selectId[i].img_id.indexOf($(list[j]).attr('href')) >= 0){
	                                    $(list[j]).parent().parent().addClass('checked');
	                                }
	                            }
	                        }
	                    }
	                },100);
	            });
	        }
	    });

	    function isChecked() {
	        var list = $('.img-show a');
	        for (var j = 0; j < list.length; j++) {
	            for (var i = 0; i < selectId.length; i++) {
	                if(selectId[i].img_id.indexOf($(list[j]).attr('href')) >= 0) {
	                    $(list[j]).parent().parent().addClass('checked');
	                }
	            }
	        }
	    }
	}

	$.extend({
		decorate: function(){
			$layout.handle(); $widgets.handle(); $control.handle();
			imagesChoose(); goodsChoose(); couponsChoose(); promotionChoose();

			$('.shop-sign-edit').click(function(event) {
				$EVENT.edit();
				$('#gallery_modal').on('shown.bs.modal',function() {
					$('.action-save').click(function(){
						var img = $('#gallery_modal').find('.checked img');
						var url = $('#gallery_modal').find('.checked .caption');
						$('.shop-sign-img img').attr('src',$(img).attr('src'));
						var shopsign = 'shopsign';
						var baseData = JSON.parse(widgetsData[shopsign]);
						if(!jsonData.shopsign){
							$data.create(shopsign,shopsign,baseData);
						}

						$EVENT.updateData('shopsign','imgurl',$(url).data('name'));
						$('.modal').off('hidden.bs.modal').off('shown.bs.modal');
					});
				});
			});
		}
	});
	
	$(function(){
		window.decorate = $.decorate();

		setHeight();
		$(window).resize(function() {
			setHeight();
		});

		function setHeight(){
			var wheight = $(window).height(); 
			$('.' + namespace).height(wheight-60);
		};

		var hasWigData = $('.'+namespace).data('haswidgets');
		if(hasWigData){
			jsonData = hasWigData;
			saveUpdate(jsonData);
		}

		$(saveBtn).click(function() {
			var request = $(this).data('remote');
			var wtype = $(this).data('type');
			jsonData.sort(sortby('sn'));
			console.log(jsonData);
			$.post(request, {'decorate': JSON.stringify(jsonData),'type': wtype}, function(rs){
				if(rs.success){
					alert('保存成功');
					jsonData = JSON.parse(rs.redirect);
					$($.className(_controlWrapper)).find('ol').empty();
					$($.className(_layoutItems)).remove();
					saveUpdate(jsonData);
				}else{
					alert(rs.message);
				}
			});
		});

		function saveUpdate(data){
			for (var i=0; i<data.length; i++) {
				if(data[i].wid!='shopsign'){
					var templ = $EVENT.createTemp(data[i].wid,data[i].widget_name);
					$($.className(_layoutWrapper)).append(templ);
					var controlItem = '<li class="dd-item decorate-control-items" data-widgets="'+ data[i].widget_name +'" data-id="'+ jsonData[i].wid +'"><div class="dd-handle"></div></li>';
					$($.className(_controlWrapper)).find('ol').append(controlItem);
				}else{
					$('.shop-sign-img img').attr('src',data[i].imgsrc);
				}
			}
		}
	});
})(jQuery,document);

$(function() {
  // 用于格式化日期显示
  Date.prototype.format = function(format) {
    var o = {
      "Y+": this.getFullYear(), //year
      "M+": this.getMonth() + 1, //month
      "d+": this.getDate(), //day
      "h+": this.getHours(), //hour
      "m+": this.getMinutes(), //minute
      "s+": this.getSeconds(), //second
      "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
      "S": this.getMilliseconds() //millisecond
    }
    if(/(y+)/.test(format)) format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for(var k in o)
      if(new RegExp("(" + k + ")").test(format))
        format = format.replace(RegExp.$1,
          RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
    return format;
  };

  template.openTag = '<%';
  template.closeTag = '%>';

  //格式化价格
  template.helper('$format_price', function(num, force) {
    var spec = {
      "decimals": 2,
      "dec_point": ".",
      "thousands_sep": "",
      "sign": "\uffe5"
    };
    var part;
    var sign = spec.sign || '';
    if(!(num || num === 0) || isNaN(+num)) return num;
    var num = parseFloat(num);
    if(spec.cur_rate) {
      num = num * spec.cur_rate;
    }
    num = Math.round(num * Math.pow(10, spec.decimals)) / Math.pow(10, spec.decimals) + '';
    var p = num.indexOf('.');
    if(p < 0) {
      p = num.length;
      part = '';
    } else {
      part = num.substr(p + 1);
    }
    while(part.length < spec.decimals) {
      part += '0';
    }
    var curr = [];
    while(p > 0) {
      if(p > 2) {
        p -= 3;
        curr.unshift(num.substr(p, 3));
      } else {
        curr.unshift(num.substr(0, p));
        break;
      }
    }
    if(!part) {
      spec.dec_point = '';
    }
    if(force) {
      sign = force;
    }
    return sign + curr.join(spec.thousands_sep) + spec.dec_point + part;
  });

  //价格转换正整数
  template.helper('$int_price', function(num) {
    return Math.round(num);
  });

  //JSON转字符串
  template.helper('$json_string', function(num) {
    return JSON.stringify(num);
  });

  //日期格式转换
  template.helper('$timestamp_To_Date', function(value) {
    if(value == null || value == "" || value == "null") return "";
    var date = new Date(Number(value * 1000));
    return date.format("Y.MM.dd");
  });

  //时间格式转换
  template.helper('$timestamp_To_Time', function(value) {
    if(value == null || value == "" || value == "null") return "";
    var date = new Date(Number(value * 1000));
    return date.format("Y.MM.dd hh:mm:ss");
  });
});
