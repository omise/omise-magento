    jQuery.noConflict();
    jQuery(document).ready(function(){
        //money withdraw button
        jQuery('.transfer-btn-delete').on('click', function(e) {
            e.preventDefault();

            if (confirm('Delete ?')) {
                var $this       = jQuery(this),
                    deleteLink  = $this.attr('href'),
                    form        = $this.closest('form');
                
                form.attr('action', deleteLink)
                    .append('<input type="hidden" name="OmiseTransfer[action]" value="delete">');

                jQuery("#omise-transfer").submit();
            }
        });

        // temporary data
        var chargeData = null, transferData = null;

        // transform charge data into charge table
        var setChargeTable = function(i, data){
            var td = jQuery('#charge-table>tbody').find('tr').eq(i).find('td');
                td.eq(0).text('฿ '+data.amount_format);
                td.eq(1).text(data.id);
                td.eq(2).html(data.failure_code?'<span class="error-label">Fail</span>':data.captured?'<span class="success-label">Captured</span>': '<span class="warning-label">Authorized</span>');
                if(data.refunded>0){
                    var aRefund = showRefundPopup('view2', data, '฿' + data.refund_format, function(aRefundAmount){
                        td.eq(3).html('').append(aRefundAmount);
                    });
                    td.eq(3).html('').append(aRefund);
                }

                
                td.eq(4).text(data.failure_code?('('+data.failure_code+')'+data.failure_code):'-');
                td.eq(5).text(data.created);

                td.eq(6).html('');     
                var isRefundButtonShow = data.refund_format?false:true;
                if(isRefundButtonShow){
                    var aRefund = showRefundPopup('view1', data, 'refund', function(aRefundAmount){
                        td.eq(3).html('').append(aRefundAmount);
                    });
                    td.eq(6).append([aRefund, ' ']);
                }

                var aCardInfo = jQuery('<a>card info</a>', {href: '#'} );
                td.eq(6).append(aCardInfo);
        }

        // load charge data with specific page
        var loadChageTable = function(page, callback){
            jQuery('.charge-loading.load-background').show();
            jQuery.getJSON( charge_url, {page: page}, function( charge ) {
                for(var i=0;i<charge.data.length;i++){
                    var data = charge.data[i];
                    setChargeTable(i, data);
                }

                jQuery('.charge-loading.load-background').hide();
                chargeData = charge;
                if(callback) callback();    
            });
        }

        // handle charge pagination
        var nextChargePage = function(direction){
            np = parseInt(jQuery('#charge-pn').text()) + direction;
            np = np < 1 ? 1 : np;
            np = np > charge_total ? charge_total : np;
            loadChageTable(np, function(){

                jQuery('#charge-btn-back').show();
                if(np == 1) jQuery('#charge-btn-back').hide();

                jQuery('#charge-btn-first').show();
                if(np == 1) jQuery('#charge-btn-first').hide();

                 jQuery('#charge-btn-next').show();
                if(np == charge_total) jQuery('#charge-btn-next').hide();

                jQuery('#charge-btn-last').show();
                if(np == charge_total) jQuery('#charge-btn-last').hide();

              jQuery('#charge-pn').text(np);  
            });
        }

        var gotoChargeFirstPage = function(){
            loadChageTable(1, function(){

                jQuery('#charge-btn-back').hide();
                jQuery('#charge-btn-first').hide();
                jQuery('#charge-btn-next').show();
                jQuery('#charge-btn-last').show();

              jQuery('#charge-pn').text(1);  
            });
        }

        var gotoChargeLastPage = function(){
            loadChageTable(charge_total, function(){

                jQuery('#charge-btn-back').show();
                jQuery('#charge-btn-first').show();
                jQuery('#charge-btn-next').hide();
                jQuery('#charge-btn-last').hide();

              jQuery('#charge-pn').text(charge_total);  
            });
        }

        // first load chrage table for the first page
        nextChargePage(0);

        // load transform data and transform into transfer table 
        var loadTransferTable = function(page, callback){
            jQuery('.transfer-loading.load-background').show();
            jQuery.getJSON( transfer_url, {page: page}, function( transfer ) {
                for(var i=0;i<transfer.data.length;i++){
                    var data = transfer.data[i];
                    var td = jQuery('#transfer-table>tbody').find('tr').eq(i).find('td');
                    td.eq(0).text(data.amount);
                    td.eq(1).text(data.id);
                    td.eq(2).html(data.failure_code?'<span class="error-label">Fail</span>':data.sent?data.paid?'<span class="success-label">Paid</span>':'<span class="primary-label">Request sent</span>':'<span class="warning-label">Requesting</span>' );
                    td.eq(3).text(data.failure_code?('('+data.failure_code+')'+data.failure_code):'-');
                    td.eq(4).text(data.created);
                }
                transferData = transfer;
                jQuery('.transfer-loading.load-background').show();
                if(callback) callback();    
            });
        }

        // handle transform pagination
        var nextTransferPage = function(direction){
            np = parseInt(jQuery('#transfer-pn').text()) + direction;
            np = np < 1 ? 1 : np;
            np = np > transfer_total ? transfer_total : np;
            loadTransferTable(np, function(){
                
                jQuery('#transfer-btn-back').show();
                if(np == 1) jQuery('#transfer-btn-back').hide();

                jQuery('#transfer-btn-first').show();
                if(np == 1) jQuery('#transfer-btn-first').hide();

                jQuery('#transfer-btn-next').show();
                if(np == transfer_total) jQuery('#transfer-btn-next').hide();

                jQuery('#transfer-btn-last').show();
                if(np == transfer_total) jQuery('#transfer-btn-last').hide();

                jQuery('#transfer-pn').text(np);  
            });
        }

        var gotoTransferFirstPage = function(){
            loadTransferTable(1, function(){

                jQuery('#transfer-btn-back').hide();
                jQuery('#transfer-btn-first').hide();
                jQuery('#transfer-btn-next').show();
                jQuery('#transfer-btn-last').show();

              jQuery('#transfer-pn').text(1);  
            });
        }

        var gotoTransferLastPage = function(){
            loadTransferTable(transfer_total, function(){

                jQuery('#transfer-btn-back').show();
                jQuery('#transfer-btn-first').show();
                jQuery('#transfer-btn-next').hide();
                jQuery('#transfer-btn-last').hide();

              jQuery('#transfer-pn').text(transfer_total);  
            });
        }

        // event handle for click page number
        jQuery('#charge-btn-back').on('click', function(e){
            e.preventDefault();
            nextChargePage(-1);
        });

        jQuery('#charge-btn-first').on('click', function(e){
            e.preventDefault();
            gotoChargeFirstPage();
        });

        jQuery('#charge-btn-next').on('click', function(e){
            e.preventDefault();
            nextChargePage(1);
        });

        jQuery('#charge-btn-last').on('click', function(e){
            e.preventDefault();
            gotoChargeLastPage();
        });

        jQuery('#transfer-btn-back').on('click', function(e){
            e.preventDefault();
            nextTransferPage(-1);
        });

        jQuery('#transfer-btn-first').on('click', function(e){
            e.preventDefault();
            gotoTransferFirstPage();
        });

        jQuery('#transfer-btn-next').on('click', function(e){
            e.preventDefault();
            nextTransferPage(1);
        });

        jQuery('#transfer-btn-last').on('click', function(e){
            e.preventDefault();
            gotoTransferLastPage();
        });

        // custom function to call to show refund popup
        var showRefundPopup = function(view, data, text, ext){
            var aRefund = jQuery('<a>'+ text +'</a>', {href: '#'} );
            aRefund.on('click', function(e){
                e.preventDefault();
                    popup = new RefundPopup(data, view, function(done, d){
                        if(done){
                            aRefund.hide();
                            var aRefundAmount = jQuery('<a> ฿'+d.refund_format+'</a>', {href: '#'} );
                                aRefundAmount.on('click', function(e){
                                    e.preventDefault();
                                        popup = new RefundPopup(d, 'view2');
                                    popup.show();
                                });
                            ext(aRefundAmount);
                        }
                    });
                popup.show();
            });

            return aRefund;
        }

        // refund popup object
        var RefundPopup = function(charge, v, done){

            var body = jQuery('body'),
                background = jQuery('<div>', {class: 'popup-background'}),
                content = jQuery('<div>', {class: 'popup-content'}),
                isBackgroundClikable = true;

            // add popup to frontend
            background.append(content);
            body.append(background);

            // popup action
            var show = function(){
                    background.fadeIn(200);
                },

                hide = function(){
                    background.fadeOut(200, function(){
                        background.remove();
                    });
                },
                close = function(btn){
                    btn.on('click', function(e){ 
                        if(isBackgroundClikable) {
                            hide(); 
                        }else{
                            alert('Payment is processing');
                        }
                    });
                };

            var views = {};

            // view 1: refund form
            views.view1 = function(){
                    var selected = 0;
                    var view = jQuery('.custom-template.refund-view1').clone().show(),
                        list = view.find('ul li'),
                        button = view.find('.create'),
                        patial = view.find('#patial-refund'),
                        load = view.find('.refund-loading');

                    // select refund option
                    // option 0 = full refund, 1 = patial refund
                    list.on('click', function(){
                        var _this = jQuery(this);
                        list.removeClass('selected');
                        _this.addClass('selected');
                        selected = _this.index();
                    });

                    button.on('click', function(){
                        isBackgroundClikable = false;
                        load.show();
                        var isPartial = (selected==1);
                        var _this = this;
                        var final_amount = (charge.amount - charge.refunded);
                        var amount_valid = isPartial?(parseInt(patial.val())*100 <= final_amount): true;
                        
                        if(amount_valid){
                            jQuery(_this).attr('disabled','disabled');

                            jQuery.get(omise_refund_url, { 
                                charge: charge.id,
                                amount: (isPartial? patial.val(): final_amount),
                                partial: isPartial
                            }).done(function(data) {
                                data = jQuery.parseJSON(data);

                                jQuery.get(omise_charge_url, {
                                    charge: charge.id
                                }).done(function(chargeData) {
                                    chargeData = jQuery.parseJSON(chargeData);
                                    if(done){
                                        done(true, chargeData);
                                    }
                                });

                                content.html('');
                                content.append(views['view3']());

                            }).fail(function(data) {
                                load.hide();
                                isBackgroundClikable = true;
                                jQuery(_this).removeAttr('disabled');
                                alert('error');
                            });
                        }else{
                            alert('Refund amount is not valid!');
                            isBackgroundClikable = true;
                            load.hide();
                        }
                    });

                    close(view.find('.popup-close'));

                    return view;
                };

            // view 2: show refund history
                views.view2 = function(){
                    var view = jQuery('.custom-template.refund-view2').clone().show(),
                        list = view.find('ul li').eq(0).clone(),
                        button = view.find('.create'),
                        remark = view.find('.remark');

                    view.find('ul li').eq(0).hide();

                    var refreshView = function(charge){
                        view.find('.refund-header .title').text('Refunded ฿ ' + charge.refund_format);
                        view.find('.refund-header .description').text('From charge id: ' + charge.id);

                        view.find('ul').html('');
                        var cd = charge.refunds.data;
                        for(var i = 0; i < cd.length; i++){
                            var li = list.clone();
                            li.show();
                            li.find('.title').text('฿ ' + cd[i].refund_format);
                            li.find('.description').text(cd[i].id);
                            li.find('.time').text(cd[i].created);
                            view.find('ul').append(li);
                        }
                    }

                    refreshView(charge);

                    //try to retrieve new data
                    jQuery.get(omise_charge_url, { 
                        charge: charge.id
                    }).done(function(chargeData) {
                        chargeData = jQuery.parseJSON(chargeData);
                        refreshView(chargeData);
                    });

                    //if no more amount to refund, then hide button to create refund
                    if(charge.amount == charge.refunded){
                        button.hide();
                        remark.show();
                    }else{
                        remark.hide();
                        button.show();
                        button.on('click', function(){
                            content.html('');
                            content.append(views['view1']());
                        });  
                    }

                    close(view.find('.popup-close'));

                    return view;
                };

                views.view3 = function(){
                    var view = jQuery('.custom-template.refund-view3').clone().show(),
                        list = view.find('ul li').eq(0).clone(),
                        button = view.find('.create');
                        isBackgroundClikable = true;

                        close(view.find('.popup-close'));

                        return view;
                };


            // add custom view
            content.append(views[v]());

            // popup init event
            background.on('click', function(e){ 
                if(isBackgroundClikable) {
                    hide(); 
                }else{
                    alert('Payment is processing');
                }
            });

            content.on('click', function(e){ e.stopPropagation(); });

            return {
                show: show,
                hide: hide
            }
        }

    });