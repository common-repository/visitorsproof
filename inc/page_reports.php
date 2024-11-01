<style>
.vp-demo{
    position: relative;
}
.vp-demo img{
    width: 100%;
    opacity: 0.4;
    position: absolute;
    top: 0px;
    left: 0px;
    right: 0px;
    margin: auto;
    z-index: -1;
}
#vp-pricing{
    width: 50%;
    background: #fff;
    margin: auto;
    box-shadow: 1px 1px 18px 2px #a3a3a3;
    -moz-box-shadow: 1px 1px 18px 2px #a3a3a3;
    -webkit-box-shadow: 1px 1px 18px 2px #a3a3a3;
    border: 5px solid #f95728;
}
.vp-pricing-amount{
    font-size: 30px;
    color: #f95728;
    margin-top: 10px;
    margin-bottom: 10px;
}
#vp-pricing .vp-table tr td:nth-child(2), #vp-pricing .vp-table tr th:nth-child(2){
    background: #dfdfdf;
    border-color: #dfdfdf;
}
@media (max-width: 768px){
    #vp-pricing{
        width: 96%;
    }
}
</style>
<div class="vp-grid vp-w-100">
	<div class="vp-ml-2 vp-mr-2">
    	<div class="vp-demo">
        	<img src="<?php echo $vpchr->visitors_proof_assets('demo-reports.png'); ?>" />
        	<br/><br/><br/><br/><br/><br/>
        	<div id="vp-pricing">
        		<table class="vp-table" align="center">
        			<tr>
        				<th class="vp-text-center"></th>
        				<th style="vertical-align: top;"><?php _e( 'Free Forever', 'visitorsproof' ) ?><br/><div class="vp-pricing-amount">$0/-</div></th>
        				<th style="vertical-align: top;"><?php _e( 'Premium', 'visitorsproof' ) ?><br/><div class="vp-pricing-amount">$49/yr</div></th>
        			</tr>
        			<tr>
        				<td><?php _e( 'Notifications', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Visitors', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Custom Notifications', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'Unlimited', 'visitorsproof' ) ?></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Themes', 'visitorsproof' ) ?></td>
        				<td>2</td>
        				<td><b>12</b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Notification Positions', 'visitorsproof' ) ?></td>
        				<td>4</td>
        				<td><b>9</b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Entrance Animations', 'visitorsproof' ) ?></td>
        				<td>2</td>
        				<td><b>50+</b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Exit Animations', 'visitorsproof' ) ?></td>
        				<td>2</td>
        				<td><b>40+</b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Random Themes', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'No', 'visitorsproof' ) ?></td>
        				<td><b><?php _e( 'Yes', 'visitorsproof' ) ?></b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Multilingual', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'No', 'visitorsproof' ) ?></td>
        				<td><b><?php _e( 'Yes', 'visitorsproof' ) ?></b></td>
        			</tr>
        			<tr>
        				<td><?php _e( 'Reporting System', 'visitorsproof' ) ?></td>
        				<td><?php _e( 'No', 'visitorsproof' ) ?></td>
        				<td><b><?php _e( 'Yes', 'visitorsproof' ) ?></b></td>
        			</tr>
        			<tr>
        				<td></td>
        				<td><b style="color: green;"><i class="dashicons-before dashicons-yes-alt"></i> <?php _e( 'Current Plan', 'visitorsproof' ) ?></b></td>
        				<td><a href="https://visitorsproof.com/wordpress-plans?url=<?php echo home_url(); ?>" target="_blank" class="button button-primary"><?php _e( 'Upgrade', 'visitorsproof' ) ?></a></td>
        			</tr>
        		</table>
        	</div>
        </div>
        
    </div>
</div>