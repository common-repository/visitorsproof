<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Customnotifications_List extends WP_List_Table {
	
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Custom Notification', 'visitorsproof' ),
			'plural'   => __( 'Custom Notifications', 'visitorsproof' ),
			'ajax'     => false //does this table support ajax?
		] );
	}
	
	public static function get_custom_notifications( $per_page = 5, $page_number = 1 ) {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . " WHERE is_custom = 1 ";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}
	
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . " WHERE is_custom = 1";
		return $wpdb->get_var( $sql );
	}
	
	public static function delete_custom_notification( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}
	
	public static function enable_custom_notification( $id ) {
		global $wpdb;
		$wpdb->update("{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS, array('status' => 1, 'updated' => current_time('Y-m-d H:i:s')), array('id' => $id));
	}
	
	public static function disable_custom_notification( $id ) {
		global $wpdb;
		$wpdb->update("{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS, array('status' => 0, 'updated' => current_time('Y-m-d H:i:s')), array('id' => $id));
	}

	public function no_items() {
		_e( 'No custom notifications available', 'visitorsproof' );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'icon':
			case 'content':
			case 'status':
			case 'created':
			case 'updated':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'icon'    => __( 'Icon', 'visitorsproof' ),
			'content' => __( 'Content', 'visitorsproof' ),
			'status'  => __( 'Status', 'visitorsproof' ),
			'created' => __( 'Created', 'visitorsproof' ),
			'updated' => __( 'Updated', 'visitorsproof' ),
		];

		return $columns;
	}
	
	public function get_sortable_columns() {
		$sortable_columns = array(
			'status' => array( 'status', false ),
			'created' => array( 'created', false ),
			'updated' => array( 'updated', false )
		);

		return $sortable_columns;
	}
	
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-id[]" value="%s" />', $item['id']
		);
	}
	
	function column_icon( $item ) {
		global $wpdb;
		$icon = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS . " WHERE id = '$item[icon]'");
		$svg = str_replace(array('width="24"', 'height="24"'), array('width="40"', 'height="40"'), $icon->content);
		return $svg/* . '<span class="vp-icon-text">' . $icon->name . '</span>'*/;
	}
	
	function column_content( $item ) {

		$delete_nonce = wp_create_nonce( 'vp_cn_delete_custom_notification' );

		$title = strip_tags($item['content']);
		
		$edit_text = __( 'Edit', 'visitorsproof' );
		$delete_text = __( 'Delete', 'visitorsproof' );
		$confirmation_text = __( 'Are you sure?', 'visitorsproof' );
		$actions = [
		    'edit' => sprintf( '<a href="?page=%s&action=%s&notification_id=%s">' . $edit_text . '</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ) ),
		    'delete' => sprintf( '<a href="?page=%s&action=%s&notification_id=%s&_wpnonce=%s" onClick="return confirm(\'' . $confirmation_text . '\');">' . $delete_text . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}
	
	function column_status( $item ) {
	    if ($item['status']) {
	        $disable_text = __( 'Disable', 'visitorsproof' );
	        $title = '<strong class="vp-text-success"><i class="dashicons-before dashicons-yes-alt"></i> ' . __( 'Enabled', 'visitorsproof' ) . '</strong>';
			$actions = [
			    'disable' => sprintf( '<a href="?page=%s&action=%s&notification_id=%s">' . $disable_text . '</a>', esc_attr( $_REQUEST['page'] ), 'disable', absint( $item['id'] ) )
			];
	    }else{
	        $enable_text = __( 'Enable', 'visitorsproof' );
			$title = '<strong class="vp-text-danger"><i class="dashicons-before dashicons-warning"></i> ' . __( 'Disabled', 'visitorsproof' ) . '</strong>';
			$actions = [
			    'enable' => sprintf( '<a href="?page=%s&action=%s&notification_id=%s">' . $enable_text . '</a>', esc_attr( $_REQUEST['page'] ), 'enable', absint( $item['id'] ) )
			];
		}
		return $title . $this->row_actions( $actions );
	}
	
	function column_created( $item ) {
		return date('M d, Y h:i:s a', strtotime($item['created']));
	}
	
	function column_updated( $item ) {
		return date('M d, Y h:i:s a', strtotime($item['updated']));
	}
	
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => __( 'Delete', 'visitorsproof' ),
			'bulk-enable' => __( 'Enable', 'visitorsproof' ),
			'bulk-disable' => __( 'Disable', 'visitorsproof' )
		];

		return $actions;
	}
	
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'vp_cn_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_custom_notifications( $per_page, $current_page );
	}

	public function process_bulk_action() {
	    
	    $redirect_url = '?page=' . VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS . '&success=1';
		$current_action = $this->current_action();
		$post_action = isset( $_POST['action'] ) ? sanitize_text_field($_POST['action']) : ''; 
		$post_action2 = isset( $_POST['action2'] ) ? sanitize_text_field($_POST['action2']) : '';
		if ( 'delete' === $current_action ) {
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'vp_cn_delete_custom_notification' ) ) {
				die( 'Oops!' );
			}else {
			    self::delete_custom_notification(absint($_GET['notification_id']));
				visitors_proof_redirect($redirect_url);
			}
		}else if ( 'enable' === $current_action ) {
		    self::enable_custom_notification(absint($_GET['notification_id']));
			visitors_proof_redirect($redirect_url);
		}else if ( 'disable' === $current_action ) {
		    self::disable_custom_notification(absint($_GET['notification_id']));
			visitors_proof_redirect($redirect_url);
		}else if ( $post_action == 'bulk-delete' || $post_action2 == 'bulk-delete' ) {
			$selected_ids = esc_sql( $_POST['bulk-id'] );
			foreach ( $selected_ids as $id ) {
				self::delete_custom_notification( $id );
			}
			visitors_proof_redirect($redirect_url);
		}else if ( $post_action == 'bulk-enable' || $post_action2 == 'bulk-enable' ) {
		    $selected_ids = esc_sql( $_POST['bulk-id'] );
		    foreach ( $selected_ids as $id ) {
				self::enable_custom_notification($id);
			}
			visitors_proof_redirect($redirect_url);
		}else if ( $post_action == 'bulk-disable' || $post_action2 == 'bulk-disable' ) {
			$selected_ids = esc_sql( $_POST['bulk-id'] );
			foreach ( $selected_ids as $id ) {
				self::disable_custom_notification($id);
			}
			visitors_proof_redirect($redirect_url);
		}
	}
}