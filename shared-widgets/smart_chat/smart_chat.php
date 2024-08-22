<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class BIFMSmartChatWidget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'smart_chat';
    }

    public function get_title() {
        return 'smart_chat';
    }

    public function get_icon() {
        return 'fa fa-code';
    }

    public function get_categories() {
        return ['general'];
    }


    

    protected function _register_controls() {

        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'bifm' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

                // Add a warning message at the beginning
        $this->add_control(
            'warning_message',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong style="color: red;">'.__('Warning','bifm').':</strong>'.__('Do not use this widget until you\'ve set up "Smart Chat" on the "Build It For Me" plugin.','bifm'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
            ]
        );

        $this->add_control(
            'setup_message',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p>'.__(' In the "Advanced" section select "width: default" to match container.','bifm').'<br/>'.__('Change position from "default" to "fixed" to see the chat bubble in a corner. Also set width: custom 300px.','bifm').'</p>',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'welcome_message',
            [
                'label' => __( 'Welcome Message', 'bifm' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('You\'re chatting with a virtual assistant.','bifm'),
            ]
        );

        $this->add_control(
            'instructions',
            [
                'label' => __( 'Instructions Message', 'bifm' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Type your message here...','bifm'),
            ]
        );
        $this->end_controls_section();


    // Top Bar Style Section
    $this->start_controls_section(
        'top_bar_style',
        [
            'label' => __( 'Top Bar Style', 'bifm' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]
    );

    $this->add_control(
        'top_bar_color',
        [
            'label' => __( 'Top Bar Background Color', 'bifm' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #chat-top-bar' => 'background-color: {{VALUE}}',
            ],
        ]
    );

    $this->add_control(
        'minimize_button_color',
        [
            'label' => __( 'Minimize Button Color', 'bifm' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #chat-minimize' => 'color: {{VALUE}}',
            ],
        ]
    );

    $this->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
            'name' => 'minimize_button_typography',
            'label' => __('Minimize Button Typography', 'bifm'),
            'selector' => '{{WRAPPER}} #chat-minimize',
        ]
    );

    $this->add_responsive_control(
        'top_bar_margins',
        [
            'label' => __( 'Top Bar Margins', 'bifm' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors' => [
                '{{WRAPPER}} #chat-top-bar' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );

    $this->add_control(
        'minimize_icon',
        [
            'label' => __( 'Minimize Icon', 'bifm' ),
            'type' => \Elementor\Controls_Manager::ICON,
            'default' => 'fa fa-minus',
        ]
    );

    $this->add_control(
        'chat_button_image',
        [
            'label' => __( 'Chat Button Image', 'bifm' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
                'url' => \Elementor\Utils::get_placeholder_image_src(),
            ],
        ]
    );

    $this->end_controls_section();

        // Style Section for Welcome Message
        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Body style', 'bifm' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __( 'Typography', 'bifm' ),
                'selector' => '{{WRAPPER}} #welcome-message',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __( 'Text Color', 'bifm' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #welcome-message' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_margin',
            [
                'label' => __( 'Margin', 'bifm' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} #welcome-message' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_padding',
            [
                'label' => __( 'Padding', 'bifm' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} #welcome-message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'chat_bubble_style',
            [
                'label' => __( 'Chat Bubble Style', 'bifm' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
    
        $this->add_control(
            'user_bubble_color',
            [
                'label' => __( 'User Bubble Color', 'bifm' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bubble.user' => 'background-color: {{VALUE}}',
                ],
            ]
        );
    
        $this->add_control(
            'bot_bubble_color',
            [
                'label' => __( 'Bot Bubble Color', 'bifm' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bubble.bot' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'chat_initial_display',
            [
                'label' => __( 'Chat Initial Display', 'bifm' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'minimized',
                'options' => [
                    'open' => __( 'Open', 'bifm' ),
                    'minimized' => __( 'Minimized', 'bifm' ),
                ],
            ]
        );
        

        $this->end_controls_section();
    
    }

    //test assistant end

    protected function render() {
        $settings = $this->get_settings_for_display();
    
        // Use the settings to customize the output
        $text_color = $settings['text_color'];
        $display_bubble = 'block';
        $display_chat = 'none';
        if ( 'open' === $settings['chat_initial_display'] ) {
            $display_bubble = 'none';
            $display_chat = 'block';
        }

        // Start outputting the HTML
        ?><div id="chat-widget" style="display:  <?php echo esc_attr($display_chat) ?>; color: <?php echo esc_attr($text_color) ?>;">

        <!-- Top bar with a minimize button -->
        <div id="chat-top-bar">
        <span id="chat-minimize" style="cursor: pointer;">&minus;</span>  <!-- Minimize button (−) -->
        </div>

        <!--  Welcome message-->
        <div id="welcome-message"><?php echo esc_html($settings['welcome_message']); ?></div>

        <!--  Other widget HTML-->
        <div id="chat-messages"></div>
        <div id="responding" style="display: none;"> <?php esc_html_e('Responding','bifm'); ?><span class="dots">...</span></div>
        <textarea id="chat-input" placeholder="<?php  echo esc_html($settings['instructions']) ?>" rows="1"></textarea>
        <button id="chat-submit"><div id="submit-icon">→</div></button>
        
        </div> <!--  Close chat-widget div-->
        <div id="chat-bubble" style="display: <?php echo esc_attr($display_bubble) ?>;"><img id="chat-bubble-img" src="<?php echo esc_url(plugins_url('/bifm/shared-widgets/smart_chat/chatbubble.png')) ?>" alt="<?php esc_html_e('Chat Bubble','bifm'); ?>"></div>  
        <!-- Replace 'Chat Logo' with an actual logo if available-->
<?php

        // Chat logo (displayed when minimized)
        
        
        // Enqueue the CSS files
        wp_enqueue_style('smart_chat_css_0', plugins_url('styles.css', __FILE__), array(), '1.0.1');

        // Enqueue the JS files
        wp_enqueue_script('smart_chat_js_0', plugins_url('main.js', __FILE__), array('jquery'), '1.0.10', true);

        $sch_nonce = wp_create_nonce('sch_widget_nonce');
        wp_localize_script('smart_chat_js_0', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => $sch_nonce));
        //wp_localize_script('smart_chat_js_0', 'ajax_object', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
        // Localize the script with new data

        

    }
}