;var goodsChoose = function(options) {
    options = $.extend({
        url: '',
        getProUrl: '',
        getBrandUrl: '',
        getGoodsUrl: '',
        target: document.body,
        modalDom: '#goods_modal',
        handle: '.select-goods',
        catBox: '.goods-category',
        catlistItem: '.goods-category li',
        catDefault: '分类',
        brandBox: '.goods-brands',
        brandList: '#brand_list',
        brandListItem: '#brand_list li',
        brandDefault: '品牌',
        filterBtn: '#search_goods',
        skuFilterBtn: '#sku_search_goods',
        clearFilterBtn: '#clear_filter',
        clearBrandBtn: '#clear_brand',
        submitBtn: '#choose_goods',
        skuSubmitBtn: '#sku_choose_goods',
        checkAll: '#check_all',
        uncheckAll: '#uncheck_all',
        goodsList: '#goods_list',
        skuList: '#sku_list',
        goodsListItem: '#goods_list > ul > li',
        skuListItem: '#sku_list > ul > li',
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
        // getBrand: function(getBraUrl, catId, insertDom){
        //     if (catId != '') {
        //         $.ajax({
        //             url: getBraUrl,
        //             type: 'POST',
        //             dataType: 'json',
        //             data: {
        //                 "catId": catId
        //             },
        //             success: function(data) {
        //                 if (data) {
        //                     var result = '';
        //                     for (var i = 0; i < data.length; i++) {
        //                         result += '<li data-val="' + data[i].brand_id + '">' + data[i].brand_name + '</li>';
        //                     };
        //                     $(insertDom).empty().append(result);
        //                 }
        //             }
        //         });
        //     }
        // }

    }, options || {});

    var getProUrl,
        catId,
        brandId,
        modalDomName,
        insertDom,
        data;

    var selectId = [];
    var url = $(options.handle).attr('data-remote') || options.url,
        editid = $(options.handle).attr('data-item_id'),
        getGoodsUrl = $(options.handle).attr('data-fetchgoods') || options.getGoodsUrl,
        insertWhere = $(options.insertWhere || '.selected-goods-list');
        limit = $(options.handle).data('limit') || options.limit;

    $(options.target).on('click', options.handle, function(e) {
        e.preventDefault();
        var container = $(this).parents('.select-goods-panel');

        insertWhere = $(container).find(this.getAttribute('data-insertwhere') || options.insertWhere || '.selected-goods-list');
        if($(this).attr('data-modal'))
            $($(this).attr('data-modal')).modal('show');
        else
            $(options.modalDom).modal('show');
    });

    $(options.handle).each(function() {
        var that = $(this);

        data = $(this).data();
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
        editid = $(this).attr('data-item_id');
        getGoodsUrl = $(this).attr('data-fetchgoods') || options.getGoodsUrl;
        if(editid && editid.length > 0){
            $.post(getGoodsUrl, data, function(rs) {
                if(rs){
                    insertWhere = that.parents('.select-goods-panel').find(options.insertWhere || '.selected-goods-list');
                    $(insertWhere).html(rs);
                };
            });
        }
        
        $($(this).attr('data-modal') || options.modalDom).on('show.bs.modal', function() {
            insertWhere = that.parents('.select-goods-panel').find(options.insertWhere || '.selected-goods-list');
            limit = that.data('limit') || options.limit;
            selectId = [];
            var lastSelected = $(insertWhere).find('tr');
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                lastSelected = $(insertWhere).find('.sku-item');
            }
            $(lastSelected).each(function(index, el) {
                selectId.push($(el).attr('date-itemid'));
            });
            url = that.attr('data-remote') || options.url;
            $(this).find('.modal-content').load(url);
        }).on('shown.bs.modal', function() {
            editid = that.attr('data-item_id')
            getGoodsUrl = that.attr('data-fetchgoods') || options.getGoodsUrl;
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                insertDom = options.skuList;
                getProUrl = $(options.skuFilterBtn).attr('data-remote') || options.getProUrl;
            } else {
                insertDom = options.goodsList;
                getProUrl = $(options.filterBtn).attr('data-remote') || options.getProUrl;
            }
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
        }).on('hide.bs.modal', function() {
            // options.getPro(options.goodsList, getProUrl);
            options.getPro(insertDom, getProUrl);
        }).on('click', options.catlistItem, function(e) {
            e.stopPropagation();
            var isData = $(this).attr('data-val');
            if(isData){
                catId = $(this).attr('data-val');
                //var getBraUrl = $(options.brandBox).attr('data-remote') || options.getBrandUrl;
                var catName = $(this).children('span').text();
                //options.getBrand(getBraUrl, catId, options.brandList);
                $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
                //$(options.brandBox).find('.filter-name').text(options.brandDefault);
            }
        }).on('click', options.brandListItem, function(){
            brandId = $(this).attr('data-val');
            var catName = $(this).text();
            $(this).parents('.filters-list').hide().siblings('.filter-name').text(catName);
        }).on('click', options.filterBtn +',' + options.skuFilterBtn, function() {
            var name = $(this).parent().find('input[name="item_title"]').val();
            var bn = $(this).parent().find('input[name="item_bn"]').val();
            var brand = $(this).parent().find('input[name="item_brand"]').val();
            options.getPro(insertDom, getProUrl, catId, brandId, name, bn, brand, function(){
                // var list = $(options.goodsList).find('li');
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
        }).on('click', options.goodsListItem + ',' + options.skuListItem, function(){
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
                        $('#messagebox').message('最多只能选择' + limit + '个商品')
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
            var list = that.attr('data-modal') && that.attr('data-modal') == "#sku_modal" ? $(options.skuListItem) : $(options.goodsListItem);
            var thisIds = []
            list.addClass('checked')
            for (var i = 0; i < list.length; i++) {
                var ids = $(list[i]).data('id');
                thisIds.push(ids);
            };
            selectId = $.unique($.merge(selectId, thisIds));
        })
        .on('click',options.uncheckAll,function(){
            var list = that.attr('data-modal') && that.attr('data-modal') == "#sku_modal" ? $(options.skuListItem) : $(options.goodsListItem);//$(options.goodsListItem);
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
              el.show()
            }else if(event.type == "mouseout"){
              el.hide()
            }
        })
        .on('mouseover mouseout', options.brandBox, function(event){
            var el = $(this).children('.filters-list');

            if(event.type == "mouseover"){
              el.show()
            }else if(event.type == "mouseout"){
              el.hide()
            }
        })
        .on('mouseover mouseout', options.catlistItem, function(event){
            var el = $(this).children('.child-list');
            
            if(!!$(el)){
                if(event.type == "mouseover"){
                  el.show()
                }else if(event.type == "mouseout"){
                  el.hide()
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
            if(that.attr('data-modal') && that.attr('data-modal') == "#sku_modal") {
                getProUrl = $(options.skuFilterBtn).attr('data-remote') || options.getProUrl;
            }
            options.getPro(insertDom, getProUrl,'','','','',function(){
                // var list = $(insertDom).find('li');
                // for (var i = 0; i < list.length; i++) {
                //     var itemId = $(list[i]).attr('data-id');
                //     for (var j = 0; j < selectId.length; j++) {
                //         if(itemId == selectId[j]){
                //             $(list[i]).addClass('checked');
                //         }
                //     };
                // };
            });
        })
        .on('click', options.clearBrandBtn, function(e){
            e.preventDefault();
            brandId = null;
            $(options.brandBox).find('.filter-name').text(options.brandDefault);
        })
        .on('click', options.submitBtn + ',' + options.skuSubmitBtn, function(){
            if (selectId.length == 0) {
                $('#messagebox').message('请选择商品');
                return;
            }
            if(that.attr('data-modal') && that.attr('data-modal') == '#sku_modal' && selectId.length > 4) {
                $('#messagebox').message('最多只能添加4件赠品');
                return;
            }
            var cid = {
                'item_id' : selectId
            };
            data = that.data();
            data = $.extend(data, cid);
            $.post(getGoodsUrl, data, function(rs) {
                if (rs) {
                    $(insertWhere).html(rs);
                    $(that.attr('data-modal') || options.modalDom).modal('hide');
                } else {
                    $('#messagebox').message('还未添加商品');
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
            }
        });
    });
}

$(function(){
    goodsChoose();
});

