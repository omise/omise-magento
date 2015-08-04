    jQuery.noConflict();
    jQuery(document).ready(function(){
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

        var setChargeTable = function(i, data){
            var td = jQuery('#charge-table>tbody').find('tr').eq(i).find('td');
                td.eq(0).text(data.amount_format);
                td.eq(1).text(data.id);
                td.eq(2).html(data.failure_code?'<span class="error-label">Fail</span>':data.captured?'<span class="success-label">Captured</span>': '<span class="warning-label">Authorized</span>');
                if(data.refunded>0){
                    var aRefund = jQuery('<a>'+data.refund_format+'</a>', {href: '#'} );
                        aRefund.on('click', function(e){
                            e.preventDefault();
                                popup = new RefundPopup(data, 'view2', function(done, d){
                                    if(done){
                                        var aRefundAmount = jQuery('<a>'+d.refund_format+'</a>', {href: '#'} );
                                            aRefundAmount.on('click', function(e){
                                                e.preventDefault();
                                                    popup = new RefundPopup(d, 'view2');
                                                popup.show();
                                            });
                                        td.eq(3).html('').append(aRefundAmount);
                                    }
                                });
                            popup.show();
                        });
                    td.eq(3).html('').append(aRefund);
                }

                
                td.eq(4).text(data.failure_code?('('+data.failure_code+')'+data.failure_code):'-');
                td.eq(5).text(data.created);

                td.eq(6).html('');     
                var isRefundButtonShow = data.refund_format?false:true;
                if(isRefundButtonShow){
                    var aRefund = jQuery('<a>refund</a>', {href: '#'} );
                        aRefund.on('click', function(e){
                            e.preventDefault();
                                popup = new RefundPopup(data, 'view1', function(done, d){
                                    if(done){
                                        aRefund.hide();
                                        var aRefundAmount = jQuery('<a>'+d.refund_format+'</a>', {href: '#'} );
                                            aRefundAmount.on('click', function(e){
                                                e.preventDefault();
                                                    popup = new RefundPopup(d, 'view2');
                                                popup.show();
                                            });
                                        td.eq(3).html('').append(aRefundAmount);
                                    }
                                });
                            popup.show();
                        });
                    td.eq(6).append([aRefund, ' ']);
                }

                var aCardInfo = jQuery('<a>card info</a>', {href: '#'} );
                td.eq(6).append(aCardInfo);
        }

        var loadChageTable = function(page, callback){
            jQuery.getJSON( charge_url, {page: page}, function( charge ) {
                for(var i=0;i<charge.data.length;i++){
                    var data = charge.data[i];
                    setChargeTable(i, data);
                }

                chargeData = charge;

                if(callback) callback();    
            });
        }

        var nextChargePage = function(direction){
            np = parseInt(jQuery('#charge-pn').text()) + direction;
            np = np < 1 ? 1 : np;
            np = np > charge_total ? charge_total : np;
            loadChageTable(np, function(){

                jQuery('#charge-btn-back').show();
                if(np == 1) jQuery('#charge-btn-back').hide();

                 jQuery('#charge-btn-next').show();
                if(np == charge_total) jQuery('#charge-btn-next').hide();

              jQuery('#charge-pn').text(np);  
            });
        }

        nextChargePage(0);

        var loadTransferTable = function(page, callback){
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
                if(callback) callback();    
            });
        }

        var nextTransferPage = function(direction){
            np = parseInt(jQuery('#transfer-pn').text()) + direction;
            np = np < 1 ? 1 : np;
            np = np > transfer_total ? transfer_total : np;
            loadTransferTable(np, function(){
                
                jQuery('#charge-btn-back').show();
                if(np == 1) jQuery('#charge-btn-back').hide();

                jQuery('#charge-btn-next').show();
                if(np == transfer_total) jQuery('#charge-btn-next').hide();

                jQuery('#transfer-pn').text(np);  
            });
        }

        jQuery('#charge-btn-back').on('click', function(e){
            e.preventDefault();
            nextChargePage(-1);
        });

        jQuery('#charge-btn-next').on('click', function(e){
            e.preventDefault();
            nextChargePage(1);
        });

        jQuery('#transfer-btn-back').on('click', function(e){
            e.preventDefault();
            nextTransferPage(-1);
        });

        jQuery('#transfer-btn-next').on('click', function(e){
            e.preventDefault();
            nextTransferPage(1);
        });

        // refund popup object
        var RefundPopup = function(charge, v, done){

            var body = jQuery('body'),
                background = jQuery('<div>', {class: 'popup-background'}),
                content = jQuery('<div>', {class: 'popup-content'});

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
                };

            var views = {};
            // view 1: refund form
            views.view1 = function(){
                    var selected = 0;
                    var view = jQuery('.custom-template.refund-view1').clone().show(),
                        list = view.find('ul li'),
                        button = view.find('button'),
                        patial = view.find('#patial-refund');

                    list.on('click', function(){
                        var _this = jQuery(this);
                        list.removeClass('selected');
                        _this.addClass('selected');
                        selected = _this.index();

                        if(selected == 1){

                        }
                    });
                    
                    button.on('click', function(){
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
                                    hide();
                                    if(done){
                                        done(true, chargeData);
                                    }
                                });

                            }).fail(function(data) {
                                jQuery(_this).removeAttr('disabled');
                                alert('error');
                            });
                        }else{
                            alert('Refund amount is not valid!')
                        }
                    });

                    return view;
                };

            // view 2: show refund history
                views.view2 = function(){
                    var view = jQuery('.custom-template.refund-view2').clone().show(),
                        list = view.find('ul li').eq(0).clone(),
                        button = view.find('button');

                        view.find('ul li').eq(0).hide();

                        view.find('.refund-header .title').text('฿ ' + charge.refund_format);
                        view.find('.refund-header .description').text('From charge id: ' + charge.id);

                        var cd = charge.refunds.data;
                        for(var i = 0; i < cd.length; i++){
                            var li = list.clone();
                            li.show();
                            li.find('.title').text('฿ ' + cd[i].refund_format);
                            li.find('.description').text(cd[i].id);
                            view.find('ul').append(li);
                        }

                        jQuery.get(omise_charge_url, { 
                            charge: charge.id
                        }).done(function(chargeData) {
                            chargeData = jQuery.parseJSON(chargeData);
                            view.find('.refund-header .title').text('฿ ' + chargeData.refund_format);
                            view.find('.refund-header .description').text('From charge id: ' + chargeData.id);
                            var cd = chargeData.refunds.data;
                            view.find('ul').html('');
                            for(var i = 0; i < cd.length; i++){
                                var li = list.clone();
                                li.show();
                                li.find('.title').text('฿ ' + cd[i].refund_format);
                                li.find('.description').text(cd[i].id);
                                view.find('ul').append(li);
                            }
                        });
                    if(charge.amount == charge.refunded){
                        button.hide();
                    }else{
                        button.show();
                        button.on('click', function(){
                            content.html('');
                            content.append(views['view1']());
                        });  
                    }

                    

                    return view;
                };


            // add custom view
            content.append(views[v]());

            // popup init event
            background.on('click', function(e){ hide(); });
            content.on('click', function(e){ e.stopPropagation(); });

            return {
                show: show,
                hide: hide
            }
        }

    });