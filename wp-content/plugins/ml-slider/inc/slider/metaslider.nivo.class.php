<?php

if (!defined('ABSPATH')) {
die('No direct access.');
}

/**
 * Nivo Slider specific markup, javascript, css and settings.
 */
class MetaNivoSlider extends MetaSlider
{
    protected $js_function = 'nivoSlider';
    protected $js_path = 'sliders/nivoslider/jquery.nivo.slider.pack.js';
    protected $css_path = 'sliders/nivoslider/nivo-slider.css';

    /**
     * Constructor
     *
     * @param int   $id                 ID
     * @param array $shortcode_settings Short Settings
     */
    public function __construct($id, $shortcode_settings)
    {
        parent::__construct($id, $shortcode_settings);

        add_filter('metaslider_nivo_slider_parameters', array( $this, 'set_autoplay_parameter' ), 10, 3);
        if(metaslider_pro_is_active() == false) {
            add_filter('metaslider_nivo_slider_parameters', array($this, 'metaslider_nivo_loop'), 99, 3);
        }
    }

    /**
     * Other slides use "AutoPlay = true" (true autoplays the slideshow)
     * Nivo slider uses "ManualAvance = false" (ie, false autoplays the slideshow)
     * Take care of the manualAdvance parameter here.
     *
     * @param array $options   Options for autoplay
     * @param array $slider_id Slider ID
     * @param array $settings  Settings
     */
    public function set_autoplay_parameter($options, $slider_id, $settings)
    {
        global $wp_filter;
        if (isset($options["autoPlay"])) {
            if ($options["autoPlay"] == 'true') {
                $options["manualAdvance"] = 'false';
            } else {
                $options["manualAdvance"] = 'true';
            }

            unset($options['autoPlay']);
        }
        // we don't want this filter hanging around if there's more than one slideshow on the page
        remove_filter('metaslider_nivo_slider_parameters', array( $this, 'set_autoplay_parameter' ), 10, 3);

        return $options;
    }

    /**
     * Add JavaScript to stop slideshow
     *
     * @param array $options SLide options
     * @param integer $slider_id Slider ID
     * @param array $settings Slide settings
     * @return array
     */
    public function metaslider_nivo_loop($options, $slider_id, $settings)
    {
        if (isset($settings['loop']) && 'stopOnFirst' === $settings['loop']) {
            $options['slideshowEnd'] = isset($options['slideshowEnd']) ? $options['slideshowEnd'] : array();
            $options['slideshowEnd'] = array_merge($options['slideshowEnd'], array(
                "$('#metaslider_" . esc_js($slider_id) . "').data('nivoslider').stop();"
            ));
        }

        if (isset($settings['loop']) && 'stopOnLast' === $settings['loop']) {
            $options['lastSlide'] = isset($options['lastSlide']) ? $options['lastSlide'] : array();
            $options['lastSlide'] = array_merge(
                $options['lastSlide'],
                array("$('#metaslider_" . esc_js($slider_id) . "').data('nivoslider').stop();")
            );
        }

        return $options;
    }

    /**
     * Detect whether thie slide supports the requested setting,
     * and if so, the name to use for the setting in the Javascript parameters
     *
     * @param  array $param Parameters
     * @return false (parameter not supported) or parameter name (parameter supported)
     */
    protected function get_param($param)
    {
        $params = array(
            'effect' => 'effect',
            'slices' => 'slices',
            'prevText' => 'prevText',
            'nextText' => 'nextText',
            'delay' => 'pauseTime',
            'animationSpeed' => 'animSpeed',
            'hoverPause' => 'pauseOnHover',
            'spw' => 'boxCols',
            'sph' => 'boxRows',
            'navigation' => 'controlNav',
            'links' => 'directionNav',
            'autoPlay' => 'autoPlay'
        );

        if (isset($params[$param])) {
            return $params[$param];
        }

        return false;
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts()
    {
        parent::enqueue_scripts();

        // If a theme is set then we need to load the default Nivo theme
        $theme = get_post_meta($this->id, 'metaslider_slideshow_theme', true);
        if ('true' === $this->get_setting('printCss') || $theme) {
            wp_enqueue_style('metaslider-' . $this->get_setting('type') . '-slider-default', METASLIDER_ASSETS_URL . "sliders/nivoslider/themes/default/default.css", false, METASLIDER_ASSETS_VERSION);
        }
    }

    /**
     * Build the HTML for a slider.
     *
     * @return string slider markup.
     */
    protected function get_html()
    {

        $return_value  = "<div class='slider-wrapper theme-default'><div class='ribbon'></div><div id='" . $this->get_identifier() . "' class='nivoSlider'>";
        foreach ($this->slides as $slide) {
            $return_value .= $slide;
        }
        $return_value .= "</div></div>";
        return apply_filters('metaslider_nivo_slider_get_html', $return_value, $this->id, $this->settings);
    }
}
