<?php

class Unzer_ConfigurationBoxContentView extends Unzer_ConfigurationBoxContentView_parent
{
    function prepare_data()
    {
        parent::prepare_data();
        UnzerConfigHelper::initTexts();
        if (isset($this->content_array['formAction']) && strpos($this->content_array['formAction'], 'module=unzer') !== false) {
            if (!defined('MODULE_PAYMENT_UNZER_STATUS')) {
                return;
            }
            $this->set_content_data(
                'additionalUnzerContent',
                '<div>v' . UnzerConstants::MODULE_VERSION . '</div>' .
                (UnzerConfigHelper::getPrivateKey()?'
                    <div>
                        <a href="' . xtc_href_link('admin.php', 'do=UnzerConfiguration') . '" class="button" style="margin:10px 0; display:block;">'.UnzerConfigHelper::getStringConstant('MODULE_PAYMENT_UNZER_CONFIGURE_METHODS').'</a>
                    </div>
                    <div>
                        <a href="' . xtc_href_link('admin.php', 'do=UnzerConfiguration/webhooks') . '" class="button" style="margin:10px 0; display:block;">'.UnzerConfigHelper::getStringConstant('MODULE_PAYMENT_UNZER_CONFIGURE_WEBHOOKS').'</a>
                    </div>
                    
                    ':'')
            );
        }
    }
}