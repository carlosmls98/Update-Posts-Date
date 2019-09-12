<?php

namespace OnpointGlobal;

/**
 * Main Update Post Date.
 *
 * @package WordPress Action Template/Includes
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use DateInterval;
use DateTime;

class UpdatePostDate
{
    /**
     * Date Range class object
     *
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $daterange = null;

    /**
     * Date Range of Posts class object
     *
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $datepost = null;

    /**
     * Date Range of Posts class object
     *
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $cron = null;

    public function __construct($daterange = 'P14D',$datepost='2 weeks ago') {

        $this->datepost=$datepost;
        
        $this->daterange=$daterange;

        add_action('update_date_post', array( $this, 'update_date_post' ));
        add_filter('cron_schedules', array( $this, 'custom_cron_job_recurrence' ));
        add_action('wp', array( $this, 'custom_cron_job' ));

    }
    // Scheduled Action Hook

    function update_date_post()
    {
        $args = array(
            'post_type' => 'post',
            'date_query' => array(
                array(
                    'before' => $this->datepost,
                    'inclusive' => true,
                ),
            ),
            'posts_per_page' => -1,
        );
        $query = query_posts($args);
        $error_log=[];
        if ($query) {
            foreach ($query as $post) :
                $my_post = $post->ID;
                /**
                 * Generate the date
                 */
                $end = new DateTime();
                $start = new DateTime();
                $start = $start->sub(new DateInterval($this->daterange));;
                $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
                $randomDate = new DateTime();
                $randomDate->setTimestamp($randomTimestamp);
                $time = $randomDate->format('Y-m-d H:i:s');
                $error_log[] = wp_update_post(
                    array(
                        'ID' => $my_post, // ID of the post to update
                        'post_date' => $time,
                        'post_date_gmt' => get_gmt_from_date($time)
                    )
                );
            endforeach;
            var_dump($error_log);
        }

        wp_reset_postdata();
    }

    // Custom Cron Recurrences
    function custom_cron_job_recurrence($schedules)
    {
        $schedules['weekly'] = array(
            'display' => __('Once Weekly', 'textdomain'),
            'interval' => 604800,
        );
        return $schedules;
    }

    // Schedule Cron Job Event
    function custom_cron_job()
    {
        if (!wp_next_scheduled('update_date_post')) {
            wp_schedule_event(time(), 'weekly', 'update_date_post');
        }
    }
}
