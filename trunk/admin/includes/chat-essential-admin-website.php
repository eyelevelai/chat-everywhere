<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Website {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      Chat_Essential_API_Client     $settings    Manages API calls to EyeLevel APIs.
	 */
	private $api;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      array     $settings    The Chat Essential information for this WP site.
	 */
	private $settings;

    /**
     * @since    0.0.1
     * @access   private
     * @var      array     $flows    The state of reordered rules.
     */
    private $reordered_rules = false;

	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;

        if (!empty($_POST)) {
            Chat_Essential_Utility::reorder_rules($_POST['order']);
            $this->reordered_rules = true;
        }
	}

    private function row($settings, $rule, $web) {
        $rid = sanitize_text_field($rule->rules_id);
        $wid = sanitize_text_field($web->id);
        $flow_name = sanitize_text_field($web->name);

        $checked = '';
        if ($rule->status === 'active') {
            $checked = 'checked';
        }
        $isOn = '<input type="checkbox" ' . $checked . ' class="ey-switch-input" id="status' . $rid . '" /><label class="ey-switch" for="status' . $rid . '">Toggle</label>';

		$edit_url = CHAT_ESSENTIAL_DASHBOARD_URL . '/view/' . sanitize_text_field($web->versionId);
		$edit = chat_essential_localize('Edit');
		$lot_val = chat_essential_localize('Site Wide');
		$analytics = chat_essential_localize('View');
		$analytics_url = CHAT_ESSENTIAL_DASHBOARD_URL . '/analytics/' . $wid;
		$theme_name = '';
		if (!empty($web->theme) && !empty($web->theme->name)) {
			$theme_name = sanitize_text_field($web->theme->name);
		}
		$offhours_name = '';
		if (!empty($web->offhoursSetting) && !empty($web->offhoursSetting->name)) {
			$offhours_name = sanitize_text_field($web->offhoursSetting->name);
		}

		$preview = '';
		$preview_url = '';
		
		$res = $this->api->request($settings['apiKey'], 'GET', 'publish/' . $settings['apiKey'] . '/' . $wid, null, null);
		if ($res['code'] != 200) {
			wp_die('There was an issue loading your settings.', $res['code']);
		}

		if (empty($res['data'])) {
			wp_die('There was an issue loading your settings.', 500);
		}
		$data = json_decode($res['data'], true);

        if (!empty($data)) {
            if (!empty($data['publish'])) {
                if (!empty($data['publish']['url'])) {
                    $disp = '';
                    if ($rule->status === 'inactive') {
                        $disp = 'style="display:none;"';
                    }
                    $preview_url = esc_url($data['publish']['url']) . '&eystate=open&eyreset=true&clearcache=true';
                    $preview = '<span ' . $disp . ' id="status' .$rid . '-preview" class="preview-web"><a href="' . $preview_url . '" target="_blank">' . chat_essential_localize('Preview') . '</a></span>';
                }
            }
        }

        $sortable_column = Chat_Essential_Utility::is_premium()
            ? '<td class="column-sortable">
                    <div class="sortable-block">
                      <span class="ui-icon ui-icon-caret-2-n-s"></span>
                      <input type="hidden" name="order[]" value="'. $rule->rules_id .'">
                    </div>
                </td>'
            : '';

		return <<<END
	<tr>
	    $sortable_column
		<td class="status column-status">$isOn</td>
		<td class="flow-name column-flow-name">
			<strong>$flow_name</strong>
			<div class="row-actions visible">
				$preview
				<span class="edit">
					<a href="$edit_url" target="_blank">$edit</a>
				</span>
			</div>
		</td>
		<td class="load-on column-load-on">
			$lot_val
		</td>
		<td class="theme column-analytics">
			<a href="$analytics_url" target="_blank">$analytics</a>
		</td>
		<td class="theme column-theme">
			$theme_name
		</td>
		<td class="offhours column-offhours">
			$offhours_name
		</td>
	</tr>
END;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = chat_essential_localize('Website Chat');
		$nonce = $settings['nonce'];
	
		$res = $this->api->request($settings['apiKey'], 'GET', 'flow/' . $settings['apiKey'] . '?platform=web&type=flow&data=full', null, null);
		if ($res['code'] != 200) {
			wp_die('There was an issue loading your settings.', $res['code']);
		}

		if (empty($res['data'])) {
			wp_die('There was an issue loading your settings.', 500);
		}
		$data = json_decode($res['data']);

        $rules = Chat_Essential_Utility::get_all_rules();

        $webflows = '';
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $flow = $this->getFlowById($data->flows, $rule->flow_name);
                if (!empty($flow)) {
                    $webflows .= $this->row($settings, $rule, $flow);
                }
            }
        }

		$h1 = chat_essential_localize('Status');
		$h2 = chat_essential_localize('Chat Flow');
		$h3 = chat_essential_localize('Load On');
		$h4 = chat_essential_localize('Analytics');
		$h5 = chat_essential_localize('Theme');
		$h6 = chat_essential_localize('Business Hours Settings');

        $plugin_pro_link = !Chat_Essential_Utility::is_premium()
            ? '<a href="https://www.chatessential.com/wp-premium" target="_blank" class="chat-essential-upgrade-link">Upgrade to premium</a>'
            : '';
        $sortable_column = Chat_Essential_Utility::is_premium()
            ? '<th scope="col" id="sortable" class="manage-column column-sortable"></th>'
            : '';
        $sortable_script = Chat_Essential_Utility::is_premium()
            ? '<script>$(function() {
                  $( ".ui-sortable" ).sortable({
                    update: function() {
                      $("#save-order-button").removeAttr("disabled");
                    }
                  });
                });</script>'
            : '';
        $add_new_links = Chat_Essential_Utility::is_premium()
            ? '<div class="ce-add-new-links">
                <button class="button button-primary ey-button top-margin" id="save-order-button" disabled>Save Order</button>
                <a class="button button-primary ey-button top-margin" href="?page=chat-essential-add-new-rule">Add Rule</a>
                <a class="button button-primary ey-button top-margin" href="' . CHAT_ESSENTIAL_DASHBOARD_URL . '" target="_blank">Create New Flow</a>
               </div>'
            : '';
        $reordered_notice = $this->reordered_rules
            ? '<div class="notice notice-success is-dismissible">The rules have been reordered</div>'
            : '';

    	echo <<<END
		<div class="wrap">
			<div class="upgrade-title-container reorder-container">
				<h1 class="upgrade-title">$title</h1>
				$plugin_pro_link
				$reordered_notice
			</div>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="web_form" class="web-rules-form">
							$nonce
							<table class="wp-list-table widefat fixed striped table-view-excerpt">
								<thead class="manage-head">
									<tr>
									    $sortable_column
										<th scope="col" id="status" class="manage-column column-status">
											$h1
										</th>
										<th scope="col" id="flow-name" class="manage-column column-flow-name">
											$h2
										</th>
										<th scope="col" id="load-on" class="manage-column column-load-on">
											$h3
										</th>
										<th scope="col" id="analytics" class="manage-column column-analytics">
											$h4
										</th>
										<th scope="col" id="theme" class="manage-column column-theme">
											$h5
										</th>
										<th scope="col" id="offhours" class="manage-column column-offhours">
											$h6
										</th>
									</tr>
								</thead>
								<tbody class="ui-sortable">
									$webflows
								</tbody>
							</table>
                            $sortable_script
                            $add_new_links
						</form>
					</div>
				</div>
		</div>
END;
  	}


    private function getFlowById($flows, $flowId) {
        foreach ($flows as $flow) {
            if ($flow->id == $flowId) {
                return $flow;
            }
        }
        return false;
    }


	/**
	 * @since    0.0.1
	 */
	private function getSettings() {
    	return $this->settings;
  	}

}