<?php
class smart_chat extends \Elementor\Widget_Base {
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
                'label' => __( 'Content', 'plugin-name' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

                // Add a warning message at the beginning
        $this->add_control(
            'warning_message',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong style="color: red;">Warning:</strong> Do not use this widget until you\'ve set up "Smart Chat" on the "Build It For Me" plugin.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
            ]
        );

        $this->add_control(
            'welcome_message',
            [
                'label' => __( 'Welcome Message', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'You\'re chatting with a virtual assistant.',
            ]
        );

        $this->add_control(
            'instructions',
            [
                'label' => __( 'Instructions Message', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Type your message here...',
            ]
        );
        $this->end_controls_section();


    // Top Bar Style Section
    $this->start_controls_section(
        'top_bar_style',
        [
            'label' => __( 'Top Bar Style', 'plugin-name' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]
    );

    $this->add_control(
        'top_bar_color',
        [
            'label' => __( 'Top Bar Background Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #chat-top-bar' => 'background-color: {{VALUE}}',
            ],
        ]
    );

    $this->add_control(
        'minimize_button_color',
        [
            'label' => __( 'Minimize Button Color', 'plugin-name' ),
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
            'label' => __('Minimize Button Typography', 'plugin-name'),
            'selector' => '{{WRAPPER}} #chat-minimize',
        ]
    );

    $this->add_responsive_control(
        'top_bar_margins',
        [
            'label' => __( 'Top Bar Margins', 'plugin-name' ),
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
            'label' => __( 'Minimize Icon', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::ICON,
            'default' => 'fa fa-minus',
        ]
    );

    $this->add_control(
        'chat_button_image',
        [
            'label' => __( 'Chat Button Image', 'plugin-name' ),
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
                'label' => __( 'Style', 'plugin-name' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __( 'Typography', 'plugin-name' ),
                'selector' => '{{WRAPPER}} #welcome-message',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __( 'Text Color', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #welcome-message' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_margin',
            [
                'label' => __( 'Margin', 'plugin-name' ),
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
                'label' => __( 'Padding', 'plugin-name' ),
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
                'label' => __( 'Chat Bubble Style', 'plugin-name' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
    
        $this->add_control(
            'user_bubble_color',
            [
                'label' => __( 'User Bubble Color', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bubble.user' => 'background-color: {{VALUE}}',
                ],
            ]
        );
    
        $this->add_control(
            'bot_bubble_color',
            [
                'label' => __( 'Bot Bubble Color', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .bubble.bot' => 'background-color: {{VALUE}}',
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

        // Start outputting the HTML
        echo '<div id="chat-widget" style="color: ' . esc_attr($text_color) . ';">';

        // Top bar with a minimize button
        echo '<div id="chat-top-bar">';
        echo '<span id="chat-minimize" style="cursor: pointer;">&minus;</span>';  // Minimize button (−)
        echo '</div>';

        // Welcome message
        echo '<div id="welcome-message">'
            . esc_html($settings['welcome_message']) . '</div>';

        // Other widget HTML
        echo '<div id="chat-messages"></div>';
        echo '<div id="responding" style="display: none;">Responding<span class="dots">...</span></div>';
        echo '<textarea id="chat-input" placeholder="' .  esc_html($settings['instructions']) . '" rows="1"></textarea>';
        echo '<button id="chat-submit"><div id="submit-icon">→</div></button>';
        
        echo '</div>'; // Close chat-widget div
        echo '<div id="chat-bubble" style="display: none;"><img id="chat-bubble-img" src="' . esc_url(plugins_url('/bifm-plugin/shared-widgets/smart_chat/chatbubble.png')) . '" alt="Chat Bubble"></div>';  // Replace 'Chat Logo' with an actual logo if available
        // Chat logo (displayed when minimized)
        
        
        // Enqueue the CSS files
        wp_enqueue_style('smart_chat_css_0', plugins_url('styles.css', __FILE__), array(), '1.0.1');

        // Enqueue the JS files
        wp_enqueue_script('smart_chat_js_0', plugins_url('main.js', __FILE__, array('jquery'), '1.0.9', true));
        wp_localize_script('smart_chat_js_0', 'ajax_object', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
        // Localize the script with new data
        

    }
}