<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GI_Hero_Slider_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'gi_hero_slider'; }
    public function get_title() { return __( 'Green India Hero Slider', 'green-india-woo' ); }
    public function get_icon() { return 'eicon-slides'; }
    public function get_categories() { return [ 'green-india' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'slides_section', [
            'label' => __( 'Hero Slides (Editable)', 'green-india-woo' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );
        for ( $i = 1; $i <= 3; $i++ ) {
            $this->add_control( "slide{$i}_tag", [
                'label'   => __( "Slide {$i} Badge", 'green-india-woo' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => $i === 1 ? '🌱 From Nature, Back to Nature' : ( $i === 2 ? '♻️ Compostable Products' : '🌾 Renewable Sources' ),
            ] );
            $this->add_control( "slide{$i}_title", [
                'label'   => __( "Slide {$i} Headline", 'green-india-woo' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => $i === 1 ? 'Pollution Free Society' : ( $i === 2 ? 'Say No to Plastic' : 'Based on natural origin renewable sources' ),
            ] );
            $this->add_control( "slide{$i}_desc", [
                'label'   => __( "Slide {$i} Description", 'green-india-woo' ),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => $i === 1 ? 'by promoting the use of Compostable Products. Certified by CPCB, New Delhi and CIPET, Chennai.' : ( $i === 2 ? 'Biodegradable is Fantastic. Switch to bags that go back to the soil.' : 'Starch-based compostable products that decompose within six months in soil.' ),
            ] );
        }
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        ?>
        <div class="gi-slider" id="gi-hero-slider">
            <div class="gi-slide on">
                <svg class="gi-bg" preserveAspectRatio="xMidYMid slice" viewBox="0 0 1200 600">
                    <defs><linearGradient id="gi-g1" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#f6d776"/><stop offset=".55" stop-color="#cfe8a0"/><stop offset="1" stop-color="#5fa55a"/></linearGradient></defs>
                    <rect fill="url(#gi-g1)" height="600" width="1200"/>
                    <circle cx="930" cy="190" fill="#fff6c8" r="85"/>
                    <path d="M0 420Q250 300 520 410T1200 380V600H0z" fill="#3f8f4a"/>
                    <path d="M0 500Q300 410 650 490T1200 470V600H0z" fill="#2e7d32"/>
                    <g fill="#1b5e20"><circle cx="830" cy="400" r="46"/><rect height="80" width="10" x="825" y="430"/><circle cx="990" cy="430" r="36"/><rect height="60" width="9" x="985" y="455"/><circle cx="1100" cy="410" r="50"/><rect height="80" width="10" x="1095" y="445"/></g>
                </svg>
                <div class="gi-tx">
                    <span class="gi-tag"><?php echo esc_html( $s['slide1_tag'] ); ?></span>
                    <h1><?php echo esc_html( $s['slide1_title'] ); ?></h1>
                    <p><?php echo esc_html( $s['slide1_desc'] ); ?></p>
                    <div class="gi-hero-btns">
                        <a class="gi-hbtn" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Shop products</a>
                        <a class="gi-hbtn o" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">Our story</a>
                    </div>
                </div>
            </div>
            <div class="gi-slide">
                <svg class="gi-bg" preserveAspectRatio="xMidYMid slice" viewBox="0 0 1200 600">
                    <defs><linearGradient id="gi-g2" x1="0" x2="1" y1="0" y2="1"><stop offset="0" stop-color="#0b4f4a"/><stop offset="1" stop-color="#43a047"/></linearGradient></defs>
                    <rect fill="url(#gi-g2)" height="600" width="1200"/>
                    <g fill="#fff" opacity=".22"><circle cx="200" cy="120" r="60"/><circle cx="1050" cy="480" r="110"/><circle cx="700" cy="80" r="34"/></g>
                    <g transform="translate(820 130)"><path d="M70 90c0-60 120-60 120 0" fill="none" stroke="#e8f5e9" stroke-width="12"/><path d="M20 90h220l16 260H4z" fill="#e8f5e9"/><path d="M70 230c0-50 50-70 110-70 0 50-30 85-80 85" fill="none" stroke="#2e7d32" stroke-linecap="round" stroke-width="12"/></g>
                </svg>
                <div class="gi-tx">
                    <span class="gi-tag"><?php echo esc_html( $s['slide2_tag'] ); ?></span>
                    <h1><?php echo esc_html( $s['slide2_title'] ); ?></h1>
                    <p><?php echo esc_html( $s['slide2_desc'] ); ?></p>
                    <div class="gi-hero-btns">
                        <a class="gi-hbtn" href="<?php echo esc_url( home_url( '/our-products/' ) ); ?>">Explore bags</a>
                        <a class="gi-hbtn o" href="<?php echo esc_url( home_url( '/certification/' ) ); ?>">Certifications</a>
                    </div>
                </div>
            </div>
            <div class="gi-slide">
                <svg class="gi-bg" preserveAspectRatio="xMidYMid slice" viewBox="0 0 1200 600">
                    <defs><linearGradient id="gi-g3" x1="0" x2="1" y1="1" y2="0"><stop offset="0" stop-color="#123d22"/><stop offset="1" stop-color="#8bc34a"/></linearGradient></defs>
                    <rect fill="url(#gi-g3)" height="600" width="1200"/>
                    <path d="M760 520C720 330 840 190 1060 180 1070 380 940 520 780 520z" fill="#a5d66b" opacity=".85"/>
                    <path d="M790 510C860 380 950 280 1050 200" fill="none" stroke="#123d22" stroke-linecap="round" stroke-width="8"/>
                </svg>
                <div class="gi-tx">
                    <span class="gi-tag"><?php echo esc_html( $s['slide3_tag'] ); ?></span>
                    <h1><?php echo esc_html( $s['slide3_title'] ); ?></h1>
                    <p><?php echo esc_html( $s['slide3_desc'] ); ?></p>
                    <div class="gi-hero-btns">
                        <a class="gi-hbtn o" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get a quote</a>
                    </div>
                </div>
            </div>
            <button type="button" aria-label="Previous" class="gi-arr l" data-gi-sl="-1">‹</button>
            <button type="button" aria-label="Next" class="gi-arr r" data-gi-sl="1">›</button>
            <div class="gi-dots"><i class="on" data-gi-dot="0"></i><i data-gi-dot="1"></i><i data-gi-dot="2"></i></div>
        </div>
        <?php
    }
}
