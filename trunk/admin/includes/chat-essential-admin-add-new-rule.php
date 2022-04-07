<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Add_New_Rule {

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
     * @var      array     $flows    The list of Flows.
     */
    private $flows;

    /**
     * @since    0.0.1
     * @access   private
     * @var      array     $flows    The state of added rule
     */
    private $rule_added_state = 0;

    /**
     * @since    0.0.1
     * @param      array    $settings       The settings to load on the website management page.
     */
    public function __construct( $settings, $api ) {
        $this->settings = $settings;
        $this->api = $api;
        $this->fetchFlows();

        if (!empty($_POST)) {
            global $current_user, $wpdb;
            $data = $_POST['data'];
            $flow = $this->getFlowById($data['flow']);

            $initial_rule_data = [
                "platform_id" => $flow->platformId,
                "api_key" => $flow->apiKey,
                "flow_name" => $flow->id,
                "options" => "",
                "display_on" => $data['current_type'],
                "in_pages" => !empty($data['in_pages']) ? implode(',', $data['in_pages']) : null,
                "ex_pages" => !empty($data['ex_pages']) ? implode(',', $data['ex_pages']) : null,
                "in_posts" => !empty($data['in_posts']) ? implode(',', $data['in_posts']) : null,
                "ex_posts" => !empty($data['ex_posts']) ? implode(',', $data['ex_posts']) : null,
                "in_postTypes" => !empty($data['in_postTypes']) ? implode(',', $data['in_postTypes']) : null,
                "in_categories" => !empty($data['in_categories']) ? implode(',', $data['in_categories']) : null,
                "in_tags" => !empty($data['in_tags']) ? implode(',', $data['in_tags']) : null,
                "status" => $data['status'],
                "created_by" => $current_user->data->user_login,
            ];

            $this->rule_added_state = Chat_Essential_Utility::create_web_rules($initial_rule_data) ? 1 : 2;
        }
    }

    public function html() {
        $settings = $this->getSettings();
        $title = chat_essential_localize('Add New Load On Rule');
        $nonce = $settings['nonce'];
        $siteOptions = Site_Options::typeSelector([]);
        $flowOptions = $this->getFlowOptions();
        $rule_added_notice = $this->getRuleAddedNotice();

        echo <<<END
		<div class="wrap">
			<div class="add-rule-title-container">
				<h1 class="upgrade-title">$title</h1>
				$rule_added_notice
			</div>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="web_form" class="web-rules-form ce-add-new-rule-table">
							$nonce
							<table class="wp-list-table widefat fixed striped table-view-excerpt">
								<tbody>
                                    <tr>
                                      <th><label for="flow">Flow Name</label></th>
                                      <td>
                                        <select name="data[flow]" id="flow">
                                          $flowOptions
                                        </select>
                                      </td>
                                    </tr>
                                    $siteOptions
                                    <tr>
                                      <th><label for="device_display">Device Display</label></th>
                                      <td>
                                        <select name="data[device_display]" id="device_display">
				                            <option value="both">Show on All Devices</option>
				                            <option value="desktop">Only Desktop</option>
				                            <option value="mobile">Only Mobile Devices</option>
                                          </select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <th><label for="status">Status</label></th>
                                      <td>
                                        <select name="data[status]" id="status">
									        <option value="active">Active</option>
									        <option value="inactive">Inactive</option>
									      </select>
                                      </td>
                                    </tr>
								</tbody>
							</table>
							<button class="button button-primary ey-button top-margin">Save</button>
						</form>
					</div>
				</div>
		</div>
END;
    }

    private function getRuleAddedNotice() {
        switch ($this->rule_added_state) {
            case 1:
                return '<div class="notice notice-success is-dismissible">The rule has been added</div>';
            case 2:
                return '<div class="notice notice-error is-dismissible">Something went wrong</div>';
            default:
                return '';
        }
    }

    private function fetchFlows() {
        $settings = $this->getSettings();
        $res = $this->api->request($settings['apiKey'], 'GET', 'flow/' . $settings['apiKey'] . '?platform=web&type=flow&data=full', null, null);

        if ($res['code'] != 200) {
            wp_die('There was an issue loading your settings.', $res['code']);
        }
        if (empty($res['data'])) {
            wp_die('There was an issue loading your settings.', 500);
        }
        $data = json_decode($res['data']);
        $this->flows = $data->flows ?: [];
    }

    private function getFlowById($flowId) {
        foreach ($this->flows as $flow) {
            if ($flow->id == $flowId) {
                return $flow;
            }
        }
        return false;
    }

    private function getFlowOptions() {
        $webflows = '';
        if (!empty($this->flows)) {
            foreach ($this->flows as $flow) {
                $webflows .= "<option value='$flow->id'>$flow->name</option>";
            }
        }
        return $webflows;
    }

    /**
     * @since    0.0.1
     */
    private function getSettings() {
        return $this->settings;
    }

}