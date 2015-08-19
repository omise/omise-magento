    jQuery.noConflict();
    jQuery(document).ready(function(){

        // number data of charge and transfer table
        var chargeNum           = 5,
            transferNum         = 5;

        // temporary data
        var chargeData          = null,
            transferData        = null;

        // Spinner DOM element
        var chargeSpinner       = jQuery('.charge-loading.load-background'),
            transferSpinner     = jQuery('.transfer-loading.load-background');


        // transform charge data into charge table
        var setChargeTable = function(i, data) {
            var tr = '<tr>';

            if (data) {
                var isRefundButtonShow = data.refund_format || !data.is_refundable ? false : true;

                tr += ' <td>฿ '+data.amount_format+'</td>';
                tr += '<td>'+data.id+''+((data.is_magento) ? '(From another store)' : '')+'</td>';
                tr += '<td>'+(data.failure_code?'<span class="error-label">Fail</span>':data.captured?'<span class="success-label">Captured</span>': '<span class="warning-label">Authorized</span>')+'</td>';
                tr += '<td class="refund-amount">'

                if (data.refunded>0) {
                    tr += '<a class="refund-number clickable" data-index="'+i+'" href="#">฿ '+data.refund_format+'</a>';
                } else {
                    tr += '<a class="refund-number normal-text" data-index="'+i+'" href="#">-</a>';
                }

                tr += '</td>'
                tr += '<td>'+(data.failure_code?('('+data.failure_code+')'+data.failure_code):'-')+'</td>';
                tr += '<td class="a-center">'+data.created+'</td>';
                tr += '<td class="a-center">'

                if (isRefundButtonShow) {
                    tr += '<a class="refund-button clickable" data-index="'+i+'" href="#">refund</a>&nbsp;'
                }

                tr += '<a class="clickable">card info</a>';
                tr += '</td>';
            } else {
                tr += '<td class="a-center" colspan="7">&nbsp;</td>';
            }

            tr += '</tr>';

            return tr;
        }

        // load charge data with specific page
        var loadChageTable = function(page, callback) {
            // Show spinner
            chargeSpinner.show();

            // Request data
            jQuery.getJSON(charge_url, { page: page }, function(charge) {
                var tbody       = jQuery('#charge-table>tbody'),
                    tbodyData   = "";

                if (charge && charge.data) {
                    tbody.html('');

                    charge_total = Math.ceil(charge.total / chargeNum);

                    for (var i = 0; i < chargeNum; i++) {
                        var data = charge.data[i] || null;
                        tbodyData += setChargeTable(i, data);
                    }

                    tbody.append(tbodyData);
                }

                tbody.find('.refund-button').on('click', function(e) {
                    e.preventDefault();
                    var _this = jQuery(this);
                    var popup = new RefundPopup(charge.data[jQuery(this).attr('data-index')], 'view1', function(done, d){
                            if(done){
                                var refundAmonth = _this.parent().parent().find('.refund-amount>a');
                                _this.hide();
                                refundAmonth.removeClass('normal-text');
                                refundAmonth.addClass('clickable');
                                refundAmonth.text('฿'+d.refund_format+'');
                            }
                        });
                    popup.show();
                });

                tbody.find('.refund-number').on('click', function(e) {
                    e.preventDefault();
                    var _this = jQuery(this);
                    if(_this.hasClass('clickable')){
                        var popup = new RefundPopup(charge.data[jQuery(this).attr('data-index')], 'view2', function(done, d){
                                if(done){
                                    _this.parent().parent().find('.refund-amount>a').text('฿'+d.refund_format+'');
                                }
                            });
                        popup.show();
                    }
                });

                chargeSpinner.hide();
                chargeData = charge;
                
                if(callback) callback();    
            });
        }

        // handle charge pagination
        var nextChargePage = function (direction) {
            np = parseInt(jQuery('#charge-pn').text()) + direction;
            np = np < 1 ? 1 : np;
            np = np > charge_total ? charge_total : np;
            loadChageTable(np, function() {
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
                jQuery('#charge-btn-last').show();

                if(charge_total<=1){
                    jQuery('#charge-btn-back').hide();
                    jQuery('#charge-btn-first').hide();
                    jQuery('#charge-btn-next').hide();
                    jQuery('#charge-btn-last').hide();
                }else{
                    jQuery('#charge-btn-back').hide();
                    jQuery('#charge-btn-first').hide();
                    jQuery('#charge-btn-next').show();
                }

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
        gotoChargeFirstPage();

        var setTransferTable = function(i, data) {
            var tr = '<tr>';

            if (data) {
                tr += '<td>฿ ' + data.amount + '</td>';
                tr += '<td>'+data.id+'</td>';
                tr += '<td>'+(data.failure_code?'<span class="error-label">Fail</span>':data.sent?data.paid?'<span class="success-label">Paid</span>':'<span class="primary-label">Request sent</span>':'<span class="warning-label">Requesting</span>')+'</td>';
                tr += '<td>'+(data.failure_code?('('+data.failure_code+')'+data.failure_code):'-')+'</td>';
                tr += '<td>'+data.created+'</td>';
                tr += '<td class="a-center">'
                if (!data.sent && !data.paid) {
                    tr += '<a href="'+omise_transfer_delete.replace('transfer_id', data.id)+'" class="delete-transfer clickable">delete</a>'
                } else {
                    tr += '-';
                }
                tr += '</td>';
            } else {
                tr += '<td class="a-center" colspan="6">&nbsp;</td>';
            }

            tr += '</tr>';

            return tr;
        }

        // load transform data and transform into transfer table 
        var loadTransferTable = function(page, callback) {
            transferSpinner.show();
            jQuery.getJSON( transfer_url, {page: page}, function( transfer ) {
                var tbody       = jQuery('#transfer-table>tbody'),
                    tbodyData   = "";

                if (transfer && transfer.data) {
                    tbody.html('');
                    transfer_total = Math.ceil(transfer.total / transferNum);

                    for (var i = 0; i < transferNum; i++) {
                        var data = transfer.data[i] || null;
                        tbodyData += setTransferTable(i, data);
                    }

                    tbody.append(tbodyData);

                    //money withdraw button
                    tbody.find('.delete-transfer').on('click', function(e) {
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
                }

                transferSpinner.hide();
                transferData = transfer;

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

                if(transfer_total<=1){
                    jQuery('#transfer-btn-back').hide();
                    jQuery('#transfer-btn-first').hide();
                    jQuery('#transfer-btn-next').hide();
                    jQuery('#transfer-btn-last').hide();
                }else{
                    jQuery('#transfer-btn-back').hide();
                    jQuery('#transfer-btn-first').hide();
                    jQuery('#transfer-btn-next').show();
                    jQuery('#transfer-btn-last').show();
                }

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

        gotoTransferFirstPage();

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
                    remark.show().text('*Your refund was completed');

                    }else if(!charge.is_refundable){
                        button.hide();
                        remark.show().text('*You can not refund this transaction.');

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
                        button = view.find('.create'),
                        aOrder = view.find('a');

                    isBackgroundClikable = true;

                        aOrder.attr('href', order_url.replace(':orderid', charge.orderid)).text('Go to order #' + charge.orderid);

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