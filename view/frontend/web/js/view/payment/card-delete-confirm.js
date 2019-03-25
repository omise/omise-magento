
require([
        'jquery',
        'Magento_Ui/js/modal/confirm'
    ],
    function($, confirmation) {
         $('#edit').on('click', function(event){
             event.preventDefault;
                 confirmation({
             title: 'Some title',
             content: 'Some content',
             actions: {
                 confirm: function(){},
                 cancel: function(){
                   return false;
                 },
                 always: function(){}
             }
           });
       })
      }
);
