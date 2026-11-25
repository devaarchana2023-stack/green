<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GI_Contact_Form_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'gi_contact_form'; }
    public function get_title() { return __( 'Green India WhatsApp Enquiry Form', 'green-india-woo' ); }
    public function get_icon() { return 'eicon-form-horizontal'; }
    public function get_categories() { return [ 'green-india' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'form_section', [
            'label' => __( 'Form Settings', 'green-india-woo' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );
        $this->add_control( 'form_title', [
            'label'   => __( 'Form Title', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'Send an enquiry',
        ] );
        $this->add_control( 'button_text', [
            'label'   => __( 'Submit Button Text', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'Send on WhatsApp',
        ] );
        $this->add_control( 'whatsapp_number', [
            'label'   => __( 'WhatsApp Number (with country code)', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => '919489465676',
        ] );
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <div class="gi-contact-form" data-wa="<?php echo esc_attr( $s['whatsapp_number'] ); ?>">
            <h3><?php echo esc_html( $s['form_title'] ); ?></h3>
            <input type="text" class="gi-cf-name" placeholder="Name / company *" required />
            <input type="tel" class="gi-cf-phone" maxlength="10" placeholder="10-digit mobile number *" required />
            <textarea class="gi-cf-msg" rows="4" placeholder="What do you need? *" required></textarea>
            <div class="gi-cf-err"></div>
            <button type="button" class="gi-btn gi-btn-primary gi-cf-submit"><?php echo esc_html( $s['button_text'] ); ?></button>
        </div>
        <?php
    }
}
