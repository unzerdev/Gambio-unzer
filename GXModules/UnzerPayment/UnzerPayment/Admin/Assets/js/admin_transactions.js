class UnzerTransactionManager{

    constructor($) {
        this.$ = $;
        this.blockReplace = false;
        this.$container = $('#admin-unzer-transactions');
        this.urls = {
            load: this.$container.data('load-url'),
            doAction: this.$container.data('action-url')
        }
        this.orderId = this.$container.data('order-id');
        this.paymentId = this.$container.data('payment-id');
        this.load();
        setInterval(this.load.bind(this), 5000);
        this.latestResponse = '';
        $('#unzer-debug-button').on('click', function(){
            $('#unzer-debug-content').toggle();
        });

    }

    setLoading(){
        this.$container.children().css({opacity:0.2});
    }

    load(){
        const me = this;
        me.$.get(me.urls.load, function (data) {
            if(this.blockReplace) return;
            if(data.html !== me.latestResponse) {
                me.$container.html(data.html);
                $('#unzer-debug-content').html(data.debug);
                me.registerEvents();
                me.latestResponse = data.html;
            }else{
                me.$container.children().css({opacity:1});
            }
            me.$('.head.grid label[title="unzer"]').html(me.$container.find('.unzer-payment-method-name').html() + ' (Unzer)')
        });
    }

    registerEvents(){
        const me = this;
        this.$container.find('[data-action]').each(function (index, actionContainer){
            const $actionContainer = me.$(actionContainer);
            $actionContainer.find('button').on('click', (e)=>{
                me.setLoading();
                this.blockReplace = true;
                e.preventDefault();
                me.$.post(
                    me.urls.doAction,
                    {
                        orderId: me.orderId,
                        paymentId: me.paymentId,
                        action:$actionContainer.data('action'),
                        amount:$actionContainer.find('input').val(),
                        transaction:$actionContainer.data('transaction')
                    },
                    function(data){
                        if(data.error){
                            alert(data.error);
                        }
                        me.blockReplace = false;
                        me.load();
                    }
                )
            });
        });
    }
}

jQuery(function () {
    new UnzerTransactionManager(jQuery);
});